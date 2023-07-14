<?php declare(strict_types=1);
/**
 * Add characters left message for inputs with max length
 *
 * @author Tim Niko Tegtmeyer
 * @created Mon, 26 Jun 2023 12:58:13 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230626125813
 */
class Migration_20230626125813 extends Migration implements IMigration
{
    protected $author = 'Tim Niko Tegtmeyer';
    protected $description = 'Add characters left message for inputs with max length';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'charactersLeft', 'Zeichen übrig');
        $this->setLocalization('eng', 'global', 'charactersLeft', 'characters left');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute("DELETE FROM `tsprachwerte` WHERE `kSprachsektion` = 1 AND `cName` = 'charactersLeft';");
    }
}
