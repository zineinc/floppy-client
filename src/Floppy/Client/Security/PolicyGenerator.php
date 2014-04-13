<?php


namespace Floppy\Client\Security;


use Floppy\Common\ChecksumChecker;

class PolicyGenerator implements CredentialsGenerator
{
    private $checksumChecker;

    public function __construct(ChecksumChecker $checksumChecker)
    {
        $this->checksumChecker = $checksumChecker;
    }


    public function generateCredentials(array $credentialAttributes = array())
    {
        if(!$credentialAttributes) {
            return array();
        }

        $policy = base64_encode(json_encode($credentialAttributes));
        $checksum = $this->checksumChecker->generateChecksum($policy);

        return array(
            'policy' => $policy,
            'signature' => $checksum,
        );
    }
}