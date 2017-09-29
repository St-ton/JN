<?php
/**
 * removes option "Zertifikat ausgestellt auf www/nicht-www"
 *
 * @author Felix Moche
 * @created Wed, 28 Sep 2017 09:28:00 +0200
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
class Migration_20170928095400 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function down()
    {
        $this->setConfig('global_ssl_www', '', CONF_GLOBAL, 'Zertifikat ausgestellt auf', 'selectbox', 541, (object)[
            'cBeschreibung' => 'Diese Einstellung ist nur gültig, wenn Sie ein eigenes Zertifikat nutzen. Geben Sie hier an, ob es auf die Domain mit www. davor ausgestellt wurde.',
            'inputOptions'  => [
                'www.' => 'Zertifikat ausgestellt auf Domain mit www',
                '' => 'Zertifikat ausgestellt auf Domain ohne www',
            ],
        ]);
    }

    public function up()
    {
        $this->removeConfig('global_ssl_www');
    }
}
