<?php
/**
 * remove-shopinfo-menu-point
 *
 * @author ms
 * @created Thu, 05 Apr 2018 09:00:00 +0200
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
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180405090000 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'removes maintenance hint setting';

    public function up()
    {
        $this->removeConfig("wartungsmodus_hinweis");
    }

    public function down()
    {
        $this->setConfig(
            'wartungsmodus_hinweis',
            'Dieser Shop befindet sich im Wartungsmodus.',
            CONF_GLOBAL,
            'Wartungsmodus Hinweis',
            'text',
            1020,
            (object) [
                'cBeschreibung' => 'Dieser Hinweis wird Besuchern angezeigt, wenn der Shop im Wartungsmodus ist. Achtung: Im Evo-Template steuern Sie diesen Text &uuml;ber die Sprachvariable maintenanceModeActive.',
            ]
        );
    }
}
