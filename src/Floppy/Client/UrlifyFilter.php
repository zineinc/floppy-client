<?php


namespace Floppy\Client;


class UrlifyFilter
{
    public function __invoke($value)
    {
        return \URLify::filter($value);
    }
} 