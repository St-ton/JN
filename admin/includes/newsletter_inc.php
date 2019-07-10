<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\MailSmarty;

/**
 * @param array $conf
 * @return JTLSmarty
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
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
    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
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

    $type = 'VL';
    $nKey = $newsletter->kNewsletterVorlage ?? 0;
    if (isset($newsletter->kNewsletter) && $newsletter->kNewsletter > 0) {
        $type = 'NL';
        $nKey = $newsletter->kNewsletter;
    }
    if ($newsletter->cArt === 'text/html' || $newsletter->cArt === 'html') {
        try {
            $bodyHtml = $mailSmarty->fetch('db:' . $type . '_' . $nKey . '_html') . $cPixel;
        } catch (Exception $e) {
            Shop::Smarty()->assign('oSmartyError', $e->getMessage());

            return $e->getMessage();
        }
    }
    try {
        $bodyText = $mailSmarty->fetch('db:' . $type . '_' . $nKey . '_text');
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
    $mail->lang          = LanguageHelper::getIsoFromLangID((int)$newsletter->kSprache)->cISO;
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
 * @deprecated since 5.0.0
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
    return '';
}

/**
 * @param array $post
 * @return array|null|stdClass
 * @deprecated since 5.0.0
 */
function speicherVorlage($post)
{
    return null;
}

/**
 * @param object $defaultTpl
 * @param int    $kNewslettervorlageStd
 * @param array  $post
 * @param int    $templateID
 * @return array
 * @deprecated since 5.0.0
 */
function speicherVorlageStd($defaultTpl, int $kNewslettervorlageStd, $post, int $templateID): array
{
    return [];
}

/**
 * @param string $type
 * @return string
 * @deprecated since 5.0.0
 */
function mappeFileTyp(string  $type): string
{
    return '.jpg';
}

/**
 * @param string $text
 * @return string
 * @deprecated since 5.0.0
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
 * @deprecated since 5.0.0
 */
function mappeVorlageStdVar($text, $stdVars, $noHTML = false)
{
    return '';
}

/**
 * @param string $name
 * @param array  $customerGroups
 * @param string $subject
 * @param string $type
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeVorlageStd($name, $customerGroups, $subject, $type): array
{
    return [];
}

/**
 * @param string $name
 * @param array  $customerGroups
 * @param string $subject
 * @param string $type
 * @param string $html
 * @param string $text
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeVorlage($name, $customerGroups, $subject, $type, $html, $text): array
{
    return [];
}

/**
 * Baut eine Vorlage zusammen
 * Falls kNewsletterVorlage angegeben wurde und kNewsletterVorlageStd = 0 ist
 * wurde eine Vorlage editiert, die von einer Std Vorlage stammt.
 *
 * @param int $defaultTemplateID
 * @param int $templateID
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function holeNewslettervorlageStd(int $defaultTemplateID, int $templateID = 0)
{
    return null;
}

/**
 * @param string $productString
 * @return stdClass
 * @deprecated since 5.0.0
 */
function explodecArtikel($productString): stdClass
{
    $productData               = new stdClass();
    $productData->kArtikel_arr = [];
    $productData->cArtNr_arr   = [];

    return $productData;
}

/**
 * @param string $customerGroup
 * @return array
 * @deprecated since 5.0.0
 */
function explodecKundengruppe($customerGroup): array
{
    return [];
}

/**
 * @param int $productID
 * @return string
 * @deprecated since 5.0.0
 */
function holeArtikelnummer(int $productID)
{
    return '';
}

/**
 * @param int $newsletterID
 * @return stdClass
 * @deprecated since 5.0.0
 */
function getNewsletterEmpfaenger(int $newsletterID)
{
    return new stdClass();
}

/**
 * @param string $time
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueZeitAusDB($time)
{
    $oZeit = new stdClass();

    if (mb_strlen($time) > 0) {
        [$dDatum, $dUhrzeit]            = explode(' ', $time);
        [$dJahr, $dMonat, $dTag]        = explode('-', $dDatum);
        [$dStunde, $dMinute, $dSekunde] = explode(':', $dUhrzeit);

        $oZeit->dZeit     = $dTag . '.' . $dMonat . '.' . $dJahr . ' ' . $dStunde . ':' . $dMinute;
        $oZeit->cZeit_arr = [$dTag, $dMonat, $dJahr, $dStunde, $dMinute];
    }

    return $oZeit;
}

/**
 * @param stdClass $activeSearchSQL
 * @return int
 * @deprecated since 5.0.0
 */
function holeAbonnentenAnzahl($activeSearchSQL): int
{
    return 0;
}

/**
 * @param string   $sql
 * @param stdClass $activeSearchSQL
 * @return array
 * @deprecated since 5.0.0
 */
function holeAbonnenten($sql, $activeSearchSQL): array
{
    return [];
}

/**
 * @param int[] $recipientIDs
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheAbonnenten($recipientIDs): bool
{
    return false;
}

/**
 * @param int[] $recipientIDs
 * @return bool
 * @deprecated since 5.0.0
 */
function aktiviereAbonnenten($recipientIDs): bool
{
    return false;
}

/**
 * @param array $post
 * @return int|stdClass
 * @deprecated since 5.0.0
 */
function gibAbonnent(array $post)
{
    return 0;
}

/**
 * @param int $recipientID
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheAbonnent(int $recipientID)
{
    return false;
}

/**
 * @param object $template
 * @return string|bool
 * @deprecated since 5.0.0
 */
function baueNewsletterVorschau($template)
{
    return false;
}

/**
 * Braucht ein String von Keys oder Nummern und gibt ein Array mit kKeys zurueck
 * Der String muss ';' separiert sein z.b. '1;2;3'
 *
 * @param string $keyName
 * @param bool   $productNo
 * @return array
 * @deprecated since 5.0.0
 */
function gibAHKKeys($keyName, $productNo = false)
{
    $res  = [];
    $keys = explode(';', $cKey);
    if (!is_array($keys) || count($keys) === 0) {
        return $res;
    }
    $res = array_filter($keys, function ($e) {
        return mb_strlen($e) > 0;
    });
    if ($bArtikelnummer) {
        $res = array_map(function ($e) {
            return "'" . $e . "'";
        }, $res);
        if (count($res) > 0) {
            $artNoData = Shop::Container()->getDB()->query(
                'SELECT kArtikel
                FROM tartikel
                WHERE cArtNr IN (' . implode(',', $res) . ')
                    AND kEigenschaftKombi = 0',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $res       = array_map(function ($e) {
                return $e->kArtikel;
            }, $artNoData);
        }
    } else {
        $res = array_map('\intval', $res);
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
 * @deprecated since 5.0.0
 */
function gibArtikelObjekte($productIDs, $campaign = '', int $customerGroupID = 0, int $langID = 0): array
{
    return [];
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
    return [];
}

/**
 * Benoetigt ein Array von kKategorie und gibt ein Array mit Kategorieobjekten zurueck
 *
 * @param array      $categoryIDs
 * @param int|object $campaign
 * @return array
 * @deprecated since 5.0.0
 */
function gibKategorieObjekte($categoryIDs, $campaign = 0)
{
    return [];
}
