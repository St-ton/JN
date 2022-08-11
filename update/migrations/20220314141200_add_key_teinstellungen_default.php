<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220314141200
 */
class Migration_20220314141200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add key teinstellungen default';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->getDB()->queryPrepared(
            "DELETE FROM `teinstellungen_default`
                WHERE `cName` = 'vergleichsliste_anzeigen'
                  AND kEinstellungenSektion = :section",
            ['section' => CONF_VERGLEICHSLISTE]
        );
        $this->execute('CREATE UNIQUE INDEX sectionName ON teinstellungen_default(kEinstellungenSektion, cName);');
        $this->getDB()->queryPrepared(
            "INSERT IGNORE INTO `teinstellungen_default` VALUES (:section, 'vergleichsliste_anzeigen', 'Y', NULL)",
            ['section' => CONF_VERGLEICHSLISTE]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP INDEX sectionName ON teinstellungen_default');
    }
}
