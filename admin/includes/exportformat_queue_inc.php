<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Request;
use JTL\Cron\LegacyCron;
use JTL\Catalog\Currency;
use JTL\Customer\Kundengruppe;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;
use JTL\Smarty\JTLSmarty;
use JTL\Alert\Alert;

/**
 * @return array|bool
 */
function holeExportformatCron()
{
    $db      = Shop::Container()->getDB();
    $exports = $db->query(
        "SELECT texportformat.*, tcron.cronID, tcron.frequency, tcron.startDate, 
            DATE_FORMAT(tcron.startDate, '%d.%m.%Y %H:%i') AS dStart_de, tcron.lastStart, 
            DATE_FORMAT(tcron.lastStart, '%d.%m.%Y %H:%i') AS dLetzterStart_de,
            DATE_FORMAT(DATE_ADD(tcron.lastStart, INTERVAL tcron.frequency HOUR), '%d.%m.%Y %H:%i') 
            AS dNaechsterStart_de
            FROM texportformat
            JOIN tcron 
                ON tcron.jobType = 'exportformat'
                AND tcron.foreignKeyID = texportformat.kExportformat
            ORDER BY tcron.startDate DESC",
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($exports as $export) {
        $export->cAlleXStdToDays = rechneUmAlleXStunden($export->frequency);
        $export->Sprache         = Sprache::getInstance()->getLanguageByID((int)$export->kSprache);
        $export->Waehrung        = $db->select(
            'twaehrung',
            'kWaehrung',
            (int)$export->kWaehrung
        );
        $export->Kundengruppe    = $db->select(
            'tkundengruppe',
            'kKundengruppe',
            (int)$export->kKundengruppe
        );
        $export->oJobQueue       = $db->queryPrepared(
            "SELECT *, DATE_FORMAT(lastStart, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_de 
                FROM tjobqueue 
                WHERE cronID = :id",
            ['id' => (int)$export->cronID],
            ReturnType::SINGLE_OBJECT
        );
        $export->nAnzahlArtikel  = holeMaxExportArtikelAnzahl($export);
    }

    return $exports;
}

/**
 * @param int $kCron
 * @return int|object
 */
function holeCron($kCron)
{
    $kCron = (int)$kCron;
    if ($kCron > 0) {
        $oCron = Shop::Container()->getDB()->query(
            "SELECT *, DATE_FORMAT(tcron.startDate, '%d.%m.%Y %H:%i') AS dStart_de
                FROM tcron
                WHERE cronID = " . $kCron,
            ReturnType::SINGLE_OBJECT
        );

        if (!empty($oCron->cronID) && $oCron->cronID > 0) {
            $oCron->cronID       = (int)$oCron->cronID;
            $oCron->frequency    = (int)$oCron->frequency;
            $oCron->foreignKeyID = (int)($oCron->foreignKeyID ?? 0);

            return $oCron;
        }
    }

    return 0;
}

/**
 * @param int $hours
 * @return bool|string
 */
function rechneUmAlleXStunden($hours)
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
        $hours .= __('hour');
    } else {
        $hours .= __('hours');
    }

    return $hours;
}

/**
 * @return array
 */
function holeAlleExportformate()
{
    $formats = Shop::Container()->getDB()->selectAll(
        'texportformat',
        [],
        [],
        '*',
        'cName, kSprache, kKundengruppe, kWaehrung'
    );
    foreach ($formats as $format) {
        $format->Sprache      = Sprache::getInstance()->getLanguageByID((int)$format->kSprache);
        $format->Waehrung     = new Currency((int)$format->kWaehrung);
        $format->Kundengruppe = new Kundengruppe((int)$format->kKundengruppe);
    }

    return $formats;
}

/**
 * @param int    $kExportformat
 * @param string $dStart
 * @param int    $nAlleXStunden
 * @param int    $kCron
 * @return int
 */
function erstelleExportformatCron($kExportformat, $dStart, $nAlleXStunden, $kCron = 0)
{
    $kExportformat = (int)$kExportformat;
    $nAlleXStunden = (int)$nAlleXStunden;
    $kCron         = (int)$kCron;
    if ($kExportformat > 0 && $nAlleXStunden >= 1 && dStartPruefen($dStart)) {
        if ($kCron > 0) {
            // Editieren
            Shop::Container()->getDB()->queryPrepared(
                'DELETE tcron, tjobqueue
                    FROM tcron
                    LEFT JOIN tjobqueue 
                        ON tjobqueue.cronID = tcron.cronID
                    WHERE tcron.cronID = :id',
                ['id' => $kCron],
                ReturnType::DEFAULT
            );
            $oCron = new LegacyCron(
                $kCron,
                $kExportformat,
                $nAlleXStunden,
                $dStart . '_' . $kExportformat,
                'exportformat',
                'texportformat',
                'kExportformat',
                baueENGDate($dStart),
                baueENGDate($dStart, 1)
            );
            $oCron->speicherInDB();

            return 1;
        }
        // Pruefe ob Exportformat nicht bereits vorhanden
        $oCron = Shop::Container()->getDB()->select(
            'tcron',
            'foreignKey',
            'kExportformat',
            'foreignKeyID',
            $kExportformat
        );
        if (isset($oCron->cronID) && $oCron->cronID > 0) {
            return -1;
        }
        $oCron = new LegacyCron(
            0,
            $kExportformat,
            $nAlleXStunden,
            $dStart . '_' . $kExportformat,
            'exportformat',
            'texportformat',
            'kExportformat',
            baueENGDate($dStart),
            baueENGDate($dStart, 1)
        );
        $oCron->speicherInDB();

        return 1;
    }

    return 0;
}

/**
 * @param string $dStart
 * @return bool
 */
function dStartPruefen($dStart)
{
    if (preg_match(
        '/^([0-3]{1}[0-9]{1}[.]{1}[0-1]{1}[0-9]{1}[.]{1}[0-9]{4}[ ]{1}[0-2]{1}[0-9]{1}[:]{1}[0-6]{1}[0-9]{1})/',
        $dStart
    )) {
        return true;
    }

    return false;
}

/**
 * @param string $dateStart
 * @param bool   $asTime
 * @return string
 */
function baueENGDate($dateStart, $asTime = false)
{
    [$date, $time]        = explode(' ', $dateStart);
    [$day, $month, $year] = explode('.', $date);

    return $asTime ? $time : $year . '-' . $month . '-' . $day . ' ' . $time;
}

/**
 * @param int[] $cronIDs
 * @return bool
 */
function loescheExportformatCron(array $cronIDs)
{
    foreach ($cronIDs as $cronID) {
        Shop::Container()->getDB()->delete('tjobqueue', 'cronID', (int)$cronID);
        Shop::Container()->getDB()->delete('tcron', 'cronID', (int)$cronID);
    }

    return true;
}

/**
 * @param int $hours
 * @return array|bool
 */
function holeExportformatQueueBearbeitet($hours)
{
    if (!$hours) {
        $hours = 24;
    } else {
        $hours = (int)$hours;
    }
    $kSprache = isset($_SESSION['kSprache']) ? (int)$_SESSION['kSprache'] : null;
    if (!$kSprache) {
        $oSpracheTMP = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
        if (isset($oSpracheTMP->kSprache) && $oSpracheTMP->kSprache > 0) {
            $kSprache = (int)$oSpracheTMP->kSprache;
        } else {
            return false;
        }
    }

    return Shop::Container()->getDB()->queryPrepared(
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
        ['lid' => $kSprache, 'hrs' => $hours],
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param JTLSmarty $smarty
 * @return string
 */
function exportformatQueueActionErstellen(JTLSmarty $smarty)
{
    $smarty->assign('oExportformat_arr', holeAlleExportformate());

    return 'erstellen';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionEditieren(JTLSmarty $smarty, array &$messages)
{
    $kCron = Request::verifyGPCDataInt('kCron');
    $oCron = $kCron > 0 ? holeCron($kCron) : 0;

    if (is_object($oCron) && $oCron->cronID > 0) {
        $step = 'erstellen';
        $smarty->assign('oCron', $oCron)
               ->assign('oExportformat_arr', holeAlleExportformate());
    } else {
        $messages['error'] .= __('errorWrongQueue');
        $step               = 'uebersicht';
    }

    return $step;
}

/**
 * @param array $messages
 * @return string
 */
function exportformatQueueActionLoeschen(array &$messages)
{
    $kCron_arr = $_POST['kCron'];

    if (is_array($kCron_arr) && count($kCron_arr) > 0) {
        if (loescheExportformatCron($kCron_arr)) {
            $messages['notice'] .= __('successQueueDelete');
        } else {
            $messages['error'] .= __('errorUnknownLong') . '<br />';
        }
    } else {
        $messages['error'] .= __('errorWrongQueue');
    }

    return 'loeschen_result';
}

/**
 * @param array $messages
 * @return string
 */
function exportformatQueueActionTriggern(array &$messages)
{
    global $bCronManuell, $oCron_arr, $oJobQueue_arr;
    $bCronManuell = true;

    require_once PFAD_ROOT . PFAD_INCLUDES . 'cron_inc.php';

    if (is_array($oCron_arr) && is_array($oJobQueue_arr)) {
        $cronCount = count($oCron_arr);
        $jobCount  = count($oJobQueue_arr);

        if ($cronCount === 0 && $jobCount === 0) {
            $messages['error'] .= __('errorCronStart') . '<br />';
        } elseif ($cronCount === 1) {
            $messages['notice'] .= __('successCronStart') . '<br />';
        } elseif ($cronCount > 1) {
            $messages['notice'] .= sprintf(__('successCronsStart'), $cronCount) . '<br />';
        }

        if ($jobCount === 1) {
            $messages['notice'] .= __('successQueueDone') . '<br />';
        } elseif ($jobCount > 1) {
            $messages['notice'] .= sprintf(__('successQueuseDone'), $jobCount) . '<br />';
        }
    }

    return 'triggern';
}

/**
 * @param JTLSmarty $smarty
 * @return string
 */
function exportformatQueueActionFertiggestellt(JTLSmarty $smarty)
{
    $nStunden = Request::verifyGPCDataInt('nStunden');
    if ($nStunden <= 0) {
        $nStunden = 24;
    }

    $_SESSION['exportformatQueue.nStunden'] = $nStunden;
    $smarty->assign('cTab', 'fertig');

    return 'fertiggestellt';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function exportformatQueueActionErstellenEintragen(JTLSmarty $smarty, array &$messages)
{
    $kExportformat = (int)$_POST['kExportformat'];
    $dStart        = $_POST['dStart'];
    $nAlleXStunden = !empty($_POST['nAlleXStundenCustom'])
        ? (int)$_POST['nAlleXStundenCustom']
        : (int)$_POST['nAlleXStunden'];
    $oValues       = new stdClass();

    $oValues->kExportformat = $kExportformat;
    $oValues->dStart        = Text::filterXSS($_POST['dStart']);
    $oValues->nAlleXStunden = Text::filterXSS($_POST['nAlleXStunden']);

    if ($kExportformat > 0) {
        if (dStartPruefen($dStart)) {
            if ($nAlleXStunden >= 1) {
                $kCron   = (isset($_POST['kCron']) && (int)$_POST['kCron'] > 0)
                    ? (int)$_POST['kCron']
                    : null;
                $nStatus = erstelleExportformatCron($kExportformat, $dStart, $nAlleXStunden, $kCron);
                if ($nStatus === 1) {
                    $messages['notice'] .= __('successQueueCreate');
                    $step                = 'erstellen_success';
                } elseif ($nStatus === -1) {
                    $messages['error'] .= __('errorFormatInQueue') . '<br />';
                    $step               = 'erstellen';
                } else {
                    $messages['error'] .= __('errorUnknownLong') . '<br />';
                    $step               = 'erstellen';
                }
            } else { // Alle X Stunden ist entweder leer oder kleiner als 6
                $messages['error'] .= __('errorGreaterEqualOne') . '<br />';
                $step               = 'erstellen';
                $smarty->assign('oFehler', $oValues);
            }
        } else { // Kein gueltiges Datum + Uhrzeit
            $messages['error'] .= __('errorEnterValidDate') . '<br />';
            $step               = 'erstellen';
            $smarty->assign('oFehler', $oValues);
        }
    } else { // Kein gueltiges Exportformat
        $messages['error'] .= __('errorFormatSelect') . '<br />';
        $step               = 'erstellen';
        $smarty->assign('oFehler', $oValues);
    }

    return $step;
}

/**
 * @param string     $cTab
 * @param array|null $messages
 * @return void
 */
function exportformatQueueRedirect($cTab = '', array &$messages = null)
{
    if (isset($messages['notice']) && !empty($messages['notice'])) {
        $_SESSION['exportformatQueue.notice'] = $messages['notice'];
    } else {
        unset($_SESSION['exportformatQueue.notice']);
    }
    if (isset($messages['error']) && !empty($messages['error'])) {
        $_SESSION['exportformatQueue.error'] = $messages['error'];
    } else {
        unset($_SESSION['exportformatQueue.error']);
    }

    $urlParams = null;
    if (!empty($cTab)) {
        $urlParams['tab'] = Text::filterXSS($cTab);
    }

    header('Location: exportformat_queue.php' .
        (is_array($urlParams) ? '?' . http_build_query($urlParams, '', '&') : ''));
    exit;
}

/**
 * @param string    $step
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return void
 */
function exportformatQueueFinalize($step, JTLSmarty $smarty, array &$messages)
{
    if (isset($_SESSION['exportformatQueue.notice'])) {
        $messages['notice'] = $_SESSION['exportformatQueue.notice'];
        unset($_SESSION['exportformatQueue.notice']);
    }
    if (isset($_SESSION['exportformatQueue.error'])) {
        $messages['error'] = $_SESSION['exportformatQueue.error'];
        unset($_SESSION['exportformatQueue.error']);
    }

    switch ($step) {
        case 'uebersicht':
            $nStunden = $_SESSION['exportformatQueue.nStunden'] ?? 24;
            $smarty->assign('oExportformatCron_arr', holeExportformatCron())
                   ->assign('oExportformatQueueBearbeitet_arr', holeExportformatQueueBearbeitet($nStunden))
                   ->assign('nStunden', $nStunden);
            break;
        case 'erstellen_success':
        case 'loeschen_result':
        case 'triggern':
            exportformatQueueRedirect('aktiv', $messages);
            break;
        case 'fertiggestellt':
            exportformatQueueRedirect('fertig', $messages);
            break;
        case 'erstellen':
            if (!empty($messages['error'])) {
                $nStunden = $_SESSION['exportformatQueue.nStunden'] ?? 24;
                $smarty->assign('oExportformatCron_arr', holeExportformatCron())
                       ->assign('oExportformatQueueBearbeitet_arr', holeExportformatQueueBearbeitet($nStunden))
                       ->assign('oExportformat_arr', holeAlleExportformate())
                       ->assign('nStunden', $nStunden);
            }
            break;
        default:
            break;
    }

    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, $messages['error'], 'expoFormatError');
    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_NOTE, $messages['notice'], 'expoFormatNote');

    $smarty->assign('step', $step)
           ->assign('cTab', Request::verifyGPDataString('tab'))
           ->display('exportformat_queue.tpl');
}
