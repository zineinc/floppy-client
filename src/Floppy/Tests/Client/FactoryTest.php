<?php


namespace Floppy\Tests\Client;


use Floppy\Client\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function passGlobalOptionsFromConstructor_useGlobalOptions()
    {
        //given

        $options = array('host' => 'host', 'secretKey' => 'abc');
        $factory = new Factory($options);

        //when

        $generator = $factory->createUrlGenerator();
        $client = $factory->createStorageClient();

        //then

        $this->assertNotNull($generator);
        $this->assertNotNull($client);
    }

    /**
     * @test
     */
    public function passLocalOptions_useThisOptions()
    {
        //given

        $factory = new Factory();

        //when

        $generator = $factory->createUrlGenerator(array('host' => 'host.com', 'secretKey' => 'abc'));
        $client = $factory->createStorageClient(array('host' => 'host.com'));

        //then

        $this->assertNotNull($generator);
        $this->assertNotNull($client);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createUrlGenerator_requiredOptionsMissing_throwEx()
    {
        //given

        $factory = new Factory();

        //when

        $factory->createUrlGenerator();
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createStorageClient_requiredOptionsMissing_throwEx()
    {
        //given

        $factory = new Factory();

        //when

        $factory->createStorageClient();
    }
}
 