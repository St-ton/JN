<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\dbeS\Mapper;
use JTL\dbeS\Starter;
use JTL\Optin\Optin;
use JTL\Helpers\Text;
use JTL\Kampagne;
use JTL\Customer\Kundengruppe;
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
            $isOptinValidActive = (new Optin(\OPTIN_AVAILAGAIN))
                ->setEmail($msg->cMail)
                ->isActive();
            if (!$isOptinValidActive) {
                continue;
            }
            $tplData                                   = new stdClass();
            $tplData->tverfuegbarkeitsbenachrichtigung = $msg;
            $tplData->tartikel                         = $product;
            $tplData->tartikel->cName                  = Text::htmlentitydecode($tplData->tartikel->cName);
            $tplMail                                   = new stdClass();
            $tplMail->toEmail                          = $msg->cMail;
            $tplMail->toName                           = ($msg->cVorname || $msg->cNachname)
                ? ($msg->cVorname . ' ' . $msg->cNachname)
                : $msg->cMail;
            $tplData->mail                             = $tplMail;

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mail->setToMail($tplMail->toEmail);
            $mail->setToName($tplMail->toName);
            $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR, $tplData));

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
     * @param int   $customerGroupID
     * @param float $fVKNetto
     */
    protected function setzePreisverlauf(int $productID, int $customerGroupID, float $fVKNetto): void
    {
        $history = $this->db->queryPrepared(
            'SELECT kPreisverlauf, fVKNetto, dDate, IF(dDate = CURDATE(), 1, 0) bToday
            FROM tpreisverlauf
            WHERE kArtikel = :kArtikel
	            AND kKundengruppe = :kKundengruppe
            ORDER BY dDate DESC LIMIT 2',
            [
                'kArtikel'      => $productID,
                'kKundengruppe' => $customerGroupID,
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );

        if (!empty($history[0]) && (int)$history[0]->bToday === 1) {
            // price for today exists
            if (\round($history[0]->fVKNetto * 100) === \round($fVKNetto * 100)) {
                // return if there is no difference
                return;
            }
            if (!empty($history[1]) && \round($history[1]->fVKNetto * 100) === \round($fVKNetto * 100)) {
                // delete todays price if the new price for today is the same as the latest price
                $this->db->delete('tpreisverlauf', 'kPreisverlauf', (int)$history[0]->kPreisverlauf);
            } else {
                // update if prices are different
                $this->db->update(
                    'tpreisverlauf',
                    'kPreisverlauf',
                    (int)$history[0]->kPreisverlauf,
                    (object)['fVKNetto' => $fVKNetto]
                );
            }
        } else {
            // no price for today exists
            if (!empty($history[0]) && \round($history[0]->fVKNetto * 100) === \round($fVKNetto * 100)) {
                // return if there is no difference
                return;
            }
            $this->db->insert('tpreisverlauf', (object)[
                'kArtikel'      => $productID,
                'kKundengruppe' => $customerGroupID,
                'fVKNetto'      => $fVKNetto,
                'dDate'         => 'NOW()',
            ]);
        }
    }

    /**
     * @param int      $kArtikel
     * @param int      $kKundengruppe
     * @param int|null $kKunde
     * @return mixed
     */
    protected function handlePriceFormat(int $kArtikel, int $kKundengruppe, int $kKunde = null)
    {
        $ins                = new stdClass();
        $ins->kArtikel      = $kArtikel;
        $ins->kKundengruppe = $kKundengruppe;
        if ($kKunde !== null && $kKunde > 0) {
            $ins->kKunde = $kKunde;
            $this->flushCustomerPriceCache($ins->kKunde);
        }

        return $this->db->insert('tpreis', $ins);
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
     * @param array $xml
     */
    protected function handleNewPriceFormat(array $xml): void
    {
        if (!\is_array($xml) || !isset($xml['tpreis'])) {
            return;
        }
        $prices = $this->mapper->mapArray($xml, 'tpreis', 'mPreis');
        if (\count($prices) === 0) {
            return;
        }
        $productID = (int)$prices[0]->kArtikel;
        $customers = $this->db->selectAll(
            'tpreis',
            ['kArtikel', 'kKundengruppe'],
            [$productID, 0],
            'kKunde'
        );
        foreach ($customers as $customer) {
            $customerID = (int)$customer->kKunde;
            if ($customerID > 0) {
                $this->flushCustomerPriceCache($customerID);
            }
        }
        $this->db->query(
            'DELETE p, d
            FROM tpreis AS p
            LEFT JOIN tpreisdetail AS d
                ON d.kPreis = p.kPreis
            WHERE p.kArtikel = ' . $productID,
            ReturnType::DEFAULT
        );
        $customerGroupHandled = [];
        foreach ($prices as $i => $price) {
            $priceID         = $this->handlePriceFormat($price->kArtikel, $price->kKundenGruppe, (int)$price->kKunde);
            $details         = empty($xml['tpreis'][$i])
                ? $this->mapper->mapArray($xml['tpreis'], 'tpreisdetail', 'mPreisDetail')
                : $this->mapper->mapArray($xml['tpreis'][$i], 'tpreisdetail', 'mPreisDetail');
            $hasDefaultPrice = false;
            foreach ($details as $preisdetail) {
                $ins = (object)[
                    'kPreis'    => $priceID,
                    'nAnzahlAb' => $preisdetail->nAnzahlAb,
                    'fVKNetto'  => $preisdetail->fNettoPreis
                ];
                $this->db->insert('tpreisdetail', $ins);
                if ((int)$ins->nAnzahlAb === 0) {
                    $hasDefaultPrice = true;
                }
            }
            // default price for customergroup set?
            if (!$hasDefaultPrice && isset($xml['fStandardpreisNetto'])) {
                $ins = (object)[
                    'kPreis'    => $priceID,
                    'nAnzahlAb' => 0,
                    'fVKNetto'  => $xml['fStandardpreisNetto']
                ];
                $this->db->insert('tpreisdetail', $ins);
            }
            $customerGroupHandled[] = (int)$price->kKundenGruppe;
        }
        // any customergroups with missing tpreis node left?
        foreach (Kundengruppe::getGroups() as $customergroup) {
            $id = $customergroup->getID();
            if (isset($xml['fStandardpreisNetto']) && !\in_array($id, $customerGroupHandled, true)) {
                $priceID = $this->handlePriceFormat($productID, $id);
                $ins     = (object)[
                    'kPreis'    => $priceID,
                    'nAnzahlAb' => 0,
                    'fVKNetto'  => $xml['fStandardpreisNetto']
                ];
                $this->db->insert('tpreisdetail', $ins);
            }
        }
    }

    /**
     * @param array $objs
     */
    protected function handleOldPriceFormat($objs): void
    {
        if (!\is_array($objs) || \count($objs) === 0) {
            return;
        }
        $productID = (int)$objs[0]->kArtikel;
        $customers = $this->db->selectAll(
            'tpreis',
            ['kArtikel', 'kKundengruppe'],
            [$productID, 0],
            'kKunde'
        );
        foreach ($customers as $customer) {
            $this->flushCustomerPriceCache((int)$customer->kKunde);
        }
        $this->db->query(
            'DELETE p, d
            FROM tpreis AS p
            LEFT JOIN tpreisdetail AS d
                ON d.kPreis = p.kPreis
            WHERE p.kArtikel = ' . $productID,
            ReturnType::DEFAULT
        );
        foreach ($objs as $obj) {
            $priceID = $this->handlePriceFormat((int)$obj->kArtikel, (int)$obj->kKundengruppe);
            $this->insertPriceDetail($obj, 0, $priceID);
            for ($i = 1; $i <= 5; $i++) {
                $this->insertPriceDetail($obj, $i, $priceID);
            }
        }
    }

    /**
     * @param object $obj
     * @param int    $index
     * @param int    $priceId
     */
    protected function insertPriceDetail($obj, $index, $priceId): void
    {
        $count = 'nAnzahl' . $index;
        $price = 'fPreis' . $index;

        if ((isset($obj->{$count}) && (int)$obj->{$count} > 0) || $index === 0) {
            $ins            = new stdClass();
            $ins->kPreis    = $priceId;
            $ins->nAnzahlAb = $index === 0 ? 0 : $obj->{$count};
            $ins->fVKNetto  = $index === 0 ? $obj->fVKNetto : $obj->{$price};

            $this->db->insert('tpreisdetail', $ins);
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
