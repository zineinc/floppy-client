<?php
namespace Floppy\Tests\Client\Stub;

use Floppy\Client\Security\CredentialsGenerator;

class FakeCredentialsGenerator implements CredentialsGenerator
{
    private $generatedCredentials;

    public function __construct($generatedCredentials)
    {
        $this->generatedCredentials = $generatedCredentials;
    }

    public function generateCredentials(array $credentials)
    {
        return $this->generatedCredentials;
    }
}