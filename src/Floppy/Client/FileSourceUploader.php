<?php


namespace Floppy\Client;


use Floppy\Common\FileSource;

interface FileSourceUploader
{
    /**
     * @param FileSource $fileSource File to upload
     * @param array|null $extraFields
     *
     * @return string response
     *
     * @throws IOException When IO error occurs or bad response is received
     * @throws RuntimeException When no IO related error occurs
     */
    public function upload(FileSource $fileSource, array $extraFields = array());
} 