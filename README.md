task/filesystem
===============

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
