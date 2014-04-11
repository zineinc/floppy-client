<?php


namespace Floppy\Tests\Client;


use Floppy\Client\FloppyClient;
use Floppy\Common\AttributesBag;
use Floppy\Common\FileId;
use Floppy\Common\FileSource;
use Floppy\Common\FileType;
use Floppy\Common\Stream\StringInputStream;
use Floppy\Tests\Client\Stub\FakeCredentialsGenerator;

class FloppyClientTest extends \PHPUnit_Framework_TestCase
{
    private $storageClient;
    private $uploader;
    private $generatedCredentials = array('some' => 'value');

    protected function setUp()
    {
        $this->uploader = $this->getMock('Floppy\Client\FileSourceUploader');
        $this->storageClient = new FloppyClient($this->uploader, new FakeCredentialsGenerator($this->generatedCredentials));
    }

    /**
     * @test
     * @dataProvider credentialsProvider
     */
    public function successfullUpload_parseAttributes(array $credentials = null)
    {
        //given

        $fileSource = $this->createFileSource();
        $expectedAttributes = array('some' => 'value', 'id' => 'someid');
        $response = json_encode(array('code' => 200, 'attributes' => $expectedAttributes));
        $this->expectsUpload($fileSource, $response, $credentials ? $this->generatedCredentials : null);

        //when

        $fileId = $this->storageClient->upload($fileSource, $credentials);

        //then

        $this->verifyMockObjects();
        $this->assertEquals(new FileId($expectedAttributes['id'], $expectedAttributes), $fileId);
    }

    public function credentialsProvider()
    {
        return array(
            array(null),
            array(
                array('extra' => 'value'),
            ),
        );
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
    private function expectsUpload($fileSource, $response, array $extraFields = null)
    {
        $this->uploader->expects($this->once())
            ->method('upload')
            ->with($fileSource, $extraFields)
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
 