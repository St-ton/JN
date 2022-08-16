<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class SystemFile
 * @package JTL\dbeS
 */
class SystemFile
{
    /**
     * @param int    $kFileID
     * @param string $cFilepath
     * @param string $cRelFilepath
     * @param string $cFilename
     * @param string $cDirname
     * @param string $cExtension
     * @param int    $nUploaded
     * @param int    $nBytes
     */
    public function __construct(
        public int    $kFileID,
        public string $cFilepath,
        public string $cRelFilepath,
        public string $cFilename,
        public string $cDirname,
        public string $cExtension,
        public int    $nUploaded,
        public int    $nBytes
    ) {
    }
}
