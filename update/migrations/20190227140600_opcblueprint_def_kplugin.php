<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Add default value for topcblueprint.kPlugin
 *
 * @author Danny Raufeisen
 */

class Migration_20190227140600 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Add default value for topcblueprint.kPlugin';

    public function up()
    {
        $this->execute("ALTER TABLE topcblueprint MODIFY kPlugin INT NOT NULL DEFAULT 0");
    }

    public function down()
    {
        $this->execute("ALTER TABLE topcblueprint MODIFY kPlugin INT NOT NULL");
    }
}
