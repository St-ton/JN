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
     * @param array  $newsItems
     */
    public function __construct($cHeadline, $cText, $cStartDate, array $newsItems)
    {
        $this->timeline            = new stdClass();
        $this->timeline->headline  = $cHeadline;
        $this->timeline->type      = 'default';
        $this->timeline->text      = $cText;
        $this->timeline->startDate = $cStartDate;
        $this->timeline->date      = [];

        if (count($newsItems) > 0) {
            $shopURL = Shop::getURL() . '/';
            foreach ($newsItems as $item) {
                $newsItem = new NewsItem(
                    $item->cBetreff,
                    $item->cText,
                    $item->dGueltigVonJS,
                    $shopURL . $item->cUrl
                );

                if ($this->checkMedia($item->cVorschauText)) {
                    $oNewsItemAsset = new NewsItemAsset($item->cVorschauText);
                    $newsItem->addAsset($oNewsItemAsset);
                } else {
                    $newsItem->text = $item->cVorschauText .
                        '<br /><a href="' . $item->cUrl . '" class="btn">Mehr...</a>';
                }

                $this->timeline->date[] = $newsItem;
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
     * @param string $link
     * @return bool
     */
    protected function checkMedia($link): bool
    {
        $media = [
            'youtube.com/watch?v=',
            'vimeo.com/',
            'twitter.com/',
            'maps.google.de/maps',
            'flickr.com/photos',
            'dailymotion.com/video',
            'wikipedia.org/wiki',
            'soundcloud.com/'
        ];

        if (strlen($link) > 3) {
            foreach ($media as $cMedia) {
                if (mb_strpos($link, $cMedia) !== false) {
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
                PFAD_NEWSBILDER . $options['news'] . '/' . $options['filename'];
        }
    }
}
