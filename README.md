task/filesystem
===============

[![Build Status](https://travis-ci.org/taskphp/filesystem.svg?branch=master)](https://travis-ci.org/taskphp/filesystem)
[![Coverage Status](https://coveralls.io/repos/taskphp/filesystem/badge.png?branch=master)](https://coveralls.io/r/taskphp/filesystem?branch=master)

Example
=======

```php
use Task\Plugin\FilesystemPlugin;
use Symfony\Component\Finder\Finder;

$project->inject(function ($container) {
    $container['fs'] = new FilesystemPlugin;
});

$project->addTask('write', ['fs', function ($fs) {
    $fs->open('/tmp/foo')->write('wow');
}]);

$project->addTask('read', ['fs', function ($fs) {
    $fs->read('/tmp/foo')->pipe($this->getOutput());
}]);

$project->addTask('copy', ['fs', function ($fs) {
    $fs->copy('/tmp/foo', '/tmp/bar');
    # OR
    $fs->read('/tmp/foo')->pipe($fs->touch('/tmp/bar'));
}]);

$project->addTask('copyTree', ['fs', function ($fs) {
    $finder = new Finder;
    $finder->name('foo')->in('/tmp/source');
    $fs->copyTree('/tmp'source', '/tmp/target', $finder);
}]);
```

Installation
============

Add to `composer.json`:
```json
...
"require-dev": {
    "task/filesystem": "~0.2"
}
...
```

Usage
=====

`Task\Plugin\FilesystemPlugin` extends Symfony's `Filesystem` component object, overring some methods and providing some new ones.

`open`
------

`FilesystemPlugin::open($filename, $mode = 'r+')`

Returns `Task\Plugin\Filesystem\File`, opened with the specified mode.

`touch`
-------

`FilesystemPlugin::touch($filename, $time = null, $atime = null)`

See Symfony's `Filesystem::touch` documentation for argument description. Returns `Task\Plugin\Filesystem\File`, opened with `r+`.


`ls`
----

`FilesystemPlugin::ls($dir)`

Returns `Task\Plugin\Filesystem\FilesystemIterator`.

`copy`
------

`FilesystemPlugin::copy($source, $target, $override = false)`

Supports multiple operations, e.g.

Given:
```php
use Task\Plugin\FilesystemPlugin;
$fs = new FilesystemPlugin;
```

File to file:

```
/
    foo
```
```php
copy('foo', 'bar')
```
```
/
    foo
    bar
```

File to directory:

```
/
    foo
    bar/
```
```php
copy('foo', 'bar')
```
```
/
    foo
    bar/
        foo
```

Link to link:

```
/
    foo
    bar -> foo
```
```php
copy('foo', 'wow')
```
```
/
    foo
    bar -> foo
    wow -> foo
```

Directory to directory:
```
/
    foo/
        bar
```
```php
copy('foo', 'wow')
```
```
/
    foo/
        bar
    wow/
        bar
```

`copyTree`
----------

`FilesystemPlugin::copyTree($basedir, $target, Finder $finder)`

Uses Symfony's `Finder` component to selectively copy one tree to another e.g.
```
/
    foo/
        .git/
            objects/
        bar
        baz
```
```php
use Task\Plugin\FilesystemPlugin;
use Symfony\Component\Finder\Finder;

$finder = new Finder;
$finder->ignoreVcs()->in('foo');

$fs = new FilesystemPlugin;
$fs->copyTree('foo', 'wow', $finder);
```
```
/
    foo/
        .git/
            objects/
        bar
        baz
    wow/
        bar
        baz
```

`$baseDir` needs to passed so that paths can be mapped from one base directory to another.
