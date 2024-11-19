pop-dir
=======

[![Build Status](https://github.com/popphp/pop-dir/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-dir/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-dir)](http://cc.popphp.org/pop-dir/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Options](#options)
* [Empty](#empty)

Overview
--------
`pop-dir` is a component for easily traversing files and subdirectories within a directory.

It is a component of the [Pop PHP Framework](https://www.popphp.org/).

[Top](#pop-dir)

Install
-------

Install `pop-dir` using Composer.

    composer require popphp/pop-dir

Or, require it in your composer.json file

    "require": {
        "popphp/pop-dir" : "^4.0.0"
    }

[Top](#pop-dir)

Quickstart
----------

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

[Top](#pop-dir)

Options
-------

The available boolean options for the `$options` array parameter are:

```php

$options = [
    'absolute'  => true,  // store the absolute, full path of the items in the directory
    'relative'  => false  // store the relative path of the items in the directory
    'recursive' => true,  // traverse the directory recursively
    'filesOnly' => false, // store only files in the object (and not other directories)
];
```

The `absolute` and `relative` options cannot be used together.

If `absolute` is set to `true`, it will return the absolute path of the files and directories:

```text
'/home/path/file1.txt`
'/home/path/file2.txt`
```

If `relative` is set to `true`, it will return the relative path of the files and directories:

```text
'path/file1.txt`
'path/file2.txt`
```

If neither are passed, it will return only the base file names and directory names:

```text
'file1.txt`
'file2.txt`
```

[Top](#pop-dir)

Empty
-----

The directory can be emptied with the `emptyDir()` method:

```php
use Pop\Dir\Dir;

$dir = new Dir('my-dir');
$dir->emptyDir();
```

The `true` flag will remove the actual directory as well (use with caution):

```php
$dir->emptyDir(true);
```

[Top](#pop-dir)
