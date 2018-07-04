<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Jobs;


use Cron\Job;
use Cron\JobInterface;
use Cron\QueueEntry;
use DB\DbInterface;
use DB\ReturnType;

require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'statusemail_inc.php';

/**
 * Class Statusmail
 * @package Cron\Jobs
 */
class Statusmail extends Job
{
    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        if (JOBQUEUE_LIMIT_M_STATUSEMAIL > 0) {
            $this->setLimit(JOBQUEUE_LIMIT_M_STATUSEMAIL);
        }
    }


    /**
     * @param string $dStart
     * @param string $cInterval - one of 'hour', 'day', 'week', 'month', 'year'
     * @return bool
     */
    private function isIntervalExceeded(string $dStart, string $cInterval): bool
    {
        if ($dStart === '0000-00-00 00:00:00' || $dStart === '1970-01-01 00:00:00') {
            return true;
        }
        $oStartTime = date_create($dStart);
        if ($oStartTime === false) {
            return false;
        }
        $oEndTime = $oStartTime->modify('+1 ' . $cInterval);
        if ($oEndTime === false) {
            return false;
        }

        return date_create()->format('YmdHis') >= $oEndTime->format('YmdHis');
    }

    /**
     * @param string $dVon
     * @param string $dBis
     * @param array  $logLevels
     * @return array|int|object
     */
    private function getLogEntries(string $dVon, string $dBis, array $logLevels)
    {
        if (strlen($dVon) > 0 && strlen($dBis) > 0) {
            $logLevels  = array_map('intval', $logLevels);
            $logEntries = \Shop::Container()->getDB()->queryPrepared(
                "SELECT *
                    FROM tjtllog
                    WHERE dErstellt >= :from
                        AND dErstellt < :to
                        AND nLevel IN (" . implode(',', $logLevels) . ")
                    ORDER BY dErstellt DESC",
                [
                    'from' => $dVon,
                    'to'   => $dBis
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );

            return $logEntries;
        }

        return [];
    }

    /**
     * @param \stdClass $oStatusemail
     * @param string    $dVon
     * @param string    $dBis
     * @return \stdClass|bool
     * @throws \SmartyException
     */
    private function generateMail(\stdClass $oStatusemail, string $dVon, string $dBis)
    {
        if (!is_array($oStatusemail->nInhalt_arr)
            || empty($dVon)
            || empty($dBis)
            || count($oStatusemail->nInhalt_arr) === 0
        ) {
            return false;
        }

        $cMailTyp                                              = \Shop::Container()->getDB()->select(
            'temailvorlage',
            'cModulId',
            MAILTEMPLATE_STATUSEMAIL,
            null,
            null,
            null,
            null,
            false,
            'cMailTyp'
        )->cMailTyp;
        $oMailObjekt                                           = new \stdClass();
        $oMailObjekt->mail                                     = new \stdClass();
        $oMailObjekt->oAnzahlArtikelProKundengruppe            = -1;
        $oMailObjekt->nAnzahlNeukunden                         = -1;
        $oMailObjekt->nAnzahlNeukundenGekauft                  = -1;
        $oMailObjekt->nAnzahlBestellungen                      = -1;
        $oMailObjekt->nAnzahlBestellungenNeukunden             = -1;
        $oMailObjekt->nAnzahlBesucher                          = -1;
        $oMailObjekt->nAnzahlBesucherSuchmaschine              = -1;
        $oMailObjekt->nAnzahlBewertungen                       = -1;
        $oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet    = -1;
        $oMailObjekt->oAnzahlGezahltesGuthaben                 = -1;
        $oMailObjekt->nAnzahlTags                              = -1;
        $oMailObjekt->nAnzahlTagsNichtFreigeschaltet           = -1;
        $oMailObjekt->nAnzahlGeworbenerKunden                  = -1;
        $oMailObjekt->nAnzahlErfolgreichGeworbenerKunden       = -1;
        $oMailObjekt->nAnzahlVersendeterWunschlisten           = -1;
        $oMailObjekt->nAnzahlDurchgefuehrteUmfragen            = -1;
        $oMailObjekt->nAnzahlNewskommentare                    = -1;
        $oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet = -1;
        $oMailObjekt->nAnzahlProduktanfrageArtikel             = -1;
        $oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit      = -1;
        $oMailObjekt->nAnzahlVergleiche                        = -1;
        $oMailObjekt->nAnzahlGenutzteKupons                    = -1;
        $oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen  = -1;
        $oMailObjekt->nAnzahlVersendeterBestellungen           = -1;
        $oMailObjekt->dVon                                     = $dVon;
        $oMailObjekt->dBis                                     = $dBis;
        $oMailObjekt->oLogEntry_arr                            = [];
        $logLevels                                             = [];

        foreach ($oStatusemail->nInhalt_arr as $nInhalt) {
            switch ($nInhalt) {
                // Anzahl Artikel pro Kundengruppe
                case 1:
                    $oMailObjekt->oAnzahlArtikelProKundengruppe = gibAnzahlArtikelProKundengruppe();
                    break;

                // Anzahl Neukunden
                case 2:
                    $oMailObjekt->nAnzahlNeukunden = gibAnzahlNeukunden($dVon, $dBis);
                    break;

                // Anzahl Neukunden die gekauft haben
                case 3:
                    $oMailObjekt->nAnzahlNeukundenGekauft = gibAnzahlNeukundenGekauft($dVon, $dBis);
                    break;

                // Anzahl Bestellungen
                case 4:
                    $oMailObjekt->nAnzahlBestellungen = gibAnzahlBestellungen($dVon, $dBis);
                    break;

                // Anzahl Bestellungen von Neukunden
                case 5:
                    $oMailObjekt->nAnzahlBestellungenNeukunden = gibAnzahlBestellungenNeukunden($dVon, $dBis);
                    break;

                // Anzahl Besucher
                case 6:
                    $oMailObjekt->nAnzahlBesucher = gibAnzahlBesucher($dVon, $dBis);
                    break;

                // Anzahl Besucher von Suchmaschinen
                case 7:
                    $oMailObjekt->nAnzahlBesucherSuchmaschine = gibAnzahlBesucherSuchmaschine($dVon, $dBis);
                    break;

                // Anzahl Bewertungen
                case 8:
                    $oMailObjekt->nAnzahlBewertungen = gibAnzahlBewertungen($dVon, $dBis);
                    break;

                // Anzahl nicht-freigeschaltete Bewertungen
                case 9:
                    $oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet = gibAnzahlBewertungenNichtFreigeschaltet($dVon,
                        $dBis);
                    break;

                case 10:
                    $oMailObjekt->oAnzahlGezahltesGuthaben = gibAnzahlGezahltesGuthaben($dVon, $dBis);
                    break;

                case 11:
                    $oMailObjekt->nAnzahlTags = gibAnzahlTags($dVon, $dBis);
                    break;

                case 12:
                    $oMailObjekt->nAnzahlTagsNichtFreigeschaltet = gibAnzahlTagsNichtFreigeschaltet($dVon, $dBis);
                    break;

                case 13:
                    $oMailObjekt->nAnzahlGeworbenerKunden = gibAnzahlGeworbenerKunden($dVon, $dBis);
                    break;

                case 14:
                    $oMailObjekt->nAnzahlErfolgreichGeworbenerKunden = gibAnzahlErfolgreichGeworbenerKunden($dVon,
                        $dBis);
                    break;

                case 15:
                    $oMailObjekt->nAnzahlVersendeterWunschlisten = gibAnzahlVersendeterWunschlisten($dVon, $dBis);
                    break;

                case 16:
                    $oMailObjekt->nAnzahlDurchgefuehrteUmfragen = gibAnzahlDurchgefuehrteUmfragen($dVon, $dBis);
                    break;

                case 17:
                    $oMailObjekt->nAnzahlNewskommentare = gibAnzahlNewskommentare($dVon, $dBis);
                    break;

                case 18:
                    $oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet = gibAnzahlNewskommentareNichtFreigeschaltet($dVon,
                        $dBis);
                    break;

                case 19:
                    $oMailObjekt->nAnzahlProduktanfrageArtikel = gibAnzahlProduktanfrageArtikel($dVon, $dBis);
                    break;

                case 20:
                    $oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit = gibAnzahlProduktanfrageVerfuegbarkeit($dVon,
                        $dBis);
                    break;

                case 21:
                    $oMailObjekt->nAnzahlVergleiche = gibAnzahlVergleiche($dVon, $dBis);
                    break;

                case 22:
                    $oMailObjekt->nAnzahlGenutzteKupons = gibAnzahlGenutzteKupons($dVon, $dBis);
                    break;

                // Anzahl Zahlungseingänge von Bestellungen
                case 23:
                    $oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen = gibAnzahlZahlungseingaengeVonBestellungen($dVon,
                        $dBis);
                    break;

                // Anzahl versendeter Bestellungen
                case 24:
                    $oMailObjekt->nAnzahlVersendeterBestellungen = gibAnzahlVersendeterBestellungen($dVon, $dBis);
                    break;

                // Log-Einträge
                case 25:
                    $logLevels[] = JTLLOG_LEVEL_ERROR;
                    break;
                case 26:
                    $logLevels[] = JTLLOG_LEVEL_NOTICE;
                    break;
                case 27:
                    $logLevels[] = JTLLOG_LEVEL_DEBUG;
                    break;
            }
        }

        if (count($logLevels) > 0) {
            $smarty                     = \Shop::Smarty();
            $oMailObjekt->oLogEntry_arr = $this->getLogEntries($dVon, $dBis, $logLevels);
            $cLogFilePath               = tempnam(sys_get_temp_dir(), 'jtl');
            $fileStream                 = fopen($cLogFilePath, 'w');
            $smarty->assign('oMailObjekt', $oMailObjekt);
            $oAttachment            = new \stdClass();
            $oAttachment->cFilePath = $cLogFilePath;

            if ($cMailTyp === 'text') {
                fwrite($fileStream, $smarty->fetch(PFAD_ROOT . PFAD_EMAILVORLAGEN . 'ger/email_bericht_plain_log.tpl'));
                $oAttachment->cName = 'jtl-log-digest.txt';
            } else {
                fwrite($fileStream, $smarty->fetch(PFAD_ROOT . PFAD_EMAILVORLAGEN . 'ger/email_bericht_html_log.tpl'));
                $oAttachment->cName = 'jtl-log-digest.html';
            }

            fclose($fileStream);
            $oMailObjekt->mail->oAttachment_arr = [$oAttachment];
        }

        $oMailObjekt->mail->toEmail = $oStatusemail->cEmail;

        return $oMailObjekt;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $jobData = $this->getJobData();
        $sent    = false;
        \Shop::dbg($jobData, false, 'jobdata@mail:');

        if ($jobData === null) {
            return $this;
        }

        $jobData->nIntervall_arr = \StringHandler::parseSSK($jobData->cIntervall);
        $jobData->nInhalt_arr    = \StringHandler::parseSSK($jobData->cInhalt);

        // Laufe alle Intervalle durch
        foreach ($jobData->nIntervall_arr as $nIntervall) {
            $nIntervall         = (int)$nIntervall;
            $cInterval          = '';
            $cIntervalAdj       = '';
            $dLetzterVersand    = '';
            $dLetzterVersandCol = '';

            switch ($nIntervall) {
                case 1:
                    $cInterval          = 'day';
                    $cIntervalAdj       = 'Tägliche';
                    $dLetzterVersand    = $jobData->dLetzterTagesVersand;
                    $dLetzterVersandCol = 'dLetzterTagesVersand';
                    break;
                case 7:
                    $cInterval          = 'week';
                    $cIntervalAdj       = 'Wöchentliche';
                    $dLetzterVersand    = $jobData->dLetzterWochenVersand;
                    $dLetzterVersandCol = 'dLetzterWochenVersand';
                    break;
                case 30:
                    $cInterval          = 'month';
                    $cIntervalAdj       = 'Monatliche';
                    $dLetzterVersand    = $jobData->dLetzterMonatsVersand;
                    $dLetzterVersandCol = 'dLetzterMonatsVersand';
                    break;
                default:
                    break;
            }

            if ($this->isIntervalExceeded($dLetzterVersand, $cInterval)) {
                $dVon        = $dLetzterVersand;
                $dBis        = date_create()->format('Y-m-d H:i:s');
                $oMailObjekt = $this->generateMail($jobData, $dVon, $dBis);

                if ($oMailObjekt) {
                    $oMailObjekt->cIntervall = $cIntervalAdj . ' Status-Email';
//                    \Shop::dbg($oMailObjekt, true, 'sending:');
                    $res = sendeMail(MAILTEMPLATE_STATUSEMAIL, $oMailObjekt, $oMailObjekt->mail);
                    \Shop::dbg($res, false, 'sendREs:');
                    \Shop::Container()->getDB()->query(
                        "UPDATE tstatusemail
                            SET " . $dLetzterVersandCol . " = now()
                            WHERE nAktiv = " . $queueEntry->kKey,
                        ReturnType::DEFAULT
                    );
                    $sent = true;

                    if (isset($oMailObjekt->mail->oAttachment_arr)) {
                        unlink($oMailObjekt->mail->oAttachment_arr[0]->cFilePath);
                    }
                }
            }
        }
        $this->setFinished($sent);
        $this->setFinished(false);

        return $this;
    }
}
