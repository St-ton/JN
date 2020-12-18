<?php declare(strict_types=1);

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201217190400
 */
class Migration_20201217190400 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Add samesite cookie option None';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $sectionID                = (int)$this->getDB()->query(
            'SELECT kEinstellungenConf
                FROM teinstellungenconfwerte
                    WHERE cName = \'Lax\' 
                    AND cWert = \'Lax\'',
            ReturnType::SINGLE_OBJECT
        )->kEinstellungenConf;
        $conf                     = new stdClass();
        $conf->kEinstellungenConf = $sectionID;
        $conf->cName              = 'None';
        $conf->cWert              = 'None';
        $conf->nSort              = 5;
        $this->getDB()->insert('teinstellungenconfwerte', $conf);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute(
            'DELETE FROM teinstellungenconfwerte
                WHERE cName = \'None\' 
                AND cWert = \'None\'
                AND nSort = 5');
    }
}
