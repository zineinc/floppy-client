<?php


namespace Floppy\Tests\Client\Security;


use Floppy\Client\Security\PolicyGenerator;

class PolicyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    const CHECKSUM = 'some checksum';
    private $checksumChecker;
    private $policyGenerator;

    protected function setUp()
    {
        $this->checksumChecker = $this->getMock('Floppy\Common\ChecksumChecker');
        $this->policyGenerator = new PolicyGenerator($this->checksumChecker);
    }

    /**
     * @test
     */
    public function testCredentialsGeneration_encodePolicyAndGenerateSignature()
    {
        //given

        $policy = array('some' => 'policy');
        $expectedPolicy = base64_encode(json_encode($policy));

        $this->checksumChecker->expects($this->any())
            ->method('generateChecksum')
            ->with($expectedPolicy)
            ->will($this->returnValue(self::CHECKSUM));

        //when

        $actualData = $this->policyGenerator->generateCredentials($policy);

        //then

        $this->assertTrue(isset($actualData['policy'], $actualData['signature']), 'policy or/and signature doesn\'t exist');
        $this->assertEquals($expectedPolicy, $actualData['policy']);
        $this->assertEquals(self::CHECKSUM, $actualData['signature']);
    }


}
 