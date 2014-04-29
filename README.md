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

`Task\Plugin\FilesystemPlugin` extends Symfony's `Filesystem` component object, overring some methods and providing some new ones. Many of these methods return streams which can be piped to other plugins.
`open`
------
`open($filename, $mode = 'r+')`

Returns `Task\Plugin\Filesystem\File`, opened with the specified mode.
`touch`
-------
`FilesystemPlugin::touch($filename, $time = null, $atime = null)`

See Symfony's `Filesystem::touch` documentation for argument description. Returns `Task\Plugin\Filesystem\File`, opened with `r+`.
`ls`
----
`ls($dir)`

Returns `Task\Plugin\Filesystem\FilesystemIterator`.
`copy`
------
`copy($source, $target, $override = false)`

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
# @return File('bar')
$fs->copy('foo', 'bar')
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
# @return File('bar/foo')
$fs->copy('foo', 'bar')
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
# @return File('wow')
$fs->copy('foo', 'wow')
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
# @return FilesystemIterator('wow')
$fs->copy('foo', 'wow')
```
```
/
    foo/
        bar
    wow/
        bar
```
`mirror`
--------
`mirror($originDir, $targetDir, Traversable $iterator = null, $options = [])`
Mirror a directory, optionally providing a `Traversable` instance to select or exclude files. Symfony's `Finder` component is really good for this: 
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
# @return FilesystemIterator('wow')
$fs->mirror('foo', 'wow', $finder);
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
