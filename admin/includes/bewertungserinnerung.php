<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

/**
 *
 */
function baueBewertungsErinnerung()
{
    $conf = holeBewertungserinnerungSettings();
    if (!is_array($conf) || count($conf) === 0) {
        return;
    }
    $customerGroups = $conf['bewertungserinnerung_kundengruppen'];
    if ($conf['bewertungserinnerung_nutzen'] !== 'Y' || $conf['bewertung_anzeigen'] !== 'Y') {
        return;
    }
    $shippingDays = (int)$conf['bewertungserinnerung_versandtage'];
    if ($shippingDays <= 0) {
        Shop::Container()->getLogService()->error('Einstellung bewertungserinnerung_versandtage ist 0');
        return;
    }
    // Baue SQL mit allen erlaubten Kundengruppen
    $sql = '';
    if (is_array($customerGroups) && count($customerGroups) > 0) {
        foreach ($customerGroups as $i => $groupID) {
            if (is_numeric($groupID)) {
                if ($i > 0) {
                    $sql .= ' OR tkunde.kKundengruppe = ' . (int)$groupID;
                } else {
                    $sql .= ' tkunde.kKundengruppe = ' . (int)$groupID;
                }
            }
        }
    } else {
        // Hole standard Kundengruppe
        $default = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
        if (isset($default->kKundengruppe) && $default->kKundengruppe > 0) {
            $sql = ' tkunde.kKundengruppe = ' . (int)$default->kKundengruppe;
        }
    }
    if (empty($sql)) {
        return;
    }
    $maxDays = $shippingDays * 2;
    if ($shippingDays === 1) {
        $maxDays = 4;
    }

    // --TO-CHECK--
    $config = Shop::getSettings([    // --TODO-- create a config for that (migration, db, and so on)
        CONF_EMAILS
    ]);
    /* fake */ $config['emails']['email_reviewreminder_allowed'] = 'bound_to_newsletter'; // e.g. [1|2|3] or better ['bound_to_newsletter'|'allways'|'never']
    if ($config['emails']['email_reviewreminder_allowed'] === 'bound_to_newsletter') {
        $cQuery = 'SELECT kBestellung
                FROM tbestellung
                    JOIN tkunde ON tkunde.kKunde = tbestellung.kKunde
                    LEFT JOIN tnewsletterempfaenger ON tkunde.kKunde = tnewsletterempfaenger.kKunde
                WHERE dVersandDatum IS NOT NULL
                    AND DATE_ADD(dVersandDatum, INTERVAL ' . $nVersandTage . ' DAY) <= NOW()
                    AND DATE_ADD(dVersandDatum, INTERVAL ' . $nMaxTage . ' DAY) > NOW()
                    AND cStatus = 4
                    AND (' . $cSQL . ')
                    AND dBewertungErinnerung IS NULL
                    AND tnewsletterempfaenger.nAktiv = 1';
    } elseif ($config['emails']['email_reviewreminder_allowed'] === 'allways') {
        $cQuery = 'SELECT kBestellung
                FROM tbestellung
                JOIN tkunde
                    ON tkunde.kKunde = tbestellung.kKunde
                WHERE dVersandDatum IS NOT NULL
                    AND DATE_ADD(dVersandDatum, INTERVAL ' . $nVersandTage . ' DAY) <= NOW()
                    AND DATE_ADD(dVersandDatum, INTERVAL ' . $nMaxTage . ' DAY) > NOW()
                    AND cStatus = 4
                    AND (' . $cSQL . ')
                    AND dBewertungErinnerung IS NULL';
    } else {
        return;  // --TO-CHECK-- (do nothing (?)this way ... the "send never"-thing)
    }
    // --TO-CHECK--

    $oBestellungen_arr = Shop::Container()->getDB()->query($cQuery, \DB\ReturnType::ARRAY_OF_OBJECTS);
    if (count($oBestellungen_arr) === 0) {
        Shop::Container()->getLogService()->debug('Keine Bestellungen für Bewertungserinnerungen gefunden.');
        return;
    }
    foreach ($orders as $orderData) {
        $openReviews = [];
        $order       = new Bestellung($orderData->kBestellung);
        $order->fuelleBestellung(false);
        $customer         = new Kunde($order->kKunde ?? 0);
        $obj              = new stdClass();
        $obj->tkunde      = $customer;
        $obj->tbestellung = $order;
        foreach ($order->Positionen as $position) {
            if ($position->kArtikel <= 0) {
                continue;
            }
            $productVisible = (new Artikel())->fuelleArtikel(
                (int)$position->kArtikel,
                null,
                (int)$customer->kKundengruppe
            );
            if ($productVisible !== null && $productVisible->kArtikel > 0) {
                $res = Shop::Container()->getDB()->query(
                    'SELECT kBewertung
                        FROM tbewertung
                        WHERE kArtikel = ' . (int)$position->kArtikel . '
                            AND kKunde = ' . (int)$order->kKunde,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if ($res === false) {
                    $openReviews[] = $position;
                }
            }
        }

        if (count($openReviews) === 0) {
            continue;
        }

        $order->Positionen = $openReviews;

        Shop::Container()->getDB()->query(
            'UPDATE tbestellung
                SET dBewertungErinnerung = NOW()
                WHERE kBestellung = ' . (int)$orderData->kBestellung,
            \DB\ReturnType::AFFECTED_ROWS
        );
        $logger = Shop::Container()->getLogService();
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->withName('Bewertungserinnerung')->debug(
                'Kunde und Bestellung aus baueBewertungsErinnerung (Mail versendet): <pre>' .
                print_r($obj, true) .
                '</pre>',
                [$orderData->kBestellung]
            );
        }

        sendeMail(MAILTEMPLATE_BEWERTUNGERINNERUNG, $obj);
    }
}
