<?php


namespace ZineInc\Storage\Client;

use Buzz\Client\ClientInterface;
use Buzz\Exception\ClientException;
use Buzz\Exception\RuntimeException;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Form\FormUpload;
use Buzz\Message\Response;
use ZineInc\Storage\Common\FileSource;

class BuzzFileSourceUploader implements FileSourceUploader
{
    private $client;
    private $fileKey;

    public function __construct(ClientInterface $client, $fileKey = 'file')
    {
        $this->client = $client;
        $this->fileKey = $fileKey;
    }

    public function upload(FileSource $fileSource)
    {
        $request = new FormRequest(FormRequest::METHOD_POST, '/', '');

        $formUpload = new FormUpload();
        $formUpload->setName($this->fileKey);
        $formUpload->setFilename(basename($fileSource->filepath()));
        $formUpload->setContent($fileSource->content());

        $request->addFields(array(
            $formUpload
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
}