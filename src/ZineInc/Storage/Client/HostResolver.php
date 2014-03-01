<?php


namespace ZineInc\Storage\Client;


use ZineInc\Storage\Common\FileId;

interface HostResolver
{
    public function resolveHost($host, FileId $fileId, $fileType);
} 