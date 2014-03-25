<?php


namespace Floppy\Client;


use Floppy\Common\FileId;

interface HostResolver
{
    public function resolveHost($host, FileId $fileId, $fileType);
} 