<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\GeneralObject;

require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

/**
 * Class KundenwerbenKunden
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
     * @param string $cEmail
     */
    public function __construct($cEmail = '')
    {
        if (mb_strlen($cEmail) > 0) {
            $this->loadFromDB($cEmail);
        }
    }

    /**
     * @param string $mail
     * @return $this
     */
    private function loadFromDB($mail): self
    {
        if (mb_strlen($mail) > 0) {
            $mail = StringHandler::filterXSS($mail);
            $oKwK = Shop::Container()->getDB()->select('tkundenwerbenkunden', 'cEmail', $mail);
            if (isset($oKwK->kKundenWerbenKunden) && $oKwK->kKundenWerbenKunden > 0) {
                foreach (array_keys(get_object_vars($oKwK)) as $member) {
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
        $conf                           = Shop::getSettings([CONF_GLOBAL, CONF_KUNDENWERBENKUNDEN]);
        $kwkb                           = new stdClass();
        $kwkb->kKunde                   = $kKunde;
        $kwkb->fGuthaben                = (float)$conf['kundenwerbenkunden']['kwk_bestandskundenguthaben'];
        $kwkb->nBonuspunkte             = 0;
        $kwkb->dErhalten                = 'NOW()';
        $kwkb->kKundenWerbenKundenBonus = Shop::Container()->getDB()->insert('tkundenwerbenkundenbonus', $kwkb);

        return $kwkb;
    }

    /**
     * @param string $cMail
     * @return $this
     */
    public function verbucheBestandskundenBoni($cMail): self
    {
        if (mb_strlen($cMail) === 0) {
            return $this;
        }
        $oBestandskunde = Shop::Container()->getDB()->queryPrepared(
            'SELECT tkunde.kKunde
                FROM tkunde
                JOIN tkundenwerbenkunden 
                    ON tkundenwerbenkunden.kKunde = tkunde.kKunde
                WHERE tkundenwerbenkunden.cEmail = :mail
                    AND tkundenwerbenkunden.nGuthabenVergeben = 0',
            ['mail' => $cMail],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($oBestandskunde->kKunde) && $oBestandskunde->kKunde > 0) {
            $conf = Shop::getSettings([CONF_GLOBAL, CONF_KUNDENWERBENKUNDEN]);
            if ($conf['kundenwerbenkunden']['kwk_nutzen'] === 'Y') {
                $guthaben              = (float)$conf['kundenwerbenkunden']['kwk_bestandskundenguthaben'];
                $mail                 = new stdClass();
                $mail->tkunde         = new Kunde($oBestandskunde->kKunde);
                $oKundeTMP             = new Kunde();
                $mail->oNeukunde      = $oKundeTMP->holRegKundeViaEmail($cMail);
                $mail->oBestandskunde = $mail->tkunde;
                $mail->Einstellungen  = $conf;
                // Update das Guthaben vom Bestandskunden
                Shop::Container()->getDB()->query(
                    'UPDATE tkunde
                        SET fGuthaben = fGuthaben + ' . $guthaben . '
                        WHERE kKunde = ' . (int)$oBestandskunde->kKunde,
                    \DB\ReturnType::AFFECTED_ROWS
                );
                // in tkundenwerbenkundenboni eintragen
                $oKundenWerbenKundenBoni = $this->insertBoniDB($oBestandskunde->kKunde);
                // tkundenwerbenkunden updaten und hinterlegen, dass der Bestandskunde das Guthaben erhalten hat
                Shop::Container()->getDB()->update(
                    'tkundenwerbenkunden',
                    'cEmail',
                    StringHandler::filterXSS($cMail),
                    (object)['nGuthabenVergeben' => 1]
                );

                $oKundenWerbenKundenBoni->fGuthaben = Preise::getLocalizedPriceString(
                    (float)$conf['kundenwerbenkunden']['kwk_bestandskundenguthaben']
                );
                $mail->BestandskundenBoni           = $oKundenWerbenKundenBoni;
                // verschicke Email an Bestandskunden
                sendeMail(MAILTEMPLATE_KUNDENWERBENKUNDENBONI, $mail);
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

        sendeMail(MAILTEMPLATE_KUNDENWERBENKUNDEN, $oMail);

        return $this;
    }

    /**
     * @param array $post
     * @return bool
     */
    public static function checkInputData(array $post): bool
    {
        $cVorname  = StringHandler::filterXSS($post['cVorname']);
        $cNachname = StringHandler::filterXSS($post['cNachname']);
        $cEmail    = StringHandler::filterXSS($post['cEmail']);

        return mb_strlen($cVorname) > 0 && mb_strlen($cNachname) > 0 && StringHandler::filterEmailAddress($cEmail) !== false;
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
        $cVorname  = StringHandler::filterXSS($post['cVorname']);
        $cNachname = StringHandler::filterXSS($post['cNachname']);
        $cEmail    = StringHandler::filterXSS($post['cEmail']);
        $oKunde    = Shop::Container()->getDB()->select('tkunde', 'cMail', $cEmail);

        if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
            return false;
        }
        $oKwK = new KundenwerbenKunden($cEmail);
        if ((int)$oKwK->kKundenWerbenKunden > 0) {
            return false;
        }
        // Setze in tkundenwerbenkunden
        $oKwK->kKunde       = $_SESSION['Kunde']->kKunde;
        $oKwK->cVorname     = $cVorname;
        $oKwK->cNachname    = $cNachname;
        $oKwK->cEmail       = $cEmail;
        $oKwK->nRegistriert = 0;
        $oKwK->fGuthaben    = (float)$conf['kundenwerbenkunden']['kwk_neukundenguthaben'];
        $oKwK->dErstellt    = 'NOW()';
        $oKwK->insertDB();
        $oKwK->sendeEmailanNeukunde();

        return true;
    }
}
