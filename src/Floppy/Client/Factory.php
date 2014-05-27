<?php


namespace Floppy\Client;


use Buzz\Client\Curl;
use Floppy\Client\Security\IgnoreIdCredentialsGenerator;
use Floppy\Client\Security\PolicyGenerator;
use Floppy\Common\ChecksumCheckerImpl;
use Floppy\Common\FileHandler\FilePathGenerator;
use Floppy\Common\FileHandler\ImagePathGenerator;
use Floppy\Common\Storage\FilepathChoosingStrategyImpl;
use Floppy\Common\Storage\PrefixedFilepathChoosingStrategy;

//TODO: obsługa prefixu
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
            ), new Url($container['host'], $container['path'], $container['protocol']), $container['urlGenerator.hostResolver'], $container['credentialsGenerator'],
            $container['urlGenerator.fileTypeGuesser']);
        };

        $container['credentialsGenerator'] = function($container){
            return new IgnoreIdCredentialsGenerator(new PolicyGenerator($container['checksumChecker']));
        };

        $container['urlGenerator.image'] = function($container){
            return new ImagePathGenerator($container['checksumChecker'], $container['filepathChoosingStrategy']);
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
            return new PrefixedFilepathChoosingStrategy(new FilepathChoosingStrategyImpl(), $container['filepathChoosingStrategy.prefix']);
        };
        $container['filepathChoosingStrategy.prefix'] = '';
        $container['path'] = '';
        $container['protocol'] = 'http';

        $container['urlGenerator.file'] = function($container){
            return new FilePathGenerator($container['checksumChecker'], $container['filepathChoosingStrategy']);
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
            return new BuzzFileSourceUploader($container['floppy.uploader.buzz'], new Url($container['host'], $container['path'].'/upload', $container['protocol']), $container['floppy.uploader.fileKey']);
        };
        $container['credentialsGenerator'] = function($container){
            return new PolicyGenerator($container['checksumChecker']);
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
        return $container;
    }
} 