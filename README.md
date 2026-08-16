pop-dir
=======

[![Build Status](https://github.com/popphp/pop-dir/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-dir/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-dir)](http://cc.popphp.org/pop-dir/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Options](#options)
* [Reading Contents](#reading-contents)
* [The Directory Tree](#the-directory-tree)
* [Copying a Directory](#copying-a-directory)
* [Deleting Files](#deleting-files)
* [Empty](#empty)
* [Exceptions](#exceptions)

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
        "popphp/pop-dir" : "^5.0.0"
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
    'relative'  => false, // store the relative path of the items in the directory
    'recursive' => true,  // traverse the directory recursively
    'filesOnly' => false, // store only files in the object (and not other directories)
];
```

The `absolute` and `relative` options cannot be used together. If both are passed as `true`, `relative` wins,
since it's applied after `absolute` internally.

If `absolute` is set to `true`, it will return the absolute path of the files and directories:

```text
/home/path/file1.txt
/home/path/file2.txt
```

If `relative` is set to `true`, it will return the relative path of the files and directories:

```text
path/file1.txt
path/file2.txt
```

If neither are passed, it will return only the base file names and directory names:

```text
file1.txt
file2.txt
```

Each option also has a matching fluent setter/getter pair — `setAbsolute()`/`isAbsolute()`,
`setRelative()`/`isRelative()`, `setRecursive()`/`isRecursive()`, `setFilesOnly()`/`isFilesOnly()` — so options
can be changed after construction instead of (or in addition to) passing them to the constructor:

```php
use Pop\Dir\Dir;

$dir = new Dir('my-dir');
$dir->setRecursive(true)
    ->setAbsolute(true);
```

Calling a setter re-scans the directory immediately, so `getFiles()`/`getTree()` reflect the new option right
away — there's no separate "apply" or "refresh" step needed.

[Top](#pop-dir)

Reading Contents
-----------------

Beyond `getFiles()`, a `Dir` object implements `Countable`, `IteratorAggregate` and `ArrayAccess`, so its
contents can be read the way you'd read a plain array:

```php
use Pop\Dir\Dir;

$dir = new Dir('my-dir');

count($dir); // number of items found

// same as foreach ($dir->getFiles() as $file)
foreach ($dir as $file) {
    echo $file;
}
```

Individual entries can be looked up either by their numeric index or by name, and both `isset()` and `[]` agree
with each other:

```php
if (isset($dir['file1.txt'])) {
    echo $dir['file1.txt'];   // 'file1.txt'
    echo $dir[0];             // whatever the entry at index 0 is
}

$dir->fileExists('file1.txt'); // bool, same lookup as isset($dir['file1.txt'])
```

Property-style access works the same way via magic methods (`__get`/`__isset`), which is handy for numeric
offsets or names that aren't valid bare property names (e.g. anything with a `.` in it):

```php
$dir->{0};             // same as $dir[0]
$dir->{'file1.txt'};   // same as $dir['file1.txt']
isset($dir->{'file1.txt'});
```

A `Dir` object is otherwise read-only through array/property syntax — `$dir['x'] = 'y'` and `$dir->x = 'y'` both
throw `Pop\Dir\Exception`, since there's no way to add a tracked entry without it corresponding to a real file on
disk. See [Deleting Files](#deleting-files) for the one write operation that *is* supported (deletion).

[Top](#pop-dir)

The Directory Tree
-------------------

In addition to the flat `getFiles()` list, `getTree()` returns a nested associative array representing the whole
directory structure, keyed by the resolved root path:

```php
use Pop\Dir\Dir;

$dir = new Dir('my-dir', ['recursive' => true]);
print_r($dir->getTree());
```

```text
Array
(
    [/path/to/my-dir] => Array
        (
            [0] => file1.txt
            [/subdir] => Array
                (
                    [0] => file2.txt
                )
        )
)
```

Subdirectories appear as keys prefixed with the directory separator (e.g. `/subdir`); files are plain,
numerically-indexed basenames. When `recursive` is `false` (the default), subdirectory keys are still present in
the tree, but their value is always an empty array rather than being expanded — set `recursive` to `true` to walk
all the way down. A subdirectory that's actually a symlink is likewise never expanded, which also protects
against symlink cycles.

[Top](#pop-dir)

Copying a Directory
--------------------

An entire directory can be copied to another location with `copyTo()`:

```php
use Pop\Dir\Dir;

$dir = new Dir('my-dir');
$dir->copyTo('/path/to/destination');
```

By default (`$full = true`), the source directory itself is created under the destination — the example above
produces `/path/to/destination/my-dir`. Pass `false` to copy the *contents* of the source directory directly into
the destination instead, without the extra nesting level:

```php
$dir->copyTo('/path/to/destination', false);
```

**The destination path must already exist** — `copyTo()` does not create intermediate directories for the
destination itself, only the source directory's own name underneath it.

[Top](#pop-dir)

Deleting Files
---------------

Individual files can be deleted with `deleteFile()`, or equivalently by `unset()`-ing an offset:

```php
use Pop\Dir\Dir;

$dir = new Dir('my-dir');

$dir->deleteFile('file1.txt');
// or
unset($dir['file1.txt']);
// or
unset($dir->{'file1.txt'});
```

This removes the file from disk (`unlink()`) as well as from the `Dir` object's internal list. It only works on
files — attempting to delete a directory entry, a non-existent entry, or a non-writable file throws
`Pop\Dir\Exception`. See [Exceptions](#exceptions).

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

`emptyDir()` recurses into subdirectories automatically and deletes everything it finds. If any individual file
can't be deleted (e.g. permissions), it throws `Pop\Dir\Exception` naming the file, and the operation stops at
that point rather than silently skipping it.

[Top](#pop-dir)

Exceptions
----------

Every error condition in this component throws `Pop\Dir\Exception` (a plain `\Exception` subclass), so a single
`catch` covers all of them:

```php
use Pop\Dir\Dir;
use Pop\Dir\Exception;

try {
    $dir = new Dir('/path/that/does-not-exist');
} catch (Exception $e) {
    echo $e->getMessage();
}
```

The cases that throw include:

- Constructing a `Dir` with a path that doesn't exist, or that becomes unreadable partway through a recursive
  scan.
- Writing through array/property syntax (`$dir['x'] = 'y'`, `$dir->x = 'y'`) — the object is read-only except for
  deletion.
- Deleting (`deleteFile()` / `unset()`) an entry that's a directory, that no longer exists on disk, that isn't
  writable, or that doesn't match any known entry.
- `emptyDir()` failing to open the target directory, or failing to delete a file it finds along the way.

[Top](#pop-dir)
