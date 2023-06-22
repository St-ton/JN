<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220201113000
 */
class Migration_20220201113000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Create sqlite3 caching option';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute("INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort)
            VALUES ('1551','SQLite','sqlite','10')"
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute("DELETE FROM teinstellungenconfwerte WHERE cName = 'SQLite' AND kEinstellungenConf = 1551");
    }
}
