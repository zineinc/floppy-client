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


    public function generateCredentials(array $credentials)
    {
        $policy = base64_encode(json_encode($credentials));
        $checksum = $this->checksumChecker->generateChecksum($policy);

        return array(
            'policy' => $policy,
            'signature' => $checksum,
        );
    }
}