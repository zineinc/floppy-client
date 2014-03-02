<?php


namespace ZineInc\Storage\Client;

use Buzz\Client\ClientInterface;
use Buzz\Exception\ClientException;
use Buzz\Exception\RuntimeException;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Form\FormUpload;
use Buzz\Message\Response;
use ZineInc\Storage\Client\Exception\BadResponseException;
use ZineInc\Storage\Client\Exception\IOException;
use ZineInc\Storage\Common\FileSource;

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

    public function upload(FileSource $fileSource)
    {
        $request = new FormRequest(FormRequest::METHOD_POST, $this->endpointUrl->path(), (string)$this->endpointUrl->replacePath(''));

        $formUpload = new FormUpload();
        $formUpload->setName($this->fileKey);
        $formUpload->setFilename(basename($fileSource->filepath()));
        $formUpload->setContent($fileSource->content());

        $request->addFields(array(
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
}