<?php


namespace Floppy\Client\Security;

/**
 * Clear credentials when only credential attribute is "id". This class can be used for UrlGenerator to ignore empty
 * credential attributes, because UrlGeneratorImpl automatically adds "id" (file id) attribute, even when attributes were
 * originally empty
 */
class IgnoreIdCredentialsGenerator implements CredentialsGenerator
{
    private $wrappedGenerator;

    public function __construct(CredentialsGenerator $wrappedGenerator)
    {
        $this->wrappedGenerator = $wrappedGenerator;
    }


    public function generateCredentials(array $credentialAttributes = array())
    {
        return $this->wrappedGenerator->generateCredentials(count($credentialAttributes) === 1 && isset($credentialAttributes['id']) ? array() : $credentialAttributes);
    }
}