<?php


namespace ZineInc\Storage\Client;

use ZineInc\Storage\Common\FileId;

//TODO: subdomains support
class UrlGeneratorImpl implements UrlGenerator
{
    private $pathGenerators;
    private $protocol;
    private $host;
    private $path;

    public function __construct(array $pathGenerators, $host, $path, $protocol)
    {
        $this->pathGenerators = $pathGenerators;
        $this->host = $host;
        $this->path = $path;
        $this->protocol = $protocol;
    }


    public function generate(FileId $fileId, $fileType)
    {
        $pathGenerator = $this->pathGenerators[$fileType];

        $path = $pathGenerator->generate($fileId);

        return sprintf('%s://%s%s/%s', $this->protocol, $this->host, $this->path, $path);
    }
}