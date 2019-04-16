<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\CheckBox;
use JTL\Customer\Kunde;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Session\Frontend;
use JTL\Shop;

require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

/**
 * @param string $dbfeld
 * @param string $email
 * @return string
 */
function create_NewsletterCode($dbfeld, $email): string
{
    $code = md5($email . time() . rand(123, 456));
    while (!unique_NewsletterCode($dbfeld, $code)) {
        $code = md5($email . time() . rand(123, 456));
    }

    return $code;
}

/**
 * @param string     $dbfeld
 * @param string|int $code
 * @return bool
 */
function unique_NewsletterCode($dbfeld, $code): bool
{
    $res = Shop::Container()->getDB()->select('tnewsletterempfaenger', $dbfeld, $code);

    return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
}

/**
 * @param Kunde|stdClass $customer
 * @param bool           $validate
 * @return stdClass
 * @throws Exception
 * @deprecated since 5.0.0
 */
function fuegeNewsletterEmpfaengerEin($customer, $validate = false): stdClass
{
    $alertHelper         = Shop::Container()->getAlertService();
    $conf                = Shop::getSettings([CONF_NEWSLETTER]);
    $plausi              = new stdClass();
    $plausi->nPlausi_arr = [];
    $nlCustomer          = null;
    if (!$validate || Text::filterEmailAddress($customer->cEmail) !== false) {
        $plausi->nPlausi_arr = newsletterAnmeldungPlausi();
        $kKundengruppe       = Frontend::getCustomerGroup()->getID();
        $checkBox            = new CheckBox();
        $plausi->nPlausi_arr = array_merge(
            $plausi->nPlausi_arr,
            $checkBox->validateCheckBox(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true)
        );

        $plausi->cPost_arr['cAnrede']   = $customer->cAnrede;
        $plausi->cPost_arr['cVorname']  = $customer->cVorname;
        $plausi->cPost_arr['cNachname'] = $customer->cNachname;
        $plausi->cPost_arr['cEmail']    = $customer->cEmail;
        $plausi->cPost_arr['captcha']   = isset($_POST['captcha'])
            ? Text::htmlentities(Text::filterXSS($_POST['captcha']))
            : null;
        if (!$validate || count($plausi->nPlausi_arr) === 0) {
            $recipient = Shop::Container()->getDB()->select(
                'tnewsletterempfaenger',
                'cEmail',
                $customer->cEmail
            );
            if (!empty($recipient->dEingetragen)) {
                $recipient->Datum = (new DateTime($recipient->dEingetragen))->format('d.m.Y H:i');
            }
            // Pruefen ob Kunde bereits eingetragen
            if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                $nlCustomer = Shop::Container()->getDB()->select(
                    'tnewsletterempfaenger',
                    'kKunde',
                    (int)$_SESSION['Kunde']->kKunde
                );
            }
            if ((isset($recipient->cEmail) && $recipient->cEmail !== '')
                || (isset($nlCustomer->kKunde) && $nlCustomer->kKunde > 0)
            ) {
                $alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('newsletterExists', 'errorMessages'),
                    'newsletterExists'
                );
            } else {
                $checkBox->triggerSpecialFunction(
                    CHECKBOX_ORT_NEWSLETTERANMELDUNG,
                    $kKundengruppe,
                    true,
                    $_POST,
                    ['oKunde' => $customer]
                );
                $checkBox->checkLogging(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true);
                unset($recipient);
                $recipient                     = new stdClass();
                $recipient->kSprache           = Shop::getLanguage();
                $recipient->kKunde             = isset($_SESSION['Kunde']->kKunde)
                    ? (int)$_SESSION['Kunde']->kKunde
                    : 0;
                $recipient->nAktiv             = isset($_SESSION['Kunde']->kKunde)
                    && $_SESSION['Kunde']->kKunde > 0
                    && $conf['newsletter']['newsletter_doubleopt'] === 'U' ? 1 : 0;
                $recipient->cAnrede            = $customer->cAnrede;
                $recipient->cVorname           = $customer->cVorname;
                $recipient->cNachname          = $customer->cNachname;
                $recipient->cEmail             = $customer->cEmail;
                $recipient->cOptCode           = create_NewsletterCode('cOptCode', $customer->cEmail);
                $recipient->cLoeschCode        = create_NewsletterCode('cLoeschCode', $customer->cEmail);
                $recipient->dEingetragen       = 'NOW()';
                $recipient->dLetzterNewsletter = '_DBNULL_';

                executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGEREINTRAGEN, [
                    'oNewsletterEmpfaenger' => $recipient
                ]);

                Shop::Container()->getDB()->insert('tnewsletterempfaenger', $recipient);
                $history               = new stdClass();
                $history->kSprache     = Shop::getLanguage();
                $history->kKunde       = (int)($_SESSION['Kunde']->kKunde ?? 0);
                $history->cAnrede      = $customer->cAnrede;
                $history->cVorname     = $customer->cVorname;
                $history->cNachname    = $customer->cNachname;
                $history->cEmail       = $customer->cEmail;
                $history->cOptCode     = $recipient->cOptCode;
                $history->cLoeschCode  = $recipient->cLoeschCode;
                $history->cAktion      = 'Eingetragen';
                $history->dEingetragen = 'NOW()';
                $history->dAusgetragen = '_DBNULL_';
                $history->dOptCode     = '_DBNULL_';
                $history->cRegIp       = $customer->cRegIp;

                $historyID = Shop::Container()->getDB()->insert(
                    'tnewsletterempfaengerhistory',
                    $history
                );
                executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, [
                    'oNewsletterEmpfaengerHistory' => $history
                ]);
                if (($conf['newsletter']['newsletter_doubleopt'] === 'U' && empty($_SESSION['Kunde']->kKunde))
                    || $conf['newsletter']['newsletter_doubleopt'] === 'A'
                ) {
                    $recipient->cLoeschURL     = Shop::getURL() . '/newsletter.php?lang=' .
                        $_SESSION['cISOSprache'] . '&lc=' . $recipient->cLoeschCode;
                    $recipient->cFreischaltURL = Shop::getURL() . '/newsletter.php?lang=' .
                        $_SESSION['cISOSprache'] . '&fc=' . $recipient->cOptCode;
                    $obj                       = new stdClass();
                    $obj->tkunde               = $_SESSION['Kunde'] ?? null;
                    $obj->NewsletterEmpfaenger = $recipient;

                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_NEWSLETTERANMELDEN, $obj));
                    Shop::Container()->getDB()->update(
                        'tnewsletterempfaengerhistory',
                        'kNewsletterEmpfaengerHistory',
                        $historyID,
                        (object)['cEmailBodyHtml' => $mail->getBodyHTML()]
                    );
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('newsletterAdd', 'messages'),
                        'newsletterAdd'
                    );
                    $plausi = new stdClass();
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_NOTE,
                        Shop::Lang()->get('newsletterNomailAdd', 'messages'),
                        'newsletterNomailAdd'
                    );
                }
            }
        }
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
            'newsletterWrongemail'
        );
    }

    return $plausi;
}

/**
 * @return array
 */
function newsletterAnmeldungPlausi(): array
{
    $res = [];
    if (Shop::getConfigValue(CONF_NEWSLETTER, 'newsletter_sicherheitscode') !== 'N' && !Form::validateCaptcha($_POST)) {
        $res['captcha'] = 2;
    }

    return $res;
}

/**
 * @param int $kKunde
 * @return bool
 */
function pruefeObBereitsAbonnent(int $kKunde): bool
{
    if ($kKunde <= 0) {
        return false;
    }
    $recipient = Shop::Container()->getDB()->select('tnewsletterempfaenger', 'kKunde', $kKunde);

    return isset($recipient->kKunde) && $recipient->kKunde > 0;
}

/**
 * @param int    $groupID
 * @param string $groupKeys
 * @return bool
 */
function pruefeNLHistoryKundengruppe(int $groupID, $groupKeys): bool
{
    if (mb_strlen($groupKeys) > 0) {
        $groupIDs = [];
        foreach (explode(';', $groupKeys) as $id) {
            if ((int)$id > 0 || (mb_strlen($id) > 0 && (int)$id === 0)) {
                $groupIDs[] = (int)$id;
            }
        }
        if (in_array(0, $groupIDs, true)) {
            return true;
        }
        if ($groupID > 0 && in_array($groupID, $groupIDs, true)) {
            return true;
        }
    }

    return false;
}
