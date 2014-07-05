<?php


namespace Floppy\Tests\Client;


use Floppy\Client\FileTypeGuesser;
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

    const IMPLICIT_FILE_EXTENSION = 'jpg';

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

        $fileTypeGuesser = new FileTypeGuesser(array(
            self::VALID_FILE_TYPE => array(self::IMPLICIT_FILE_EXTENSION)
        ));

        $this->generator = new UrlGeneratorImpl(array(
            self::VALID_FILE_TYPE => $this->pathGenerator,
        ), new Url(self::HOST, self::PATH, self::PROTOCOL), new UrlGeneratorImplTest_HostResolver(self::SUBDOMAIN), $this->credentialsGenerator, $fileTypeGuesser);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testUrlGeneration($providedCredentials, $fileType)
    {
        //given

        $fileId = $this->createFileId();
        $path = 'a/b/c.jpg';
        $this->expectsPathGenerating($fileId, $path);

        //when

        $url = $this->generator->generate($fileId, $fileType, $providedCredentials);

        //then

        $this->verifyMockObjects();
        $expectedUrl = $this->hostAndPrefixPath() .'/'.$path.($providedCredentials ? '?'.http_build_query($this->generatedCredentials) : '');
        $this->assertEquals($expectedUrl, $url);
    }

    public function dataProvider()
    {
        return array(
            array(
                array('some' => 'value'),
                self::VALID_FILE_TYPE,
            ),
            array(
                array('some' => 'value'),
                null,
            )
        );
    }

    /**
     * @test
     */
    public function pathGeneratorGeneratesPathWithQueryString_mergeCredentialsWithThatQueryString()
    {
        //given

        $fileId = $this->createFileId();
        $path = 'a/b/c.jpg?attrs=someValue';
        $this->expectsPathGenerating($fileId, $path);
        $credentials = array('some' => 'value');

        //when

        $url = $this->generator->generate($fileId, null, $credentials);

        //then

        $this->verifyMockObjects();

        $expectedUrl = $this->hostAndPrefixPath().'/'.$path.'&'.http_build_query($this->generatedCredentials);
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

    /**
     * @return string
     */
    private function hostAndPrefixPath()
    {
        return self::PROTOCOL . '://' . self::SUBDOMAIN . '.' . self::HOST . self::PATH;
    }

    private function expectsPathGenerating(FileId $fileId, $path)
    {
        $this->pathGenerator->expects($this->atLeastOnce())
            ->method('generate')
            ->with($fileId)
            ->will($this->returnValue($path));
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