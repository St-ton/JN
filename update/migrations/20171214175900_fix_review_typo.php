<?php
/** fix typo in lang var */

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
class Migration_20171214175900 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("UPDATE tsprachwerte SET cWert = 'Geben Sie die erste Bewertung für diesen Artikel ab und helfen Sie Anderen bei der Kaufentscheidung' WHERE cName = 'firstReview' AND cWert = 'Geben Sie die erste Bewertung für diesen Artikel ab und helfen Sie Anderen bei der Kaufenscheidung'");
    }

    public function down()
    {
        $this->execute("UPDATE tsprachwerte SET cWert = 'Geben Sie die erste Bewertung für diesen Artikel ab und helfen Sie Anderen bei der Kaufenscheidung' WHERE cName = 'firstReview' AND cWert = 'Geben Sie die erste Bewertung für diesen Artikel ab und helfen Sie Anderen bei der Kaufentscheidung'");
    }
}
