<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class NewsJson
 */
class NewsJson
{
    /**
     * @var stdClass
     */
    public $timeline;

    /**
     * @param string $cHeadline
     * @param string $cText
     * @param string $cStartDate
     * @param array $oNews_arr
     */
    public function __construct($cHeadline, $cText, $cStartDate, array $oNews_arr)
    {
        $this->timeline            = new stdClass();
        $this->timeline->headline  = $cHeadline;
        $this->timeline->type      = 'default';
        $this->timeline->text      = $cText;
        $this->timeline->startDate = $cStartDate;
        $this->timeline->date      = [];

        if (count($oNews_arr) > 0) {
            $shopURL = Shop::getURL() . '/';
            foreach ($oNews_arr as $oNews) {
                $oNewsItem = new NewsItem(
                    $oNews->cBetreff,
                    $oNews->cText,
                    $oNews->dGueltigVonJS,
                    $shopURL . $oNews->cUrl
                );

                if ($this->checkMedia($oNews->cVorschauText)) {
                    $oNewsItemAsset = new NewsItemAsset($oNews->cVorschauText);
                    $oNewsItem->addAsset($oNewsItemAsset);
                } else {
                    $oNewsItem->text = $oNews->cVorschauText .
                        '<br /><a href="' . $oNews->cUrl . '" class="btn">Mehr...</a>';
                }

                $this->timeline->date[] = $oNewsItem;
            }
        }
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode(StringHandler::utf8_convert_recursive($this));
    }

    /**
     * @param string $cMediaLink
     * @return bool
     */
    protected function checkMedia($cMediaLink): bool
    {
        $cMedia_arr = [
            'youtube.com/watch?v=',
            'vimeo.com/',
            'twitter.com/',
            'maps.google.de/maps',
            'flickr.com/photos',
            'dailymotion.com/video',
            'wikipedia.org/wiki',
            'soundcloud.com/'
        ];

        if (strlen($cMediaLink) > 3) {
            foreach ($cMedia_arr as $cMedia) {
                if (strpos($cMediaLink, $cMedia) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $options
     */
    public static function buildThumbnail($options): void
    {
        if (isset($options['filename'], $options['path'], $options['isdir']) && !$options['isdir']) {
            $options['thumb'] = Shop::getImageBaseURL() .
                PFAD_NEWSBILDER . "{$options['news']}/{$options['filename']}";
        }
    }
}
