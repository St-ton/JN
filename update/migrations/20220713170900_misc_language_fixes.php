<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220713170900
 */
class Migration_20220713170900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Misc language fixes';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'login', 'wishlistRename', 'Wunschzettel umbenennen');
        $this->setLocalization('ger', 'account data', 'noOrdersYet', 'Sie haben noch keine Bestellung aufgegeben.');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
