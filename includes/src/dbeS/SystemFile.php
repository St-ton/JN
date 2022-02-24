<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class SystemFile
 * @package JTL\dbeS
 */
class SystemFile
{
    /**
     * @var int
     */
    public $kFileID;

    /**
     * @var string
     */
    public $cFilepath;

    /**
     * @var string
     */
    public $cRelFilepath;

    /**
     * @var string
     */
    public $cFilename;

    /**
     * @var string
     */
    public $cDirname;

    /**
     * @var string
     */
    public $cExtension;

    /**
     * @var int
     */
    public $nUploaded;

    /**
     * @var int
     */
    public $nBytes;

    /**
     * @param int    $fileID
     * @param string $filePath
     * @param string $relFilePath
     * @param string $fileName
     * @param string $dirName
     * @param string $extension
     * @param int    $dateUploaded
     * @param int    $bytes
     */
    public function __construct(
        int $fileID,
        string $filePath,
        string $relFilePath,
        string $fileName,
        string $dirName,
        string $extension,
        int $dateUploaded,
        int $bytes
    ) {
        $this->kFileID      = $fileID;
        $this->cFilepath    = $filePath;
        $this->cRelFilepath = $relFilePath;
        $this->cFilename    = $fileName;
        $this->cDirname     = $dirName;
        $this->cExtension   = $extension;
        $this->nUploaded    = $dateUploaded;
        $this->nBytes       = $bytes;
    }
}
