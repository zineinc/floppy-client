<?php


namespace Floppy\Client\Security;


interface CredentialsGenerator
{
    public function generateCredentials(array $credentialAttributes = array());
}