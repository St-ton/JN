<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Customer;

use JTL\Catalog\Product\Preise;
use JTL\Customer\Kunde;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Text;
use JTL\Shop;
use stdClass;

/**
 * Class KundenwerbenKunden
 * @package JTL\Customer
 */
class KundenwerbenKunden
{
    /**
     * @var int
     */
    public $kKundenWerbenKunden;

    /**
     * @var string
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
     * @var Kunde
     */
    public $oNeukunde;

    /**
     * @var Kunde
     */
    public $oBestandskunde;

    /**
     * KundenwerbenKunden constructor.
     * @param string $email
     */
    public function __construct($email = '')
    {
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'mailTools.php';
        if (\mb_strlen($email) > 0) {
            $this->loadFromDB($email);
        }
    }

    /**
     * @param string $email
     * @return $this
     */
    private function loadFromDB($email): self
    {
        if (\mb_strlen($email) > 0) {
            $email = Text::filterXSS($email);
            $oKwK  = Shop::Container()->getDB()->select('tkundenwerbenkunden', 'cEmail', $email);
            if (isset($oKwK->kKundenWerbenKunden) && $oKwK->kKundenWerbenKunden > 0) {
                foreach (\array_keys(\get_object_vars($oKwK)) as $member) {
                    $this->$member = $oKwK->$member;
                }
                $oKundeTMP                = new Kunde();
                $this->fGuthabenLocalized = Preise::getLocalizedPriceString($this->fGuthaben);
                $this->oNeukunde          = $oKundeTMP->holRegKundeViaEmail($this->cEmail);
                $this->oBestandskunde     = new Kunde($this->kKunde);
            }
        }

        return $this;
    }

    /**
     * @param bool $bLoadDB
     * @return $this
     */
    public function insertDB(bool $bLoadDB = false): self
    {
        $ins = GeneralObject::copyMembers($this);
        unset($ins->fGuthabenLocalized, $ins->oNeukunde, $ins->oBestandskunde);

        $this->kKundenWerbenKunden = Shop::Container()->getDB()->insert('tkundenwerbenkunden', $ins);
        if ($bLoadDB) {
            $this->loadFromDB($this->cEmail);
        }

        return $this;
    }

    /**
     * @param int $kKunde
     * @return null|stdClass
     */
    public function insertBoniDB(int $kKunde): ?stdClass
    {
        if ($kKunde <= 0) {
            return null;
        }
        $conf                           = Shop::getSettings([\CONF_GLOBAL, \CONF_KUNDENWERBENKUNDEN]);
        $kwkb                           = new stdClass();
        $kwkb->kKunde                   = $kKunde;
        $kwkb->fGuthaben                = (float)$conf['kundenwerbenkunden']['kwk_bestandskundenguthaben'];
        $kwkb->nBonuspunkte             = 0;
        $kwkb->dErhalten                = 'NOW()';
        $kwkb->kKundenWerbenKundenBonus = Shop::Container()->getDB()->insert('tkundenwerbenkundenbonus', $kwkb);

        return $kwkb;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function verbucheBestandskundenBoni($email): self
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
                $mail                 = new stdClass();
                $mail->tkunde         = new Kunde($customer->kKunde);
                $oKundeTMP            = new Kunde();
                $mail->oNeukunde      = $oKundeTMP->holRegKundeViaEmail($email);
                $mail->oBestandskunde = $mail->tkunde;
                $mail->Einstellungen  = $conf;
                // Update das Guthaben vom Bestandskunden
                Shop::Container()->getDB()->query(
                    'UPDATE tkunde
                        SET fGuthaben = fGuthaben + ' . $guthaben . '
                        WHERE kKunde = ' . (int)$customer->kKunde,
                    ReturnType::AFFECTED_ROWS
                );
                // in tkundenwerbenkundenboni eintragen
                $oKundenWerbenKundenBoni = $this->insertBoniDB($customer->kKunde);
                // tkundenwerbenkunden updaten und hinterlegen, dass der Bestandskunde das Guthaben erhalten hat
                Shop::Container()->getDB()->update(
                    'tkundenwerbenkunden',
                    'cEmail',
                    Text::filterXSS($email),
                    (object)['nGuthabenVergeben' => 1]
                );

                $oKundenWerbenKundenBoni->fGuthaben = Preise::getLocalizedPriceString(
                    (float)$conf['kundenwerbenkunden']['kwk_bestandskundenguthaben']
                );
                $mail->BestandskundenBoni           = $oKundenWerbenKundenBoni;
                // verschicke Email an Bestandskunden
                \sendeMail(\MAILTEMPLATE_KUNDENWERBENKUNDENBONI, $mail);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function sendeEmailanNeukunde(): self
    {
        $oMail                       = new stdClass();
        $oMail->oBestandskunde       = new Kunde($this->kKunde);
        $oMail->oNeukunde            = $this;
        $this->fGuthabenLocalized    = Preise::getLocalizedPriceString($this->fGuthaben);
        $oMail->oNeukunde->fGuthaben = $this->fGuthabenLocalized;
        $oMail->tkunde               = $oMail->oNeukunde;
        $oMail->tkunde->cMail        = $this->cEmail;

        \sendeMail(\MAILTEMPLATE_KUNDENWERBENKUNDEN, $oMail);

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
