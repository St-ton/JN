<?php
/**
 * Move language variables "invalidHash" und "invalidCustomer" to account data
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
class Migration_20171215121900 extends Migration implements IMigration
{
    protected $author      = 'Franz Gotthardt';
    protected $description = 'Add language variables for feedback section';

    public function up()
    {
        // Sprachsektion feedback hinzufügen
        Shop::DB()->query('INSERT INTO tsprachsektion (cName) VALUES ("feedback")', 1);
        // Sprachwert 'Guthabenbons' hinzufügen
        Shop::DB()->query('INSERT INTO tsprachwerte (kSprachISO, kSprachsektion, cName, cWert, cStandard) VALUES (1, 18, "balance bonus", "Guthabenbonus", "Guthabenbonus")', 1);
        Shop::DB()->query('INSERT INTO tsprachwerte (kSprachISO, kSprachsektion, cName, cWert, cStandard) VALUES (2, 18, "balance bonus", "balance bonus", "balance bonus")', 1);
        // Sprachwert 'Bewertung ist freigeschaltet' hinzufügen
        Shop::DB()->query('INSERT INTO tsprachwerte (kSprachISO, kSprachsektion, cName, cWert, cStandard) VALUES (1, 18, "feedback activated", "Bewertung ist freigeschaltet!", "Bewertung ist freigeschaltet!")', 1);
        Shop::DB()->query('INSERT INTO tsprachwerte (kSprachISO, kSprachsektion, cName, cWert, cStandard) VALUES (2, 18, "feedback activated", "Feedback is activated!", "Feedback is activated!")', 1);
        // Sprachwert 'Bewertung ist noch nicht freigeschaltet' hinzufügen
        Shop::DB()->query('INSERT INTO tsprachwerte (kSprachISO, kSprachsektion, cName, cWert, cStandard) VALUES (1, 18, "feedback deactivated", "Diese Bewertung ist noch nicht freigeschaltet!", "Bewertung ist noch nicht freigeschaltet!")', 1);
        Shop::DB()->query('INSERT INTO tsprachwerte (kSprachISO, kSprachsektion, cName, cWert, cStandard) VALUES (2, 18, "feedback deactivated", "This feedback was not activated yet!", "This feedback was not activated yet!")', 1);
        // Antwort von
        Shop::DB()->query('INSERT INTO tsprachwerte (kSprachISO, kSprachsektion, cName, cWert, cStandard) VALUES (1, 18, "reply", "Antwort von", "Antwort von")', 1);
        Shop::DB()->query('INSERT INTO tsprachwerte (kSprachISO, kSprachsektion, cName, cWert, cStandard) VALUES (2, 18, "reply", "Reply from", "Reply from")', 1);

    }

    public function down()
    {
        // Sprachsektion feedback entfernen
        Shop::DB()->query('DELETE FROM tsprachsektion WHERE cName = "feedback"', 1);
        // Sprachwerte entfernen
        Shop::DB()->query('DELETE FROM tsprachwerte WHERE cName = "balance bonus"', 1);
        Shop::DB()->query('DELETE FROM tsprachwerte WHERE cName = "feedback activated"', 1);
        Shop::DB()->query('DELETE FROM tsprachwerte WHERE cName = "feedback deactivated"', 1);
        Shop::DB()->query('DELETE FROM tsprachwerte WHERE cName = "reply"', 1);
    }
}