<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

$return = 3;
if (auth()) {
    checkFile();
    $return  = 2;
    $archive = new PclZip($_FILES['data']['tmp_name']);

    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('Entpacke: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
    }
    if ($list = $archive->listContent()) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Anzahl Dateien im Zip: ' . count($list), JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
        }
        if ($archive->extract(PCLZIP_OPT_PATH, PFAD_SYNC_TMP)) {
            $return = 0;
            foreach ($list as $zip) {
                if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                    Jtllog::writeLog('bearbeite: ' . PFAD_SYNC_TMP . $zip['filename'] . ' size: ' . filesize(PFAD_SYNC_TMP . $zip['filename']), JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
                }
                $d   = file_get_contents(PFAD_SYNC_TMP . $zip['filename']);
                $xml = XML_unserialize($d);
                if ($zip['filename'] === 'del_kunden.xml') {
                    bearbeiteDeletes($xml);
                } elseif ($zip['filename'] === 'ack_kunden.xml') {
                    bearbeiteAck($xml);
                } elseif ($zip['filename'] === 'gutscheine.xml') {
                    bearbeiteGutscheine($xml);
                } elseif ($zip['filename'] === 'aktiviere_kunden.xml') {
                    aktiviereKunden($xml);
                } elseif ($zip['filename'] === 'passwort_kunden.xml') {
                    generiereNeuePasswoerter($xml);
                }
            }
        } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
            Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Kunden_xml');
        }
    } elseif (Jtllog::doLog(JTLLOG_LEVEL_ERROR)) {
        Jtllog::writeLog('Error : ' . $archive->errorInfo(true), JTLLOG_LEVEL_ERROR, false, 'Kunden_xml');
    }
}

if ($return == 1) {
    syncException('Error : ' . $archive->errorInfo(true));
}

echo $return;

/**
 * @param array $xml
 */
function aktiviereKunden($xml)
{
    $kunden = mapArray($xml['aktiviere_kunden'], 'tkunde', array());
    foreach ($kunden as $kunde) {
        if ($kunde->kKunde > 0 && $kunde->kKundenGruppe > 0) {
            $kunde_db = new Kunde($kunde->kKunde);

            if ($kunde_db->kKunde > 0 && $kunde_db->kKundengruppe != $kunde->kKundenGruppe) {
                Shop::DB()->query("UPDATE tkunde SET kKundengruppe = " . (int)$kunde->kKundenGruppe . " WHERE kKunde = " . (int)$kunde->kKunde, 4);
                //mail
                $kunde_db->kKundengruppe = (int)$kunde->kKundenGruppe;
                $obj                     = new stdClass();
                $obj->tkunde             = $kunde_db;
                if ($kunde_db->cMail) {
                    sendeMail(MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN, $obj);
                }
            }
            Shop::DB()->query("UPDATE tkunde SET cAktiv = 'Y' WHERE kKunde = " . (int)$kunde->kKunde, 4);
        }
    }
}

/**
 * @param array $xml
 */
function generiereNeuePasswoerter($xml)
{
    $oKundeXML_arr = mapArray($xml['passwort_kunden'], 'tkunde', array());
    foreach ($oKundeXML_arr as $oKundeXML) {
        if (isset($oKundeXML->kKunde) && $oKundeXML->kKunde > 0) {
            $oKunde = new Kunde((int)$oKundeXML->kKunde);
            if ($oKunde->nRegistriert == 1 && $oKunde->cMail) {
                $oKunde->prepareResetPassword($oKunde->cMail);
            } else {
                syncException("Kunde hat entweder keine Emailadresse oder es ist ein unregistrierter Kunde", 8);
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    if (isset($xml['del_kunden']['kKunde'])) {
        if (is_array($xml['del_kunden']['kKunde'])) {
            foreach ($xml['del_kunden']['kKunde'] as $kKunde) {
                $kKunde = (int)$kKunde;
                if ($kKunde > 0) {
                    Shop::DB()->query("DELETE FROM tkunde WHERE kKunde = " . $kKunde, 4);
                    Shop::DB()->query("DELETE FROM tlieferadresse WHERE kKunde = " . $kKunde, 4);
                    Shop::DB()->query("DELETE FROM tkundenattribut WHERE kKunde = " . $kKunde, 4);
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog('Kunde geloescht: ' . $kKunde, JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
                    }
                }
            }
        } elseif ((int)$xml['del_kunden']['kKunde'] > 0) {
            $kKunde = (int)$xml['del_kunden']['kKunde'];
            Shop::DB()->query("DELETE FROM tkunde WHERE kKunde = " . $kKunde, 4);
            Shop::DB()->query("DELETE FROM tlieferadresse WHERE kKunde = " . $kKunde, 4);
            Shop::DB()->query("DELETE FROM tkundenattribut WHERE kKunde = " . $kKunde, 4);
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Kunde geloescht: ' . $kKunde, JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteAck($xml)
{
    if (isset($xml['ack_kunden']['kKunde'])) {
        if (is_array($xml['ack_kunden']['kKunde'])) {
            foreach ($xml['ack_kunden']['kKunde'] as $kKunde) {
                $kKunde = (int)$kKunde;
                if ($kKunde > 0) {
                    Shop::DB()->query("UPDATE tkunde SET cAbgeholt = 'Y' WHERE kKunde = " . $kKunde, 4);
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog('Kunde erfolgreich abgeholt: ' . $kKunde, JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
                    }
                }
            }
        } elseif ((int)$xml['ack_kunden']['kKunde'] > 0) {
            $kKunde = (int)$xml['ack_kunden']['kKunde'];
            Shop::DB()->query("UPDATE tkunde SET cAbgeholt = 'Y' WHERE kKunde = " . $kKunde, 4);
            if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                Jtllog::writeLog('Kunde erfolgreich abgeholt: ' . $kKunde, JTLLOG_LEVEL_DEBUG, false, 'Kunden_xml');
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteGutscheine($xml)
{
    if (isset($xml['gutscheine']['gutschein']) && is_array($xml['gutscheine']['gutschein'])) {
        $gutscheine_arr = mapArray($xml['gutscheine'], 'gutschein', $GLOBALS['mGutschein']);
        foreach ($gutscheine_arr as $gutschein) {
            if ($gutschein->kGutschein > 0 && $gutschein->kKunde > 0) {
                $gutschein_exists = Shop::DB()->query("SELECT kGutschein FROM tgutschein WHERE kGutschein = " . (int)$gutschein->kGutschein, 1);
                if (!isset($gutschein_exists->kGutschein) || !$gutschein_exists->kGutschein) {
                    $kGutschein = Shop::DB()->insert('tgutschein', $gutschein);
                    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                        Jtllog::writeLog('Gutschein fuer kKunde ' . intval($gutschein->kKunde) . ' wurde eingeloest. ' . print_r($gutschein, true), JTLLOG_LEVEL_DEBUG, 'kGutschein', $kGutschein);
                    }
                    //kundenkto erhöhen
                    Shop::DB()->query("UPDATE tkunde SET fGuthaben = fGuthaben+" . floatval($gutschein->fWert) . " WHERE kKunde = " . (int)$gutschein->kKunde, 4);
                    Shop::DB()->query("UPDATE tkunde SET fGuthaben = 0 WHERE kKunde = " . (int)$gutschein->kKunde . " AND fGuthaben < 0", 3);
                    //mail
                    $kunde           = new Kunde((int)$gutschein->kKunde);
                    $obj             = new stdClass();
                    $obj->tkunde     = $kunde;
                    $obj->tgutschein = $gutschein;
                    if ($kunde->cMail) {
                        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
                            Jtllog::writeLog('Gutschein Email wurde an ' . $kunde->cMail . ' versendet.', JTLLOG_LEVEL_DEBUG, 'kGutschein', $kGutschein);
                        }
                        sendeMail(MAILTEMPLATE_GUTSCHEIN, $obj);
                    }
                }
            }
        }
    }
}
