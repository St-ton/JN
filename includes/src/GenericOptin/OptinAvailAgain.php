<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
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

/**
 * Class OptinAvailAgain
 * @package JTL\GenericOptin
 */
class OptinAvailAgain extends GenericOptinBase implements GenericOptinInterface
{
    /**
     * @var stdClass
     */
    private $product;

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
        $this->product = $this->dbHandler->select('tartikel', 'kArtikel', $this->refData->getProductId());
        $this->saveOptin($this->generateUniqOptinCode());

        return $this;
    }

    /**
     * send the optin activation mail
     */
    public function sendActivationMail(): void
    {
        $customerId = !empty(Frontend::getCustomer()->getID()) ? Frontend::getCustomer()->getID() : 0;

        $recipient               = new stdClass();
        $recipient->kSprache     = Shop::getLanguage();
        $recipient->kKunde       = $customerId;
        $recipient->nAktiv       = $customerId > 0;
        $recipient->cAnrede      = $this->refData->getSalutation();
        $recipient->cVorname     = $this->refData->getFirstName();
        $recipient->cNachname    = $this->refData->getLastName();
        $recipient->cEmail       = $this->refData->getEmail();
        $recipient->dEingetragen = $this->nowDataTime->format('Y-m-d H:i:s');

        $optin                  = new stdClass();
        $articleSeoURL          = Shop::getURL() . '/' . $this->product->cSeo;
        $optin->activationURL   = $articleSeoURL . '?oc=' . self::ACTIVATE_CODE . $this->optCode;
        $optin->deactivationURL = $articleSeoURL . '?oc=' . self::CLEAR_CODE . $this->optCode;

        $templateData                                   = new stdClass();
        $templateData->tkunde                           = $_SESSION['Kunde'] ?? null;
        $templateData->tartikel                         = $this->product;
        $templateData->tverfuegbarkeitsbenachrichtigung = [];
        $templateData->optin                            = $optin;
        $templateData->mailReceiver                     = $recipient;

        $mailer = Shop::Container()->get(Mailer::class);
        $mail   = new Mail();
        $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR_OPTIN, $templateData));

        Shop::Container()->getAlertService()->addAlert(
            Alert::TYPE_INFO,
            Shop::Lang()->get('availAgainOptinCreated', 'messages'),
            'availAgainOptinCreated'
        );
    }

    /**
     * @throws \Exception
     */
    public function activateOptin(): void
    {
        $inquiry            = Product::getAvailabilityFormDefaults();
        $inquiry->kSprache  = Shop::getLanguage();
        $inquiry->cIP       = Request::getRealIP();
        $inquiry->dErstellt = 'NOW()';
        $inquiry->nStatus   = 0;
        $inquiry->kArtikel  = $this->refData->getProductId();
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
    }

    /**
     * do opt-in specific de-activations
     */
    public function deactivateOptin(): void
    {
        $this->dbHandler->delete('tverfuegbarkeitsbenachrichtigung', 'cMail', $this->refData->getEmail());
    }
}
