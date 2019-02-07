<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace dbeS;

/**
 * Class SystemFolder
 * @package dbeS
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
     * @param string $cBaseName
     * @param string $cBasePath
     * @param array  $oSubFolders
     */
    public function __construct($cBaseName = '', $cBasePath = '', $oSubFolders = [])
    {
        $this->cBaseName   = $cBaseName;
        $this->cBasePath   = $cBasePath;
        $this->oSubFolders = $oSubFolders;
    }
}
