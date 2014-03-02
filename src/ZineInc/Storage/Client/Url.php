<?php


namespace ZineInc\Storage\Client;


class Url
{
    private $protocol;
    private $host;
    private $path;

    public function __construct($host, $path, $protocol = 'http')
    {
        $this->host = $host;
        $this->path = $path;
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function protocol()
    {
        return $this->protocol;
    }

    /**
     * @return Url
     */
    public function replaceHost($host)
    {
        return new self($host, $this->path, $this->protocol);
    }

    /**
     * @return Url
     */
    public function replacePath($path)
    {
        return new self($this->host, $path, $this->protocol);
    }

    /**
     * @return Url
     */
    public function replaceProtocol($protocol)
    {
        return new self($this->host, $this->path, $protocol);
    }

    public function __toString()
    {
        return sprintf('%s://%s%s', $this->protocol, $this->host, $this->path);
    }
}