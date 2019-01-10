<?php
/**
 * change_check_city_description
 *
 * @author mh
 * @created Wed, 12 Sep 2018 11:26:14 +0200
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
class Migration_20180912112614 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Change check city description';

    public function up()
    {
        $this->execute('UPDATE teinstellungenconf SET cBeschreibung="Fehlermeldung ausgeben, wenn eingegebene Stadt eine Zahl enthält." WHERE cWertName="kundenregistrierung_pruefen_ort";');
    }

    public function down()
    {
        $this->execute('UPDATE teinstellungenconf SET cBeschreibung="Wenn die eingegebene Stadt eine Zahle enthät abbrechen" WHERE cWertName="kundenregistrierung_pruefen_ort";');
    }
}