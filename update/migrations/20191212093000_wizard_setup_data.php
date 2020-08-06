<?php
/**
 * Wizard setup data
 *
 * @author mh
 * @created Thu, 12 Dec 2019 09:30:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Shopsetting;

/**
 * Class Migration_20191212093000
 */
class Migration_20191212093000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Wizard setup data';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        if(Shopsetting::getInstance()->getValue(\CONF_GLOBAL, 'global_wizard_done') === null) {
            $this->setConfig(
                'global_wizard_done',
                'Y',
                \CONF_GLOBAL,
                'Einrichtungsassistent durchlaufen',
                'selectbox',
                1,
                (object)[
                    'cBeschreibung' => 'Einrichtungsassistent durchlaufen',
                    'inputOptions'  => [
                        'Y'      => 'Ja',
                        'N'      => 'Nein',
                    ],
                    'nStandardAnzeigen' => 0
                ]
            );
        }
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->removeConfig('global_wizard_done');
    }
}
