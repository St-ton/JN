<?php
/**
 * enable_semantic_versioning
 *
 * @author mh
 * @created Wed, 01 Aug 2018 12:41:35 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180801124135
 */
class Migration_20180801124135 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Enable semantic versioning for templates';

    public function up()
    {
        $this->execute('ALTER TABLE ttemplate CHANGE COLUMN version version VARCHAR(20) NOT NULL');
    }

    public function down()
    {
        $this->execute('ALTER TABLE ttemplate CHANGE COLUMN version version FLOAT NOT NULL');
    }
}
