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
    public int $kFileID;

    /**
     * @var string
     */
    public string $cFilepath;

    /**
     * @var string
     */
    public string $cRelFilepath;

    /**
     * @var string
     */
    public string $cFilename;

    /**
     * @var string
     */
    public string $cDirname;

    /**
     * @var string
     */
    public string $cExtension;

    /**
     * @var int
     */
    public int $nUploaded;

    /**
     * @var int
     */
    public int $nBytes;

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
