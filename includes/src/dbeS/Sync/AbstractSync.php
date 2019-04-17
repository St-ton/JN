<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\Catalog\Product\Artikel;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\dbeS\Mapper;
use JTL\dbeS\Starter;
use JTL\Helpers\Text;
use JTL\Kampagne;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Redirect;
use JTL\Shop;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class AbstractSync
 * @package JTL\dbeS\Sync
 */
abstract class AbstractSync
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JTLCacheInterface
     */
    protected $cache;

    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * Products constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param LoggerInterface   $logger
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->cache  = $cache;
        $this->logger = $logger;
        $this->mapper = new Mapper();
    }

    /**
     * @param Starter $starter
     * @return mixed|null
     */
    abstract public function handle(Starter $starter);

    /**
     * @param array      $xml
     * @param string     $table
     * @param string     $toMap
     * @param string     $pk1
     * @param int|string $pk2
     */
    protected function updateXMLinDB($xml, $table, $toMap, $pk1, $pk2 = 0): void
    {
        $idx = $table . ' attr';
        if ((isset($xml[$table]) && \is_array($xml[$table])) || (isset($xml[$idx]) && \is_array($xml[$idx]))) {
            $this->upsert($table, $this->mapper->mapArray($xml, $table, $toMap), $pk1, $pk2);
        }
    }

    /**
     * @param string     $tablename
     * @param array      $objects
     * @param string     $pk1
     * @param string|int $pk2
     */
    protected function upsert($tablename, array $objects, $pk1, $pk2 = 0): void
    {
        foreach ($objects as $object) {
            if (isset($object->$pk1) && !$pk2 && $pk1 && $object->$pk1) {
                $this->db->delete($tablename, $pk1, $object->$pk1);
            }
            if (isset($object->$pk2) && $pk1 && $pk2 && $object->$pk1 && $object->$pk2) {
                $this->db->delete($tablename, [$pk1, $pk2], [$object->$pk1, $object->$pk2]);
            }
            $key = $this->db->insert($tablename, $object);
            if (!$key) {
                $this->logger->error('Failed upsert@' . $tablename . ' with data: ' . \print_r($object, true));
            }
        }
    }

    /**
     * @param null|stdClass $image
     * @param int            $productID
     * @param int            $imageID
     */
    protected function deleteProductImage($image = null, int $productID = 0, int $imageID = 0): void
    {
        if ($image === null && $imageID > 0) {
            $image     = $this->db->select('tartikelpict', 'kArtikelPict', $imageID);
            $productID = isset($image->kArtikel) ? (int)$image->kArtikel : 0;
        }
        // Das Bild ist eine Verknüpfung
        if (isset($image->kMainArtikelBild) && $image->kMainArtikelBild > 0 && $productID > 0) {
            // Existiert der Artikel vom Mainbild noch?
            $main = $this->db->query(
                'SELECT kArtikel
                FROM tartikel
                WHERE kArtikel = (
                    SELECT kArtikel
                        FROM tartikelpict
                        WHERE kArtikelPict = ' . (int)$image->kMainArtikelBild . ')',
                ReturnType::SINGLE_OBJECT
            );
            // Main Artikel existiert nicht mehr
            if (!isset($main->kArtikel) || (int)$main->kArtikel === 0) {
                // Existiert noch eine andere aktive Verknüpfung auf das Mainbild?
                $productImages = $this->db->query(
                    'SELECT kArtikelPict
                    FROM tartikelpict
                    WHERE kMainArtikelBild = ' . (int)$image->kMainArtikelBild . '
                        AND kArtikel != ' . $productID,
                    ReturnType::ARRAY_OF_OBJECTS
                );
                // Lösche das MainArtikelBild
                if (\count($productImages) === 0) {
                    // Bild von der Platte löschen
                    @\unlink(\PFAD_ROOT . \PFAD_PRODUKTBILDER_MINI . $image->cPfad);
                    @\unlink(\PFAD_ROOT . \PFAD_PRODUKTBILDER_KLEIN . $image->cPfad);
                    @\unlink(\PFAD_ROOT . \PFAD_PRODUKTBILDER_NORMAL . $image->cPfad);
                    @\unlink(\PFAD_ROOT . \PFAD_PRODUKTBILDER_GROSS . $image->cPfad);
                    // Bild vom Main aus DB löschen
                    $this->db->delete('tartikelpict', 'kArtikelPict', (int)$image->kMainArtikelBild);
                }
            }
            // Bildverknüpfung aus DB löschen
            $this->db->delete('tartikelpict', 'kArtikelPict', (int)$image->kArtikelPict);
        } elseif (isset($image->kMainArtikelBild) && $image->kMainArtikelBild == 0) {
            // Das Bild ist ein Hauptbild
            // Gibt es Artikel die auf Bilder des zu löschenden Artikel verknüpfen?
            $childProducts = $this->db->queryPrepared(
                'SELECT kArtikelPict
                FROM tartikelpict
                WHERE kMainArtikelBild = :img',
                ['img' => (int)$image->kArtikelPict],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (\count($childProducts) === 0) {
                // Gibt ein neue Artikel die noch auf den physikalischen Pfad zeigen?
                $oObj = $this->db->queryPrepared(
                    'SELECT COUNT(*) AS nCount
                    FROM tartikelpict
                    WHERE cPfad = :pth',
                    ['pth' => $image->cPfad],
                    ReturnType::SINGLE_OBJECT
                );
                if (isset($oObj->nCount) && $oObj->nCount < 2) {
                    // Bild von der Platte löschen
                    @\unlink(\PFAD_ROOT . \PFAD_PRODUKTBILDER_MINI . $image->cPfad);
                    @\unlink(\PFAD_ROOT . \PFAD_PRODUKTBILDER_KLEIN . $image->cPfad);
                    @\unlink(\PFAD_ROOT . \PFAD_PRODUKTBILDER_NORMAL . $image->cPfad);
                    @\unlink(\PFAD_ROOT . \PFAD_PRODUKTBILDER_GROSS . $image->cPfad);
                }
            } else {
                // Reorder linked images because master imagelink will be deleted
                $next = $childProducts[0]->kArtikelPict;
                // this will be the next masterimage
                $this->db->update(
                    'tartikelpict',
                    'kArtikelPict',
                    (int)$next,
                    (object)['kMainArtikelBild' => 0]
                );
                // now link other images to the new masterimage
                $this->db->update(
                    'tartikelpict',
                    'kMainArtikelBild',
                    (int)$image->kArtikelPict,
                    (object)['kMainArtikelBild' => (int)$next]
                );
            }
            $this->db->delete('tartikelpict', 'kArtikelPict', (int)$image->kArtikelPict);
        }
        $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $productID]);
    }

    /**
     * @param object $product
     * @param array  $conf
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
     */
    protected function versendeVerfuegbarkeitsbenachrichtigung($product, array $conf): void
    {
        if ($product->kArtikel <= 0) {
            return;
        }
        $subscriptions = $this->db->selectAll(
            'tverfuegbarkeitsbenachrichtigung',
            ['nStatus', 'kArtikel'],
            [0, $product->kArtikel]
        );
        $subCount      = \count($subscriptions);
        if ($subCount === 0
            || (($product->fLagerbestand / $subCount) < ($conf['artikeldetails']['benachrichtigung_min_lagernd'] / 100)
                && ($product->cLagerKleinerNull ?? '') !== 'Y'
                && (!isset($product->cLagerBeachten)
                    || $product->cLagerBeachten === 'Y')
            )
        ) {
            return;
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'sprachfunktionen.php';

        $options                             = Artikel::getDefaultOptions();
        $options->nKeineSichtbarkeitBeachten = 1;
        $product                             = (new Artikel())->fuelleArtikel($product->kArtikel, $options);
        if ($product === null) {
            return;
        }
        $campaign = new Kampagne(\KAMPAGNE_INTERN_VERFUEGBARKEIT);
        if ($campaign->kKampagne > 0) {
            $cSep           = \strpos($product->cURL, '.php') === false ? '?' : '&';
            $product->cURL .= $cSep . $campaign->cParameter . '=' . $campaign->cWert;
        }
        foreach ($subscriptions as $msg) {
            $obj                                   = new stdClass();
            $obj->tverfuegbarkeitsbenachrichtigung = $msg;
            $obj->tartikel                         = $product;
            $obj->tartikel->cName                  = Text::htmlentitydecode($obj->tartikel->cName);
            $mail                                  = new stdClass();
            $mail->toEmail                         = $msg->cMail;
            $mail->toName                          = ($msg->cVorname || $msg->cNachname)
                ? ($msg->cVorname . ' ' . $msg->cNachname)
                : $msg->cMail;
            $obj->mail                             = $mail;

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR, $obj));

            $upd                    = new stdClass();
            $upd->nStatus           = 1;
            $upd->dBenachrichtigtAm = 'NOW()';
            $upd->cAbgeholt         = 'N';
            $this->db->update(
                'tverfuegbarkeitsbenachrichtigung',
                'kVerfuegbarkeitsbenachrichtigung',
                $msg->kVerfuegbarkeitsbenachrichtigung,
                $upd
            );
        }
    }

    /**
     * @param int   $productID
     * @param array $xml
     */
    protected function handlePriceHistory(int $productID, array $xml)
    {
        if (!\is_array($xml)) {
            return;
        }
        // Delete price history from not existing customer groups
        $this->db->queryPrepared(
            'DELETE tpreisverlauf
                FROM tpreisverlauf
                    LEFT JOIN tkundengruppe ON tkundengruppe.kKundengruppe = tpreisverlauf.kKundengruppe
                WHERE tpreisverlauf.kArtikel = :productID
                    AND tkundengruppe.kKundengruppe IS NULL',
            [
                'productID' => $productID,
            ],
            ReturnType::DEFAULT
        );
        // Insert new base price for each customer group - update existing history for today
        $this->db->queryPrepared(
            'INSERT INTO tpreisverlauf (kArtikel, kKundengruppe, fVKNetto, dDate)
                SELECT :productID, kKundengruppe, :nettoPrice, CURDATE()
                FROM tkundengruppe
                ON DUPLICATE KEY UPDATE
                    fVKNetto = :nettoPrice',
            [
                'productID'  => $productID,
                'nettoPrice' => (int)$xml['fStandardpreisNetto'],
            ],
            ReturnType::DEFAULT
        );
        // Handle price details from xml...
        $prices = isset($xml['tpreis']) ? $this->mapper->mapArray($xml, 'tpreis', 'mPreis') : [];
        foreach ($prices as $i => $price) {
            $details = empty($xml['tpreis'][$i])
                ? $this->mapper->mapArray($xml['tpreis'], 'tpreisdetail', 'mPreisDetail')
                : $this->mapper->mapArray($xml['tpreis'][$i], 'tpreisdetail', 'mPreisDetail');
            if (count($details) > 0 && (int)$details[0]->nAnzahlAb === 0) {
                $this->db->queryPrepared(
                    'UPDATE tpreisverlauf SET
                        fVKNetto = :nettoPrice
                        WHERE kArtikel = :productID
                            AND kKundengruppe = :customerGroupID
                            AND dDate = CURDATE()',
                    [
                        'nettoPrice'      => $details[0]->fNettoPreis,
                        'productID'       => $productID,
                        'customerGroupID' => $price->kKundenGruppe,
                    ],
                    ReturnType::DEFAULT
                );
            }
        }
        // Handle special prices from xml...
        $prices = isset($xml['tartikelsonderpreis'])
            ? $this->mapper->mapArray($xml, 'tartikelsonderpreis', 'mArtikelSonderpreis')
            : [];
        foreach ($prices as $i => $price) {
            if ($price->cAktiv === 'Y') {
                try {
                    $startDate = new \DateTime($price->dStart);
                } catch (\Exception $e) {
                    $startDate = (new \DateTime())->setTime(0, 0, 0);
                }
                try {
                    $endDate = new \DateTime($price->dEnde);
                } catch (\Exception $e) {
                    $endDate = (new \DateTime())->setTime(0, 0, 0);
                }
                $toDay = (new \DateTime())->setTime(0, 0, 0);
                if ($startDate <= $toDay
                    && $endDate >= $toDay
                    && ((int)$price->nIstAnzahl === 0 || (int)$price->nAnzahl < (int)$xml['fLagerbestand'])
                ) {
                    $specialPrices = empty($xml['tartikelsonderpreis'][$i])
                        ? $this->mapper->mapArray($xml['tartikelsonderpreis'], 'tsonderpreise', 'mSonderpreise')
                        : $this->mapper->mapArray($xml['tartikelsonderpreis'][$i], 'tsonderpreise', 'mSonderpreise');

                    foreach ($specialPrices as $specialPrice) {
                        $this->db->queryPrepared(
                            'UPDATE tpreisverlauf SET
                                fVKNetto = :nettoPrice
                                WHERE kArtikel = :productID
                                    AND kKundengruppe = :customerGroupID
                                    AND dDate = CURDATE()',
                            [
                                'nettoPrice'      => $specialPrice->fNettoPreis,
                                'productID'       => $productID,
                                'customerGroupID' => $specialPrice->kKundengruppe,
                            ],
                            ReturnType::DEFAULT
                        );
                    }
                }
            }
        }
        // Delete last price history if price is same as next to last
        $this->db->queryPrepared(
            'DELETE FROM tpreisverlauf
                WHERE tpreisverlauf.kArtikel = :productID
                    AND (tpreisverlauf.kKundengruppe, tpreisverlauf.dDate) IN (SELECT * FROM (
                        SELECT tpv1.kKundengruppe, MAX(tpv1.dDate)
                        FROM tpreisverlauf tpv1
                        LEFT JOIN tpreisverlauf tpv2 ON tpv2.dDate > tpv1.dDate
                            AND tpv2.kArtikel = tpv1.kArtikel
                            AND tpv2.kKundengruppe = tpv1.kKundengruppe
                            AND tpv2.dDate < (
                                SELECT MAX(tpv3.dDate)
                                FROM tpreisverlauf tpv3
                                WHERE tpv3.kArtikel = tpv1.kArtikel
                                    AND tpv3.kKundengruppe = tpv1.kKundengruppe
                            )
                        WHERE tpv1.kArtikel = :productID
                            AND tpv2.kPreisverlauf IS NULL
                        GROUP BY tpv1.kKundengruppe
                        HAVING COUNT(DISTINCT tpv1.fVKNetto) = 1
                            AND COUNT(tpv1.kPreisverlauf) > 1
                    ) i)',
            [
                'productID' => $productID,
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int $productID
     * @param int $customerGroupID
     * @param int $customerID
     * @return mixed
     */
    protected function handlePriceFormat(int $productID, int $customerGroupID, int $customerID = 0)
    {
        if ($customerID > 0) {
            $this->flushCustomerPriceCache($customerID);
        }

        return $this->db->queryPrepared(
            'INSERT INTO tpreis (kArtikel, kKundengruppe, kKunde)
                VALUES (:productID, :customerGroup, :customerID)
                ON DUPLICATE KEY UPDATE
                    kKunde = :customerID',
            [
                'productID'     => $productID,
                'customerGroup' => $customerGroupID,
                'customerID'    => $customerID,
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * Handle new PriceFormat (Wawi >= v.1.00):
     *
     * Sample XML:
     *  <tpreis kPreis="8" kArtikel="15678" kKundenGruppe="1" kKunde="0">
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>100</nAnzahlAb>
     *          <fNettoPreis>0.756303</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>250</nAnzahlAb>
     *          <fNettoPreis>0.714286</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>500</nAnzahlAb>
     *          <fNettoPreis>0.672269</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>750</nAnzahlAb>
     *          <fNettoPreis>0.630252</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>1000</nAnzahlAb>
     *          <fNettoPreis>0.588235</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>2000</nAnzahlAb>
     *          <fNettoPreis>0.420168</fNettoPreis>
     *      </tpreisdetail>
     *      <tpreisdetail kPreis="8">
     *          <nAnzahlAb>0</nAnzahlAb>
     *          <fNettoPreis>0.798319</fNettoPreis>
     *      </tpreisdetail>
     *  </tpreis>
     *
     * @param int   $productID
     * @param array $xml
     */
    protected function handleNewPriceFormat(int $productID, array $xml): void
    {
        if (!\is_array($xml)) {
            return;
        }

        $prices = isset($xml['tpreis']) ? $this->mapper->mapArray($xml, 'tpreis', 'mPreis') : [];

        // Delete prices and price details from not existing customer groups
        $this->db->queryPrepared(
            'DELETE tpreis, tpreisdetail
                FROM tpreis
                    INNER JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                    LEFT JOIN tkundengruppe ON tkundengruppe.kKundengruppe = tpreis.kKundengruppe
                WHERE tpreis.kArtikel = :productID
                    AND tkundengruppe.kKundengruppe IS NULL',
            [
                'productID' => $productID,
            ],
            ReturnType::DEFAULT
        );
        // Delete all prices who are not base prices
        $this->db->queryPrepared(
            'DELETE tpreisdetail
                FROM tpreis
                    INNER JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                WHERE tpreis.kArtikel = :productID
                    AND tpreisdetail.nAnzahlAb > 0',
            [
                'productID' => $productID,
            ],
            ReturnType::DEFAULT
        );
        // Insert price record for each customer group - ignore existing
        $this->db->queryPrepared(
            'INSERT IGNORE INTO tpreis (kArtikel, kKundengruppe, kKunde)
                SELECT :productID, kKundengruppe, 0
                FROM tkundengruppe',
            [
                'productID' => $productID,
            ],
            ReturnType::DEFAULT
        );
        // Insert base price for each price record - update existing
        $this->db->queryPrepared(
            'INSERT INTO tpreisdetail (kPreis, nAnzahlAb, fVKNetto)
                SELECT tpreis.kPreis, 0, :basePrice
                FROM tpreis
                WHERE tpreis.kArtikel = :productID
                ON DUPLICATE KEY UPDATE
                    tpreisdetail.fVKNetto = :basePrice',
            [
                'basePrice' => $xml['fStandardpreisNetto'],
                'productID' => $productID,
            ],
            ReturnType::DEFAULT
        );
        // Handle price details from xml...
        foreach ($prices as $i => $price) {
            $this->handlePriceFormat($price->kArtikel, $price->kKundenGruppe, (int)$price->kKunde);
            $details = empty($xml['tpreis'][$i])
                ? $this->mapper->mapArray($xml['tpreis'], 'tpreisdetail', 'mPreisDetail')
                : $this->mapper->mapArray($xml['tpreis'][$i], 'tpreisdetail', 'mPreisDetail');

            foreach ($details as $preisdetail) {
                $this->db->queryPrepared(
                    'INSERT INTO tpreisdetail (kPreis, nAnzahlAb, fVKNetto)
                        SELECT tpreis.kPreis, :countingFrom, :nettoPrice
                        FROM tpreis
                        WHERE tpreis.kArtikel = :productID
                            AND tpreis.kKundengruppe = :customerGroup
                            AND tpreis.kKunde = :customerPrice
                        ON DUPLICATE KEY UPDATE
                            tpreisdetail.fVKNetto = :nettoPrice',
                    [
                        'countingFrom'  => $preisdetail->nAnzahlAb,
                        'nettoPrice'    => $preisdetail->fNettoPreis,
                        'productID'     => $productID,
                        'customerGroup' => $price->kKundenGruppe,
                        'customerPrice' => (int)$price->kKunde,
                    ],
                    ReturnType::DEFAULT
                );
            }
        }
    }

    /**
     * @param int $kKunde
     * @return bool|int
     */
    protected function flushCustomerPriceCache(int $kKunde)
    {
        return $this->cache->flush('custprice_' . $kKunde);
    }

    /**
     * @param string $salutation
     * @return string
     */
    protected function mapSalutation(string $salutation): string
    {
        $salutation = \strtolower($salutation);
        if ($salutation === 'w' || $salutation === 'm') {
            return $salutation;
        }
        if ($salutation === 'frau' || $salutation === 'mrs' || $salutation === 'mrs.') {
            return 'w';
        }

        return 'm';
    }

    /**
     * @param int         $keyValue
     * @param string      $keyName
     * @param int|null    $langID
     * @param string|null $assoc
     * @return array|null|stdClass
     */
    protected function getSeoFromDB(int $keyValue, string $keyName, int $langID = null, $assoc = null)
    {
        if (!($keyValue > 0 && \strlen($keyName) > 0)) {
            return null;
        }
        if ($langID !== null && $langID > 0) {
            $oSeo = $this->db->select('tseo', 'kKey', $keyValue, 'cKey', $keyName, 'kSprache', $langID);
            if (isset($oSeo->kKey) && (int)$oSeo->kKey > 0) {
                return $oSeo;
            }
        } else {
            $seo = $this->db->selectAll('tseo', ['kKey', 'cKey'], [$keyValue, $keyName]);
            if (\is_array($seo) && \count($seo) > 0) {
                if ($assoc !== null && \strlen($assoc) > 0) {
                    $seoData = [];
                    foreach ($seo as $oSeo) {
                        if (isset($oSeo->{$assoc})) {
                            $seoData[$oSeo->{$assoc}] = $oSeo;
                        }
                    }
                    if (\count($seoData) > 0) {
                        $seo = $seoData;
                    }
                }

                return $seo;
            }
        }

        return null;
    }

    /**
     * @param array $arr
     * @param array $excludes
     * @return array
     */
    protected function buildAttributes(&$arr, $excludes = []): array
    {
        $attributes = [];
        if (\is_array($arr)) {
            $keys     = \array_keys($arr);
            $keyCount = \count($keys);
            for ($i = 0; $i < $keyCount; $i++) {
                if (!\in_array($keys[$i], $excludes) && $keys[$i]{0} === 'k') {
                    $attributes[$keys[$i]] = $arr[$keys[$i]];
                    unset($arr[$keys[$i]]);
                }
            }
        }

        return $attributes;
    }

    /**
     * @param object $object
     */
    protected function extractStreet(&$object): void
    {
        $data  = \explode(' ', $object->cStrasse);
        $parts = \count($data);
        if ($parts > 1) {
            $object->cHausnummer = $data[$parts - 1];
            unset($data[$parts - 1]);
            $object->cStrasse = \implode(' ', $data);
        }
    }

    /**
     * @param string $oldSeo
     * @param string $newSeo
     * @return bool
     */
    protected function checkDbeSXmlRedirect($oldSeo, $newSeo): bool
    {
        // Insert into tredirect weil sich das SEO von der Kategorie geändert hat
        if ($oldSeo === $newSeo || \strlen($oldSeo) === 0 || \strlen($newSeo) === 0) {
            return false;
        }
        $redirect = new Redirect();
        $parsed   = \parse_url(Shop::getURL());
        if (isset($parsed['path'])) {
            $source = $parsed['path'] . '/' . $oldSeo;
        } else {
            $source = '/' . $oldSeo;
        }

        return $redirect->saveExt($source, $newSeo, true);
    }

    /**
     * @param int[] $productIDs
     */
    protected function handlePriceRange(array $productIDs): void
    {
        $this->db->executeQuery(
            'DELETE FROM tpricerange
            WHERE kArtikel IN (' . \implode(',', $productIDs) . ')',
            ReturnType::DEFAULT
        );
        $uniqueProductIDs = \implode(',', \array_unique($productIDs));
        $this->db->executeQuery(
            'INSERT INTO tpricerange
            (kArtikel, kKundengruppe, kKunde, nRangeType, fVKNettoMin, fVKNettoMax, nLagerAnzahlMax, dStart, dEnde)
            SELECT baseprice.kArtikel,
                COALESCE(baseprice.kKundengruppe, 0) AS kKundengruppe,
                COALESCE(baseprice.kKunde, 0) AS kKunde,
                baseprice.nRangeType,
                MIN(IF(varaufpreis.fMinAufpreisNetto IS NULL,
                    baseprice.fVKNetto, baseprice.fVKNetto + varaufpreis.fMinAufpreisNetto)) fVKNettoMin,
                MAX(IF(varaufpreis.fMaxAufpreisNetto IS NULL,
                    baseprice.fVKNetto, baseprice.fVKNetto + varaufpreis.fMaxAufpreisNetto)) fVKNettoMax,
                baseprice.nLagerAnzahlMax,
                baseprice.dStart,
                baseprice.dEnde
            FROM (
                SELECT IF(tartikel.kVaterartikel = 0, tartikel.kArtikel, tartikel.kVaterartikel) kArtikel,
                    tartikel.kArtikel kKindArtikel,
                    tartikel.nIstVater,
                    tpreis.kKundengruppe,
                    tpreis.kKunde,
                    IF (tpreis.kKundengruppe > 0, 9, 1) nRangeType,
                    null nLagerAnzahlMax,
                    tpreisdetail.fVKNetto,
                    null dStart, null dEnde
                FROM tartikel
                INNER JOIN tpreis 
                    ON tpreis.kArtikel = tartikel.kArtikel
                INNER JOIN tpreisdetail 
                    ON tpreisdetail.kPreis = tpreis.kPreis
                WHERE IF(tartikel.kVaterartikel = 0, tartikel.kArtikel, tartikel.kVaterartikel) IN ('
            . $uniqueProductIDs . ')

                UNION ALL

                SELECT IF(tartikel.kVaterartikel = 0, tartikel.kArtikel, tartikel.kVaterartikel) kArtikel,
                    tartikel.kArtikel kKindArtikel,
                    tartikel.nIstVater,
                    tsonderpreise.kKundengruppe,
                    null kKunde,
                    IF(tartikelsonderpreis.nIstAnzahl = 0 AND tartikelsonderpreis.nIstDatum = 0, 5, 3) nRangeType,
                    IF(tartikelsonderpreis.nIstAnzahl = 0, null, tartikelsonderpreis.nAnzahl) nLagerAnzahlMax,
                    IF(tsonderpreise.fNettoPreis < tpreisdetail.fVKNetto, 
                        tsonderpreise.fNettoPreis, tpreisdetail.fVKNetto) fVKNetto,
                    tartikelsonderpreis.dStart dStart,
                    IF(tartikelsonderpreis.nIstDatum = 0, null, tartikelsonderpreis.dEnde) dEnde
                FROM tartikel
                INNER JOIN tpreis 
                    ON tpreis.kArtikel = tartikel.kArtikel
	            INNER JOIN tpreisdetail 
	                ON tpreisdetail.kPreis = tpreis.kPreis
                INNER JOIN tartikelsonderpreis 
                    ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                INNER JOIN tsonderpreise 
                    ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                WHERE tartikelsonderpreis.cAktiv = \'Y\'
                    AND IF(tartikel.kVaterartikel = 0, tartikel.kArtikel, tartikel.kVaterartikel) IN ('
            . $uniqueProductIDs . ')
            ) baseprice
            LEFT JOIN (
                SELECT variations.kArtikel, variations.kKundengruppe,
                    SUM(variations.fMinAufpreisNetto) fMinAufpreisNetto,
                    SUM(variations.fMaxAufpreisNetto) fMaxAufpreisNetto
                FROM (
                    SELECT teigenschaft.kArtikel,
                        tkundengruppe.kKundengruppe,
                        teigenschaft.kEigenschaft,
                        MIN(COALESCE(teigenschaftwertaufpreis.fAufpreisNetto, 
                            teigenschaftwert.fAufpreisNetto)) fMinAufpreisNetto,
                        MAX(COALESCE(teigenschaftwertaufpreis.fAufpreisNetto, 
                            teigenschaftwert.fAufpreisNetto)) fMaxAufpreisNetto
                    FROM teigenschaft
                    INNER JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                    JOIN tkundengruppe
                    LEFT JOIN teigenschaftwertaufpreis 
                        ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                        AND teigenschaftwertaufpreis.kKundengruppe = tkundengruppe.kKundengruppe
                    WHERE teigenschaft.kArtikel IN (' . $uniqueProductIDs . ')
                    GROUP BY teigenschaft.kArtikel, tkundengruppe.kKundengruppe, teigenschaft.kEigenschaft
                ) variations
                GROUP BY variations.kArtikel, variations.kKundengruppe
            ) varaufpreis 
                ON varaufpreis.kArtikel = baseprice.kKindArtikel 
                AND baseprice.nIstVater = 0
            WHERE baseprice.kArtikel IN (' . $uniqueProductIDs . ')
            GROUP BY baseprice.kArtikel,
                baseprice.kKundengruppe,
                baseprice.kKunde,
                baseprice.nRangeType,
                baseprice.nLagerAnzahlMax,
                baseprice.dStart,
                baseprice.dEnde',
            ReturnType::DEFAULT
        );
    }
}
