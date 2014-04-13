<?php


namespace Floppy\Tests\Client;


use Floppy\Client\HostResolver;
use Floppy\Client\Security\CredentialsGenerator;
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
    private $credentialsGenerator;

    private $generatedCredentials = array(
        'credentials' => 'some value',
    );

    protected function setUp()
    {
        $this->pathGenerator = $this->getMock('Floppy\Common\FileHandler\PathGenerator');
        $this->credentialsGenerator = new Stub\FakeCredentialsGenerator($this->generatedCredentials);

        $this->generator = new UrlGeneratorImpl(array(
            self::VALID_FILE_TYPE => $this->pathGenerator,
        ), new Url(self::HOST, self::PATH, self::PROTOCOL), new UrlGeneratorImplTest_HostResolver(self::SUBDOMAIN), $this->credentialsGenerator);
    }

    /**
     * @test
     * @dataProvider credentialsProvider
     */
    public function testUrlGeneration($providedCredentials)
    {
        //given

        $fileId = $this->createFileId();
        $path = 'a/b/c.jpg';
        $this->pathGenerator->expects($this->atLeastOnce())
            ->method('generate')
            ->with($fileId)
            ->will($this->returnValue($path));

        //when

        $url = $this->generator->generate($fileId, self::VALID_FILE_TYPE, $providedCredentials);

        //then

        $this->verifyMockObjects();
        $expectedUrl = self::PROTOCOL.'://'.self::SUBDOMAIN.'.'.self::HOST.self::PATH.'/'.$path.($providedCredentials ? '?'.http_build_query($this->generatedCredentials) : '');
        $this->assertEquals($expectedUrl, $url);
    }

    public function credentialsProvider()
    {
        return array(
            array(
                array('some' => 'value'),
            ),
        );
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