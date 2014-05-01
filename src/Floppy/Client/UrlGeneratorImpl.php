<?php


namespace Floppy\Client;

use Floppy\Client\Exception\InvalidArgumentException;
use Floppy\Client\Security\CredentialsGenerator;
use Floppy\Common\FileId;

class UrlGeneratorImpl implements UrlGenerator
{
    private $pathGenerators;
    private $hostResolver;
    private $endpointUrl;
    private $credentialsGenerator;
    private $fileTypeGuesser;

    public function __construct(array $pathGenerators, Url $endpointUrl, HostResolver $hostResolver, CredentialsGenerator $credentialsGenerator, FileTypeGuesser $fileTypeGuesser)
    {
        $this->pathGenerators = $pathGenerators;
        $this->hostResolver = $hostResolver;
        $this->endpointUrl = $endpointUrl;
        $this->credentialsGenerator = $credentialsGenerator;
        $this->fileTypeGuesser = $fileTypeGuesser;
    }


    public function generate(FileId $fileId, $fileType = null, array $credentialAttributes = array())
    {
        $fileType = $fileType ?: $this->fileTypeGuesser->guessFileType($fileId);

        if(!isset($this->pathGenerators[$fileType])) {
            throw new InvalidArgumentException(sprintf('File type "%s" doesn\'t exist, supported file types: %s', $fileType, implode(', ', array_keys($this->pathGenerators))));
        }

        $pathGenerator = $this->pathGenerators[$fileType];

        $path = $pathGenerator->generate($fileId);

        $url = (string) $this->endpointUrl
            ->replaceHost($this->host($fileId, $fileType))
            ->replacePath($this->endpointUrl->path().'/'.$path);

        $credentialAttributes['id'] = $fileId->id();
        $generatedCredentials = $this->credentialsGenerator->generateCredentials($credentialAttributes);
        if($generatedCredentials) {
            $qs = '?'.http_build_query($generatedCredentials);
            $url .= $qs;
        }

        return $url;
    }

    private function host(FileId $fileId, $fileType)
    {
        return $this->hostResolver->resolveHost($this->endpointUrl->host(), $fileId, $fileType);
    }
}