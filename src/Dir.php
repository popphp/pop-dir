<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Dir;

/**
 * Directory class
 *
 * @category   Pop
 * @package    Pop_Dir
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Dir implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * The directory path
     * @var string
     */
    protected $path = null;

    /**
     * The files within the directory
     * @var array
     */
    protected $files = [];

    /**
     * The file info objects within the directory
     * @var array
     */
    protected $objects = [];

    /**
     * The nested tree map of the directory and its files
     * @var array
     */
    protected $tree = [];

    /**
     * Flag to store the absolute path.
     * @var boolean
     */
    protected $absolute = false;

    /**
     * Flag to store the relative path.
     * @var boolean
     */
    protected $relative = false;

    /**
     * Flag to dig recursively.
     * @var boolean
     */
    protected $recursive = false;

    /**
     * Flag to include only files and no directories
     * @var boolean
     */
    protected $filesOnly = false;

    /**
     * Constructor
     *
     * Instantiate a directory object
     *
     * @param  string  $dir
     * @param  array   $options
     * @throws Exception
     */
    public function __construct($dir, array $options = [])
    {
        // Check to see if the directory exists.
        if (!file_exists($dir)) {
            throw new Exception('Error: The directory does not exist.');
        }

        // Set the directory path.
        if ((strpos($dir, '/') !== false) && (DIRECTORY_SEPARATOR != '/')) {
            $this->path = str_replace('/', "\\", $dir);
        } else if ((strpos($dir, "\\") !== false) && (DIRECTORY_SEPARATOR != "\\")) {
            $this->path = str_replace("\\", '/', $dir);
        } else {
            $this->path = $dir;
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

        $this->tree[realpath($this->path)] = $this->buildTree(new \DirectoryIterator($this->path));
        $this->traverse();
    }

    /**
     * Method to get the count of files in the directory
     *
     * @return int
     */
    public function count()
    {
        return count($this->files);
    }

    /**
     * Method to iterate over the files
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->files);
    }

    /**
     * Set absolute
     *
     * @param  boolean $absolute
     * @return Dir
     */
    public function setAbsolute($absolute)
    {
        $this->absolute = (bool)$absolute;
        if (($this->absolute) && ($this->isRelative())) {
            $this->setRelative(false);
        }
        return $this;
    }

    /**
     * Set relative
     *
     * @param  boolean $relative
     * @return Dir
     */
    public function setRelative($relative)
    {
        $this->relative = (bool)$relative;
        if (($this->relative) && ($this->isAbsolute())) {
            $this->setAbsolute(false);
        }
        return $this;
    }

    /**
     * Set recursive
     *
     * @param  boolean $recursive
     * @return Dir
     */
    public function setRecursive($recursive)
    {
        $this->recursive = (bool)$recursive;
        return $this;
    }

    /**
     * Set files only
     *
     * @param  boolean $filesOnly
     * @return Dir
     */
    public function setFilesOnly($filesOnly)
    {
        $this->filesOnly = (bool)$filesOnly;
        return $this;
    }

    /**
     * Is absolute
     *
     * @return boolean
     */
    public function isAbsolute()
    {
        return $this->absolute;
    }

    /**
     * Is relative
     *
     * @return boolean
     */
    public function isRelative()
    {
        return $this->relative;
    }

    /**
     * Is recursive
     *
     * @return boolean
     */
    public function isRecursive()
    {
        return $this->recursive;
    }

    /**
     * Is files only
     *
     * @return boolean
     */
    public function isFilesOnly()
    {
        return $this->filesOnly;
    }

    /**
     * Get the path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Get the objects
     *
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Get the tree
     *
     * @return array
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * Copy an entire directory recursively
     *
     * @param  string  $dest
     * @param  boolean $full
     * @return void
     */
    public function copyDir($dest, $full = true)
    {
        if ($full) {
            if (strpos($this->path, DIRECTORY_SEPARATOR) !== false) {
                $folder = substr($this->path, (strrpos($this->path, DIRECTORY_SEPARATOR) + 1));
            } else {
                $folder = $this->path;
            }

            if (!file_exists($dest . DIRECTORY_SEPARATOR . $folder)) {
                mkdir($dest . DIRECTORY_SEPARATOR . $folder);
            }
            $dest = $dest . DIRECTORY_SEPARATOR . $folder;
        }

        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if ($item->isDir()) {
                mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    /**
     * Empty an entire directory
     *
     * @param  boolean $remove
     * @param  string  $path
     * @return void
     */
    public function emptyDir($remove = false, $path = null)
    {
        if (null === $path) {
            $path = $this->path;
        }
        // Get a directory handle.
        if (!$dh = @opendir($path)) {
            return;
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
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->files[$offset]);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return (isset($this->files[$offset])) ? $this->files[$offset] : null;
    }

    /**
     * ArrayAccess offsetSet
     *
     * @throws Exception
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception('The directory object is read-only');
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @throws Exception
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new Exception('The directory object is read-only');
    }

    /**
     * Traverse the directory
     *
     * @return void
     */
    protected function traverse()
    {
        // If the recursive flag is passed, traverse recursively.
        if ($this->recursive) {
            $objects = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->path), \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($objects as $fileInfo) {
                if (($fileInfo->getFilename() != '.') && ($fileInfo->getFilename() != '..')) {
                    $this->objects[] = $fileInfo;
                    // If absolute path flag was passed, store the absolute path.
                    if ($this->absolute) {
                        $f = null;
                        if (!$this->filesOnly) {
                            $f = ($fileInfo->isDir()) ? (realpath($fileInfo->getPathname())) : realpath($fileInfo->getPathname());
                        } else if (!$fileInfo->isDir()) {
                            $f = realpath($fileInfo->getPathname());
                        }
                        if (($f !== false) && (null !== $f)) {
                            $this->files[] = $f;
                        }
                    // If relative path flag was passed, store the relative path.
                    } else if ($this->relative) {
                        $f = null;
                        if (!$this->filesOnly) {
                            $f = ($fileInfo->isDir()) ? (realpath($fileInfo->getPathname())) : realpath($fileInfo->getPathname());
                        } else if (!$fileInfo->isDir()) {
                            $f = realpath($fileInfo->getPathname());
                        }
                        if (($f !== false) && (null !== $f)) {
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
            // Else, only traverse the single directory that was passed.
        } else {
            foreach (new \DirectoryIterator($this->path) as $fileInfo) {
                if(!$fileInfo->isDot()) {
                    $this->objects[] = $fileInfo;
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
                        if (($f !== false) && (null !== $f)) {
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
                        if (($f !== false) && (null !== $f)) {
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
    }

    /**
     * Build the directory tree
     *
     * @param  \DirectoryIterator $it
     * @return array
     */
    protected function buildTree(\DirectoryIterator $it)
    {
        $result = [];

        foreach ($it as $key => $child) {
            if ($child->isDot()) {
                continue;
            }

            $name = $child->getBasename();

            if ($child->isDir()) {
                $subdir = new \DirectoryIterator($child->getPathname());
                $result[DIRECTORY_SEPARATOR . $name] = $this->buildTree($subdir);
            } else {
                $result[] = $name;
            }
        }

        return $result;
    }

}
