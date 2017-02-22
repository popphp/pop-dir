pop-dir
=======

[![Build Status](https://travis-ci.org/popphp/pop-dir.svg?branch=master)](https://travis-ci.org/popphp/pop-dir)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-dir)](http://cc.popphp.org/pop-dir/)

OVERVIEW
--------
`pop-dir` is a component for traversing files within a directory. It is a 
component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-dir` using Composer.

    composer require popphp/pop-dir

BASIC USAGE
-----------

### Directory traversal

##### Traversing a directory

```php
use Pop\Dir\Dir;

$dir = new Dir('my-dir');

foreach ($dir->getFiles() as $file) {
    echo $file;
}
```

If you want to traverse the directory recursively and get the full path of each file.

```php
use Pop\Dir\Dir;

$dir = new Dir('my-dir', [
    'absolute'  => true,
    'recursive' => true
]);

foreach ($dir->getFiles() as $file) {
    echo $file;
}
```

The available boolean options for the `$options` array parameter are:

* 'absolute'  => store the absolute, full path of the items in the directory
* 'relative'  => store the relative path of the items in the directory
* 'recursive' => traverse the directory recursively
* 'filesOnly' => store only files in the object (and not other directories)

##### Emptying a directory

```php
use Pop\Dir\Dir;

$dir = new Dir('my-dir');
$dir->emptyDir(true);
```

The `true` flag will remove the actual directory as well.

