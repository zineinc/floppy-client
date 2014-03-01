<?php


namespace ZineInc\Storage\Tests\Client;


use ZineInc\Storage\Client\SubdomainRotationHostResolver;
use ZineInc\Storage\Common\FileId;

class SubdomainRotationHostResolverTest extends \PHPUnit_Framework_TestCase
{
    const SUBDOMAINS_LIMIT = 5;
    const HOST = 'host.com';
    const FILE_TYPE = 'type';

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function testHostResolving($subdomainDelimiter)
    {
        //given

        $resolver = new SubdomainRotationHostResolver(self::SUBDOMAINS_LIMIT, $subdomainDelimiter);

        //when

        $fileId = new FileId('some.id', array());
        $resolvedHost1 = $resolver->resolveHost(self::HOST, $fileId, self::FILE_TYPE);
        $resolvedHost2 = $resolver->resolveHost(self::HOST, $fileId, self::FILE_TYPE);

        //then

        $this->assertEquals($resolvedHost1, $resolvedHost2, 'for the same fileId resolved hosts should be equal');
        $this->assertRegExp('/^'.self::FILE_TYPE.'[1-'.self::SUBDOMAINS_LIMIT.']'.$subdomainDelimiter.self::HOST.'$/', $resolvedHost1);
    }

    public function dataProvider()
    {
        return array(
            array('.'),
            array('-'),
        );
    }
}
 