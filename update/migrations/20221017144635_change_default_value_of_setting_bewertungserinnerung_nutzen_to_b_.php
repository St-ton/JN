<?php declare(strict_types=1);
/**
 * Change default value of setting "bewertungserinnerung_nutzen" to "B"
 *
 * @author sl
 * @created Mon, 17 Oct 2022 14:46:35 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221017144635
 */
class Migration_20221017144635 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Change default value of setting "bewertungserinnerung_nutzen" to "B"';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute("UPDATE teinstellungen_default SET cWERT = 'B' WHERE cName = 'bewertungserinnerung_nutzen'");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute("UPDATE teinstellungen_default SET cWERT = 'Y' WHERE cName = 'bewertungserinnerung_nutzen'");
    }
}
