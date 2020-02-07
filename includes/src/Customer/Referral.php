<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Customer;

use JTL\Catalog\Product\Preise;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;
use stdClass;

/**
 * Class Referral
 * @package JTL\Customer
 */
class Referral
{
    /**
     * @var int
     */
    public $kKundenWerbenKunden;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cVorname;

    /**
     * @var string
     */
    public $cNachname;

    /**
     * @var string
     */
    public $cEmail;

    /**
     * @var string
     */
    public $nRegistriert;

    /**
     * @var string
     */
    public $nGuthabenVergeben;

    /**
     * @var float
     */
    public $fGuthaben;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var float
     */
    public $fGuthabenLocalized;

    /**
     * @var Customer
     */
    public $oNeukunde;

    /**
     * @var Customer
     */
    public $oBestandskunde;

    /**
     * Referral constructor.
     * @param string $email
     */
    public function __construct(string $email = '')
    {
        if (\mb_strlen($email) > 0) {
            $this->loadFromDB($email);
        }
    }

    /**
     * @param string $email
     * @return $this
     */
    private function loadFromDB(string $email): self
    {
        if (\mb_strlen($email) === 0) {
            return $this;
        }
        $email = Text::filterXSS($email);
        $data  = Shop::Container()->getDB()->select('tkundenwerbenkunden', 'cEmail', $email);
        if (isset($data->kKundenWerbenKunden) && $data->kKundenWerbenKunden > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                if (\in_array($member, ['kKunde', 'kKundenWerbenKunden'], true)) {
                    $data->$member = (int)$data->$member;
                }
                $this->$member = $data->$member;
            }
            $tmpCustomer              = new Customer();
            $this->fGuthabenLocalized = Preise::getLocalizedPriceString($this->fGuthaben);
            $this->oNeukunde          = $tmpCustomer->holRegKundeViaEmail($this->cEmail);
            $this->oBestandskunde     = new Customer($this->kKunde);
        }

        return $this;
    }

    /**
     * @param bool $reload
     * @return $this
     */
    public function insertDB(bool $reload = false): self
    {
        $ins = GeneralObject::copyMembers($this);
        unset($ins->fGuthabenLocalized, $ins->oNeukunde, $ins->oBestandskunde);

        $this->kKundenWerbenKunden = Shop::Container()->getDB()->insert('tkundenwerbenkunden', $ins);
        if ($reload) {
            $this->loadFromDB($this->cEmail);
        }

        return $this;
    }

    /**
     * @param int $customerID
     * @return null|stdClass
     */
    public function insertBoniDB(int $customerID): ?stdClass
    {
        if ($customerID <= 0) {
            return null;
        }
        $conf                          = Shop::getSettings([\CONF_GLOBAL, \CONF_KUNDENWERBENKUNDEN]);
        $ins                           = new stdClass();
        $ins->kKunde                   = $customerID;
        $ins->fGuthaben                = (float)$conf['kundenwerbenkunden']['kwk_bestandskundenguthaben'];
        $ins->nBonuspunkte             = 0;
        $ins->dErhalten                = 'NOW()';
        $ins->kKundenWerbenKundenBonus = Shop::Container()->getDB()->insert('tkundenwerbenkundenbonus', $ins);

        return $ins;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function verbucheBestandskundenBoni(string $email): self
    {
        if (\mb_strlen($email) === 0) {
            return $this;
        }
        $customer = Shop::Container()->getDB()->queryPrepared(
            'SELECT tkunde.kKunde
                FROM tkunde
                JOIN tkundenwerbenkunden 
                    ON tkundenwerbenkunden.kKunde = tkunde.kKunde
                WHERE tkundenwerbenkunden.cEmail = :mail
                    AND tkundenwerbenkunden.nGuthabenVergeben = 0',
            ['mail' => $email],
            ReturnType::SINGLE_OBJECT
        );
        if (isset($customer->kKunde) && $customer->kKunde > 0) {
            $conf = Shop::getSettings([\CONF_GLOBAL, \CONF_KUNDENWERBENKUNDEN]);
            if ($conf['kundenwerbenkunden']['kwk_nutzen'] === 'Y') {
                $guthaben             = (float)$conf['kundenwerbenkunden']['kwk_bestandskundenguthaben'];
                $data                 = new stdClass();
                $data->tkunde         = new Customer($customer->kKunde);
                $tmpCustomer          = new Customer();
                $data->oNeukunde      = $tmpCustomer->holRegKundeViaEmail($email);
                $data->oBestandskunde = $data->tkunde;
                $data->Einstellungen  = $conf;
                // Update das Guthaben vom Bestandskunden
                Shop::Container()->getDB()->query(
                    'UPDATE tkunde
                        SET fGuthaben = fGuthaben + ' . $guthaben . '
                        WHERE kKunde = ' . (int)$customer->kKunde,
                    ReturnType::AFFECTED_ROWS
                );
                // in tkundenwerbenkundenboni eintragen
                $bonus = $this->insertBoniDB($customer->kKunde);
                // tkundenwerbenkunden updaten und hinterlegen, dass der Bestandskunde das Guthaben erhalten hat
                Shop::Container()->getDB()->update(
                    'tkundenwerbenkunden',
                    'cEmail',
                    Text::filterXSS($email),
                    (object)['nGuthabenVergeben' => 1]
                );

                $bonus->fGuthaben         = Preise::getLocalizedPriceString(
                    (float)$conf['kundenwerbenkunden']['kwk_bestandskundenguthaben']
                );
                $data->BestandskundenBoni = $bonus;
                // verschicke Email an Bestandskunden
                $mailer = Shop::Container()->get(Mailer::class);
                $mail   = new Mail();
                $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_KUNDENWERBENKUNDENBONI, $data));
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function sendeEmailanNeukunde(): self
    {
        $data                       = new stdClass();
        $data->oBestandskunde       = new Customer($this->kKunde);
        $data->oNeukunde            = $this;
        $this->fGuthabenLocalized   = Preise::getLocalizedPriceString($this->fGuthaben);
        $data->oNeukunde->fGuthaben = $this->fGuthabenLocalized;
        $data->tkunde               = $data->oNeukunde;
        $data->tkunde->cMail        = $this->cEmail;

        $mailer = Shop::Container()->get(Mailer::class);
        $mail   = new Mail();
        $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_KUNDENWERBENKUNDEN, $data));

        return $this;
    }

    /**
     * @param array $post
     * @return bool
     */
    public static function checkInputData(array $post): bool
    {
        return \mb_strlen(Text::filterXSS($post['cVorname'])) > 0
            && \mb_strlen(Text::filterXSS($post['cNachname'])) > 0
            && Text::filterEmailAddress(Text::filterXSS($post['cEmail'])) !== false;
    }

    /**
     * @param array $post
     * @param array $conf
     * @return bool
     */
    public static function saveToDB(array $post, array $conf): bool
    {
        if ($conf['kundenwerbenkunden']['kwk_nutzen'] !== 'Y') {
            return false;
        }
        $email    = Text::filterXSS($post['cEmail']);
        $customer = Shop::Container()->getDB()->select('tkunde', 'cMail', $email);
        if (isset($customer->kKunde) && $customer->kKunde > 0) {
            return false;
        }
        $instance = new self($email);
        if ((int)$instance->kKundenWerbenKunden > 0) {
            return false;
        }
        // Setze in tkundenwerbenkunden
        $instance->kKunde       = $_SESSION['Kunde']->kKunde;
        $instance->cVorname     = Text::filterXSS($post['cVorname']);
        $instance->cNachname    = Text::filterXSS($post['cNachname']);
        $instance->cEmail       = $email;
        $instance->nRegistriert = 0;
        $instance->fGuthaben    = (float)$conf['kundenwerbenkunden']['kwk_neukundenguthaben'];
        $instance->dErstellt    = 'NOW()';
        $instance->insertDB();
        $instance->sendeEmailanNeukunde();

        return true;
    }
}
