<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxTrustedShopsReviews
 * @package Boxes
 */
final class BoxTrustedShopsReviews extends AbstractBox
{
    /**
     * @var string
     */
    private $imagePath = '';

    /**
     * @var \stdClass|null
     */
    private $stats;

    /**
     * @var string
     */
    private $imageURL = '';

    /**
     * @var string
     */
    private $backGroundImageURL = '';

    /**
     * BoxDirectPurchase constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('oStatistik', 'Stats');
        parent::addMapping('cBildPfadURL', 'ImageURL');
        parent::addMapping('cBildPfad', 'ImagePath');
        $this->setShow(false);
        $cValidSprachISO_arr = ['de', 'en', 'fr', 'es', 'pl'];
        $langCode            = \StringHandler::convertISO2ISO639(\Shop::getLanguageCode());
        if ($config['trustedshops']['trustedshops_nutzen'] === 'Y' && \in_array($langCode, $cValidSprachISO_arr, true)) {
            $ts       = new \TrustedShops(-1, $langCode);
            $tsRating = $ts->holeKundenbewertungsstatus($langCode);
            if (isset($tsRating->cTSID) && (int)$tsRating->nStatus === 1 && \strlen($tsRating->cTSID) > 0) {
                $localizedURLs = [
                    'de' => 'https://www.trustedshops.com/bewertung/info_' . $tsRating->cTSID . '.html',
                    'en' => 'https://www.trustedshops.com/buyerrating/info_' . $tsRating->cTSID . '.html',
                    'fr' => 'https://www.trustedshops.com/evaluation/info_' . $tsRating->cTSID . '.html',
                    'es' => 'https://www.trustedshops.com/evaluacion/info_' . $tsRating->cTSID . '.html',
                    'pl' => ''
                ];
                $this->setShow(true);
                if (!$this->cachecheck($filename = $tsRating->cTSID . '.gif', 10800)) {
                    if (!$ts::ladeKundenbewertungsWidgetNeu($filename)) {
                        $this->setShow(false);
                    }
                    // Prüft alle X Stunden ob ein Zertifikat noch gültig ist
                    $ts->pruefeZertifikat($langCode);
                }
                $this->setImagePath(\Shop::getImageBaseURL() . \PFAD_GFX_TRUSTEDSHOPS . $filename);
                $this->setImageURL($localizedURLs[$langCode]);
                $this->setStats($ts->gibKundenbewertungsStatistik());
            }
        }
    }

    /**
     * @param string $filename_cache
     * @param int    $timeout
     * @return bool
     */
    private function cachecheck(string $filename_cache, int $timeout = 10800): bool
    {
        $filename_cache = PFAD_ROOT . \PFAD_GFX_TRUSTEDSHOPS . $filename_cache;

        return \file_exists($filename_cache)
            ? ((\time() - \filemtime($filename_cache)) < $timeout)
            : false;
    }

    /**
     * @return string
     */
    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    /**
     * @param string $path
     */
    public function setImagePath(string $path)
    {
        $this->imagePath = $path;
    }

    /**
     * @return null|\stdClass
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param null|\stdClass $stats
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
    }

    /**
     * @return string
     */
    public function getImageURL(): string
    {
        return $this->imageURL;
    }

    /**
     * @param string $imageURL
     */
    public function setImageURL(string $imageURL)
    {
        $this->imageURL = $imageURL;
    }
}
