<?php

namespace ZineInc\Storage\Client;

use ZineInc\Storage\Client\Exception\BadResponseException;
use ZineInc\Storage\Common\AttributesBag;
use \ZineInc\Storage\Common\FileSource;

class StorageClient
{
    private $uploader;

    public function __construct(FileSourceUploader $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * @param FileSource $fileSource File source to upload
     *
     * @return \ZineInc\Storage\Common\AttributesBag attributes for uploaded file
     *
     * @throws IOException
     */
    public function upload(FileSource $fileSource)
    {
        $response = $this->uploader->upload($fileSource);

        $res = @json_decode($response, true);

        if($res === false || !isset($res['attributes']))
        {
            throw new BadResponseException('expecting json response with attributes property, actual response: '.$response);
        }

        return new AttributesBag((array) $res['attributes']);
    }
}