<?php


namespace Floppy\Client\Security;

/**
 * CredentialsGenerator that is able to provide customizable default credentials when empty credentials are passed
 */
class DefaultCredentialsGenerator implements CredentialsGenerator
{
    private $wrappedGenerator;
    private $defaultCredentials = array();

    public function __construct(CredentialsGenerator $wrappedGenerator, array $defaultCredentials)
    {
        $this->wrappedGenerator = $wrappedGenerator;
        $this->defaultCredentials = $defaultCredentials;
    }

    public function generateCredentials(array $credentialAttributes = array())
    {
        $credentialAttributes = $this->areCredentialsEmpty($credentialAttributes) ? $this->getDefaultCredentials() + $credentialAttributes : $credentialAttributes;

        return $this->wrappedGenerator->generateCredentials($credentialAttributes);
    }

    private function getDefaultCredentials()
    {
        $credentials = $this->defaultCredentials;

        if(isset($credentials['expiration'])) {
            $credentials['expiration'] += time();
        }

        return $credentials;
    }

    private function areCredentialsEmpty(array $credentialAttributes)
    {
        return !$credentialAttributes || count($credentialAttributes) === 1 && isset($credentialAttributes['id']);
    }
}