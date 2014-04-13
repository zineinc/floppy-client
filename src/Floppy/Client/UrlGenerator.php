<?php


namespace Floppy\Client;

use Floppy\Common\FileId;

interface UrlGenerator
{
    public function generate(FileId $fileId, $fileType, array $credentialAttributes = array());
} 