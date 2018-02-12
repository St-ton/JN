<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class NewsItemAsset
 */
class NewsItemAsset
{
    /**
     * @var string
     */
    public $media;

    /**
     * @var string
     */
    public $thumbnail;

    /**
     * @var string
     */
    public $credit;

    /**
     * @var string
     */
    public $caption;

    /**
     * @param string $cMedia
     * @param string $cThumbnail
     * @param string $cCredit
     * @param string $cCaption
     */
    public function __construct($cMedia, $cThumbnail = '', $cCredit = '', $cCaption = '')
    {
        $this->media     = $cMedia;
        $this->thumbnail = $cThumbnail;
        $this->credit    = $cCredit;
        $this->caption   = $cCaption;
    }
}
