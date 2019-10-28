<?php
/**
 * Update admin bootstrap template in database
 *
 * @author msc
 * @created Mon, 27 Aug 2018 09:11:16 +0200
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180827091116
 */
class Migration_20180827091116 extends Migration implements IMigration
{
    protected $author      = 'msc';
    protected $description = 'Update admin bootstrap template in database';

    public function up()
    {
        $this->getDB()->query(
            "UPDATE `ttemplate` SET
            `cTemplate` = 'bootstrap',
            `eTyp` = 3,
            `parent` = NULL,
            `name` = 'bootstrap',
            `author` = 'JTL-Software-GmbH',
            `url` = 'https://www.jtl-software.de',
            `version` = '1.0.0',
            `preview` = 'preview.png'
            WHERE `cTemplate` = 'bootstrap' AND `eTyp` = 'admin'
            LIMIT 1;",
            ReturnType::DEFAULT
        );
    }

    public function down()
    {
    }
}
