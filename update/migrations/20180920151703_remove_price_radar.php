<?php
/**
 * remove_price_radar
 *
 * @author mh
 * @created Thu, 20 Sep 2018 15:17:03 +0200
 */

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180920151703 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove Priceradar';

    public function up()
    {
        $this->execute("DELETE FROM tboxvorlage WHERE cTemplate='box_priceradar.tpl';");
    }

    public function down()
    {
        $this->execute(
            "INSERT INTO tboxvorlage 
                  (kBoxvorlage, kCustomID, eTyp, cName, cVerfuegbar, cTemplate) 
                VALUES (100, 0, 'tpl', 'Preisradar', '0', 'box_priceradar.tpl')"
        );
    }
}
