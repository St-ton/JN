<?php
/**
 * remove_google_analytics
 *
 * @author mh
 * @created Thu, 20 Dec 2018 10:42:00 +0100
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
class Migration_20181220104200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'remove Google Analytics';

    public function up()
    {
        $this->removeConfig('global_google_analytics_id');
        $this->removeConfig('global_google_ecommerce');
    }

    public function down()
    {
        $this->setConfig('global_google_analytics_id', '', 1, 'Google Analytics ID', 'text', 520, (object)[
            'cBeschreibung' => 'Falls Sie einen Google Analytics Account haben, tragen Sie hier Ihre ID ein (z.B. UA-xxxxxxx-x)'
        ]);
        $this->setConfig(
            'global_google_ecommerce',
            0,
            1,
            'Google Analytics eCommerce Erweiterung nutzen',
            'selectbox',
            520,
            (object)[
                'cBeschreibung' => 'M&ouml;chten Sie, dass Google alle Ihre Verk&auml;ufe trackt?',
                'inputOptions' => [
                    0 => 'Nein',
                    1 => 'Ja'
                ]
            ]
        );
    }
}
