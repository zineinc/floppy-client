<?php


namespace ZineInc\Storage\Client;

use ZineInc\Storage\Client\Exception\InvalidArgumentException;
use ZineInc\Storage\Common\FileId;

class UrlGeneratorImpl implements UrlGenerator
{
    private $pathGenerators;
    private $protocol;
    private $path;
    private $hostResolver;
    private $host;

    public function __construct(array $pathGenerators, $host, $path, $protocol, HostResolver $hostResolver)
    {
        $this->pathGenerators = $pathGenerators;
        $this->host = $host;
        $this->path = $path;
        $this->protocol = $protocol;
        $this->hostResolver = $hostResolver;
    }


    public function generate(FileId $fileId, $fileType)
    {
        if(!isset($this->pathGenerators[$fileType])) {
            throw new InvalidArgumentException(sprintf('File type "%s" doesn\'t exist, supported file types: %s', $fileType, implode(', ', array_keys($this->pathGenerators))));
        }

        $pathGenerator = $this->pathGenerators[$fileType];

        $path = $pathGenerator->generate($fileId);

        return sprintf('%s://%s%s/%s', $this->protocol, $this->host($fileId, $fileType), $this->path, $path);
    }

    private function host(FileId $fileId, $fileType)
    {
        return $this->hostResolver->resolveHost($this->host, $fileId, $fileType);
    }
}