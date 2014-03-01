<?php

namespace ZineInc\Storage\Client;

use \ZineInc\Storage\Common\FileSource;

interface StorageClient
{
    /**
     * @param FileSource $fileSource File source to upload
     *
     * @return \ZineInc\Storage\Common\AttributesBag attributes for uploaded file
     *
     * @throws \Exception
     */
    public function upload(FileSource $fileSource);
}