<?php declare(strict_types=1);
/**
 * adds street name and street number input concatenation option
 *
 * @author ms
 * @created Tue, 09 May 2023 09:04:35 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230509090435
 */
class Migration_20230509090435 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'adds street name and street number input concatenation option';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'street_name_number_concatenation',
            'Y',
            CONF_KUNDEN,
            'Straße und Hausnummer gemeinsam abfragen',
            'selectbox',
            240,
            (object)[
                'cBeschreibung' => 'Straße und Hausnummer in Adressen gemeinsam abfragen?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('street_name_number_concatenation');
    }
}
