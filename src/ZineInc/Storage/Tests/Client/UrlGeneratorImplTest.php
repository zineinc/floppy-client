<?php


namespace ZineInc\Storage\Tests\Client;


use ZineInc\Storage\Client\UrlGeneratorImpl;
use ZineInc\Storage\Common\FileId;

class UrlGeneratorImplTest extends \PHPUnit_Framework_TestCase
{
    const PROTOCOL = 'https';
    const HOST = 'some-super-host.com';
    const PATH = '/some/path';
    const FILE_TYPE = 'file';

    private $generator;
    private $pathGenerator;

    protected function setUp()
    {
        $this->pathGenerator = $this->getMock('ZineInc\Storage\Common\FileHandler\PathGenerator');

        $this->generator = new UrlGeneratorImpl(array(
            self::FILE_TYPE => $this->pathGenerator,
        ), self::HOST, self::PATH, self::PROTOCOL);
    }

    /**
     * @test
     */
    public function testUrlGeneration()
    {
        //given

        $fileId = new FileId('some.jpg', array('a' => 'b'));
        $path = 'a/b/c.jpg';
        $this->pathGenerator->expects($this->atLeastOnce())
            ->method('generate')
            ->with($fileId)
            ->will($this->returnValue($path));

        //when

        $url = $this->generator->generate($fileId, self::FILE_TYPE);

        //then

        $this->verifyMockObjects();
        $expectedUrl = self::PROTOCOL.'://'.self::HOST.self::PATH.'/'.$path;
        $this->assertEquals($expectedUrl, $url);
    }
}
 