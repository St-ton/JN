<?php
/**
 * Hook interface for captcha
 *
 * @author Falk Prüfer
 * @created Wed, 23 May 2018 09:27:32 +0200
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
class Migration_20180523092732 extends Migration implements IMigration
{
    protected $author = 'Falk Prüfer';
    protected $description = 'Hook interface for captcha';

    public function up()
    {
    }

    public function down()
    {
    }
}