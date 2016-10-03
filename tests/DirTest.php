<?php

namespace Pop\File\Test;

use Pop\File\Dir;

class DirTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
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
        $this->assertInstanceOf('Pop\File\Dir', $dir);
        $this->assertEquals(__DIR__ . '/tmp', $dir->getPath());
        $this->assertEquals(3, count($dir->getFiles()));
        $this->assertEquals(3, count($dir->getObjects()));
        $this->assertEquals(1, count($dir->getTree()));
    }

    public function testConstructorDoesNotExistException()
    {
        $this->setExpectedException('Pop\File\Exception');
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

    public function testCopyDir()
    {
        mkdir(__DIR__ . '/copy');
        $dir = new Dir(__DIR__ . '/tmp');
        $dir->copyDir(__DIR__ . '/copy');
        $this->assertFileExists(__DIR__ . '/copy/tmp');

        $dir = new Dir(__DIR__ . '/copy');
        $dir->emptyDir(true);
        $this->assertFileNotExists(__DIR__ . '/copy');
    }

}