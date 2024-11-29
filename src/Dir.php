<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Dir;

use ArrayIterator;
use DirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Directory class
 *
 * @category   Pop
 * @package    Pop\Dir
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.1
 */
class Dir implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * The directory path
     * @var ?string
     */
    protected ?string $path = null;

    /**
     * The files within the directory
     * @var array
     */
    protected array $files = [];

    /**
     * The nested tree map of the directory and its files
     * @var array
     */
    protected array $tree = [];

    /**
     * Flag to store the absolute path.
     * @var bool
     */
    protected bool $absolute = false;

    /**
     * Flag to store the relative path.
     * @var bool
     */
    protected bool $relative = false;

    /**
     * Flag to dig recursively.
     * @var bool
     */
    protected bool $recursive = false;

    /**
     * Flag to include only files and no directories
     * @var bool
     */
    protected bool $filesOnly = false;

    /**
     * Constructor
     *
     * Instantiate a directory object
     *
     * @param  string  $dir
     * @param  array   $options
     * @throws Exception
     */
    public function __construct(string $dir, array $options = [])
    {
        // Set the directory path.
        if ((str_contains($dir, "\\")) && (DIRECTORY_SEPARATOR != "\\")) {
            $this->path = str_replace("\\", '/', $dir);
        } else {
            $this->path = $dir;
        }

        // Check to see if the directory exists.
        if (!file_exists($this->path)) {
            throw new Exception("Error: The directory '" . $this->path . "' does not exist");
        }

        // Trim the trailing slash.
        if (strrpos($this->path, DIRECTORY_SEPARATOR) == (strlen($this->path) - 1)) {
            $this->path = substr($this->path, 0, -1);
        }

        if (isset($options['absolute'])) {
            $this->setAbsolute($options['absolute']);
        }
        if (isset($options['relative'])) {
            $this->setRelative($options['relative']);
        }
        if (isset($options['recursive'])) {
            $this->setRecursive($options['recursive']);
        }
        if (isset($options['filesOnly'])) {
            $this->setFilesOnly($options['filesOnly']);
        }

        $this->tree[realpath($this->path)] = $this->buildTree(new DirectoryIterator($this->path));

        if ($this->recursive) {
            $this->traverseRecursively();
        } else {
            $this->traverse();
        }
    }

    /**
     * Method to get the count of files in the directory
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->files);
    }

    /**
     * Method to iterate over the files
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->files);
    }

    /**
     * Set absolute
     *
     * @param  bool $absolute
     * @return Dir
     */
    public function setAbsolute(bool $absolute): Dir
    {
        $this->absolute = $absolute;
        if (($this->absolute) && ($this->isRelative())) {
            $this->setRelative(false);
        }
        return $this;
    }

    /**
     * Set relative
     *
     * @param  bool $relative
     * @return Dir
     */
    public function setRelative(bool $relative): Dir
    {
        $this->relative = $relative;
        if (($this->relative) && ($this->isAbsolute())) {
            $this->setAbsolute(false);
        }
        return $this;
    }

    /**
     * Set recursive
     *
     * @param  bool $recursive
     * @return Dir
     */
    public function setRecursive(bool $recursive): Dir
    {
        $this->recursive = $recursive;
        return $this;
    }

    /**
     * Set files only
     *
     * @param  bool $filesOnly
     * @return Dir
     */
    public function setFilesOnly(bool $filesOnly): Dir
    {
        $this->filesOnly = $filesOnly;
        return $this;
    }

    /**
     * Is absolute
     *
     * @return bool
     */
    public function isAbsolute(): bool
    {
        return $this->absolute;
    }

    /**
     * Is relative
     *
     * @return bool
     */
    public function isRelative(): bool
    {
        return $this->relative;
    }

    /**
     * Is recursive
     *
     * @return bool
     */
    public function isRecursive(): bool
    {
        return $this->recursive;
    }

    /**
     * Is files only
     *
     * @return bool
     */
    public function isFilesOnly(): bool
    {
        return $this->filesOnly;
    }

    /**
     * Get the path
     *
     * @return string|null
     */
    public function getPath(): string|null
    {
        return $this->path;
    }

    /**
     * Get the files
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Get the tree
     *
     * @return array
     */
    public function getTree(): array
    {
        return $this->tree;
    }

    /**
     * Copy an entire directory recursively to another destination directory
     *
     * @param  string $destination
     * @param  bool   $full
     * @return void
     */
    public function copyTo(string $destination, bool $full = true): void
    {
        if ($full) {
            if (str_contains($this->path, DIRECTORY_SEPARATOR)) {
                $folder = substr($this->path, (strrpos($this->path, DIRECTORY_SEPARATOR) + 1));
            }

            if (!file_exists($destination . DIRECTORY_SEPARATOR . $folder)) {
                mkdir($destination . DIRECTORY_SEPARATOR . $folder);
            }
            $destination = $destination . DIRECTORY_SEPARATOR . $folder;
        }

        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if ($item->isDir()) {
                mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    /**
     * File exists
     *
     * @param  string  $file
     * @return bool
     */
    public function fileExists(string $file): bool
    {
        return $this->offsetExists($file);
    }

    /**
     * Delete a file
     *
     * @param  string  $file
     * @throws Exception
     * @return void
     */
    public function deleteFile(string $file): void
    {
        $this->offsetUnset($file);
    }

    /**
     * Empty an entire directory
     *
     * @param  bool    $remove
     * @param  ?string $path
     * @throws Exception
     * @return void
     */
    public function emptyDir(bool $remove = false, ?string $path = null): void
    {
        if ($path === null) {
            $path = $this->path;
        }

        // Get a directory handle.
        if (!($dh = @opendir($path))) {
            throw new Exception('Error: Unable to open the directory path "' . $path . '"');
        }

        // Recursively dig through the directory, deleting files where applicable.
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }
            if (!@unlink($path . DIRECTORY_SEPARATOR . $obj)) {
                $this->emptyDir(true, $path . DIRECTORY_SEPARATOR . $obj);
            }
        }

        // Close the directory handle.
        closedir($dh);

        // If the delete flag was passed, remove the top level directory.
        if ($remove) {
            @rmdir($path);
        }
    }

    /**
     * Get a file
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->offsetGet($name);
    }

    /**
     * Does file exist
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * Set method
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws Exception
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->offsetSet($name, $value);
    }

    /**
     * Unset method
     *
     * @param  string $name
     * @throws Exception
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->offsetUnset($name);
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        if (!is_numeric($offset) && in_array($offset, $this->files)) {
            $offset = array_search($offset, $this->files);
        }
        return isset($this->files[$offset]);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return (isset($this->files[$offset])) ? $this->files[$offset] : null;
    }

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('Error: The directory object is read-only');
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @throws Exception
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        if (!is_numeric($offset) && in_array($offset, $this->files)) {
            $offset = array_search($offset, $this->files);
        }
        if (isset($this->files[$offset])) {
            if (is_dir($this->path . DIRECTORY_SEPARATOR . $this->files[$offset])) {
                throw new Exception("Error: The file '" . $this->path . DIRECTORY_SEPARATOR . $this->files[$offset] . "' is a directory");
            } else if (!file_exists($this->path . DIRECTORY_SEPARATOR . $this->files[$offset])) {
                throw new Exception("Error: The file '" . $this->path . DIRECTORY_SEPARATOR . $this->files[$offset] . "' does not exist");
            } else if (!is_writable($this->path . DIRECTORY_SEPARATOR . $this->files[$offset])) {
                throw new Exception("Error: The file '" . $this->path . DIRECTORY_SEPARATOR . $this->files[$offset] . "' is read-only");
            } else {
                unlink($this->path . DIRECTORY_SEPARATOR . $this->files[$offset]);
                unset($this->files[$offset]);
            }
        } else {
            throw new Exception("Error: The file does not exist");
        }
    }

    /**
     * Traverse the directory
     *
     * @return void
     */
    protected function traverse(): void
    {
        foreach (new DirectoryIterator($this->path) as $fileInfo) {
            if(!$fileInfo->isDot()) {
                // If absolute path flag was passed, store the absolute path.
                if ($this->absolute) {
                    $f = null;
                    if (!$this->filesOnly) {
                        $f = ($fileInfo->isDir()) ?
                            ($this->path . DIRECTORY_SEPARATOR . $fileInfo->getFilename() . DIRECTORY_SEPARATOR) :
                            ($this->path . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
                    } else if (!$fileInfo->isDir()) {
                        $f = $this->path . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
                    }
                    if (($f !== false) && ($f !== null)) {
                        $this->files[] = $f;
                    }
                // If relative path flag was passed, store the relative path.
                } else if ($this->relative) {
                    $f = null;
                    if (!$this->filesOnly) {
                        $f = ($fileInfo->isDir()) ?
                            ($this->path . DIRECTORY_SEPARATOR . $fileInfo->getFilename() . DIRECTORY_SEPARATOR) :
                            ($this->path . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
                    } else if (!$fileInfo->isDir()) {
                        $f = $this->path . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
                    }
                    if (($f !== false) && ($f !== null)) {
                        $this->files[] = substr($f, (strlen(realpath($this->path)) + 1));
                    }
                // Else, store only the directory or file name.
                } else {
                    if (!$this->filesOnly) {
                        $this->files[] = ($fileInfo->isDir()) ? ($fileInfo->getFilename()) : $fileInfo->getFilename();
                    } else if (!$fileInfo->isDir()) {
                        $this->files[] = $fileInfo->getFilename();
                    }
                }
            }
        }
    }

    /**
     * Traverse the directory recursively
     *
     * @return void
     */
    protected function traverseRecursively(): void
    {
        $objects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path), RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($objects as $fileInfo) {
            if (($fileInfo->getFilename() != '.') && ($fileInfo->getFilename() != '..')) {
                // If absolute path flag was passed, store the absolute path.
                if ($this->absolute) {
                    $f = null;
                    if (!$this->filesOnly) {
                        $f = ($fileInfo->isDir()) ?
                            (realpath($fileInfo->getPathname())) : realpath($fileInfo->getPathname());
                    } else if (!$fileInfo->isDir()) {
                        $f = realpath($fileInfo->getPathname());
                    }
                    if (($f !== false) && ($f !== null)) {
                        $this->files[] = $f;
                    }
                // If relative path flag was passed, store the relative path.
                } else if ($this->relative) {
                    $f = null;
                    if (!$this->filesOnly) {
                        $f = ($fileInfo->isDir()) ?
                            (realpath($fileInfo->getPathname())) : realpath($fileInfo->getPathname());
                    } else if (!$fileInfo->isDir()) {
                        $f = realpath($fileInfo->getPathname());
                    }
                    if (($f !== false) && ($f !== null)) {
                        $this->files[] = substr($f, (strlen(realpath($this->path)) + 1));
                    }
                // Else, store only the directory or file name.
                } else {
                    if (!$this->filesOnly) {
                        $this->files[] = ($fileInfo->isDir()) ? ($fileInfo->getFilename()) : $fileInfo->getFilename();
                    } else if (!$fileInfo->isDir()) {
                        $this->files[] = $fileInfo->getFilename();
                    }
                }
            }
        }
    }

    /**
     * Build the directory tree
     *
     * @param  DirectoryIterator $it
     * @return array
     */
    protected function buildTree(DirectoryIterator $it): array
    {
        $result = [];

        foreach ($it as $key => $child) {
            if ($child->isDot()) {
                continue;
            }

            $name = $child->getBasename();

            if ($child->isDir()) {
                $subDir = new DirectoryIterator($child->getPathname());
                $result[DIRECTORY_SEPARATOR . $name] = $this->buildTree($subDir);
            } else {
                $result[] = $name;
            }
        }

        return $result;
    }

}
