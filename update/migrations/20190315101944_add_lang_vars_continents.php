<?php
/**
 * add_lang_vars_continents
 *
 * @author mh
 * @created Fri, 15 Mar 2019 10:19:44 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

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
class Migration_20190315101944 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang vars continents';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'Europa', 'Europa');
        $this->setLocalization('eng', 'global', 'Europa', 'Europe');
        $this->setLocalization('ger', 'global', 'Asien', 'Asien');
        $this->setLocalization('eng', 'global', 'Asien', 'Asia');
        $this->setLocalization('ger', 'global', 'Nordamerika', 'Nordamerika');
        $this->setLocalization('eng', 'global', 'Nordamerika', 'North America');
        $this->setLocalization('ger', 'global', 'Suedamerika', 'Suedamerika');
        $this->setLocalization('eng', 'global', 'Suedamerika', 'South America');
        $this->setLocalization('ger', 'global', 'Ozeanien', 'Ozeanien');
        $this->setLocalization('eng', 'global', 'Ozeanien', 'Oceania');
        $this->setLocalization('ger', 'global', 'Afrika', 'Afrika');
        $this->setLocalization('eng', 'global', 'Afrika', 'Africa');
        $this->setLocalization('ger', 'global', 'Antarktis', 'Antarktis');
        $this->setLocalization('eng', 'global', 'Antarktis', 'Antarctica');
    }

    public function down()
    {
        $this->removeLocalization('Europa');
        $this->removeLocalization('Asien');
        $this->removeLocalization('Nordamerika');
        $this->removeLocalization('Suedamerika');
        $this->removeLocalization('Ozeanien');
        $this->removeLocalization('Afrika');
        $this->removeLocalization('Antarctica');
    }
}
