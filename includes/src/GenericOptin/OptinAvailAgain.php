<?php
/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 13.03.19
 * Time: 10:53
 */

namespace JTL\GenericOptin;

use JTL\Alert\Alert;
use JTL\CheckBox;
use JTL\DB\ReturnType;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Kampagne;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

class OptinAvailAgain extends GenericOptinBase implements GenericOptinInterface
{
    private $article; // --TRY-OUT--

    /**
     * OptinAvailAgain constructor.
     * @param $inheritData
     */
    public function __construct($inheritData)
    {
        [
            $this->dbHandler,
            $this->nowDataTime,
            $this->refData,
            $this->emailAddress,
            $this->optCode,
            $this->actionPrefix
        ] = $inheritData;
    }

    /**
     * @param GenericOptinRefData $refData
     * @return OptinAvailAgain
     */
    public function createOptin(GenericOptinRefData $refData): self
    {
        $this->refData = $refData;
        $this->article = $this->dbHandler->select('tartikel', 'kArtikel', $this->refData->getArticleId());
        $this->saveOptin($this->generateUniqOptinCode());

        return $this;
    }

    /**
     * send the optin activation mail
     */
    public function sendActivationMail(): void
    {
        $recipient               = new stdClass();
        $recipient->kSprache     = Shop::getLanguage();
        $recipient->kKunde       = isset($_SESSION['Kunde']->kKunde)
            ? (int)$_SESSION['Kunde']->kKunde
            : 0;
        $recipient->nAktiv       = isset($_SESSION['Kunde']->kKunde)
            && $_SESSION['Kunde']->kKunde > 0;
        $recipient->cAnrede      = $this->refData->getSalutation();
        $recipient->cVorname     = $this->refData->getFirstName();
        $recipient->cNachname    = $this->refData->getLastName();
        $recipient->cEmail       = $this->refData->getEmail();
        $recipient->dEingetragen = $this->nowDataTime->format('Y-m-d H:i:s');

        $optin                  = new stdClass();
        $optin->activationURL   = Shop::getURL() . '/' . $this->article->cSeo . '?oc=ac' . $this->optCode;
        $optin->deactivationURL = Shop::getURL() . '/' . $this->article->cSeo . '?oc=cc' . $this->optCode;

        $templateData                                   = new stdClass();
        $templateData->tkunde                           = $_SESSION['Kunde'] ?? null;   // maybe --OBSOLETE--
        $templateData->tartikel                         = $this->article;
        $templateData->tverfuegbarkeitsbenachrichtigung = [];
        $templateData->optin                            = $optin;
        $templateData->mailReceiver                     = $recipient;

        $mailer = Shop::Container()->get(Mailer::class);
        $mail   = new Mail();
        $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR_OPTIN, $templateData));

        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_INFO,
            Shop::Lang()->get('availAgainOptinCreated', 'messages'),
            'availAgainOptinCreated'
        );
    }

    /**
     * --TODO--
     *
     * @throws \Exception
     */
    public function activateOptin(): void
    {
        $inquiry            = Product::getAvailabilityFormDefaults();
        $inquiry->kSprache  = Shop::getLanguage();
        $inquiry->cIP       = Request::getRealIP();
        $inquiry->dErstellt = 'NOW()';                  // --TO-CHECK--
        $inquiry->nStatus   = 0;
        $inquiry->kArtikel  = $this->refData->getArticleId();
        $inquiry->cMail     = $this->refData->getEmail();
        $inquiry->cVorname  = $this->refData->getFirstName();
        $inquiry->cNachname = $this->refData->getLastName();
        $checkBox           = new CheckBox();
        $customerGroupID    = Frontend::getCustomerGroup()->getID();
        if (empty($inquiry->cNachname)) {
            $inquiry->cNachname = '';
        }
        if (empty($inquiry->cVorname)) {
            $inquiry->cVorname = '';
        }
        \executeHook(\HOOK_ARTIKEL_INC_BENACHRICHTIGUNG, ['Benachrichtigung' => $inquiry]);
        $checkBox->triggerSpecialFunction(
            \CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT,
            $customerGroupID,
            true,
            $_POST,
            ['oKunde' => $inquiry, 'oNachricht' => $inquiry]
        )->checkLogging(\CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT, $customerGroupID, $_POST, true);
        $inquiryID = $this->dbHandler->queryPrepared(
            'INSERT INTO tverfuegbarkeitsbenachrichtigung
                (cVorname, cNachname, cMail, kSprache, kArtikel, cIP, dErstellt, nStatus)
                VALUES
                (:cVorname, :cNachname, :cMail, :kSprache, :kArtikel, :cIP, NOW(), :nStatus)
                ON DUPLICATE KEY UPDATE
                    cVorname = :cVorname, cNachname = :cNachname, ksprache = :kSprache,
                    cIP = :cIP, dErstellt = NOW(), nStatus = :nStatus',
            \get_object_vars($inquiry),
            ReturnType::LAST_INSERTED_ID
        );
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Kampagne::setCampaignAction(\KAMPAGNE_DEF_VERFUEGBARKEITSANFRAGE, $inquiryID, 1.0);
        }
//        Shop::Container()->getAlertService()->addAlert(
//            Alert::TYPE_SUCCESS,
//            Shop::Lang()->get('thankYouForNotificationSubscription', 'messages'),
//            'thankYouForNotificationSubscription'
//        );
    }

    public function deactivateOptin(): void
    {
        $this->oLogger->debug('DE-ACTIVATING AVIAL-OPTIN...'); // --DEBUG--

        $this->dbHandler->delete('tverfuegbarkeitsbenachrichtigung', 'cMail', $this->refData->getEmail());
    }
}
