<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard\steps;

use jtl\Wizard\Question;
use jtl\Wizard\Shop4Wizard;

/**
 * Class GlobalSettingsStep
 * Step 1
 */
class GlobalSettingsStep extends Step implements IStep
{
    /**
     * @var int
     */
    private $standardTax;

    /**
     * @var Shop4Wizard
     */
    private $wizard;

    /**
     * GlobalSettingsStep constructor.
     * @param Shop4Wizard $wizard
     */
    public function __construct($wizard)
    {
        $this->wizard    = $wizard;
        $this->questions = [
            new Question('Kleinunternehmerregelung nach	 §19 UStG anwenden?', Question::TYPE_BOOL, 10),
            new Question('Globale Email-Adresse:', Question::TYPE_EMAIL, 11)
        ];
    }

    /**
     * @return int[]
     */
    public function getAvailableQuestions()
    {
        $availables   = [];
        $availables[] = 0;
        $availables[] = 1;

        return $availables;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Globale Einstellungen';
    }

    /**
     * @return int
     */
    public function getTax()
    {
        $res               = \Shop::DB()->query("SELECT fSteuersatz FROM tsteuersatz WHERE kSteuersatz = 1", 1);
        $this->standardTax = $res->fSteuersatz;

        return $this->standardTax;
    }

    /**
     * @param bool $jumpToNext
     * @return mixed|void
     */
    public function finishStep($jumpToNext = true)
    {
        if ($this->questions[0]->getValue()) {
            //  Einstellung 223 auf "endpreis"
            \Shop::DB()->update('teinstellungen', 'cName', 'global_ust_auszeichnung', (object)[
                'cWert' => 'endpreis'
            ]);

            //  Einstellung 224 FooterText anpassen
            \Shop::DB()->update('teinstellungen', 'cName', 'global_fusszeilehinweis', (object)[
                'cWert' => '* Gemäß §19 UStG wird keine Umsatzsteuer berechnet'
            ]);

            //  Einstellung 225 auf "nein" stellen
            \Shop::DB()->update('teinstellungen', 'cName', 'global_steuerpos_anzeigen', (object)[
                'cWert' => 'N'
            ]);
        } else {
            \Shop::DB()->update('teinstellungen', 'cName', 'global_ust_auszeichnung', (object)[
                'cWert' => 'auto'
            ]);
            \Shop::DB()->update('teinstellungen', 'cName', 'global_fusszeilehinweis', (object)[
                'cWert' => ''
            ]);
            \Shop::DB()->update('teinstellungen', 'cName', 'global_steuerpos_anzeigen', (object)[
                'cWert' => 'Y'
            ]);
        }

        if ($this->questions[1]->getValue()) {
            \Shop::DB()->update('teinstellungen', 'cName', 'email_master_absender', (object)[
                'cWert' => $this->questions[1]->getValue()
            ]);

            if ($jumpToNext === true) {
                if ($this->questions[0]->getValue()) {
                    $this->wizard->setStep(3);
                    $this->wizard->setStep(new AdditionalLinks($this->wizard));
                } else {
                    $this->wizard->setStep(new CustomerFormStep($this->wizard));
                }
            }
        }
    }
}