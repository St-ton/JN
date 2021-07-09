<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210525130500
 */
class Migration_20210525130500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove comparelist row setting';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->removeConfig('vergleichsliste_spaltengroesseattribut');
        $this->getDB()->queryPrepared(
            "INSERT INTO `teinstellungen_default` VALUES (:section, 'vergleichsliste_anzeigen', 'Y', NULL)",
            ['section' => CONF_VERGLEICHSLISTE]
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->setConfig(
            'vergleichsliste_spaltengroesseattribut',
            '300',
            CONF_VERGLEICHSLISTE,
            'Spaltenbreite der Attribute',
            'number',
            210
        );
        $this->getDB()->queryPrepared(
            "DELETE FROM `teinstellungen_default`
                WHERE `cName` = 'vergleichsliste_anzeigen'
                  AND kEinstellungenSektion = :section",
            ['section' => CONF_VERGLEICHSLISTE]
        );
    }
}
