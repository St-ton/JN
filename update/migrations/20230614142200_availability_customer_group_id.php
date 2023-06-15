<?php declare(strict_types=1);

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230614142200
 */
class Migration_20230614142200 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'add mandatory consent item';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute(
            'ALTER TABLE `tverfuegbarkeitsbenachrichtigung` 
                ADD COLUMN `customerGroupID` INT NOT NULL DEFAULT 0 AFTER `dBenachrichtigtAm`'
        );

        $this->execute(
            'UPDATE `tverfuegbarkeitsbenachrichtigung`
                SET `customerGroupID` = (SELECT kKundengruppe FROM tkundengruppe WHERE cStandard = \'Y\')'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute(
            'ALTER TABLE `tverfuegbarkeitsbenachrichtigung`
                DROP COLUMN `customerGroupID`'
        );
    }
}
