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
class Migration_20180222084900 extends Migration implements IMigration
{
    protected $author      = 'Franz Gotthardt';
    protected $description = 'add language variables for feedback';

    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'balance bonus', 'Guthabenbonus');
        $this->setLocalization('eng', 'product rating', 'balance bonus', 'balance bonus');

        $this->setLocalization('ger', 'product rating', 'feedback activated', 'Bewertung ist freigeschaltet!');
        $this->setLocalization('eng', 'product rating', 'feedback activated', 'Feedback is activated!');

        $this->setLocalization('ger', 'product rating', 'feedback deactivated', 'Bewertung ist noch nicht freigeschaltet!');
        $this->setLocalization('eng', 'product rating', 'feedback deactivated', 'Feedback is not activated yet!');

        $this->setLocalization('ger', 'product rating', 'reply', 'Antwort von');
        $this->setLocalization('eng', 'product rating', 'reply', 'Reply from');

        $this->setLocalization('ger', 'product rating', 'edit', 'Bewertung Ändern');
        $this->setLocalization('eng', 'product rating', 'edit', 'Edit feedback');

        $this->setLocalization('ger', 'product rating', 'delete', 'Bewertung löschen');
        $this->setLocalization('eng', 'product rating', 'delete', 'Delete feedback');

        $this->setLocalization('ger', 'product rating', 'delete all', 'Alle Bewertungen löschen');
        $this->setLocalization('eng', 'product rating', 'delete all', 'Delete all feedback');
    }
    public function down()
    {
        $this->removeLocalization('balance bonus');
        $this->removeLocalization('feedback activated');
        $this->removeLocalization('feedback deactivated');
        $this->removeLocalization('reply');
        $this->removeLocalization('edit');
        $this->removeLocalization('delete');
        $this->removeLocalization('delete all');
    }
}