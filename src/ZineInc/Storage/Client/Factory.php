<?php


namespace ZineInc\Storage\Client;


use Buzz\Client\Curl;
use ZineInc\Storage\Common\ChecksumCheckerImpl;
use ZineInc\Storage\Common\FileHandler\FilePathGenerator;
use ZineInc\Storage\Common\FileHandler\ImagePathGenerator;
use ZineInc\Storage\Common\Storage\FilepathChoosingStrategyImpl;

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
            ), new Url($container['host'], $container['path'], $container['protocol']), $container['urlGenerator.hostResolver']);
        };

        $container['urlGenerator.image'] = function($container){
            return new ImagePathGenerator($container['checksumChecker'], $container['filepathChoosingStrategy']);
        };
        $container['checksumChecker'] = function($container){
            return new ChecksumCheckerImpl($container['secretKey'], $container['checksumChecker.length']);
        };
        $container['checksumChecker.length'] = 5;
        $container['filepathChoosingStrategy'] = function ($container) {
            return new FilepathChoosingStrategyImpl();
        };
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

    public function createStorageClient(array $options = array())
    {
        $container = new \Pimple();

        $container['storageClient'] = function($container){
            return new StorageClient($container['storageClient.uploader']);
        };
        $container['storageClient.uploader'] = function($container){
            return new BuzzFileSourceUploader($container['storageClient.uploader.buzz'], new Url($container['host'], $container['path'].'/upload', $container['protocol']), $container['storageClient.uploader.fileKey']);
        };
        $container['storageClient.uploader.fileKey'] = 'file';
        $container['protocol'] = 'http';
        $container['path'] = '';
        $container['storageClient.uploader.buzz'] = function($container){
            return new Curl();
        };

        $this->mergeContainer($container, $options);

        return $container['storageClient'];
    }

    private function mergeContainer(\Pimple $container, array $options)
    {
        $options = $options + $this->options;

        foreach($options as $name => $value) {
            $container[$name] = $value;
        }
    }
} 