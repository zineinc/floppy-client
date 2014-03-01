<?php


namespace ZineInc\Storage\Tests\Client;


use Buzz\Client\ClientInterface;
use Buzz\Exception\ClientException;
use Buzz\Message\MessageInterface;
use Buzz\Message\Request;
use Buzz\Message\RequestInterface;
use Buzz\Message\Response;
use ZineInc\Storage\Client\BuzzFileSourceUploader;
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

    /**
     * @test
     */
    public function fileSourceOk_successfullRequestSending_statusCodeOk_returnResponse()
    {
        //given

        $fileSource = $this->createFileSource();
        $client = new BuzzFileSourceUploaderTest_Client(self::RESPONSE_CONTENT, 200, array($this, 'assertRequest'));
        $uploader = new BuzzFileSourceUploader($client, self::FILE_KEY);

        //when

        $response = $uploader->upload($fileSource);

        //then

        $this->assertEquals(self::RESPONSE_CONTENT, $response);
    }

    /**
     * @test
     * @expectedException ZineInc\Storage\Client\BadResponseException
     */
    public function fileSourceOk_successfullRequestSending_badStatusCode_throwException()
    {
        //given

        $fileSource = $this->createFileSource();
        $client = new BuzzFileSourceUploaderTest_Client(self::RESPONSE_CONTENT, 400, array($this, 'assertRequest'));
        $uploader = new BuzzFileSourceUploader($client, self::FILE_KEY);

        //when

        $uploader->upload($fileSource);
    }

    /**
     * @test
     * @expectedException ZineInc\Storage\Client\IOException
     */
    public function fileSourceOk_clientException_throwException()
    {
        //given

        $fileSource = $this->createFileSource();
        $client = $this->getMock('Buzz\Client\ClientInterface');
        $uploader = new BuzzFileSourceUploader($client, self::FILE_KEY);

        $client->expects($this->once())
            ->method('send')
            ->will($this->throwException(new ClientException()));

        //when

        $uploader->upload($fileSource);
    }

    public function assertRequest($request)
    {
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $request);
        $fields = $request->getFields();
        $this->assertTrue(count($fields) > 0);
        $field = current($fields);
        $this->assertInstanceOf('Buzz\Message\Form\FormUpload', $field);
        $this->assertEquals(BuzzFileSourceUploaderTest::FILE_KEY, $field->getName());
        $this->assertEquals(basename(BuzzFileSourceUploaderTest::FILEPATH), $field->getFilename());
        $this->assertEquals(basename(BuzzFileSourceUploaderTest::FILE_CONTENT), $field->getContent());
    }

    /**
     * @return FileSource
     */
    private function createFileSource()
    {
        return new FileSource(new BuzzFileSourceUploaderTest_Stream(self::FILE_CONTENT, self::FILEPATH), new FileType('a/a', 'jpg'));
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