<?php
/**
 * @author fm
 * @created Wed, 4 Sep 2019 17:58:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190904175800
 */
class Migration_20190904175800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add image extension auto detection option';

    public function up()
    {
        $this->execute("INSERT INTO teinstellungenconfwerte (`kEinstellungenConf`, `cName`, `cWert`, `nSort`)
            VALUES(1483, 'AUTO', 'AUTO', 2)");
        $this->execute("INSERT INTO teinstellungenconfwerte (`kEinstellungenConf`, `cName`, `cWert`, `nSort`)
            VALUES(1483, 'WEBP', 'WEBP', 2)");
    }

    public function down()
    {
        $this->execute("DELETE FROM teinstellungenconfwerte WHERE `kEinstellungenConf` = 1483 AND `cName` = 'AUTO'");
        $this->execute("DELETE FROM teinstellungenconfwerte WHERE `kEinstellungenConf` = 1483 AND `cName` = 'WEBP'");
    }
}
