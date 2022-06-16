<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Router\Controller\Backend\BoxController;

/**
 * Class Migration_20200514090200
 */
class Migration_20200514090200 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Remove box visibilites of invalid/deprecated page types';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $pageTypes = implode(',', BoxController::getValidPageTypes());
        $this->execute("DELETE FROM tboxensichtbar WHERE kSeite NOT IN ($pageTypes)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
