<?php

namespace WebAction\Storage\FileRename;

use WebAction\Action;
use WebAction\Storage\FileRenameMethods;
use Universal\Http\UploadedFile;
use WebAction\Param\Param;

class Md5Rename
{
    public function __invoke($newFile, $tmpFile, UploadedFile $uploadedFile = null)
    {
        return FileRenameMethods::md5ize($newFile, $tmpFile, $uploadedFile);
    }
}
