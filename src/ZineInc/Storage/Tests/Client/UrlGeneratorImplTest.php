<?php


namespace ZineInc\Storage\Tests\Client;


use ZineInc\Storage\Client\HostResolver;
use ZineInc\Storage\Client\Url;
use ZineInc\Storage\Client\UrlGeneratorImpl;
use ZineInc\Storage\Common\FileId;

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
        $this->pathGenerator = $this->getMock('ZineInc\Storage\Common\FileHandler\PathGenerator');

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
     * @expectedException ZineInc\Storage\Client\Exception\InvalidArgumentException
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