<?php
/**
 * update tsynclogin table
 *
 * @author Felix Moche
 * @created Mon, 15 Jan 2018 15:08:00 +0100
 */

/**
 * Class Migration_20180115150800
 */
class Migration_20180115150800 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Update tsynclogin table';

    public function up()
    {
        $this->execute("ALTER TABLE `tsynclogin`
            CHANGE COLUMN `cMail` `cMail` VARCHAR(255) NULL DEFAULT '0' ,
            CHANGE COLUMN `cName` `cName` VARCHAR(255) NULL DEFAULT '0' ,
            CHANGE COLUMN `cPass` `cPass` VARCHAR(255) NULL DEFAULT '0'"
        );
        $values = Shop::DB()->select('tsynclogin', [], []);
        if (!empty($values->cPass) && strlen($values->cPass) < 60) {
            $values->cPass = password_hash($values->cPass, PASSWORD_DEFAULT);
            Shop::DB()->update('tsynclogin', 1, 1, $values);
        }
    }

    public function down()
    {
    }
}
