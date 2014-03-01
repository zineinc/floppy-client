<?php


namespace ZineInc\Storage\Tests\Client;


use ZineInc\Storage\Client\StorageClient;
use ZineInc\Storage\Common\AttributesBag;
use ZineInc\Storage\Common\FileSource;
use ZineInc\Storage\Common\FileType;
use ZineInc\Storage\Common\Stream\StringInputStream;

class StorageClientTest extends \PHPUnit_Framework_TestCase
{
    private $storageClient;
    private $uploader;

    protected function setUp()
    {
        $this->uploader = $this->getMock('ZineInc\Storage\Client\FileSourceUploader');
        $this->storageClient = new StorageClient($this->uploader);
    }

    /**
     * @test
     */
    public function successfullUpload_parseAttributes()
    {
        //given

        $fileSource = $this->createFileSource();
        $expectedAttributes = array('some' => 'value');
        $response = json_encode(array('code' => 200, 'attributes' => $expectedAttributes));
        $this->expectsUpload($fileSource, $response);

        //when

        $attrs = $this->storageClient->upload($fileSource);

        //then

        $this->verifyMockObjects();
        $this->assertEquals(new AttributesBag($expectedAttributes), $attrs);
    }

    /**
     * @test
     * @expectedException ZineInc\Storage\Client\Exception\BadResponseException
     */
    public function malformedResponse_throwException()
    {
        //given

        $fileSource = $this->createFileSource();
        $this->expectsUpload($fileSource, 'some bad response');

        //when

        $this->storageClient->upload($fileSource);
    }

    /**
     * @param $fileSource
     * @param $response
     */
    private function expectsUpload($fileSource, $response)
    {
        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($fileSource)
            ->will($this->returnValue($response));
    }

    /**
     * @return FileSource
     */
    private function createFileSource()
    {
        return new FileSource(new StringInputStream(''), new FileType('a/a', 'e'));
    }
}
 