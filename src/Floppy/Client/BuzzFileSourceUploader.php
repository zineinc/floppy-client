<?php


namespace Floppy\Client;

use Buzz\Client\ClientInterface;
use Buzz\Exception\ClientException;
use Buzz\Exception\RuntimeException;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Form\FormUpload;
use Buzz\Message\Response;
use Floppy\Client\Exception\BadResponseException;
use Floppy\Client\Exception\IOException;
use Floppy\Common\FileSource;

class BuzzFileSourceUploader implements FileSourceUploader
{
    private $client;
    private $fileKey;
    private $endpointUrl;

    public function __construct(ClientInterface $client, Url $endpointUrl, $fileKey = 'file')
    {
        $this->client = $client;
        $this->fileKey = $fileKey;
        $this->endpointUrl = $endpointUrl;
    }

    public function upload(FileSource $fileSource, array $extraFields = array())
    {
        $request = new FormRequest(FormRequest::METHOD_POST, $this->endpointUrl->path(), (string)$this->endpointUrl->replacePath(''));

        $formUpload = new FormUpload();
        $formUpload->setName($this->fileKey);
        $formUpload->setFilename($this->getFilename($fileSource));
        $formUpload->setContent($fileSource->content());

        $request->addFields($extraFields + array(
            $this->fileKey => $formUpload,
        ));

        try
        {
            $response = new Response();

            $this->client->send($request, $response);

            if($response->getStatusCode() > 299)
            {
                throw new BadResponseException($response->getContent());
            }
            else
            {
                return $response->getContent();
            }
        }
        catch(ClientException $e)
        {
            throw new IOException($e->getMessage(), $e->getCode(), $e);
        }
        catch(\Buzz\Exception\ExceptionInterface $e)
        {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function getFilename(FileSource $fileSource)
    {
        $filename = basename($fileSource->filepath());
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $preferredExtension = $fileSource->fileType()->extension();

        //make sure filename has valid extension. When FileSource was created from UploadedFile, filepath might be a hash
        //and extension would be meaningless
        return $preferredExtension && $preferredExtension !== $extension
            ? 'file.'.$preferredExtension
            : $filename;
    }
}