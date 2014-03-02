<?php


namespace ZineInc\Storage\Client;

use ZineInc\Storage\Client\Exception\InvalidArgumentException;
use ZineInc\Storage\Common\FileId;

class UrlGeneratorImpl implements UrlGenerator
{
    private $pathGenerators;
    private $hostResolver;
    private $endpointUrl;

    public function __construct(array $pathGenerators, Url $endpointUrl, HostResolver $hostResolver)
    {
        $this->pathGenerators = $pathGenerators;
        $this->hostResolver = $hostResolver;
        $this->endpointUrl = $endpointUrl;
    }


    public function generate(FileId $fileId, $fileType)
    {
        if(!isset($this->pathGenerators[$fileType])) {
            throw new InvalidArgumentException(sprintf('File type "%s" doesn\'t exist, supported file types: %s', $fileType, implode(', ', array_keys($this->pathGenerators))));
        }

        $pathGenerator = $this->pathGenerators[$fileType];

        $path = $pathGenerator->generate($fileId);

        return (string) $this->endpointUrl
            ->replaceHost($this->host($fileId, $fileType))
            ->replacePath($this->endpointUrl->path().'/'.$path);
    }

    private function host(FileId $fileId, $fileType)
    {
        return $this->hostResolver->resolveHost($this->endpointUrl->host(), $fileId, $fileType);
    }
}