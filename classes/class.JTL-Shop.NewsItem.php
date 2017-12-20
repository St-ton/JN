<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class NewsItem
 */
class NewsItem
{
    /**
     * @var string
     */
    public $startDate;

    /**
     * @var string
     */
    public $endDate;

    /**
     * @var string
     */
    public $headline;

    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $tag;

    /**
     * @var mixed
     */
    public $asset;

    /**
     * @param string $cHeadline
     * @param string $cText
     * @param string $cStartDate
     * @param string $cUrl
     * @param string $cTag
     * @param string $cEndDate
     */
    public function __construct($cHeadline, $cText, $cStartDate, $cUrl, $cTag = '', $cEndDate = '')
    {
        $this->headline  = $cHeadline;
        $this->text      = $cText;
        $this->tag       = $cTag;
        $this->url       = $cUrl;
        $this->startDate = $cStartDate;
        $this->endDate   = $cEndDate;
    }

    /**
     * @param mixed $oAsset
     * @return $this
     */
    public function addAsset($oAsset)
    {
        $this->asset = $oAsset;

        return $this;
    }
}
