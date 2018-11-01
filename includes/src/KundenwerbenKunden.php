<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
        if (strlen($cEmail) > 0) {
            $this->loadFromDB($cEmail);
        }
    }

    /**
     * @param string $cEmail
     * @return $this
     */
    private function loadFromDB($cEmail): self
    {
        if (strlen($cEmail) > 0) {
            $cEmail = StringHandler::filterXSS($cEmail);
            // Hole Daten durch Email vom Neukunden
            $oKwK = Shop::Container()->getDB()->select('tkundenwerbenkunden', 'cEmail', $cEmail);
            if (isset($oKwK->kKundenWerbenKunden) && $oKwK->kKundenWerbenKunden > 0) {
                $cMember_arr = array_keys(get_object_vars($oKwK));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oKwK->$cMember;
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
        $oObj = ObjectHelper::copyMembers($this);
        unset($oObj->fGuthabenLocalized, $oObj->oNeukunde, $oObj->oBestandskunde);

        $this->kKundenWerbenKunden = Shop::Container()->getDB()->insert('tkundenwerbenkunden', $oObj);
        if ($bLoadDB) {
            $this->loadFromDB($this->cEmail);
        }

        return $this;
    }

    /**
     * @param int $kKunde
     * @return null|stdClass
     */
    public function insertBoniDB(int $kKunde)
    {
        if ($kKunde > 0) {
            $conf = Shop::getSettings([CONF_GLOBAL, CONF_KUNDENWERBENKUNDEN]);

            $kwkb                           = new stdClass();
            $kwkb->kKunde                   = $kKunde;
            $kwkb->fGuthaben                = (float)$conf['kundenwerbenkunden']['kwk_bestandskundenguthaben'];
            $kwkb->nBonuspunkte             = 0;
            $kwkb->dErhalten                = 'NOW()';
            $kwkb->kKundenWerbenKundenBonus = Shop::Container()->getDB()->insert('tkundenwerbenkundenbonus', $kwkb);

            return $kwkb;
        }

        return null;
    }

    /**
     * @param string $cMail
     * @return $this
     */
    public function verbucheBestandskundenBoni($cMail): self
    {
        if (strlen($cMail) === 0) {
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
            $Einstellungen = Shop::getSettings([CONF_GLOBAL, CONF_KUNDENWERBENKUNDEN]);
            if (isset($Einstellungen['kundenwerbenkunden']['kwk_nutzen'])
                && $Einstellungen['kundenwerbenkunden']['kwk_nutzen'] === 'Y'
            ) { //#8023
                $guthaben              = (float)$Einstellungen['kundenwerbenkunden']['kwk_bestandskundenguthaben'];
                $oMail                 = new stdClass();
                $oMail->tkunde         = new Kunde($oBestandskunde->kKunde);
                $oKundeTMP             = new Kunde();
                $oMail->oNeukunde      = $oKundeTMP->holRegKundeViaEmail($cMail);
                $oMail->oBestandskunde = $oMail->tkunde;
                $oMail->Einstellungen  = $Einstellungen;
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
                $_upd                    = new stdClass();
                $_upd->nGuthabenVergeben = 1;
                Shop::Container()->getDB()->update(
                    'tkundenwerbenkunden',
                    'cEmail',
                    StringHandler::filterXSS($cMail),
                    $_upd
                );

                $oKundenWerbenKundenBoni->fGuthaben = Preise::getLocalizedPriceString(
                    (float)$Einstellungen['kundenwerbenkunden']['kwk_bestandskundenguthaben']
                );
                $oMail->BestandskundenBoni          = $oKundenWerbenKundenBoni;
                // verschicke Email an Bestandskunden
                sendeMail(MAILTEMPLATE_KUNDENWERBENKUNDENBONI, $oMail);
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

        return strlen($cVorname) > 0 && strlen($cNachname) > 0 && StringHandler::filterEmailAddress($cEmail) !== false;
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
        $oKunde = Shop::Container()->getDB()->select('tkunde', 'cMail', $cEmail);

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
