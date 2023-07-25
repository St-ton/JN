<?php
/**
 * associate UstId-settings
 *
 * @author cr
 * @created Tue, 05 Feb 2019 14:59:48 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**+
 * Class Migration_20190205145948
 */
class Migration_20190205145948 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Associate UstId-settings';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('UPDATE teinstellungenconf SET nSort = 415 WHERE kEinstellungenConf = 6');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('UPDATE teinstellungenconf SET nSort = 140 WHERE kEinstellungenConf = 6');
    }
}
