<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard\steps;

use jtl\Wizard;
use jtl\Wizard\Question;

/**
 * Class CustomerFormStep
 */
class CustomerFormStep extends Step implements IStep
{
    /**
     * @var Wizard\Shop4Wizard
     */
    private $wizard;

    /**
     * @var string
     */
    private $ustidStatus;

    /**
     * CustomerFormStep constructor.
     * @param Wizard\Shop4Wizard $wizard
     *
     * Step 2
     */
    public function __construct($wizard)
    {
        $dependOnQ0 = function () {
            return false;
        };

        $this->wizard    = $wizard;
        $this->questions = [
            new Question('Verkauf an Endkunden', Question::TYPE_BOOL, 20),
            new Question('Eindeutige Artikelmerkmale: Merkmale', Question::TYPE_BOOL, 21, 20),
            new Question('Eindeutige Artikelmerkmale: Attribute anzeigen', Question::TYPE_BOOL, 22, 20),
            new Question('Eindeutige Artikelkurzbeschreibung anzeigen', Question::TYPE_BOOL, 23, 20),
            new Question('Verkauf an Händler', Question::TYPE_BOOL, 24),
            new Question('UstID des Shops', Question::TYPE_TEXT, 25, 24),
            new Question('Telefonnummer abfragen', Question::TYPE_BOOL, 26),
            new Question('Geburtsdatum abfragen', Question::TYPE_BOOL, 27),
            new Question('Weltweit versenden', Question::TYPE_BOOL, 28)
        ];
    }

    /**
     * @return int[]
     */
    public function getAvailableQuestions()
    {
        $availables = [];

        $availables[] = 0;

        if ($this->questions[0]->getValue()) {
            $availables[] = 1;
            $availables[] = 2;
            $availables[] = 3;
        }

        $availables[] = 4;

        if ($this->questions[4]->getValue()) {
            $availables[] = 5;
        }

        $availables[] = 6;
        $availables[] = 7;
        $availables[] = 8;

        return $availables;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Formularfelder';
    }

    /**
     * @return bool
     */
    public function isUstidStatus()
    {
        $res               = \Shop::DB()->query('SELECT cUSTID FROM tfirma', 1);
        $this->ustidStatus = !empty($res->cUSTID);

        return $this->ustidStatus;
    }

    /**
     * @return mixed|void
     * @param bool $jumpToNext
     */
    public function finishStep($jumpToNext = true)
    {
        // B2B
        if ($this->questions[4]->getValue()) {
            // Einschalten
            // KundenAbfrage Firma
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_firma', (object)[
                'cWert' => 'Y'
            ]);

            // KundenAbfrage UstID
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_ustid', (object)[
                'cWert' => 'Y'
            ]);

            // Zentralamt
            \Shop::DB()->update('teinstellungen', 'cName', 'shop_ustid_bzstpruefung', (object)[
                'cWert' => 'Y'
            ]);

            // Firmenzusatz
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_firmazusatz', (object)[
                'cWert' => 'Y'
            ]);

            // Adresszusatz
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_adresszusatz', (object)[
                'cWert' => 'Y'
            ]);

        }

        // B2C
        if ($this->questions[0]->getValue()) {
            // Ausschalten
            // KundenAbfrage Firma
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_firma', (object)[
                'cWert' => '0'
            ]);

            // KundenAbfrage UstID
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_ustid', (object)[
                'cWert' => '0'
            ]);

            // Zentralamt
            \Shop::DB()->update('teinstellungen', 'cName', 'shop_ustid_bzstpruefung', (object)[
                'cWert' => 'N'
            ]);

            // Firmenzusatz
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_firmazusatz', (object)[
                'cWert' => '0'
            ]);

            // Adresszusatz
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_adresszusatz', (object)[
                'cWert' => '0'
            ]);
        }

        if ($this->questions[5]->getValue()) {
            // Abfrage UstID
            \Shop::DB()->update('teinstellungen', 'cName', 'shop_ustid', (object)[
                'cWert' => $this->questions[5]->getValue()
            ]);
        }

        if ($this->questions[1]->getValue()) {
            // Eindeutige Artikelmerkmale Merkmale
            \Shop::DB()->update('teinstellungen', 'cName', 'bestellvorgang_artikelmerkmale', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            \Shop::DB()->update('teinstellungen', 'cName', 'bestellvorgang_artikelmerkmale', (object)[
                'cWert' => 'N'
            ]);
        }

        if ($this->questions[2]->getValue()) {
            // Eindeutige Artikelmerkmale Attribute
            \Shop::DB()->update('teinstellungen', 'cName', 'bestellvorgang_artikelattribute', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            \Shop::DB()->update('teinstellungen', 'cName', 'bestellvorgang_artikelattribute', (object)[
                'cWert' => 'N'
            ]);
        }

        if ($this->questions[3]->getValue()) {
            // Eindeutige Artikelkurzbeschreibung
            \Shop::DB()->update('teinstellungen', 'cName', 'bestellvorgang_artikelkurzbeschreibung', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            \Shop::DB()->update('teinstellungen', 'cName', 'bestellvorgang_artikelkurzbeschreibung', (object)[
                'cWert' => 'N'
            ]);
        }

        if ($this->questions[6]->getValue()) {
            // Telefonnummer abfragen
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_tel', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_tel', (object)[
                'cWert' => '0'
            ]);
        }

        if ($this->questions[7]->getValue()) {
            // Geburtstag abfragen
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_geburtstag', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_geburtstag', (object)[
                'cWert' => '0'
            ]);
        }

        // Weltweit versenden
        if ($this->questions[8]->getValue()) {
            // Land abfragen
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_standardland', (object)[
                'cWert' => 'Deutschland'
            ]);
            \Shop::DB()->update('teinstellungen', 'cName', 'lieferadresse_abfragen_standardland', (object)[
                'cWert' => 'Deutschland'
            ]);

            // Bundesland abfragen
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_bundesland', (object)[
                'cWert' => 'Y'
            ]);

            \Shop::DB()->update('teinstellungen', 'cName', 'lieferadresse_abfragen_bundesland', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            // Land nicht abfragen
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_standardland', (object)[
                'cWert' => ''
            ]);
            \Shop::DB()->update('teinstellungen', 'cName', 'lieferadresse_abfragen_standardland', (object)[
                'cWert' => ''
            ]);

            // Bundesland nicht abfragen
            \Shop::DB()->update('teinstellungen', 'cName', 'kundenregistrierung_bundesland', (object)[
                'cWert' => '0'
            ]);

            \Shop::DB()->update('teinstellungen', 'cName', 'lieferadresse_abfragen_bundesland', (object)[
                'cWert' => '0'
            ]);
        }

        if ($jumpToNext && ($this->questions[0]->getValue() || $this->questions[4]->getValue())) {
            $this->wizard->setStep(new AdditionalLinks($this->wizard));
        }
    }
}
