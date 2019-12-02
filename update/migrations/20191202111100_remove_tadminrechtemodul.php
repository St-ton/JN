<?php

/**
 * Remove tadminrechtemodul
 *
 * @author mh
 * @created Mon, 02 Dec 2019 11:11:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191202111100
 */
class Migration_20191202111100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove tadminrechtemodul';

    /**
     * @return mixed|void
     */
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tadminrechtemodul`');
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->execute('
            CREATE TABLE `tadminrechtemodul` (
              `kAdminrechtemodul` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `cName` varchar(255) NOT NULL,
              `nSort` int(10) unsigned NOT NULL,
              PRIMARY KEY (`kAdminrechtemodul`)
            )
            ENGINE = MyISAM
            DEFAULT CHARSET = utf8
            COLLATE = utf8_unicode_ci
        ');
        $this->execute("INSERT INTO tadminrechtemodul VALUES
            (1, 'Admin', 1),
            (2, 'Einstellungen', 2),
            (3, 'Darstellung', 3),
            (4, 'Inhalt', 4),
            (5, 'Kaufabwicklung', 5),
            (6, 'Module', 6),
            (7, 'Import / Export', 7),
            (8, 'Erweiterungen', 8),
            (9, 'Plugins', 9),
            (10, 'Statistik', 10)
        ");
    }
}
