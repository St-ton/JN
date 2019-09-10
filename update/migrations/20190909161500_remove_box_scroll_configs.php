<?php
/**
 * Remove box scroll configs
 *
 * @author mh
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190909161500
 */
class Migration_20190909161500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove box scroll configs';

    public function up()
    {
        $this->removeConfig('box_bestseller_scrollen');
        $this->removeConfig('box_sonderangebote_scrollen');
        $this->removeConfig('box_neuimsortiment_scrollen');
        $this->removeConfig('box_topangebot_scrollen');
        $this->removeConfig('box_erscheinende_scrollen');
        $this->removeConfig('boxen_topbewertet_scrollbar');
        $this->removeConfig('boxen_preisradar_scrollbar');
    }

    public function down()
    {
        $this->setConfig(
            'box_bestseller_scrollen',
            '0',
            \CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            115,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'box_sonderangebote_scrollen',
            '0',
            \CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            215,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'box_neuimsortiment_scrollen',
            '0',
            \CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            315,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'box_topangebot_scrollen',
            '0',
            \CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            415,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'box_erscheinende_scrollen',
            '0',
            \CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            815,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'boxen_topbewertet_scrollbar',
            '0',
            \CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            1320,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
        $this->setConfig(
            'boxen_preisradar_scrollbar',
            '0',
            \CONF_BOXEN,
            'Scrolling benutzen',
            'selectbox',
            1410,
            (object)[
                'cBeschreibung' => 'Sollen die Produkte scrollbar sein?',
                'inputOptions'  => [
                    '0' => 'Nein',
                    '1' => 'Ja, von oben nach unten',
                    '2' => 'Ja, von unten nach oben'
                ]
            ],
            true
        );
    }
}
