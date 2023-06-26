<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Add default value for topcblueprint.kPlugin
 *
 * @author dr
 */

class Migration_20190227140600 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add default value for topcblueprint.kPlugin';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE topcblueprint MODIFY kPlugin INT NOT NULL DEFAULT 0');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE topcblueprint MODIFY kPlugin INT NOT NULL');
    }
}
