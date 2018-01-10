<?php
/**
 * Remove caching method "mysql"
 */

/**
 * Class Migration_20180108142900
 */
class Migration_20180108142900 extends Migration implements IMigration
{
    protected $author      = 'Felix Moche';
    protected $description = 'Remove caching method mysql';

    /**
     * @return bool|void
     */
    public function up()
    {
        $this->execute("DELETE FROM `teinstellungenconfwerte` WHERE kEinstellungenConf = 1551 AND cWert = 'mysql'");
        $this->execute("UPDATE `teinstellungen` SET `cWert`='null' WHERE `cWert`='mysql' AND cName = 'caching_method'");
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->execute("INSERT INTO `teinstellungenconfwerte` (kEinstellungenConf, cName, cWert, nSort) VALUES (1551, 'MySQL', 'mysql', 9)");
    }
}
