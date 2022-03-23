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
        $this->execute('CREATE UNIQUE INDEX sectionName ON teinstellungen_default(kEinstellungenSektion, cName);');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP INDEX sectionName ON teinstellungen_default');
    }
}
