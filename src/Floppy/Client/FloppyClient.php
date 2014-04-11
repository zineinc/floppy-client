<?php

namespace Floppy\Client;

use Floppy\Client\Exception\BadResponseException;
use Floppy\Client\Security\CredentialsGenerator;
use Floppy\Common\AttributesBag;
use Floppy\Common\FileId;
use \Floppy\Common\FileSource;

class FloppyClient
{
    private $uploader;
    private $credentialsGenerator;

    public function __construct(FileSourceUploader $uploader, CredentialsGenerator $credentialsGenerator)
    {
        $this->uploader = $uploader;
        $this->credentialsGenerator = $credentialsGenerator;
    }

    /**
     * @param FileSource $fileSource File source to upload
     *
     * @return \Floppy\Common\FileId uploaded file id
     *
     * @throws Exception\IOException
     */
    public function upload(FileSource $fileSource, array $credentials = null)
    {
        $extraFields = $credentials !== null ? $this->credentialsGenerator->generateCredentials($credentials) : null;
        $response = $this->uploader->upload($fileSource, $extraFields);

        $res = @json_decode($response, true);

        if($res === false || !isset($res['attributes']))
        {
            throw new BadResponseException('expecting json response with attributes property, actual response: '.$response);
        }

        return new FileId(isset($res['attributes']['id']) ? $res['attributes']['id'] : null, (array) $res['attributes']);
    }
}