<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Backend\Revision;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Hersteller;
use JTL\Kampagne;
use JTL\Catalog\Category\Kategorie;
use JTL\Customer\Kunde;
use JTL\Shop;
use JTL\Smarty\MailSmarty;
use JTL\Sprache;
use JTL\DB\ReturnType;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\ContextType;
use JTL\Smarty\SmartyResourceNiceDB;
use JTL\Session\Frontend;
use JTL\Alert\Alert;

require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

/**
 * @param array $conf
 * @return JTLSmarty
 */
function bereiteNewsletterVor($conf)
{
    $db         = Shop::Container()->getDB();
    $mailSmarty = new MailSmarty($db, ContextType::NEWSLETTER);

    return $mailSmarty
        ->assign('Firma', $db->query(
            'SELECT *  FROM tfirma',
            ReturnType::SINGLE_OBJECT
        ))
       ->assign('URL_SHOP', Shop::getURL())
       ->assign('Einstellungen', $conf);
}

/**
 * @param JTLSmarty $mailSmarty
 * @param object    $newsletter
 * @param array     $conf
 * @param stdClass  $recipients
 * @param array     $products
 * @param array     $manufacturers
 * @param array     $categories
 * @param string    $campaign
 * @param string    $oKunde
 * @return string|bool
 */
function versendeNewsletter(
    $mailSmarty,
    $newsletter,
    $conf,
    $recipients,
    $products = [],
    $manufacturers = [],
    $categories = [],
    $campaign = '',
    $oKunde = ''
) {
    $mailSmarty->assign('oNewsletter', $newsletter)
               ->assign('Emailempfaenger', $recipients)
               ->assign('Kunde', $oKunde)
               ->assign('Artikelliste', $products)
               ->assign('Herstellerliste', $manufacturers)
               ->assign('Kategorieliste', $categories)
               ->assign('Kampagne', $campaign)
               ->assign(
                   'cNewsletterURL',
                   Shop::getURL() .
                   '/newsletter.php?show=' .
                   ($newsletter->kNewsletter ?? '0')
               );
    $net      = 0;
    $bodyHtml = '';
    if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
        $oKundengruppe = Shop::Container()->getDB()->query(
            'SELECT tkundengruppe.nNettoPreise
                FROM tkunde
                JOIN tkundengruppe
                    ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
                WHERE tkunde.kKunde = ' . (int)$oKunde->kKunde,
            ReturnType::SINGLE_OBJECT
        );
        if (isset($oKundengruppe->nNettoPreise)) {
            $net = $oKundengruppe->nNettoPreise;
        }
    }

    $mailSmarty->assign('NettoPreise', $net);

    $cPixel = '';
    if (isset($campaign->kKampagne) && $campaign->kKampagne > 0) {
        $cPixel = '<br /><img src="' . Shop::getURL() . '/' . PFAD_INCLUDES .
            'newslettertracker.php?kK=' . $campaign->kKampagne .
            '&kN=' . ($newsletter->kNewsletter ?? 0) . '&kNE=' .
            ($recipients->kNewsletterEmpfaenger ?? 0) . '" alt="Newsletter" />';
    }

    $cTyp = 'VL';
    $nKey = $newsletter->kNewsletterVorlage ?? 0;
    if (isset($newsletter->kNewsletter) && $newsletter->kNewsletter > 0) {
        $cTyp = 'NL';
        $nKey = $newsletter->kNewsletter;
    }
    if ($newsletter->cArt === 'text/html' || $newsletter->cArt === 'html') {
        try {
            $bodyHtml = $mailSmarty->fetch('db:' . $cTyp . '_' . $nKey . '_html') . $cPixel;
        } catch (Exception $e) {
            Shop::Smarty()->assign('oSmartyError', $e->getMessage());

            return $e->getMessage();
        }
    }
    try {
        $bodyText = $mailSmarty->fetch('db:' . $cTyp . '_' . $nKey . '_text');
    } catch (Exception $e) {
        Shop::Smarty()->assign('oSmartyError', $e->getMessage());

        return $e->getMessage();
    }
    $mail          = new stdClass();
    $mail->toEmail = $recipients->cEmail;
    $mail->toName  = ($recipients->cVorname ?? '') . ' ' . ($recipients->cNachname ?? '');
    if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
        $mail->toName = ($oKunde->cVorname ?? '') . ' ' . ($oKunde->cNachname ?? '');
    }

    $mail->fromEmail     = $conf['newsletter']['newsletter_emailadresse'];
    $mail->fromName      = $conf['newsletter']['newsletter_emailabsender'];
    $mail->replyToEmail  = $conf['newsletter']['newsletter_emailadresse'];
    $mail->replyToName   = $conf['newsletter']['newsletter_emailabsender'];
    $mail->subject       = $newsletter->cBetreff;
    $mail->bodyText      = $bodyText;
    $mail->bodyHtml      = $bodyHtml;
    $mail->lang          = Sprache::getIsoFromLangID((int)$newsletter->kSprache)->cISO;
    $mail->methode       = $conf['newsletter']['newsletter_emailmethode'];
    $mail->sendmail_pfad = $conf['newsletter']['newsletter_sendmailpfad'];
    $mail->smtp_hostname = $conf['newsletter']['newsletter_smtp_host'];
    $mail->smtp_port     = $conf['newsletter']['newsletter_smtp_port'];
    $mail->smtp_auth     = $conf['newsletter']['newsletter_smtp_authnutzen'];
    $mail->smtp_user     = $conf['newsletter']['newsletter_smtp_benutzer'];
    $mail->smtp_pass     = $conf['newsletter']['newsletter_smtp_pass'];
    $mail->SMTPSecure    = $conf['newsletter']['newsletter_smtp_verschluesselung'];
    verschickeMail($mail);

    return true;
}

/**
 * @param JTLSmarty       $mailSmarty
 * @param object          $newsletter
 * @param array           $products
 * @param array           $manufacturers
 * @param array           $categories
 * @param string          $campaign
 * @param stdClass|string $recipient
 * @param stdClass|string $customer
 * @return string
 */
function gibStaticHtml(
    $mailSmarty,
    $newsletter,
    $products = [],
    $manufacturers = [],
    $categories = [],
    $campaign = '',
    $recipient = '',
    $customer = ''
) {
    $mailSmarty->assign('Emailempfaenger', $recipient)
               ->assign('Kunde', $customer)
               ->assign('Artikelliste', $products)
               ->assign('Herstellerliste', $manufacturers)
               ->assign('Kategorieliste', $categories)
               ->assign('Kampagne', $campaign);

    $cTyp = 'VL';
    $nKey = $newsletter->kNewsletterVorlage ?? null;
    if ($newsletter->kNewsletter > 0) {
        $cTyp = 'NL';
        $nKey = $newsletter->kNewsletter;
    }

    return $mailSmarty->fetch('db:' . $cTyp . '_' . $nKey . '_html');
}

/**
 * @param array $post
 * @return array|null|stdClass
 */
function speicherVorlage($post)
{
    $alertHelper = Shop::Container()->getAlertService();
    $tpl         = null;
    $checks      = pruefeVorlage(
        $post['cName'],
        $post['kKundengruppe'],
        $post['cBetreff'],
        $post['cArt'],
        $post['cHtml'],
        $post['cText']
    );

    if (is_array($checks) && count($checks) === 0) {
        $GLOBALS['step'] = 'uebersicht';

        $dTag    = $post['dTag'];
        $dMonat  = $post['dMonat'];
        $dJahr   = $post['dJahr'];
        $dStunde = $post['dStunde'];
        $dMinute = $post['dMinute'];

        $dZeitDB = $dJahr . '-' . $dMonat . '-' . $dTag . ' ' . $dStunde . ':' . $dMinute . ':00';
        $oZeit   = baueZeitAusDB($dZeitDB);

        $kNewsletterVorlage = isset($post['kNewsletterVorlage'])
            ? (int)$post['kNewsletterVorlage']
            : null;
        $kKampagne          = (int)$post['kKampagne'];
        $cArtikel           = $post['cArtikel'];
        $cHersteller        = $post['cHersteller'];
        $cKategorie         = $post['cKategorie'];
        $cKundengruppe      = ';' . implode(';', $post['kKundengruppe']) . ';';
        $cArtikel           = ';' . $cArtikel . ';';
        $cHersteller        = ';' . $cHersteller . ';';
        $cKategorie         = ';' . $cKategorie . ';';
        $tpl                = new stdClass();
        if ($kNewsletterVorlage !== null) {
            $tpl->kNewsletterVorlage = $kNewsletterVorlage;
        }
        $tpl->kSprache      = (int)$_SESSION['kSprache'];
        $tpl->kKampagne     = $kKampagne;
        $tpl->cName         = $post['cName'];
        $tpl->cBetreff      = $post['cBetreff'];
        $tpl->cArt          = $post['cArt'];
        $tpl->cArtikel      = $cArtikel;
        $tpl->cHersteller   = $cHersteller;
        $tpl->cKategorie    = $cKategorie;
        $tpl->cKundengruppe = $cKundengruppe;
        $tpl->cInhaltHTML   = $post['cHtml'];
        $tpl->cInhaltText   = $post['cText'];

        $dt              = new DateTime($oZeit->dZeit);
        $now             = new DateTime();
        $tpl->dStartZeit = ($dt > $now)
            ? $dt->format('Y-m-d H:i:s')
            : $now->format('Y-m-d H:i:s');
        if (isset($post['kNewsletterVorlage']) && (int)$post['kNewsletterVorlage'] > 0) {
            $revision = new Revision(Shop::Container()->getDB());
            $revision->addRevision('newsletter', $kNewsletterVorlage, true);
            $upd                = new stdClass();
            $upd->cName         = $tpl->cName;
            $upd->kKampagne     = $tpl->kKampagne;
            $upd->cBetreff      = $tpl->cBetreff;
            $upd->cArt          = $tpl->cArt;
            $upd->cArtikel      = $tpl->cArtikel;
            $upd->cHersteller   = $tpl->cHersteller;
            $upd->cKategorie    = $tpl->cKategorie;
            $upd->cKundengruppe = $tpl->cKundengruppe;
            $upd->cInhaltHTML   = $tpl->cInhaltHTML;
            $upd->cInhaltText   = $tpl->cInhaltText;
            $upd->dStartZeit    = $tpl->dStartZeit;
            Shop::Container()->getDB()->update('tnewslettervorlage', 'kNewsletterVorlage', $kNewsletterVorlage, $upd);
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successNewsletterTemplateEdit'), $tpl->cName),
                'successNewsletterTemplateEdit'
            );
        } else {
            $kNewsletterVorlage = Shop::Container()->getDB()->insert('tnewslettervorlage', $tpl);
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successNewsletterTemplateSave'), $tpl->cName),
                'successNewsletterTemplateSave'
            );
        }
        $tpl->kNewsletterVorlage = $kNewsletterVorlage;

        return $tpl;
    }

    return $checks;
}

/**
 * @param object $defaultTpl
 * @param int    $kNewslettervorlageStd
 * @param array  $post
 * @param int    $templateID
 * @return array
 */
function speicherVorlageStd($defaultTpl, int $kNewslettervorlageStd, $post, int $templateID): array
{
    $checks = [];
    if ($kNewslettervorlageStd <= 0) {
        return $checks;
    }
    $db = Shop::Container()->getDB();
    if (!isset($post['kKundengruppe'])) {
        $post['kKundengruppe'] = null;
    }
    $checks = pruefeVorlageStd(
        $post['cName'],
        $post['kKundengruppe'],
        $post['cBetreff'],
        $post['cArt']
    );

    if (!is_array($checks) || count($checks) !== 0) {
        return $checks;
    }
    $dTag    = $post['dTag'];
    $dMonat  = $post['dMonat'];
    $dJahr   = $post['dJahr'];
    $dStunde = $post['dStunde'];
    $dMinute = $post['dMinute'];
    $dZeitDB = $dJahr . '-' . $dMonat . '-' . $dTag . ' ' . $dStunde . ':' . $dMinute . ':00';
    $oZeit   = baueZeitAusDB($dZeitDB);

    $cArtikel      = ';' . $post['cArtikel'] . ';';
    $cHersteller   = ';' . $post['cHersteller'] . ';';
    $cKategorie    = ';' . $post['cKategorie'] . ';';
    $cKundengruppe = ';' . implode(';', $post['kKundengruppe']) . ';';
    if (isset($defaultTpl->oNewslettervorlageStdVar_arr)
        && is_array($defaultTpl->oNewslettervorlageStdVar_arr)
        && count($defaultTpl->oNewslettervorlageStdVar_arr) > 0
    ) {
        foreach ($defaultTpl->oNewslettervorlageStdVar_arr as $i => $nlTplStdVar) {
            if ($nlTplStdVar->cTyp === 'TEXT') {
                $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cInhalt =
                    $post['kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar];
            }
            if ($nlTplStdVar->cTyp === 'BILD') {
                $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cLinkURL = $post['cLinkURL'];
                $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cAltTag  = $post['cAltTag'];
            }
        }
    }

    $tpl                        = new stdClass();
    $tpl->kNewslettervorlageStd = $kNewslettervorlageStd;
    $tpl->kKampagne             = (int)$post['kKampagne'];
    $tpl->kSprache              = $_SESSION['kSprache'];
    $tpl->cName                 = $post['cName'];
    $tpl->cBetreff              = $post['cBetreff'];
    $tpl->cArt                  = $post['cArt'];
    $tpl->cArtikel              = $cArtikel;
    $tpl->cHersteller           = $cHersteller;
    $tpl->cKategorie            = $cKategorie;
    $tpl->cKundengruppe         = $cKundengruppe;
    $tpl->cInhaltHTML           = mappeVorlageStdVar(
        $defaultTpl->cInhaltHTML,
        $defaultTpl->oNewslettervorlageStdVar_arr
    );
    $tpl->cInhaltText           = mappeVorlageStdVar(
        $defaultTpl->cInhaltText,
        $defaultTpl->oNewslettervorlageStdVar_arr,
        true
    );

    $dt  = new DateTime($oZeit->dZeit);
    $now = new DateTime();

    $tpl->dStartZeit = ($dt > $now)
        ? $dt->format('Y-m-d H:i:s')
        : $now->format('Y-m-d H:i:s');

    if ($templateID > 0) {
        $revision = new Revision($db);
        $revision->addRevision('newsletterstd', $templateID, true);

        $upd                = new stdClass();
        $upd->cName         = $tpl->cName;
        $upd->cBetreff      = $tpl->cBetreff;
        $upd->kKampagne     = (int)$tpl->kKampagne;
        $upd->cArt          = $tpl->cArt;
        $upd->cArtikel      = $tpl->cArtikel;
        $upd->cHersteller   = $tpl->cHersteller;
        $upd->cKategorie    = $tpl->cKategorie;
        $upd->cKundengruppe = $tpl->cKundengruppe;
        $upd->cInhaltHTML   = $tpl->cInhaltHTML;
        $upd->cInhaltText   = $tpl->cInhaltText;
        $upd->dStartZeit    = $tpl->dStartZeit;
        $db->update(
            'tnewslettervorlage',
            'kNewsletterVorlage',
            $templateID,
            $upd
        );
    } else {
        $templateID = $db->insert('tnewslettervorlage', $tpl);
    }
    if ($templateID > 0
        && isset($defaultTpl->oNewslettervorlageStdVar_arr)
        && is_array($defaultTpl->oNewslettervorlageStdVar_arr)
        && count($defaultTpl->oNewslettervorlageStdVar_arr) > 0
    ) {
        $db->delete(
            'tnewslettervorlagestdvarinhalt',
            'kNewslettervorlage',
            $templateID
        );
        $uploadDir = PFAD_ROOT . PFAD_BILDER . PFAD_NEWSLETTERBILDER;
        foreach ($defaultTpl->oNewslettervorlageStdVar_arr as $i => $nlTplStdVar) {
            $imageExists = false;
            if ($nlTplStdVar->cTyp === 'BILD') {
                if (!is_dir($uploadDir . $templateID)) {
                    mkdir($uploadDir . $templateID);
                }
                $idx = 'kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar;
                if (isset($_FILES[$idx]['name']) && mb_strlen($_FILES[$idx]['name']) > 0) {
                    $file = $uploadDir . $templateID .
                        '/kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar .
                        mappeFileTyp($_FILES['kNewslettervorlageStdVar_' .
                            $nlTplStdVar->kNewslettervorlageStdVar]['type']);
                    if (file_exists($file)) {
                        unlink($file);
                    }
                    move_uploaded_file(
                        $_FILES['kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar]['tmp_name'],
                        $file
                    );
                    if (isset($post['cLinkURL']) && mb_strlen($post['cLinkURL']) > 0) {
                        $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cLinkURL = $post['cLinkURL'];
                    }
                    if (isset($post['cAltTag']) && mb_strlen($post['cAltTag']) > 0) {
                        $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cAltTag = $post['cAltTag'];
                    }
                    $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cInhalt =
                        Shop::getURL() . '/' . PFAD_BILDER . PFAD_NEWSLETTERBILDER . $templateID .
                        '/kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar .
                        mappeFileTyp(
                            $_FILES['kNewslettervorlageStdVar_' .
                            $nlTplStdVar->kNewslettervorlageStdVar]['type']
                        );

                    $imageExists = true;
                }
            }

            $nlTplContent                           = new stdClass();
            $nlTplContent->kNewslettervorlageStdVar = $nlTplStdVar->kNewslettervorlageStdVar;
            $nlTplContent->kNewslettervorlage       = $templateID;
            if ($nlTplStdVar->cTyp === 'TEXT') {
                $nlTplContent->cInhalt = $nlTplStdVar->cInhalt;
            } elseif ($nlTplStdVar->cTyp === 'BILD') {
                if ($imageExists) {
                    $nlTplContent->cInhalt = $defaultTpl->oNewslettervorlageStdVar_arr[$i]->cInhalt;
                    if (isset($post['cLinkURL']) && mb_strlen($post['cLinkURL']) > 0) {
                        $nlTplContent->cLinkURL = $post['cLinkURL'];
                    }
                    if (isset($post['cAltTag']) && mb_strlen($post['cAltTag']) > 0) {
                        $nlTplContent->cAltTag = $post['cAltTag'];
                    }
                    $upd              = new stdClass();
                    $upd->cInhaltHTML = mappeVorlageStdVar(
                        $defaultTpl->cInhaltHTML,
                        $defaultTpl->oNewslettervorlageStdVar_arr
                    );
                    $upd->cInhaltText = mappeVorlageStdVar(
                        $defaultTpl->cInhaltText,
                        $defaultTpl->oNewslettervorlageStdVar_arr,
                        true
                    );
                    $db->update(
                        'tnewslettervorlage',
                        'kNewsletterVorlage',
                        $templateID,
                        $upd
                    );
                } else {
                    $nlTplContent->cInhalt = $nlTplStdVar->cInhalt;
                    if (isset($post['cLinkURL']) && mb_strlen($post['cLinkURL']) > 0) {
                        $nlTplContent->cLinkURL = $post['cLinkURL'];
                    }
                    if (isset($post['cAltTag']) && mb_strlen($post['cAltTag']) > 0) {
                        $nlTplContent->cAltTag = $post['cAltTag'];
                    }
                }
            }
            $db->insert('tnewslettervorlagestdvarinhalt', $nlTplContent);
        }
    }

    return $checks;
}

/**
 * @param string $type
 * @return string
 */
function mappeFileTyp(string  $type): string
{
    switch ($type) {
        case 'image/jpeg':
            return '.jpg';
        case 'image/pjpeg':
            return '.jpg';
        case 'image/gif':
            return '.gif';
        case 'image/png':
            return '.png';
        case 'image/bmp':
            return '.bmp';
        default:
            return '.jpg';
    }
}

/**
 * @param string $text
 * @return string
 */
function br2nl(string $text): string
{
    return str_replace(['<br>', '<br />', '<br/>'], "\n", $text);
}

/**
 * @param string $text
 * @param array  $stdVars
 * @param bool   $noHTML
 * @return mixed|string
 */
function mappeVorlageStdVar($text, $stdVars, $noHTML = false)
{
    if (!is_array($stdVars) || count($stdVars) === 0) {
        return $text;
    }
    foreach ($stdVars as $stdVar) {
        if ($stdVar->cTyp === 'TEXT') {
            if ($noHTML) {
                $text = strip_tags(br2nl(str_replace(
                    '$#' . $stdVar->cName . '#$',
                    $stdVar->cInhalt,
                    $text
                )));
            } else {
                $text = str_replace('$#' . $stdVar->cName . '#$', $stdVar->cInhalt, $text);
            }
        } elseif ($stdVar->cTyp === 'BILD') {
            // Bildervorlagen auf die URL SHOP umbiegen
            $stdVar->cInhalt = str_replace(
                NEWSLETTER_STD_VORLAGE_URLSHOP,
                Shop::getURL() . '/',
                $stdVar->cInhalt
            );
            if ($noHTML) {
                $text = strip_tags(br2nl(
                    str_replace(
                        '$#' . $stdVar->cName . '#$',
                        $stdVar->cInhalt,
                        $text
                    )
                ));
            } else {
                $cAltTag = '';
                if (isset($stdVar->cAltTag) && mb_strlen($stdVar->cAltTag) > 0) {
                    $cAltTag = $stdVar->cAltTag;
                }

                if (isset($stdVar->cLinkURL) && mb_strlen($stdVar->cLinkURL) > 0) {
                    $text = str_replace(
                        '$#' . $stdVar->cName . '#$',
                        '<a href="' .
                        $stdVar->cLinkURL .
                        '"><img src="' .
                        $stdVar->cInhalt . '" alt="' . $cAltTag . '" title="' .
                        $cAltTag .
                        '" /></a>',
                        $text
                    );
                } else {
                    $text = str_replace(
                        '$#' . $stdVar->cName . '#$',
                        '<img src="' .
                        $stdVar->cInhalt .
                        '" alt="' .
                        $cAltTag . '" title="' . $cAltTag . '" />',
                        $text
                    );
                }
            }
        }
    }

    return $text;
}

/**
 * @param string $name
 * @param array  $customerGroups
 * @param string $subject
 * @param string $type
 * @return array
 */
function pruefeVorlageStd($name, $customerGroups, $subject, $type): array
{
    $checks = [];
    if (empty($name)) {
        $checks['cName'] = 1;
    }
    if (!is_array($customerGroups) || count($customerGroups) === 0) {
        $checks['kKundengruppe_arr'] = 1;
    }
    if (empty($subject)) {
        $checks['cBetreff'] = 1;
    }
    if (empty($type)) {
        $checks['cArt'] = 1;
    }

    return $checks;
}

/**
 * @param string $name
 * @param array  $customerGroups
 * @param string $subject
 * @param string $type
 * @param string $html
 * @param string $text
 * @return array
 */
function pruefeVorlage($name, $customerGroups, $subject, $type, $html, $text): array
{
    $checks = [];
    if (empty($name)) {
        $checks['cName'] = 1;
    }
    if (!is_array($customerGroups) || count($customerGroups) === 0) {
        $checks['kKundengruppe_arr'] = 1;
    }
    if (empty($subject)) {
        $checks['cBetreff'] = 1;
    }
    if (empty($type)) {
        $checks['cArt'] = 1;
    }
    if (empty($html)) {
        $checks['cHtml'] = 1;
    }
    if (empty($text)) {
        $checks['cText'] = 1;
    }

    return $checks;
}

/**
 * Baut eine Vorlage zusammen
 * Falls kNewsletterVorlage angegeben wurde und kNewsletterVorlageStd = 0 ist
 * wurde eine Vorlage editiert, die von einer Std Vorlage stammt.
 *
 * @param int $defaultTemplateID
 * @param int $templateID
 * @return stdClass|null
 */
function holeNewslettervorlageStd(int $defaultTemplateID, int $templateID = 0)
{
    if ($defaultTemplateID === 0 && $templateID === 0) {
        return null;
    }
    $db  = Shop::Container()->getDB();
    $tpl = new stdClass();
    if ($templateID > 0) {
        $tpl = $db->select(
            'tnewslettervorlage',
            'kNewsletterVorlage',
            $templateID
        );
        if (isset($tpl->kNewslettervorlageStd) && $tpl->kNewslettervorlageStd > 0) {
            $defaultTemplateID = $tpl->kNewslettervorlageStd;
        }
    }

    $defaultTpl = $db->select(
        'tnewslettervorlagestd',
        'kNewslettervorlageStd',
        $defaultTemplateID
    );
    if ($defaultTpl !== null && $defaultTpl->kNewslettervorlageStd > 0) {
        if (isset($tpl->kNewslettervorlageStd) && $tpl->kNewslettervorlageStd > 0) {
            $defaultTpl->kNewsletterVorlage = $tpl->kNewsletterVorlage;
            $defaultTpl->kKampagne          = $tpl->kKampagne;
            $defaultTpl->cName              = $tpl->cName;
            $defaultTpl->cBetreff           = $tpl->cBetreff;
            $defaultTpl->cArt               = $tpl->cArt;
            $defaultTpl->cArtikel           = mb_substr(mb_substr($tpl->cArtikel, 1), 0, -1);
            $defaultTpl->cHersteller        = mb_substr(mb_substr($tpl->cHersteller, 1), 0, -1);
            $defaultTpl->cKategorie         = mb_substr(mb_substr($tpl->cKategorie, 1), 0, -1);
            $defaultTpl->cKundengruppe      = $tpl->cKundengruppe;
            $defaultTpl->dStartZeit         = $tpl->dStartZeit;
        }

        $defaultTpl->oNewslettervorlageStdVar_arr = $db->selectAll(
            'tnewslettervorlagestdvar',
            'kNewslettervorlageStd',
            $defaultTemplateID
        );

        foreach ($defaultTpl->oNewslettervorlageStdVar_arr as $j => $nlTplStdVar) {
            $nlTplContent = new stdClass();
            if (isset($nlTplStdVar->kNewslettervorlageStdVar) && $nlTplStdVar->kNewslettervorlageStdVar > 0) {
                $cSQL = ' AND kNewslettervorlage IS NULL';
                if ($templateID > 0) {
                    $cSQL = ' AND kNewslettervorlage = ' . $templateID;
                }

                $nlTplContent = $db->query(
                    'SELECT *
                        FROM tnewslettervorlagestdvarinhalt
                        WHERE kNewslettervorlageStdVar = ' . (int)$nlTplStdVar->kNewslettervorlageStdVar .
                        $cSQL,
                    ReturnType::SINGLE_OBJECT
                );
            }

            if (isset($nlTplContent->cInhalt) && mb_strlen($nlTplContent->cInhalt) > 0) {
                $defaultTpl->oNewslettervorlageStdVar_arr[$j]->cInhalt = str_replace(
                    NEWSLETTER_STD_VORLAGE_URLSHOP,
                    Shop::getURL() . '/',
                    $nlTplContent->cInhalt
                );
                if (isset($nlTplContent->cLinkURL) && mb_strlen($nlTplContent->cLinkURL) > 0) {
                    $defaultTpl->oNewslettervorlageStdVar_arr[$j]->cLinkURL = $nlTplContent->cLinkURL;
                }
                if (isset($nlTplContent->cAltTag) && mb_strlen($nlTplContent->cAltTag) > 0) {
                    $defaultTpl->oNewslettervorlageStdVar_arr[$j]->cAltTag = $nlTplContent->cAltTag;
                }
            } else {
                $defaultTpl->oNewslettervorlageStdVar_arr[$j]->cInhalt = '';
            }
        }
    }

    return $defaultTpl;
}

/**
 * @param string $productString
 * @return stdClass
 */
function explodecArtikel($productString): stdClass
{
    $productIDs                = explode(';', $productString);
    $productData               = new stdClass();
    $productData->kArtikel_arr = [];
    $productData->cArtNr_arr   = [];
    if (is_array($productIDs) && count($productIDs) > 0) {
        foreach ($productIDs as $item) {
            if ($item) {
                $productData->kArtikel_arr[] = $item;
            }
        }
        // hole zu den kArtikeln die passende cArtNr
        foreach ($productData->kArtikel_arr as $kArtikel) {
            $cArtNr = holeArtikelnummer($kArtikel);
            if (mb_strlen($cArtNr) > 0) {
                $productData->cArtNr_arr[] = $cArtNr;
            }
        }
    }

    return $productData;
}

/**
 * @param string $cKundengruppe
 * @return array
 */
function explodecKundengruppe($cKundengruppe): array
{
    $groupIDs = [];
    foreach (explode(';', $cKundengruppe) as $item) {
        if (mb_strlen($item) > 0) {
            $groupIDs[] = $item;
        }
    }

    return $groupIDs;
}

/**
 * @param int $kArtikel
 * @return string
 */
function holeArtikelnummer(int $kArtikel)
{
    $cArtNr   = '';
    $oArtikel = null;

    if ($kArtikel > 0) {
        $oArtikel = Shop::Container()->getDB()->select('tartikel', 'kArtikel', $kArtikel);
    }

    return $oArtikel->cArtNr ?? $cArtNr;
}

/**
 * @param int $kNewsletter
 * @return stdClass
 */
function getNewsletterEmpfaenger(int $kNewsletter)
{
    if ($kNewsletter <= 0) {
        return new stdClass();
    }
    // Kundengruppen holen um spaeter die maximal Anzahl Empfaenger gefiltert werden kann
    $oNewsletter = Shop::Container()->getDB()->select('tnewsletter', 'kNewsletter', $kNewsletter);
    // Kundengruppe pruefen und spaeter in den Empfaenger SELECT einbauen
    $tmpGroups         = explode(';', $oNewsletter->cKundengruppe);
    $groupIDs          = [];
    $cKundengruppe_arr = [];
    $cSQL              = '';
    if (is_array($tmpGroups) && count($tmpGroups) > 0) {
        foreach ($tmpGroups as $cKundengruppe) {
            $kKundengruppe = (int)$cKundengruppe;
            if ($kKundengruppe > 0) {
                $groupIDs[] = $kKundengruppe;
            }
            if (mb_strlen($cKundengruppe) > 0) {
                $cKundengruppe_arr[] = $cKundengruppe;
            }
        }

        $cSQL = 'AND (';
        foreach ($groupIDs as $i => $kKundengruppe) {
            if ($i > 0) {
                $cSQL .= ' OR tkunde.kKundengruppe = ' . (int)$kKundengruppe;
            } else {
                $cSQL .= 'tkunde.kKundengruppe = ' . (int)$kKundengruppe;
            }
        }

        if (in_array('0', $tmpGroups)) {
            if (is_array($groupIDs) && count($groupIDs) > 0) {
                $cSQL .= ' OR tkunde.kKundengruppe IS NULL';
            } else {
                $cSQL .= 'tkunde.kKundengruppe IS NULL';
            }
        }

        $cSQL .= ')';
    }

    $recipients = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tnewsletterempfaenger
            LEFT JOIN tsprache
                ON tsprache.kSprache = tnewsletterempfaenger.kSprache
            LEFT JOIN tkunde
                ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            WHERE tnewsletterempfaenger.kSprache = ' . (int)$oNewsletter->kSprache . '
                AND tnewsletterempfaenger.nAktiv = 1 ' . $cSQL,
        ReturnType::SINGLE_OBJECT
    );

    $recipients->cKundengruppe_arr = $cKundengruppe_arr;

    return $recipients;
}

/**
 * @param string $dZeitDB
 * @return stdClass
 */
function baueZeitAusDB($dZeitDB)
{
    $oZeit = new stdClass();

    if (mb_strlen($dZeitDB) > 0) {
        [$dDatum, $dUhrzeit]            = explode(' ', $dZeitDB);
        [$dJahr, $dMonat, $dTag]        = explode('-', $dDatum);
        [$dStunde, $dMinute, $dSekunde] = explode(':', $dUhrzeit);

        $oZeit->dZeit     = $dTag . '.' . $dMonat . '.' . $dJahr . ' ' . $dStunde . ':' . $dMinute;
        $oZeit->cZeit_arr = [$dTag, $dMonat, $dJahr, $dStunde, $dMinute];
    }

    return $oZeit;
}

/**
 * @param stdClass $cAktiveSucheSQL
 * @return int
 */
function holeAbonnentenAnzahl($cAktiveSucheSQL): int
{
    return (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tnewsletterempfaenger
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . $cAktiveSucheSQL->cWHERE,
        ReturnType::SINGLE_OBJECT
    )->nAnzahl;
}

/**
 * @param string   $cSQL
 * @param stdClass $cAktiveSucheSQL
 * @return array
 */
function holeAbonnenten($cSQL, $cAktiveSucheSQL): array
{
    return Shop::Container()->getDB()->query(
        "SELECT tnewsletterempfaenger.*,
            DATE_FORMAT(tnewsletterempfaenger.dEingetragen, '%d.%m.%Y %H:%i') AS dEingetragen_de,
            DATE_FORMAT(tnewsletterempfaenger.dLetzterNewsletter, '%d.%m.%Y %H:%i') AS dLetzterNewsletter_de,
            tkunde.kKundengruppe, tkundengruppe.cName, tnewsletterempfaengerhistory.cOptIp,
            DATE_FORMAT(tnewsletterempfaengerhistory.dOptCode, '%d.%m.%Y %H:%i') AS optInDate
            FROM tnewsletterempfaenger
            LEFT JOIN tkunde
                ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            LEFT JOIN tkundengruppe
                ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
            LEFT JOIN tnewsletterempfaengerhistory
                ON tnewsletterempfaengerhistory.cEmail = tnewsletterempfaenger.cEmail
                  AND tnewsletterempfaengerhistory.cAktion = 'Eingetragen'
            WHERE tnewsletterempfaenger.kSprache = " . (int)$_SESSION['kSprache'] .
        $cAktiveSucheSQL->cWHERE . '
            ORDER BY tnewsletterempfaenger.dEingetragen DESC' . $cSQL,
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param array $recipientIDs
 * @return bool
 */
function loescheAbonnenten($recipientIDs): bool
{
    if (!is_array($recipientIDs) || count($recipientIDs) === 0) {
        return false;
    }
    $db         = Shop::Container()->getDB();
    $where      = ' IN (' . implode(',', array_map('\intval', $recipientIDs)) . ')';
    $recipients = $db->query(
        'SELECT *
            FROM tnewsletterempfaenger
            WHERE kNewsletterEmpfaenger' .
        $where,
        ReturnType::ARRAY_OF_OBJECTS
    );

    if (count($recipients) === 0) {
        return false;
    }
    $db->query(
        'DELETE FROM tnewsletterempfaenger
            WHERE kNewsletterEmpfaenger' . $where,
        ReturnType::AFFECTED_ROWS
    );
    foreach ($recipients as $recipient) {
        $hist               = new stdClass();
        $hist->kSprache     = $recipient->kSprache;
        $hist->kKunde       = $recipient->kKunde;
        $hist->cAnrede      = $recipient->cAnrede;
        $hist->cVorname     = $recipient->cVorname;
        $hist->cNachname    = $recipient->cNachname;
        $hist->cEmail       = $recipient->cEmail;
        $hist->cOptCode     = $recipient->cOptCode;
        $hist->cLoeschCode  = $recipient->cLoeschCode;
        $hist->cAktion      = 'Geloescht';
        $hist->dEingetragen = $recipient->dEingetragen;
        $hist->dAusgetragen = 'NOW()';
        $hist->dOptCode     = '_DBNULL_';

        $db->insert('tnewsletterempfaengerhistory', $hist);
    }

    return true;
}

/**
 * @param array $recipientIDs
 * @return bool
 */
function aktiviereAbonnenten($recipientIDs): bool
{
    if (!is_array($recipientIDs) || count($recipientIDs) === 0) {
        return false;
    }
    $db         = Shop::Container()->getDB();
    $where      = ' IN (' . implode(',', array_map('\intval', $recipientIDs)) . ')';
    $recipients = $db->query(
        'SELECT *
            FROM tnewsletterempfaenger
            WHERE kNewsletterEmpfaenger' .
        $where,
        ReturnType::ARRAY_OF_OBJECTS
    );

    if (count($recipients) === 0) {
        return false;
    }
    $db->query(
        'UPDATE tnewsletterempfaenger
            SET nAktiv = 1
            WHERE kNewsletterEmpfaenger' . $where,
        ReturnType::AFFECTED_ROWS
    );
    foreach ($recipients as $recipient) {
        $hist               = new stdClass();
        $hist->kSprache     = $recipient->kSprache;
        $hist->kKunde       = $recipient->kKunde;
        $hist->cAnrede      = $recipient->cAnrede;
        $hist->cVorname     = $recipient->cVorname;
        $hist->cNachname    = $recipient->cNachname;
        $hist->cEmail       = $recipient->cEmail;
        $hist->cOptCode     = $recipient->cOptCode;
        $hist->cLoeschCode  = $recipient->cLoeschCode;
        $hist->cAktion      = 'Aktiviert';
        $hist->dEingetragen = $recipient->dEingetragen;
        $hist->dAusgetragen = 'NOW()';
        $hist->dOptCode     = '_DBNULL_';

        $db->insert('tnewsletterempfaengerhistory', $hist);
    }

    return true;
}

/**
 * @param array $post
 * @return int|stdClass
 */
function gibAbonnent($post)
{
    $db        = Shop::Container()->getDB();
    $cVorname  = strip_tags($db->escape($post['cVorname']));
    $cNachname = strip_tags($db->escape($post['cNachname']));
    $cEmail    = strip_tags($db->escape($post['cEmail']));
    // Etwas muss gesetzt sein um zu suchen
    if (!$cVorname && !$cNachname && !$cEmail) {
        return 1;
    }
    // SQL bauen
    $cSQL = '';
    if (mb_strlen($cVorname) > 0) {
        $cSQL .= "tnewsletterempfaenger.cVorname LIKE '%" . strip_tags($db->realEscape($cVorname)) . "%'";
    }
    if (mb_strlen($cNachname) > 0 && mb_strlen($cVorname) > 0) {
        $cSQL .= " AND tnewsletterempfaenger.cNachname LIKE '%" . strip_tags($db->realEscape($cNachname)) . "%'";
    } elseif (mb_strlen($cNachname) > 0) {
        $cSQL .= "tnewsletterempfaenger.cNachname LIKE '%" . strip_tags($db->realEscape($cNachname)) . "%'";
    }
    if (mb_strlen($cEmail) > 0 && (mb_strlen($cVorname) > 0 || mb_strlen($cNachname) > 0)) {
        $cSQL .= " AND tnewsletterempfaenger.cEmail LIKE '%" . strip_tags($db->realEscape($cEmail)) . "%'";
    } elseif (mb_strlen($cEmail) > 0) {
        $cSQL .= "tnewsletterempfaenger.cEmail LIKE '%" . strip_tags($db->realEscape($cEmail)) . "%'";
    }
    $oAbonnent = $db->query(
        "SELECT tnewsletterempfaenger.kNewsletterEmpfaenger, tnewsletterempfaenger.cVorname AS newsVorname,
            tnewsletterempfaenger.cNachname AS newsNachname, tkunde.cVorname, tkunde.cNachname,
            tnewsletterempfaenger.cEmail, tnewsletterempfaenger.nAktiv, tkunde.kKundengruppe, tkundengruppe.cName,
            DATE_FORMAT(tnewsletterempfaenger.dEingetragen, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterempfaenger
            JOIN tkunde
                ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            JOIN tkundengruppe
                ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
            WHERE " . $cSQL . '
            ORDER BY tnewsletterempfaenger.dEingetragen DESC',
        ReturnType::SINGLE_OBJECT
    );
    if (isset($oAbonnent->kNewsletterEmpfaenger) && $oAbonnent->kNewsletterEmpfaenger > 0) {
        $oKunde               = new Kunde($oAbonnent->kKunde ?? 0);
        $oAbonnent->cNachname = $oKunde->cNachname;

        return $oAbonnent;
    }

    return 0;
}

/**
 * @param int $recipientID
 * @return bool
 */
function loescheAbonnent(int $recipientID)
{
    if ($recipientID <= 0) {
        return false;
    }
    Shop::Container()->getDB()->delete('tnewsletterempfaenger', 'kNewsletterEmpfaenger', $recipientID);

    return true;
}

/**
 * @param object $template
 * @return string|bool
 */
function baueNewsletterVorschau($template)
{
    $conf                   = Shop::getSettings([CONF_NEWSLETTER]);
    $mailSmarty             = bereiteNewsletterVor($conf);
    $productIDs             = gibAHKKeys($template->cArtikel, true);
    $manufacturerIDs        = gibAHKKeys($template->cHersteller);
    $categoryIDs            = gibAHKKeys($template->cKategorie);
    $campaign               = new Kampagne((int)$template->kKampagne);
    $products               = gibArtikelObjekte($productIDs, $campaign);
    $manufacturers          = gibHerstellerObjekte($manufacturerIDs, $campaign);
    $categories             = gibKategorieObjekte($categoryIDs, $campaign);
    $customer               = new stdClass();
    $customer->cAnrede      = 'm';
    $customer->cVorname     = 'Max';
    $customer->cNachname    = 'Mustermann';
    $recipient              = new stdClass();
    $recipient->cEmail      = $conf['newsletter']['newsletter_emailtest'];
    $recipient->cLoeschCode = '78rev6gj8er6we87gw6er8';
    $recipient->cLoeschURL  = Shop::getURL() . '/newsletter.php?lang=ger' . '&lc=' . $recipient->cLoeschCode;

    $mailSmarty->assign('NewsletterEmpfaenger', $recipient)
               ->assign('Emailempfaenger', $recipient)
               ->assign('oNewsletterVorlage', $template)
               ->assign('Kunde', $customer)
               ->assign('Artikelliste', $products)
               ->assign('Herstellerliste', $manufacturers)
               ->assign('Kategorieiste', $categories)
               ->assign('Kampagne', $campaign);

    try {
        $bodyHtml = $mailSmarty->fetch('db:VL_' . $template->kNewsletterVorlage . '_html');
        $bodyText = $mailSmarty->fetch('db:VL_' . $template->kNewsletterVorlage . '_text');
    } catch (Exception $e) {
        return $e->getMessage();
    }
    $template->cInhaltHTML = $bodyHtml;
    $template->cInhaltText = $bodyText;

    return true;
}

/**
 * Braucht ein String von Keys oder Nummern und gibt ein Array mit kKeys zurueck
 * Der String muss ';' separiert sein z.b. '1;2;3'
 *
 * @param string $cKey
 * @param bool   $bArtikelnummer
 * @return array
 */
function gibAHKKeys($cKey, $bArtikelnummer = false)
{
    $res  = [];
    $keys = explode(';', $cKey);
    if (is_array($keys) && count($keys) > 0) {
        foreach ($keys as $key) {
            if (mb_strlen($key) > 0) {
                if ($bArtikelnummer) {
                    $res[] = "'" . $key . "'";
                } else {
                    $res[] = (int)$key;
                }
            }
        }
        // Ausnahme: Wurden Artikelnummern uebergebenn?
        // Wenn ja, dann hole fuer die Artikelnummern die entsprechenden kArtikel
        if ($bArtikelnummer && count($res) > 0) {
            $productIDs = [];
            $artNoData  = Shop::Container()->getDB()->query(
                'SELECT kArtikel
                    FROM tartikel
                    WHERE cArtNr IN (' . implode(',', $res) . ')
                        AND kEigenschaftKombi = 0',
                ReturnType::ARRAY_OF_OBJECTS
            );
            // Existieren Artikel zu den entsprechenden Artikelnummern?
            foreach ($artNoData as $artNo) {
                if (isset($artNo->kArtikel) && (int)$artNo->kArtikel) {
                    $productIDs[] = (int)$artNo->kArtikel;
                }
            }

            if (count($productIDs) > 0) {
                $res = $productIDs;
            }
        }
    }

    return $res;
}

/**
 * Benoetigt ein Array von kArtikel und gibt ein Array mit Artikelobjekten zurueck
 *
 * @param array         $productIDs
 * @param string|object $campaign
 * @param int           $customerGroupID
 * @param int           $langID
 * @return Artikel[]
 */
function gibArtikelObjekte($productIDs, $campaign = '', int $customerGroupID = 0, int $langID = 0): array
{
    if (!is_array($productIDs) || count($productIDs) === 0) {
        return [];
    }
    $products       = [];
    $shopURL        = Shop::getURL() . '/';
    $imageBaseURL   = Shop::getImageBaseURL();
    $defaultOptions = Artikel::getDefaultOptions();
    foreach ($productIDs as $id) {
        $id = (int)$id;
        if ($id > 0) {
            Frontend::getCustomerGroup()->setMayViewPrices(1);
            $product = new Artikel();
            $product->fuelleArtikel($id, $defaultOptions, $customerGroupID, $langID);
            if (!($product->kArtikel > 0)) {
                Shop::Container()->getLogService()->notice(
                    'Newsletter Cron konnte den Artikel ' . $id . ' für Kundengruppe ' .
                    $customerGroupID . ' und Sprache ' . $langID . ' nicht laden (Sichtbarkeit?)'
                );
                continue;
            }
            $product->cURL = $shopURL . $product->cURL;
            if (isset($campaign->cParameter) && mb_strlen($campaign->cParameter) > 0) {
                $product->cURL = $product->cURL .
                    (mb_strpos($product->cURL, '.php') !== false ? '&' : '?') .
                    $campaign->cParameter . '=' . $campaign->cWert;
            }
            foreach ($product->Bilder as $image) {
                $image->cPfadMini   = $imageBaseURL . $image->cPfadMini;
                $image->cPfadKlein  = $imageBaseURL . $image->cPfadKlein;
                $image->cPfadNormal = $imageBaseURL . $image->cPfadNormal;
                $image->cPfadGross  = $imageBaseURL . $image->cPfadGross;
            }
            $product->cVorschaubild = $imageBaseURL . $product->cVorschaubild;

            $products[] = $product;
        }
    }

    return $products;
}

/**
 * Benoetigt ein Array von kHersteller und gibt ein Array mit Herstellerobjekten zurueck
 *
 * @param array      $manufacturerIDs
 * @param int|object $campaign
 * @param int|object $langID
 * @return array
 */
function gibHerstellerObjekte($manufacturerIDs, $campaign = 0, int $langID = 0)
{
    if (!is_array($manufacturerIDs) || count($manufacturerIDs) === 0) {
        return [];
    }
    $manufacturers = [];
    $shopURL       = Shop::getURL() . '/';
    $imageBaseURL  = Shop::getImageBaseURL();
    foreach ($manufacturerIDs as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $manufacturer = new Hersteller($id, $langID);
            if (mb_strpos($manufacturer->cURL, $shopURL) === false) {
                $manufacturer->cURL = $manufacturer->cURL = $shopURL . $manufacturer->cURL;
            }
            if (isset($campaign->cParameter) && mb_strlen($campaign->cParameter) > 0) {
                $cSep = '?';
                if (mb_strpos($manufacturer->cURL, '.php') !== false) {
                    $cSep = '&';
                }
                $manufacturer->cURL = $manufacturer->cURL . $cSep . $campaign->cParameter . '=' . $campaign->cWert;
            }
            $manufacturer->cBildpfadKlein  = $imageBaseURL . $manufacturer->cBildpfadKlein;
            $manufacturer->cBildpfadNormal = $imageBaseURL . $manufacturer->cBildpfadNormal;

            $manufacturers[] = $manufacturer;
        }
    }

    return $manufacturers;
}

/**
 * Benoetigt ein Array von kKategorie und gibt ein Array mit Kategorieobjekten zurueck
 *
 * @param array      $categoryIDs
 * @param int|object $campaign
 * @return array
 */
function gibKategorieObjekte($categoryIDs, $campaign = 0)
{
    if (!is_array($categoryIDs) || count($categoryIDs) === 0) {
        return [];
    }
    $categories = [];
    $shopURL    = Shop::getURL() . '/';
    foreach ($categoryIDs as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $category = new Kategorie($id);
            if (mb_strpos($category->cURL, $shopURL) === false) {
                $category->cURL = $shopURL . $category->cURL;
            }
            if (isset($campaign->cParameter) && mb_strlen($campaign->cParameter) > 0) {
                $sep = '?';
                if (strpos($category->cURL, '.php') !== false) {
                    $sep = '&';
                }
                $category->cURL = $category->cURL . $sep . $campaign->cParameter . '=' . $campaign->cWert;
            }
            $categories[] = $category;
        }
    }

    return $categories;
}

// OptCode erstellen und ueberpruefen - Werte fuer $dbfeld 'cOptCode','cLoeschCode'
if (!function_exists('create_NewsletterCode')) {
    /**
     * @param string $dbfeld
     * @param string $email
     * @return string
     */
    function create_NewsletterCode($dbfeld, $email)
    {
        $code = md5($email . time() . rand(123, 456));
        while (!unique_NewsletterCode($dbfeld, $code)) {
            $code = md5($email . time() . rand(123, 456));
        }

        return $code;
    }
}

if (!function_exists('unique_NewsletterCode')) {
    /**
     * @param string $dbfeld
     * @param string $code
     * @return bool
     */
    function unique_NewsletterCode($dbfeld, $code)
    {
        $res = Shop::Container()->getDB()->select('tnewsletterempfaenger', $dbfeld, $code);

        return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
    }
}
