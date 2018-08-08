<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

$return  = 3;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    $zipFile = checkFile();
    $return  = 2;
    if (($syncFiles = unzipSyncFiles($zipFile, PFAD_SYNC_TMP, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        foreach ($syncFiles as $xmlFile) {
            $d        = file_get_contents($xmlFile);
            $xml      = XML_unserialize($d);
            $fileName = pathinfo($xmlFile)['basename'];
            if ($fileName === 'del_kunden.xml') {
                bearbeiteDeletes($xml);
            } elseif ($fileName === 'ack_kunden.xml') {
                bearbeiteAck($xml);
            } elseif ($fileName === 'gutscheine.xml') {
                bearbeiteGutscheine($xml);
            } elseif ($fileName === 'aktiviere_kunden.xml') {
                aktiviereKunden($xml);
            } elseif ($fileName === 'passwort_kunden.xml') {
                generiereNeuePasswoerter($xml);
            }
        }
    }
}

echo $return;

/**
 * @param array $xml
 */
function aktiviereKunden($xml)
{
    $kunden = mapArray($xml['aktiviere_kunden'], 'tkunde', []);
    foreach ($kunden as $kunde) {
        if (!($kunde->kKunde > 0 && $kunde->kKundenGruppe > 0)) {
            continue;
        }
        $kunde_db = new Kunde($kunde->kKunde);

        if ($kunde_db->kKunde > 0 && $kunde_db->kKundengruppe != $kunde->kKundenGruppe) {
            Shop::Container()->getDB()->update(
                'tkunde',
                'kKunde',
                (int)$kunde->kKunde,
                (object)['kKundengruppe' => (int)$kunde->kKundenGruppe]
            );
            $kunde_db->kKundengruppe = (int)$kunde->kKundenGruppe;
            $obj                     = new stdClass();
            $obj->tkunde             = $kunde_db;
            if ($kunde_db->cMail) {
                sendeMail(MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN, $obj);
            }
        }
        Shop::Container()->getDB()->update('tkunde', 'kKunde', (int)$kunde->kKunde, (object)['cAktiv' => 'Y']);
    }
}

/**
 * @param array $xml
 */
function generiereNeuePasswoerter($xml)
{
    $oKundeXML_arr = mapArray($xml['passwort_kunden'], 'tkunde', []);
    foreach ($oKundeXML_arr as $oKundeXML) {
        if (isset($oKundeXML->kKunde) && $oKundeXML->kKunde > 0) {
            $oKunde = new Kunde((int)$oKundeXML->kKunde);
            if ($oKunde->nRegistriert == 1 && $oKunde->cMail) {
                $oKunde->prepareResetPassword();
            } else {
                syncException(
                    'Kunde hat entweder keine Emailadresse oder es ist ein unregistrierter Kunde',
                    FREIDEFINIERBARER_FEHLER
                );
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    if (!isset($xml['del_kunden']['kKunde'])) {
        return;
    }
    if (!is_array($xml['del_kunden']['kKunde'])) {
        $xml['del_kunden']['kKunde'] = [$xml['del_kunden']['kKunde']];
    }
    foreach ($xml['del_kunden']['kKunde'] as $kKunde) {
        $kKunde = (int)$kKunde;
        if ($kKunde > 0) {
            Shop::Container()->getDB()->delete('tkunde', 'kKunde', $kKunde);
            Shop::Container()->getDB()->delete('tlieferadresse', 'kKunde', $kKunde);
            Shop::Container()->getDB()->delete('tkundenattribut', 'kKunde', $kKunde);
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteAck($xml)
{
    if (!isset($xml['ack_kunden']['kKunde'])) {
        return;
    }
    if (!is_array($xml['ack_kunden']['kKunde']) && (int)$xml['ack_kunden']['kKunde'] > 0) {
        $xml['ack_kunden']['kKunde'] = [$xml['ack_kunden']['kKunde']];
    }
    if (is_array($xml['ack_kunden']['kKunde'])) {
        foreach ($xml['ack_kunden']['kKunde'] as $kKunde) {
            $kKunde = (int)$kKunde;
            if ($kKunde > 0) {
                Shop::Container()->getDB()->update('tkunde', 'kKunde', $kKunde, (object)['cAbgeholt' => 'Y']);
            }
        }
    }
}

/**
 * @param array $xml
 */
function bearbeiteGutscheine($xml)
{
    if (!isset($xml['gutscheine']['gutschein']) || !is_array($xml['gutscheine']['gutschein'])) {
        return;
    }
    $gutscheine_arr = mapArray($xml['gutscheine'], 'gutschein', $GLOBALS['mGutschein']);
    foreach ($gutscheine_arr as $gutschein) {
        if (!($gutschein->kGutschein > 0 && $gutschein->kKunde > 0)) {
            continue;
        }
        $gutschein_exists = Shop::Container()->getDB()->select('tgutschein', 'kGutschein', (int)$gutschein->kGutschein);
        if (!isset($gutschein_exists->kGutschein) || !$gutschein_exists->kGutschein) {
            $kGutschein = Shop::Container()->getDB()->insert('tgutschein', $gutschein);
            Shop::Container()->getLogService()->debug('Gutschein fuer kKunde ' .
                (int)$gutschein->kKunde . ' wurde eingeloest. ' .
                print_r($gutschein, true)
            );
            //kundenkto erhöhen
            Shop::Container()->getDB()->query(
                'UPDATE tkunde 
                    SET fGuthaben = fGuthaben + ' . (float)$gutschein->fWert . ' 
                    WHERE kKunde = ' . (int)$gutschein->kKunde,
                \DB\ReturnType::DEFAULT
            );
            Shop::Container()->getDB()->query(
                'UPDATE tkunde 
                    SET fGuthaben = 0 
                    WHERE kKunde = ' . (int)$gutschein->kKunde . ' 
                        AND fGuthaben < 0',
                \DB\ReturnType::AFFECTED_ROWS
            );
            //mail
            $kunde           = new Kunde((int)$gutschein->kKunde);
            $obj             = new stdClass();
            $obj->tkunde     = $kunde;
            $obj->tgutschein = $gutschein;
            if ($kunde->cMail) {
                sendeMail(MAILTEMPLATE_GUTSCHEIN, $obj);
            }
        }
    }
}
