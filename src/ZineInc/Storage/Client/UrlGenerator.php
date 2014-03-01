<?php


namespace ZineInc\Storage\Client;

use ZineInc\Storage\Common\FileId;

interface UrlGenerator
{
    public function generate(FileId $fileId, $fileType);
} 