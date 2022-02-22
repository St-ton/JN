<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210616151700
 */
class Migration_20210616151700 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Consent support for templates';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute(
            'ALTER TABLE `tconsent` 
                ADD COLUMN `templateID` VARCHAR(255) NULL DEFAULT NULL'
        );
        $this->execute(
            'ALTER TABLE `tglobals` 
                ADD COLUMN `consentVersion` INT NOT NULL DEFAULT 1'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute(
            'ALTER TABLE `tconsent` 
                DROP COLUMN `templateID`'
        );
        $this->execute(
            'ALTER TABLE `tglobals` 
                DROP COLUMN `consentVersion`'
        );
    }
}
