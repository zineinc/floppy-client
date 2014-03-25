<?php


namespace Floppy\Tests\Client;


use Floppy\Client\HostResolver;
use Floppy\Client\Url;
use Floppy\Client\UrlGeneratorImpl;
use Floppy\Common\FileId;

class UrlGeneratorImplTest extends \PHPUnit_Framework_TestCase
{
    const PROTOCOL = 'https';
    const HOST = 'some-super-host.com';
    const SUBDOMAIN = 'a';
    const PATH = '/some/path';
    const VALID_FILE_TYPE = 'file';
    const INVALID_FILE_TYPE = 'file2';

    private $generator;
    private $pathGenerator;

    protected function setUp()
    {
        $this->pathGenerator = $this->getMock('Floppy\Common\FileHandler\PathGenerator');

        $this->generator = new UrlGeneratorImpl(array(
            self::VALID_FILE_TYPE => $this->pathGenerator,
        ), new Url(self::HOST, self::PATH, self::PROTOCOL), new UrlGeneratorImplTest_HostResolver(self::SUBDOMAIN));
    }

    /**
     * @test
     */
    public function testUrlGeneration()
    {
        //given

        $fileId = $this->createFileId();
        $path = 'a/b/c.jpg';
        $this->pathGenerator->expects($this->atLeastOnce())
            ->method('generate')
            ->with($fileId)
            ->will($this->returnValue($path));

        //when

        $url = $this->generator->generate($fileId, self::VALID_FILE_TYPE);

        //then

        $this->verifyMockObjects();
        $expectedUrl = self::PROTOCOL.'://'.self::SUBDOMAIN.'.'.self::HOST.self::PATH.'/'.$path;
        $this->assertEquals($expectedUrl, $url);
    }

    /**
     * @test
     * @expectedException Floppy\Client\Exception\InvalidArgumentException
     */
    public function fileTypeDoesntExist_throwInvalidArgEx()
    {
        $this->generator->generate($this->createFileId(), self::INVALID_FILE_TYPE);
    }

    /**
     * @return FileId
     */
    private function createFileId()
    {
        return new FileId('some.jpg', array('a' => 'b'));
    }
}

class UrlGeneratorImplTest_HostResolver implements HostResolver
{
    private $subdomain;

    public function __construct($subdomain)
    {
        $this->subdomain = $subdomain;
    }

    public function resolveHost($host, FileId $fileId, $fileType)
    {
        return $this->subdomain.'.'.$host;
    }
}