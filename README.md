# FloppyClient

FloppyClient is a client for [FloppyServer][1] library. Before using this library you should install you instance of
[FloppyServer][1]. How to use FloppyServer you can find in [documentation][1].

FloppyClient provides two simple integration points:

- url generation for files stored on FloppyServer
- client for file upload on FloppyServer

FloppyClient is a pure client for FloppyServer, if you want to use Floppy in Symfony2 app you should be interested in
[FloppyBundle][2].

# Documentation

## ToC

* [Features](#features)
* [Usage example](#usage-example)
* [Configuration](#config)
    * [Security credentials generator](#security)
    * [File handlers](#file-handlers)
* [Detailed usage](#detailed-usage)
    * [Url generator](#url-generator)
    * [File uploading](#file-uploading)
* [License](#license)

<a name="features"></a>
## Features

FloppyClient and FloppyServer combo provides simple to use file storage server. Thanks to Floppy you won't care about
write code to upload and storage files, code to generate (on runtime or pre-store) various thumbnails for photos, code
to optimize your files etc. Application that you develop should only know where to upload file, from where the requested
file variant can be retrieve and eventually know security rules and credentials that should be provided.

StorageServer's responsibility is to store files in efficient way (on filesystem by default, but it can be for example
on cloud) and transparently preparing requested file variant - for example thumbnail in given sizes.

<a name="usage-example"></a>
## Usage example

```php

    use Floppy\Client\Factory;
    use Floppy\Common\FileSource;
    use Floppy\Common\FileId;

    //configure FloppyClient library
    $factory = new Factory(array(
        'host' => 'your-floppy-server-host',
        'secretKey' => 'your-floppy-secret-key-the-same-as-in-server',
    ));

    //create client and url generator
    $client = $factory->createFloppyClient();
    $urlGenerator = $factory->createUrlGenerator();

    //upload file
    $fileId = $client->upload(FileSource::fromFile($someSplFileInstance)); //$fileId is Floppy\Common\FileId instance

    //info about file type, file size, file mime type etc.
    $info = $fileId->info();
    $info->get('size');

    //value that you should store to be able to recreate FileId instance
    $someFileStringId = $fileId->id();
    $recreatedFileId = new FileId($someFileStringId);

    //generate url to image thumbnail
    $url = $urlGenerator->generate(
        //create identifier to concrete thumbnail
        $fileId->with([ 'thumbnail' => [ 'size' => [ 80, 80 ] ] ]) //assume uploaded file is an image
    );

    //generate url to original file
    $url = $urlGenerator->generate($fileId);
```

<a name="config"></a>
## Configuration

Fundamental FloppyClient configuration options:

* host - FloppyServer host, required option
* secretKey - secret key that have to be the same as in FloppyServer. It should be enough strong, because security depends
on its strength. 16-32 length hash should be ok.
* protocol - FloppyServer protocol (http or https) - default value: http
* path - path to FloppyServer instance, default value: empty string

<a name="security"></a>
### Security credentials generator

You can define security rules to upload or download file from Floppy. Class that is responsible to add credentials to
upload or download request is Floppy\Client\Security\CredentialsGenerator. You can pass credentials to UrlGenerator::generate
or FloppyClient::upload methods.

```php

    $urlGenerator->generate($fileId, 'image', /** credential attrs */ array('expiration' => time() + 60, 'customFiled' => 'value'));
    $client->upload($fileSource, array('file_types' => 'image'));
    
```

Default CredentialsGenerator implementation is PolicyGenerator, that cooporates with 
Floppy\Server\RequestHandler\Security\PolicyRule from FloppyServer library. Supported credential attributes:

* expiration - timestamp that defines date after that given request will expire
* file_types - array of allowed types of files (names of file handlers, not mime type!) that can be uploaded by the request,
it works only with upload request (FloppyClient::upload method)
* access - public or private (public by default) - uploaded/retrieved file should be to/from public/private storage

You can change implementation of CredentialsGenerator by passing your own implementation to Factory `credentialsGenerator` attribute.
If you want to change credentials generator you should remember to change `action.download.securityRule` and `action.upload.securityRule`
in your instance of FloppyServer too.

```php

    use Floppy\Client\Factory;
    
    $factory = new Factory(array(
        'credentialsGenerator' => function($container){
            return new CustomGenerator($container['checksumChecker']);
        }
    ));
    
    //...

```

<a name="file-handlers"></a>
### File handlers (file types)

There are two file handlers by default: **image** and **file** (other files). File handler on client side are important only for
url generator - generator should know for what file type generate url. Url generator recognize file types by extensions.
By default file is image when has given extensions: png, jpg, jpeg or gif. Files with other extensions have "file" type.
You can customize image extensions by passing `urlGenerator.image.extensions` attribute to `Factory`. You should remember
that file handlers in client side should be configured as same as on server side (you should configure 
`fileHandlers.image.extensions`, `fileHandlers.image.mimeTypes`, `fileHandlers.file.extensions`, `fileHandlers.file.mimeTypes`
attributes in your instance of FloppyServer). Example FloppyClient configuration:

```php

    use Floppy\Client\Factory;
    
    $factory = new Factory(array(
        'urlGenerator.image.extensions' => array('jpeg', 'png', 'jpg'),//gif files will not be threaten as images
    ));
    
```

<a name="detailed-usage"></a>
## Detailed usage

Create Floppy factory object example:

```php

    use Floppy\Client\Factory;

    $factory = new Factory(array(
        'host' => 'your-floppy-server-host',
        'secretKey' => 'your-floppy-secret-key-the-same-as-in-server',
        /** other options */
    ));

```

<a name="url-generator"></a>
### Url generator

You can create UrlGenerator using factory:

```php

    $urlGenerator = $factory->createUrlGenerator();

```

UrlGenerator has one method: `UrlGenerator::generate(FileId $fileId, $fileType = null, array $credentialAttributes = array())`.
First argument is **FileId** to what url you want to generate. Second argument is **file type** (it is not mime type, it is name of FileHandler
that should be used for this file). By default file type will be guessed depends on file extension, but you are able to
enforce file type. Third argument is credential attributes. More info about credential attributes you can find in
[Security credentials generator](#security) section.

As first argument you can pass FileId to original file or to proper file variant (for example thumbnail in given sizes).
The thumbnail with size 60x60 can be generated in this way:

```php

    use Floppy\Common\FileId;

    $fileId = new FileId('id-of-the-file.png');
    $url = $urlGenerator->generate($fileId->with(array(
        'thumbnail' => array(
            'size' => array(60, 60),
        ),
    ), /** explicitly file type */ 'image');

```

<a name="filters"></a>
For images there are available bunch of filters as same as in [LiipImagineBundle library][3]:

- `auto_rotate`:
    - (no options)

- `background`:
    - color (default #fff)
    - size - array( width, height )

- `crop`:
    - start - array( x, y )
    - size - array( width, height )
    
- `paste`:
    - start - array( x, y )
    - image - image name, image should be stored on FloppyServer instance by default in directory above storage.dir
    
- `relative_resize` **in this filter only one option at once can be passed!**:
    - heighten: height (in pixels)
    - widen: width (in pixels)
    - increase: number of pixels
    - scale: float number
    
- `resize`:
    - size - array( width, height )
    
- `thumbnail`:
    - mode - outbound or inset
    - size - array( width, height )
    - filter - ImageInterface::FILTER_* constants
    
- `upscale`:
    - min - array( width, height )
    
- `watermark`:
    - size - float or percent - relative size of watermark
    - position - top/center(or empty string)/bottom + left/(empty string)/right, for example topleft, center, right etc.
    - image - as same meaning as in `paste` filter

Supported attributes for `file` file type:

* name - name of downloaded file, it will be added to response Content-Disposition http header

```php

    $url = $urlGenerator->generate($fileId->with([ "name" => "Some name" ]));

```

<a name="file-uploading"></a>
### File uploading

You can create FloppyClient using factory:

```php

    $client = $factory->createFloppyClient();

```

FloppyClient has one method: `FloppyClient::upload(FileSource $fileSource, array $credentialAttributes = array())`.
Instance of `FileSource` you can create using factory method `FileSource::fromFile(\SplFileInfo $file)` or directly by
constructor.

```php

    use Floppy\Common\FileSource;
    use Floppy\Common\Stream\LazyLoadedInputStream;
    use Floppy\Common\FileType;
    
    //by factory method - it is recommended
    $fileSource = FileSource::fromFile(new \SplFileInfo('some/path'));//you can pass instance of `UploadedFile` from Symfony too
    
    //by constructor
    $fileSource = new FileSource(new LazyLoadedInputStream('some/path'), new FileType('image/jpg', 'jpg'));

```

Second parameter of upload method is credential attributes - more about it is on [Security credentials generator](#security) section.

Return type of upload method is Floppy\Common\FileId. On failure Floppy\Client\Exception\IOException is thrown. FileId
returned by upload method contains extra info about uploaded file:

* type - file type, default file types: image or file
* mime-type
* extension
* size - in bytes
* width and height - (when file is image)

This extra info is accessible by FileId::info() method. FileId has id attribute accessible by method FileId::id() - this
value you should store somewhere (database?) to be able to generate url to this file in the future.

<a name="license"></a>
### License

This project is under **MIT** license.

[1]: https://github.com/zineinc/floppy-server
[2]: https://github.com/zineinc/floppy-bundle
[3]: https://github.com/liip/LiipImagineBundle/blob/master/Resources/doc/filters.md
