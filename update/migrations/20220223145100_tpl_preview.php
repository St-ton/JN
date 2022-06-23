<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220223145100
 */
class Migration_20220223145100 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Template preview';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("ALTER TABLE `ttemplate` 
                CHANGE COLUMN `eTyp` `eTyp` ENUM('standard', 'mobil', 'admin', 'test') NOT NULL"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("ALTER TABLE `ttemplate` 
                CHANGE COLUMN `eTyp` `eTyp` ENUM('standard', 'mobil', 'admin') NOT NULL"
        );
    }
}
