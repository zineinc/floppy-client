<?php


namespace Floppy\Client;


use Floppy\Common\FileId;

class SubdomainRotationHostResolver implements HostResolver
{
    private $subdomainsLimit;
    private $subdomainDelimiter;

    function __construct($subdomainsLimit = 3, $subdomainDelimiter = '.')
    {
        $this->subdomainDelimiter = (string) $subdomainDelimiter;
        $this->subdomainsLimit = (int) $subdomainsLimit;
    }

    public function resolveHost($host, FileId $fileId, $fileType)
    {
        $id = (abs(crc32($fileId->id())) % $this->subdomainsLimit) + 1;
        return $fileType.$id.$this->subdomainDelimiter.$host;
    }
}