<?php


namespace Floppy\Client;

use Floppy\Common\FileId;

class FileTypeGuesser
{
    private $fileTypeExtensions;

    public function __construct(array $fileTypeExtensions)
    {
        $this->fileTypeExtensions = $fileTypeExtensions;
    }

    /**
     * Guess file type code
     *
     * @param FileId $fileId
     * @return string
     */
    public function guessFileType(FileId $fileId)
    {
        $extension = strtolower(\pathinfo($fileId->id(), PATHINFO_EXTENSION));

        foreach($this->fileTypeExtensions as $fileType => $extensions) {
            if(in_array($extension, $extensions)) {
                return $fileType;
            }
        }

        return 'file';
    }
} 