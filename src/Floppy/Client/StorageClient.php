<?php

namespace Floppy\Client;

use Floppy\Client\Exception\BadResponseException;
use Floppy\Common\AttributesBag;
use Floppy\Common\FileId;
use \Floppy\Common\FileSource;

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
     * @return \Floppy\Common\FileId uploaded file id
     *
     * @throws Exception\IOException
     */
    public function upload(FileSource $fileSource)
    {
        $response = $this->uploader->upload($fileSource);

        $res = @json_decode($response, true);

        if($res === false || !isset($res['attributes']))
        {
            throw new BadResponseException('expecting json response with attributes property, actual response: '.$response);
        }

        return new FileId(isset($res['attributes']['id']) ? $res['attributes']['id'] : null, (array) $res['attributes']);
    }
}