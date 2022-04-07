<?php declare(strict_types=1);

use JTL\Catalog\Currency;
use JTL\Cron\Checker;
use JTL\Cron\JobFactory;
use JTL\Cron\LegacyCron;
use JTL\Cron\Queue;
use JTL\Customer\CustomerGroup;
use JTL\Export\ExporterFactory;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * @param int $cronID
 * @return int
 * @deprecated since 5.2.0
 */
function holeCron(int $cronID): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function holeAlleExportformate(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int    $exportID
 * @param string $start
 * @param int    $freq
 * @param int    $cronID
 * @return int
 * @deprecated since 5.2.0
 */
function erstelleExportformatCron(int $exportID, string $start, int $freq, int $cronID = 0): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @param string $start
 * @return bool
 * @deprecated since 5.2.0
 */
function dStartPruefen($start): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param int[] $cronIDs
 * @return bool
 * @deprecated since 5.2.0
 */
function loescheExportformatCron(array $cronIDs): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return true;
}

/**
 * @param JTLSmarty $smarty
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionErstellen(JTLSmarty $smarty): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);

    return 'erstellen';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionEditieren(JTLSmarty $smarty, array &$messages): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $messages['error'] .= __('errorWrongQueue');

    return 'uebersicht';
}

/**
 * @param array $messages
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionLoeschen(array &$messages): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $messages['error'] .= __('errorWrongQueue');

    return 'loeschen_result';
}

/**
 * @param array $messages
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionTriggern(array &$messages): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $messages['error'] .= __('errorCronStart') . '<br />';

    return 'triggern';
}

/**
 * @param JTLSmarty $smarty
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionFertiggestellt(JTLSmarty $smarty): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 'fertiggestellt';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionErstellenEintragen(JTLSmarty $smarty, array &$messages): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 'erstellen';
}

/**
 * @param string     $tab
 * @param array|null $messages
 * @deprecated since 5.2.0
 */
function exportformatQueueRedirect(string $tab = '', array $messages = null): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    exit;
}

/**
 * @param string    $step
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @deprecated since 5.2.0
 */
function exportformatQueueFinalize(string $step, JTLSmarty $smarty, array &$messages): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}

/**
 * @param string $dateStart
 * @param bool   $asTime
 * @return string
 * @todo!
 */
function baueENGDate($dateStart, $asTime = false): string
{
    [$date, $time]        = explode(' ', $dateStart);
    [$day, $month, $year] = explode('.', $date);

    return $asTime ? $time : $year . '-' . $month . '-' . $day . ' ' . $time;
}

/**
 * @param int $hours
 * @return stdClass[]|bool
 * @todo!
 */
function holeExportformatQueueBearbeitet(int $hours = 24)
{
    $languageID = (int)($_SESSION['kSprache'] ?? 0);
    if (!$languageID) {
        $tmp = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
        if (isset($tmp->kSprache) && $tmp->kSprache > 0) {
            $languageID = (int)$tmp->kSprache;
        } else {
            return false;
        }
    }
    $languages = Shop::Lang()->getAllLanguages(1);
    $queues    = Shop::Container()->getDB()->getObjects(
        "SELECT texportformat.cName, texportformat.cDateiname, texportformatqueuebearbeitet.*,
            DATE_FORMAT(texportformatqueuebearbeitet.dZuletztGelaufen, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_DE,
            tsprache.cNameDeutsch AS cNameSprache, tkundengruppe.cName AS cNameKundengruppe,
            twaehrung.cName AS cNameWaehrung
            FROM texportformatqueuebearbeitet
            JOIN texportformat
                ON texportformat.kExportformat = texportformatqueuebearbeitet.kExportformat
                AND texportformat.kSprache = :lid
            JOIN tsprache
                ON tsprache.kSprache = texportformat.kSprache
            JOIN tkundengruppe
                ON tkundengruppe.kKundengruppe = texportformat.kKundengruppe
            JOIN twaehrung
                ON twaehrung.kWaehrung = texportformat.kWaehrung
            WHERE DATE_SUB(NOW(), INTERVAL :hrs HOUR) < texportformatqueuebearbeitet.dZuletztGelaufen
            ORDER BY texportformatqueuebearbeitet.dZuletztGelaufen DESC",
        ['lid' => $languageID, 'hrs' => $hours]
    );
    foreach ($queues as $exportFormat) {
        $exportFormat->name      = $languages[$languageID]->getLocalizedName();
        $exportFormat->kJobQueue = (int)$exportFormat->kJobQueue;
        $exportFormat->nLimitN   = (int)$exportFormat->nLimitN;
        $exportFormat->nLimitM   = (int)$exportFormat->nLimitM;
        $exportFormat->nInArbeit = (int)$exportFormat->nInArbeit;
    }

    return $queues;
}

/**
 * @return stdClass[]
 * @todo!
 */
function holeExportformatCron(): array
{
    $db      = Shop::Container()->getDB();
    $exports = $db->getObjects(
        "SELECT texportformat.*, tcron.cronID, tcron.frequency, tcron.startDate, 
            DATE_FORMAT(tcron.startDate, '%d.%m.%Y %H:%i') AS dStart_de, tcron.lastStart, 
            DATE_FORMAT(tcron.lastStart, '%d.%m.%Y %H:%i') AS dLetzterStart_de,
            DATE_FORMAT(DATE_ADD(ADDTIME(DATE(tcron.lastStart), tcron.startTime),
                INTERVAL tcron.frequency HOUR), '%d.%m.%Y %H:%i')
            AS dNaechsterStart_de
            FROM texportformat
            JOIN tcron 
                ON tcron.jobType = 'exportformat'
                AND tcron.foreignKeyID = texportformat.kExportformat
            ORDER BY tcron.startDate DESC"
    );

    $factory = new ExporterFactory($db, Shop::Container()->getLogService(), Shop::Container()->getCache());
    foreach ($exports as $export) {
        $export->kExportformat      = (int)$export->kExportformat;
        $export->kKundengruppe      = (int)$export->kKundengruppe;
        $export->kSprache           = (int)$export->kSprache;
        $export->kWaehrung          = (int)$export->kWaehrung;
        $export->kKampagne          = (int)$export->kKampagne;
        $export->kPlugin            = (int)$export->kPlugin;
        $export->nSpecial           = (int)$export->nSpecial;
        $export->nVarKombiOption    = (int)$export->nVarKombiOption;
        $export->nSplitgroesse      = (int)$export->nSplitgroesse;
        $export->nUseCache          = (int)$export->nUseCache;
        $export->nFehlerhaft        = (int)$export->nFehlerhaft;
        $export->cronID             = (int)$export->cronID;
        $export->frequency          = (int)$export->frequency;
        $export->cAlleXStdToDays    = rechneUmAlleXStunden($export->frequency);
        $export->frequencyLocalized = $export->cAlleXStdToDays;

        $exporter = $factory->getExporter($export->kExportformat);
        $exporter->init($export->kExportformat);
        try {
            $export->Sprache = Shop::Lang()->getLanguageByID($export->kSprache);
        } catch (Exception $e) {
            $export->Sprache = LanguageHelper::getDefaultLanguage();
            $export->Sprache->setLocalizedName('???');
            $export->Sprache->setId(0);
            $export->nFehlerhaft = 1;
        }
        $export->Waehrung     = $db->select(
            'twaehrung',
            'kWaehrung',
            $export->kWaehrung
        );
        $export->Kundengruppe = $db->select(
            'tkundengruppe',
            'kKundengruppe',
            $export->kKundengruppe
        );
        $export->oJobQueue    = $db->getSingleObject(
            "SELECT *, DATE_FORMAT(lastStart, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_de 
                FROM tjobqueue 
                WHERE cronID = :id",
            ['id' => $export->cronID]
        );
        $export->productCount = $exporter->getExportProductCount();
    }

    return $exports;
}

/**
 * @param int $hours
 * @return bool|string
 * @todo!
 */
function rechneUmAlleXStunden(int $hours)
{
    if ($hours <= 0) {
        return false;
    }
    if ($hours > 24) {
        $hours = round($hours / 24);
        if ($hours >= 365) {
            $hours /= 365;
            if ($hours == 1) {
                $hours .= __('year');
            } else {
                $hours .= __('years');
            }
        } elseif ($hours == 1) {
            $hours .= __('day');
        } else {
            $hours .= __('days');
        }
    } elseif ($hours > 1) {
        $hours .= __('hours');
    } else {
        $hours .= __('hour');
    }

    return $hours;
}
