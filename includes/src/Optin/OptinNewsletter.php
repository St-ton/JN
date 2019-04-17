<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Optin;

use JTL\Alert\Alert;
use JTL\CheckBox;
use JTL\DB\ReturnType;
use JTL\Exceptions\InvalidInputException;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use stdClass;

class OptinNewsletter extends OptinBase implements OptinInterface
{
    /**
     * @var bool
     */
    private $hasSendingPermission = false;

    /**
     * @var int
     */
    private $historyID;

    /**
     * @var AlertServiceInterface
     */
    private $alertHelper;

    /**
     * @var array
     */
    private $conf;

    public function __construct($inheritData)
    {
        [
            $this->dbHandler,
            $this->nowDataTime,
            $this->refData,
            $this->emailAddress,
            $this->optCode,
            $this->actionPrefix
        ]                  = $inheritData;
        $this->alertHelper = Shop::Container()->getAlertService();
        $this->conf        = Shop::getSettings([CONF_NEWSLETTER]);
    }

    /**
     * @param OptinRefData $refData
     * @return OptinNewsletter
     * @throws InvalidInputException
     */
    public function createOptin(OptinRefData $refData): self
    {
        $this->refData = $refData;
        $this->optCode = $this->generateUniqOptinCode();

        if (!SimpleMail::checkBlacklist($this->refData->getEmail())) {
            //$plausi = fuegeNewsletterEmpfaengerEin($customer, true);
            // the following code replaces the function from "newsletter_inc.php"

            $plausi              = new \stdClass();
            $plausi->nPlausi_arr = [];
            $nlCustomer          = null;
            if (Text::filterEmailAddress($this->refData->getEmail()) !== false) {
                $plausi->nPlausi_arr = newsletterAnmeldungPlausi();
                $kKundengruppe       = Frontend::getCustomerGroup()->getID();
                $checkBox            = new CheckBox();
                $plausi->nPlausi_arr = array_merge(
                    $plausi->nPlausi_arr,
                    $checkBox->validateCheckBox(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true)
                );

                $plausi->cPost_arr['cAnrede']   = $this->refData->getSalutation();
                $plausi->cPost_arr['cVorname']  = $this->refData->getFirstName();
                $plausi->cPost_arr['cNachname'] = $this->refData->getLastName();
                $plausi->cPost_arr['cEmail']    = $this->refData->getEmail();
                $plausi->cPost_arr['captcha']   = isset($_POST['captcha'])
                    ? Text::htmlentities(Text::filterXSS($_POST['captcha']))
                    : null;
                if (count($plausi->nPlausi_arr) === 0) {
                    $recipient = $this->dbHandler->select(
                        'tnewsletterempfaenger',
                        'cEmail',
                        $this->refData->getEmail()
                    );
                    if (!empty($recipient->dEingetragen)) {
                        $recipient->Datum = (new \DateTime($recipient->dEingetragen))->format('d.m.Y H:i');
                    }
                    // Pruefen ob Kunde bereits eingetragen
                    $customer   = Frontend::getCustomer();
                    $customerId = $customer->getID();
                    if ($customerId > 0) {
                        $nlCustomer = $this->dbHandler->select(
                            'tnewsletterempfaenger',
                            'kKunde',
                            $customerId
                        );
                    }
                    if ((isset($recipient->cEmail) && $recipient->cEmail !== '')
                        || (isset($nlCustomer->kKunde) && $nlCustomer->kKunde > 0)
                    ) {
                        $this->alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            Shop::Lang()->get('newsletterExists', 'errorMessages'),
                            'newsletterExists'
                        );
                    } else {
                        $customer            = new \stdClass();
                        $customer->cAnrede   = $this->refData->getSalutation();
                        $customer->cVorname  = $this->refData->getFirstName();
                        $customer->cNachname = $this->refData->getLastName();
                        $customer->cEmail    = $this->refData->getEmail();
                        $customer->cRegIp    = $this->refData->getRealIP();
                        $checkBox->triggerSpecialFunction(
                            CHECKBOX_ORT_NEWSLETTERANMELDUNG,
                            $kKundengruppe,
                            true,
                            $_POST,
                            ['oKunde' => $customer]
                        );
                        $checkBox->checkLogging(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true);

                        unset($recipient);
                        $recipient                     = new \stdClass();
                        $recipient->kSprache           = Shop::getLanguage();
                        $recipient->kKunde             = ($customerId ?? 0);
                        $recipient->nAktiv             = ($customerId > 0 &&
                            $this->conf['newsletter']['newsletter_doubleopt'] === 'U') ? 1 : 0;
                        $recipient->cAnrede            = $this->refData->getSalutation();
                        $recipient->cVorname           = $this->refData->getFirstName();
                        $recipient->cNachname          = $this->refData->getLastName();
                        $recipient->cEmail             = $this->refData->getEmail();
                        $recipient->cOptCode           = self::ACTIVATE_CODE . $this->optCode;
                        $recipient->cLoeschCode        = self::DELETE_CODE . $this->optCode;
                        $recipient->dEingetragen       = 'NOW()';
                        $recipient->dLetzterNewsletter = '_DBNULL_';
                        executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGEREINTRAGEN, [
                            'oNewsletterEmpfaenger' => $recipient
                        ]);

                        $this->dbHandler->insert('tnewsletterempfaenger', $recipient);
                        $history               = new \stdClass();
                        $history->kSprache     = Shop::getLanguage();
                        $history->kKunde       = ($customerId ?? 0);
                        $history->cAnrede      = $this->refData->getSalutation();
                        $history->cVorname     = $this->refData->getFirstName();
                        $history->cNachname    = $this->refData->getLastName();
                        $history->cEmail       = $this->refData->getEmail();
                        $history->cOptCode     = $recipient->cOptCode;
                        $history->cLoeschCode  = $recipient->cLoeschCode;
                        $history->cAktion      = 'Eingetragen';
                        $history->dEingetragen = 'NOW()';
                        $history->dAusgetragen = '_DBNULL_';
                        $history->dOptCode     = '_DBNULL_';
                        $history->cRegIp       = $this->refData->getRealIP();

                        $this->historyID = $this->dbHandler->insert(
                            'tnewsletterempfaengerhistory',
                            $history
                        );
                        executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, [
                            'oNewsletterEmpfaengerHistory' => $history
                        ]);

                        $this->oLogger->debug('this->conf: '.print_r($this->conf['newsletter'], true)); // --DEBUG--


                        // double-opt-in only for unknown user or for all customers too (setting no. 680)
                        if (($this->conf['newsletter']['newsletter_doubleopt'] === 'U'
                            && empty($_SESSION['Kunde']->kKunde))
                            || $this->conf['newsletter']['newsletter_doubleopt'] === 'A'
                        ) {
                            $this->hasSendingPermission = true;
                            $plausi                     = new \stdClass();
                        } else {
                            $this->alertHelper->addAlert(
                                Alert::TYPE_NOTE,
                                Shop::Lang()->get('newsletterNomailAdd', 'messages'),
                                'newsletterNomailAdd'
                            );
                        }
                    }
                }
            } else {
                $this->alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
                    'newsletterWrongemail'
                );
            }

            Shop::Smarty()->assign('oPlausi', $plausi);
            $this->dbHandler->delete('tnewsletterempfaengerblacklist', 'cMail', $this->refData->getEmail());
        } else {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                Text::filterEmailAddress($_POST['cEmail']) !== false
                    ? (Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />')
                    : (Shop::Lang()->get('invalidEmail') . '<br />'),
                'newsletterBlockedInvalid'
            );

            throw new InvalidInputException('invalid email: ', $this->refData->getEmail());
        }

        if ($this->hasSendingPermission === true) {
            $this->saveOptin($this->optCode);
        }

        return $this;
    }

    /**
     * @throws InvalidInputException
     */
    public function sendActivationMail(): void
    {
        if ($this->hasSendingPermission !== true) {
            return;
        }
        // --TODO-- maybe find a better place to check and complain this
        if (!Text::filterEmailAddress($this->refData->getEmail()) !== false) {
            throw new InvalidInputException(
                Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
                $this->refData->getEmail()
            );
        }

        $shopURL                       = Shop::getURL();
        $optinCodePrefix               = '/?oc=';
        $recipient                     = new \stdClass();
        $recipient->kSprache           = Shop::getLanguage();
        $recipient->kKunde             = isset($_SESSION['Kunde']->kKunde)
            ? (int)$_SESSION['Kunde']->kKunde
            : 0;
        $recipient->nAktiv             = isset($_SESSION['Kunde']->kKunde)
            && $_SESSION['Kunde']->kKunde > 0;
        $recipient->cAnrede            = $this->refData->getSalutation();
        $recipient->cVorname           = $this->refData->getFirstName();
        $recipient->cNachname          = $this->refData->getLastName();
        $recipient->cEmail             = $this->refData->getEmail();
        $recipient->cLoeschURL         = $shopURL . $optinCodePrefix . self::DELETE_CODE . $this->optCode;
        $recipient->cFreischaltURL     = $shopURL . $optinCodePrefix . self::ACTIVATE_CODE . $this->optCode;
        $recipient->dLetzterNewsletter = '_DBNULL_';
        $recipient->dEingetragen       = $this->nowDataTime->format('Y-m-d H:i:s');
        // --TODO-- dEingetragen: needed? only used in old-fashioned table with 'NOW()'-sql - for mail not relevant

        $templateData                       = new \stdClass();
        $templateData->tkunde               = $_SESSION['Kunde'] ?? null;
        $templateData->NewsletterEmpfaenger = $recipient;

        $mailer = Shop::Container()->get(Mailer::class);
        $mail   = new Mail();
        $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_NEWSLETTERANMELDEN, $templateData));

        $this->dbHandler->update(
            'tnewsletterempfaengerhistory',
            'kNewsletterEmpfaengerHistory',
            $this->historyID,
            (object)['cEmailBodyHtml' => $mail->getBodyHTML()]
        );
        $this->alertHelper->addAlert(
            Alert::TYPE_NOTE,
            Shop::Lang()->get('newsletterAdd', 'messages'),
            'newsletterAdd'
        );
    }

    public function activateOptin(): void
    {
        $optinCode  = self::ACTIVATE_CODE . $this->optCode;
        $recicpient = $this->dbHandler->select('tnewsletterempfaenger', 'cOptCode', $optinCode);
        if (isset($recicpient->kNewsletterEmpfaenger) && $recicpient->kNewsletterEmpfaenger > 0) {
            executeHook(
                HOOK_NEWSLETTER_PAGE_EMPFAENGERFREISCHALTEN,
                ['oNewsletterEmpfaenger' => $recicpient]
            );
            $this->dbHandler->update(
                'tnewsletterempfaenger',
                'kNewsletterEmpfaenger',
                (int)$recicpient->kNewsletterEmpfaenger,
                (object)['nAktiv' => 1]
            );
            $this->dbHandler->query(
                'UPDATE tnewsletterempfaenger, tkunde
                SET tnewsletterempfaenger.kKunde = tkunde.kKunde
                WHERE tkunde.cMail = tnewsletterempfaenger.cEmail
                    AND tnewsletterempfaenger.kKunde = 0',
                ReturnType::DEFAULT
            );
            $upd           = new \stdClass();
            $upd->dOptCode = 'NOW()';
            $upd->cOptIp   = Request::getRealIP();
            $this->dbHandler->update(
                'tnewsletterempfaengerhistory',
                ['cOptCode', 'cAktion'],
                [$optinCode, 'Eingetragen'],
                $upd
            );
            /*
            $this->alertHelper->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('newsletterActive', 'messages'),
                'newsletterActive'
            );
            */
        }
    }

    /**
     * legacy de-activation
     */
    public function deactivateOptin(): void
    {
        if (!empty($this->optCode)) {
            // de-activate by opt-code
            $deleteCode = self::DELETE_CODE . $this->optCode;
            $recicpient = $this->dbHandler->select('tnewsletterempfaenger', 'cLoeschCode', $deleteCode);
            if (!empty($recicpient->cLoeschCode)) {
                executeHook(
                    HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN,
                    ['oNewsletterEmpfaenger' => $recicpient]
                );

                $this->dbHandler->delete('tnewsletterempfaenger', 'cLoeschCode', $deleteCode);
                $hist               = new \stdClass();
                $hist->kSprache     = $recicpient->kSprache;
                $hist->kKunde       = $recicpient->kKunde;
                $hist->cAnrede      = $recicpient->cAnrede;
                $hist->cVorname     = $recicpient->cVorname;
                $hist->cNachname    = $recicpient->cNachname;
                $hist->cEmail       = $recicpient->cEmail;
                $hist->cOptCode     = $recicpient->cOptCode;
                $hist->cLoeschCode  = $recicpient->cLoeschCode;
                $hist->cAktion      = 'Geloescht';
                $hist->dEingetragen = $recicpient->dEingetragen;
                $hist->dAusgetragen = 'NOW()';
                $hist->dOptCode     = '_DBNULL_';
                $hist->cRegIp       = Request::getRealIP();
                $this->dbHandler->insert('tnewsletterempfaengerhistory', $hist);

                executeHook(
                    HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN,
                    ['oNewsletterEmpfaengerHistory' => $hist]
                );
                $blacklist            = new \stdClass();
                $blacklist->cMail     = $recicpient->cEmail;
                $blacklist->dErstellt = 'NOW()';
                $this->dbHandler->insert('tnewsletterempfaengerblacklist', $blacklist);

                /* --OBSOLETE--  comes from Shop::Optin...
                former "Sie wurden erfolgreich aus unserem Newsletterverteiler ausgetragen."

                $this->alertHelper->addAlert(
                    Alert::TYPE_NOTE,
                    Shop::Lang()->get('newsletterDelete', 'messages'),
                    'newsletterDelete'
                );
                */
            } else {
                $this->alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('newsletterNocode', 'errorMessages'),
                    'newsletterNocode'
                );
            }
        } elseif (!empty($this->emailAddress)) {
            // de-activate by mail-address
            $recicpient = $this->dbHandler->select(
                'tnewsletterempfaenger',
                'cEmail',
                Text::htmlentities(Text::filterXSS($this->dbHandler->escape($_POST['cEmail'])))
            );
            if (!empty($recicpient->kNewsletterEmpfaenger)) {
                executeHook(
                    HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN,
                    ['oNewsletterEmpfaenger' => $recicpient]
                );
                $this->dbHandler->delete(
                    'tnewsletterempfaenger',
                    'cEmail',
                    Text::htmlentities(Text::filterXSS($_POST['cEmail']))
                );
                $hist               = new stdClass();
                $hist->kSprache     = $recicpient->kSprache;
                $hist->kKunde       = $recicpient->kKunde;
                $hist->cAnrede      = $recicpient->cAnrede;
                $hist->cVorname     = $recicpient->cVorname;
                $hist->cNachname    = $recicpient->cNachname;
                $hist->cEmail       = $recicpient->cEmail;
                $hist->cOptCode     = $recicpient->cOptCode;
                $hist->cLoeschCode  = $recicpient->cLoeschCode;
                $hist->cAktion      = 'Geloescht';
                $hist->dEingetragen = $recicpient->dEingetragen;
                $hist->dAusgetragen = 'NOW()';
                $hist->dOptCode     = '_DBNULL_';
                $hist->cRegIp       = Request::getRealIP();
                $this->dbHandler->insert('tnewsletterempfaengerhistory', $hist);

                executeHook(
                    HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN,
                    ['oNewsletterEmpfaengerHistory' => $hist]
                );
                $blacklist            = new stdClass();
                $blacklist->cMail     = $recicpient->cEmail;
                $blacklist->dErstellt = 'NOW()';
                $this->dbHandler->insert('tnewsletterempfaengerblacklist', $blacklist);

                $this->alertHelper->addAlert(
                    Alert::TYPE_NOTE,
                    Shop::Lang()->get('newsletterDelete', 'messages'),
                    'newsletterDelete'
                );
            } else {
                $this->alertHelper->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('newsletterNoexists', 'errorMessages'),
                    'newsletterNoexists'
                );
            }
        }

        // --TODO-- do perant deactivation, however
    }
}
