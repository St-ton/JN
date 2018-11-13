<?php
/**
 * @author fm
 * @created Tue, 13 Nov 2018 11:29:00 +0200
 */

/**
 * Class Migration_20181113122900
 */
class Migration_20181113122900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Changes for new plugins';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tplugin` CHANGE COLUMN `nVersion` `nVersion` VARCHAR(255) NOT NULL');
        $this->execute("CREATE TABLE IF NOT EXISTS tpluginmigration 
            (
                kMigration bigint(14) NOT NULL, 
                nVersion int(3) NOT NULL, 
                dExecuted datetime NOT NULL,
                PRIMARY KEY (kMigration)
            ) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'");
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tplugin` CHANGE COLUMN `nVersion` `nVersion` INT NOT NULL');
        $this->execute('DROP TABLE `tpluginmigration`');
    }
}
