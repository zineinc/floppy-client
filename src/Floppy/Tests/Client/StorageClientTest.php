<?php


namespace Floppy\Tests\Client;


use Floppy\Client\StorageClient;
use Floppy\Common\AttributesBag;
use Floppy\Common\FileSource;
use Floppy\Common\FileType;
use Floppy\Common\Stream\StringInputStream;

class StorageClientTest extends \PHPUnit_Framework_TestCase
{
    private $storageClient;
    private $uploader;

    protected function setUp()
    {
        $this->uploader = $this->getMock('Floppy\Client\FileSourceUploader');
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
     * @expectedException Floppy\Client\Exception\BadResponseException
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
 