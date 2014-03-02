<?php


namespace ZineInc\Storage\Tests\Client;


use Buzz\Client\ClientInterface;
use Buzz\Exception\ClientException;
use Buzz\Message\MessageInterface;
use Buzz\Message\Request;
use Buzz\Message\RequestInterface;
use Buzz\Message\Response;
use ZineInc\Storage\Client\BuzzFileSourceUploader;
use ZineInc\Storage\Client\Url;
use ZineInc\Storage\Common\FileSource;
use ZineInc\Storage\Common\FileType;
use ZineInc\Storage\Common\Stream\InputStream;
use ZineInc\Storage\Common\Stream\IOException;

class BuzzFileSourceUploaderTest extends \PHPUnit_Framework_TestCase
{
    const FILE_KEY = 'abc';
    const FILENAME = 'somename.jpg';
    const FILE_CONTENT = 'content';
    const FILEPATH = '/some/filepath/somename.jpg';
    const RESPONSE_CONTENT = 'abcdfasfasf';

    const PROTOCOL = 'http';
    const HOST = 'localhost';
    const REQUEST_PATH = '/some/path';

    /**
     * @test
     */
    public function fileSourceOk_successfullRequestSending_statusCodeOk_returnResponse()
    {
        //given

        $fileSource = $this->createFileSource();
        $client = new BuzzFileSourceUploaderTest_Client(self::RESPONSE_CONTENT, 200, array($this, 'assertRequest'));
        $uploader = $this->createUploader($client);

        //when

        $response = $uploader->upload($fileSource);

        //then

        $this->assertEquals(self::RESPONSE_CONTENT, $response);
    }


    /**
     * @test
     * @expectedException ZineInc\Storage\Client\Exception\BadResponseException
     */
    public function fileSourceOk_successfullRequestSending_badStatusCode_throwException()
    {
        //given

        $fileSource = $this->createFileSource();
        $client = new BuzzFileSourceUploaderTest_Client(self::RESPONSE_CONTENT, 400, array($this, 'assertRequest'));
        $uploader = $this->createUploader($client);

        //when

        $uploader->upload($fileSource);
    }

    /**
     * @test
     * @expectedException ZineInc\Storage\Client\Exception\IOException
     */
    public function fileSourceOk_clientException_throwException()
    {
        //given

        $fileSource = $this->createFileSource();
        $client = $this->getMock('Buzz\Client\ClientInterface');
        $uploader = $this->createUploader($client);

        $client->expects($this->once())
            ->method('send')
            ->will($this->throwException(new ClientException()));

        //when

        $uploader->upload($fileSource);
    }

    public function assertRequest($request)
    {
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $request);

        $this->assertEquals(self::PROTOCOL.'://'.self::HOST, $request->getHost());
        $this->assertEquals(self::REQUEST_PATH, $request->getResource());

        $fields = $request->getFields();
        $this->assertTrue(isset($fields[self::FILE_KEY]));
        $field = $fields[self::FILE_KEY];
        $this->assertInstanceOf('Buzz\Message\Form\FormUpload', $field);
        $this->assertEquals(self::FILE_KEY, $field->getName());
        $this->assertEquals(basename(self::FILEPATH), $field->getFilename());
        $this->assertEquals(basename(self::FILE_CONTENT), $field->getContent());
    }

    /**
     * @return FileSource
     */
    private function createFileSource()
    {
        return new FileSource(new BuzzFileSourceUploaderTest_Stream(self::FILE_CONTENT, self::FILEPATH), new FileType('a/a', 'jpg'));
    }

    /**
     * @param $client
     *
     * @return BuzzFileSourceUploader
     */
    private function createUploader($client)
    {
        return new BuzzFileSourceUploader($client, new Url(self::HOST, self::REQUEST_PATH, self::PROTOCOL), self::FILE_KEY);
    }
}

class BuzzFileSourceUploaderTest_Client implements ClientInterface
{
    private $responseStatusCode;
    private $responseContent;
    private $requestAssertion;

    public function __construct($responseContent, $responseStatusCode, $requestAssertion)
    {
        $this->responseContent = $responseContent;
        $this->responseStatusCode = $responseStatusCode;
        $this->requestAssertion = $requestAssertion;
    }

    public function send(RequestInterface $request, MessageInterface $response)
    {
        call_user_func($this->requestAssertion, $request);

        $response->addHeader('HTTP/1.1 '.$this->responseStatusCode.' OK');
        $response->setContent($this->responseContent);
    }
}

class BuzzFileSourceUploaderTest_Stream implements InputStream
{
    private $bytes;
    private $filepath;

    public function __construct($bytes, $filepath)
    {
        $this->bytes = $bytes;
        $this->filepath = $filepath;
    }

    public function close()
    {
    }

    public function read()
    {
        return $this->bytes;
    }

    public function filepath()
    {
        return $this->filepath;
    }
}