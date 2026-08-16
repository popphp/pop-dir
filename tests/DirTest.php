<?php

namespace Pop\Dir\Test;

use Pop\Dir\Dir;
use PHPUnit\Framework\TestCase;

class DirTest extends TestCase
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
        $this->assertFileDoesNotExist(__DIR__ . '/copy');
    }

    public function testEmptyToBadPath()
    {
        $this->expectException('Pop\Dir\Exception');
        $dir = new Dir(__DIR__ . '/tmp');
        $dir->emptyDir(false, __DIR__ . '/badpath');
    }

    public function testMagicMethods()
    {
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        $this->assertTrue(isset($dir->{0}));
        $this->assertTrue(isset($dir->{'test'}));
        $this->assertTrue($dir->fileExists('test.txt'));
        $this->assertStringContainsString('test', $dir->{0});
    }

    public function testSetException()
    {
        $this->expectException('Pop\Dir\Exception');
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        $dir->{5} = 'tmp/file.txt';
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

    public function testOffsetUnset()
    {
        touch(__DIR__ . '/tmp/unlink1.txt');
        touch(__DIR__ . '/tmp/unlink2.txt');
        touch(__DIR__ . '/tmp/unlink3.txt');
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        $this->assertTrue($dir->fileExists('unlink1.txt'));
        $this->assertTrue($dir->fileExists('unlink2.txt'));
        $this->assertTrue($dir->fileExists('unlink3.txt'));
        unset($dir['unlink1.txt']);
        unset($dir->{'unlink2.txt'});
        $dir->deleteFile('unlink3.txt');
        $this->assertFalse($dir->fileExists('unlink1.txt'));
        $this->assertFalse($dir->fileExists('unlink2.txt'));
        $this->assertFalse($dir->fileExists('unlink3.txt'));
    }

    public function testOffsetUnsetDirException()
    {
        $this->expectException('Pop\Dir\Exception');
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        unset($dir['test']);
    }

    public function testOffsetUnsetFileException()
    {
        $this->expectException('Pop\Dir\Exception');
        $dir = new Dir(__DIR__ . '/tmp/', [
            'relative'  => true,
            'recursive' => true,
            'filesOnly' => false
        ]);
        unset($dir['bad']);
    }

    public function testCopyToPathWithNoSeparator()
    {
        $cwd = getcwd();
        chdir(__DIR__);
        mkdir('copynosep_dest');

        $dir = new Dir('tmp');
        $dir->copyTo('copynosep_dest');

        $this->assertFileExists('copynosep_dest' . DIRECTORY_SEPARATOR . 'tmp');

        $cleanup = new Dir('copynosep_dest');
        $cleanup->emptyDir(true);
        chdir($cwd);
    }

    public function testOffsetGetByName()
    {
        $dir = new Dir(__DIR__ . '/tmp/');
        $this->assertEquals('test.txt', $dir['test.txt']);
    }

    public function testConstructorUnreadableDirectoryThrowsPopDirException()
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('Cannot test unreadable directories while running as root.');
        }

        $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('popdirtest_');
        mkdir($base);
        mkdir($base . '/locked');
        file_put_contents($base . '/locked/secret.txt', 'x');
        chmod($base . '/locked', 0000);

        try {
            $this->expectException('Pop\Dir\Exception');
            new Dir($base, ['recursive' => true]);
        } finally {
            chmod($base . '/locked', 0755);
            unlink($base . '/locked/secret.txt');
            rmdir($base . '/locked');
            rmdir($base);
        }
    }

    public function testEmptyDirUnlinkFailureThrowsClearException()
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('Cannot test unlink permission failures while running as root.');
        }

        $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('popdirtest_');
        mkdir($base);
        file_put_contents($base . '/file.txt', 'x');
        chmod($base, 0555);

        $dir = new Dir($base);

        try {
            $exception = null;
            try {
                $dir->emptyDir();
            } catch (\Pop\Dir\Exception $e) {
                $exception = $e;
            }
            $this->assertNotNull($exception);
            $this->assertStringContainsString('file.txt', $exception->getMessage());
            $this->assertStringNotContainsString('open the directory', $exception->getMessage());
        } finally {
            chmod($base, 0755);
            unlink($base . '/file.txt');
            rmdir($base);
        }
    }

    public function testEmptyDirDoesNotFollowSymlinksByDefault()
    {
        $base   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('popdirtest_');
        $target = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('popdirtarget_');

        mkdir($base);
        mkdir($target);
        file_put_contents($target . '/keep.txt', 'x');
        symlink($target, $base . '/link');

        try {
            $dir = new Dir($base);
            $dir->emptyDir();

            $this->assertFileExists($target . '/keep.txt');
            $this->assertFalse(is_link($base . '/link'));
        } finally {
            unlink($target . '/keep.txt');
            rmdir($target);
            rmdir($base);
        }
    }

    public function testEmptyDirFollowsSymlinksWhenRequested()
    {
        $base   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('popdirtest_');
        $target = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('popdirtarget_');

        mkdir($base);
        mkdir($target);
        file_put_contents($target . '/keep.txt', 'x');
        symlink($target, $base . '/link');

        try {
            $dir = new Dir($base);
            $dir->emptyDir(false, null, true);

            $this->assertFileDoesNotExist($target . '/keep.txt');
        } finally {
            if (file_exists($target . '/keep.txt')) {
                unlink($target . '/keep.txt');
            }
            rmdir($target);
            if (is_link($base . '/link') || file_exists($base . '/link')) {
                unlink($base . '/link');
            }
            rmdir($base);
        }
    }

    public function testGetTreeNonRecursiveDoesNotExpandSubdirectories()
    {
        $dir  = new Dir(__DIR__ . '/tmp/', ['recursive' => false]);
        $tree = $dir->getTree();
        $root = array_key_first($tree);

        $this->assertEquals([], $tree[$root][DIRECTORY_SEPARATOR . 'test']);
    }

    public function testGetTreeDoesNotFollowSymlinkCycle()
    {
        $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('popdirtest_');
        mkdir($base . '/sub', 0777, true);
        touch($base . '/sub/a.txt');
        symlink($base, $base . '/sub/loop');

        try {
            $dir     = new Dir($base, ['recursive' => true]);
            $tree    = $dir->getTree();
            $root    = array_key_first($tree);
            $subTree = $tree[$root][DIRECTORY_SEPARATOR . 'sub'];

            $this->assertEquals([], $subTree[DIRECTORY_SEPARATOR . 'loop']);
        } finally {
            unlink($base . '/sub/loop');
            unlink($base . '/sub/a.txt');
            rmdir($base . '/sub');
            rmdir($base);
        }
    }

    public function testSetAbsoluteRebuildsFiles()
    {
        $dir = new Dir(__DIR__ . '/tmp/');
        $this->assertContains('test.txt', $dir->getFiles());

        $dir->setAbsolute(true);

        $this->assertContains(__DIR__ . '/tmp' . DIRECTORY_SEPARATOR . 'test.txt', $dir->getFiles());
    }

    public function testSetRecursiveRebuildsFiles()
    {
        $dir = new Dir(__DIR__ . '/tmp/');
        $this->assertNotContains('foo.txt', $dir->getFiles());

        $dir->setRecursive(true);

        $this->assertContains('foo.txt', $dir->getFiles());
    }

    public function testSetFilesOnlyRebuildsFiles()
    {
        $dir = new Dir(__DIR__ . '/tmp/');
        $this->assertContains('test', $dir->getFiles());

        $dir->setFilesOnly(true);

        $this->assertNotContains('test', $dir->getFiles());
    }

    public function testGetFilesListsDirectoryBeforeItsChildrenWhenRecursive()
    {
        $dir = new Dir(__DIR__ . '/tmp', [
            'relative'  => true,
            'recursive' => true,
        ]);

        $files     = $dir->getFiles();
        $dirIndex  = array_search('test', $files);
        $fileIndex = array_search('test' . DIRECTORY_SEPARATOR . 'foo.txt', $files);

        $this->assertNotFalse($dirIndex);
        $this->assertNotFalse($fileIndex);
        $this->assertLessThan($fileIndex, $dirIndex);
    }

    public function testGetTreeAndGetFilesAgreeWhenRecursive()
    {
        $dir  = new Dir(__DIR__ . '/tmp', ['recursive' => true]);
        $tree = $dir->getTree();
        $root = array_key_first($tree);

        $this->assertContains('foo.txt', $tree[$root][DIRECTORY_SEPARATOR . 'test']);
        $this->assertContains('foo.txt', $dir->getFiles());
    }

}