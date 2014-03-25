<?php


namespace Floppy\Client;


use Floppy\Common\FileId;

class EmptyHostResolver implements HostResolver
{
    public function resolveHost($host, FileId $fileId, $fileType)
    {
        return $host;
    }
}