<?php

namespace WebAction\Storage;

use PHPUnit\Framework\TestCase;

class FileRenameMethodsTest extends TestCase
{
    public function testMd5ize()
    {
        // return something like /private/tmp/webaction_HM0FV5
        $p = tempnam('/tmp','webaction_');
        file_put_contents($p, 'Hello');
        $newp = FileRenameMethods::md5ize('/tmp/upload/foo.jpg', $p);
        $this->assertEquals('/tmp/upload/8b1a9953c4611296a827abf8c47804d7.jpg', $newp);
    }
}


