<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210621093800
 */
class Migration_20210621093800 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add pw too long lang';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'login', 'passwordTooLong', 'Das Passwort ist zu lang.');
        $this->setLocalization('eng', 'login', 'passwordTooLong', 'Password too long.');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeLocalization('passwordTooLong', 'login');
    }
}
