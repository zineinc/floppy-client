<?php


namespace ZineInc\Storage\Client;


use ZineInc\Storage\Common\FileId;

class EmptyHostResolver implements HostResolver
{
    public function resolveHost($host, FileId $fileId, $fileType)
    {
        return $host;
    }
}