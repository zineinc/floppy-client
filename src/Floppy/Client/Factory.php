<?php


namespace Floppy\Client;


use Buzz\Client\Curl;
use Floppy\Client\Security\DefaultCredentialsGenerator;
use Floppy\Client\Security\IgnoreIdCredentialsGenerator;
use Floppy\Client\Security\PolicyGenerator;
use Floppy\Common\ChecksumCheckerImpl;
use Floppy\Common\FileHandler\Base64PathGenerator;
use Floppy\Common\FileHandler\FilenameFileInfoAssembler;
use Floppy\Common\FileHandler\QueryStringFileInfoAssembler;
use Floppy\Common\Storage\FilepathChoosingStrategyImpl;

class Factory
{
    private $options;

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function createUrlGenerator(array $options = array())
    {
        $container = new \Pimple();

        $container['urlGenerator'] = function($container){
            return new UrlGeneratorImpl(array(
                    'image' => $container['urlGenerator.image'],
                    'file' => $container['urlGenerator.file'],
                ), new Url($container['host'], $container['path'], $container['protocol']),
                $container['urlGenerator.hostResolver'],
                $container['credentialsGenerator'],
                $container['urlGenerator.fileTypeGuesser']
            );
        };

        $container['credentialsGenerator'] = function($container){
            return new DefaultCredentialsGenerator(
                new IgnoreIdCredentialsGenerator(
                    new PolicyGenerator($container['checksumChecker'])
                ),
                $container['credentialsGenerator.defaultCredentials']
            );
        };

        $container['urlGenerator.image'] = function($container){
            return new Base64PathGenerator(
                $container['checksumChecker'],
                new QueryStringFileInfoAssembler($container['filepathChoosingStrategy'])
            );
        };

        $container['urlGenerator.fileTypeGuesser'] = function($container){
            return new FileTypeGuesser($container['urlGenerator.extensions']);
        };

        $container['urlGenerator.extensions'] = function($container){
            return array('image' => $container['urlGenerator.image.extensions']);
        };

        $container['urlGenerator.image.extensions'] = function($container){
            return array('png', 'jpeg', 'gif', 'jpg');
        };

        $this->sharedDefinitions($container);

        $container['filepathChoosingStrategy'] = function ($container) {
            return new FilepathChoosingStrategyImpl();
        };
        $container['path'] = '';
        $container['protocol'] = 'http';

        $container['urlGenerator.file'] = function($container){
            return new Base64PathGenerator(
                $container['checksumChecker'],
                new FilenameFileInfoAssembler($container['filepathChoosingStrategy']),
                array(
                    'name' => $container['urlGenerator.file.filter.name']
                )
            );
        };
        $container['urlGenerator.file.filter.name'] = function($container){
            return new UrlifyFilter();
        };
        $container['urlGenerator.hostResolver'] = function(){
            return new EmptyHostResolver();
        };

        $this->mergeContainer($container, $options);

        return $container['urlGenerator'];
    }

    public function createFloppyClient(array $options = array())
    {
        $container = new \Pimple();

        $container['floppy'] = function($container){
            return new FloppyClient($container['floppy.uploader'], $container['credentialsGenerator']);
        };
        $container['floppy.uploader'] = function($container){
            return new BuzzFileSourceUploader(
                $container['floppy.uploader.buzz'],
                new Url($container['host'], $container['path'].'/upload', $container['protocol']),
                $container['floppy.uploader.fileKey']
            );
        };
        $container['credentialsGenerator'] = function($container){
            return new DefaultCredentialsGenerator(
                new PolicyGenerator($container['checksumChecker']),
                $container['credentialsGenerator.defaultCredentials']
            );
        };

        $this->sharedDefinitions($container);

        $container['floppy.uploader.fileKey'] = 'file';
        $container['protocol'] = 'http';
        $container['path'] = '';
        $container['floppy.uploader.buzz'] = function($container){
            return new Curl();
        };

        $this->mergeContainer($container, $options);

        return $container['floppy'];
    }

    private function mergeContainer(\Pimple $container, array $options)
    {
        $options = $options + $this->options;

        foreach($options as $name => $value) {
            $container[$name] = $value;
        }
    }

    private function sharedDefinitions($container)
    {
        $container['checksumChecker'] = function ($container) {
            return new ChecksumCheckerImpl($container['secretKey'], $container['checksumChecker.length']);
        };
        $container['checksumChecker.length'] = -1;
        $container['credentialsGenerator.defaultCredentials'] = array();
        return $container;
    }
} 