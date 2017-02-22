<?php

namespace Pop\Dir\Test;

use Pop\Dir\Dir;

class DirTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $dir = new Dir(__DIR__ . '\tmp\\');
        $dir = new Dir(__DIR__ . '/tmp/');
        $dir = new Dir(__DIR__ . '/tmp/', [
            'absolute'  => false,
            'recursive' => false,
            'filesOnly' => true
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'absolute'  => false,
            'recursive' => true,
            'filesOnly' => true
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'absolute'  => true,
            'recursive' => false,
            'filesOnly' => false
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'absolute'  => true,
            'recursive' => false,
            'filesOnly' => true
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'absolute'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'absolute'  => true,
            'recursive' => true,
            'filesOnly' => true
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => false,
            'filesOnly' => false
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => false,
            'filesOnly' => true
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => true
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        $dir = new Dir(__DIR__ . '/tmp/', [
            'absolute'  => false,
            'relative'  => false,
            'recursive' => true,
            'filesOnly' => false
        ]);
        $this->assertInstanceOf('Pop\Dir\Dir', $dir);
        $this->assertEquals(__DIR__ . '/tmp', $dir->getPath());
        $this->assertEquals(3, count($dir));
        $this->assertEquals(3, count($dir->getFiles()));
        $this->assertEquals(1, count($dir->getTree()));

        $c = [];
        foreach ($dir as $file) {
            $c[] = $file;
        }
        $this->assertEquals(3, count($c));
    }

    public function testConstructorDoesNotExistException()
    {
        $this->expectException('Pop\Dir\Exception');
        $dir = new Dir(__DIR__ . '/bad');
    }

    public function testOptions()
    {
        $dir = new Dir(__DIR__ . '/tmp', [
            'absolute'  => true,
            'relative'  => false,
            'recursive' => true,
            'filesOnly' => true
        ]);

        $this->assertTrue($dir->isAbsolute());
        $this->assertFalse($dir->isRelative());
        $this->assertTrue($dir->isRecursive());
        $this->assertTrue($dir->isFilesOnly());
    }

    public function testSetAbsolute()
    {
        $dir = new Dir(__DIR__ . '/tmp');
        $dir->setRelative(true);
        $dir->setAbsolute(true);
        $this->assertTrue($dir->isAbsolute());
        $this->assertFalse($dir->isRelative());
    }

    public function testSetRelative()
    {
        $dir = new Dir(__DIR__ . '/tmp');
        $dir->setAbsolute(true);
        $dir->setRelative(true);
        $this->assertFalse($dir->isAbsolute());
        $this->assertTrue($dir->isRelative());
    }

    public function testCopyTo()
    {
        mkdir(__DIR__ . '/copy');
        $dir = new Dir(__DIR__ . '/tmp');
        $dir->copyTo(__DIR__ . '/copy');
        $this->assertFileExists(__DIR__ . '/copy/tmp');

        $dir = new Dir(__DIR__ . '/copy');
        $dir->emptyDir(true);
        $this->assertFileNotExists(__DIR__ . '/copy');
    }

    public function testEmptyToBadPath()
    {
        $this->expectException('Pop\Dir\Exception');
        $dir = new Dir(__DIR__ . '/tmp');
        $dir->emptyDir(false, __DIR__ . '/badpath');
    }

    public function testOffsets()
    {
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        $this->assertTrue(isset($dir[0]));
        $this->assertTrue(is_string($dir[0]));
    }

    public function testOffsetSetException()
    {
        $this->expectException('Pop\Dir\Exception');
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        $dir[5] = 'tmp/file.txt';
    }

    public function testOffsetUnsetException()
    {
        $this->expectException('Pop\Dir\Exception');
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        unset($dir[0]);
    }

}