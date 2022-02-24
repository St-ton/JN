<?php declare(strict_types=1);

namespace JTL\dbeS;

/**
 * Class SystemFolder
 * @package JTL\dbeS
 */
class SystemFolder
{
    /**
     * @var string
     */
    public $cBaseName;

    /**
     * @var string
     */
    public $cBasePath;

    /**
     * @var array
     */
    public $oSubFolders;

    /**
     * @param string $baseName
     * @param string $basePath
     * @param array  $subFolders
     */
    public function __construct(string $baseName = '', string $basePath = '', array $subFolders = [])
    {
        $this->cBaseName   = $baseName;
        $this->cBasePath   = $basePath;
        $this->oSubFolders = $subFolders;
    }
}
