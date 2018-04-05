<?php
/**
 * Add language variables for product rating
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
    protected $description = 'Add language variables for product rating';

    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'feedback activated', 'Bewertung ist freigeschaltet!');
        $this->setLocalization('eng', 'product rating', 'feedback activated', 'Feedback is activated!');

        $this->setLocalization('ger', 'product rating', 'feedback deactivated', 'Bewertung ist noch nicht freigeschaltet!');
        $this->setLocalization('eng', 'product rating', 'feedback deactivated', 'Feedback is not activated yet!');

        $this->setLocalization('ger', 'product rating', 'reply', 'Antwort von');
        $this->setLocalization('eng', 'product rating', 'reply', 'Reply from');

        $this->setLocalization('ger', 'product rating', 'edit', 'Bewertung ändern');
        $this->setLocalization('eng', 'product rating', 'edit', 'Edit feedback');

        $this->setLocalization('ger', 'product rating', 'balance bonus', 'Guthabenbonus');
        $this->setLocalization('eng', 'product rating', 'balance bonus', 'balance bonus');

        $this->setLocalization('ger', 'product rating', 'no feedback', 'Noch keine Bewertung abgegeben');
        $this->setLocalization('eng', 'product rating', 'no feedback', 'No feedback was given yet');
    }
    public function down()
    {
        $this->removeLocalization('feedback activated');
        $this->removeLocalization('feedback deactivated');
        $this->removeLocalization('reply');
        $this->removeLocalization('edit');
        $this->removeLocalization('balance bonus');
        $this->removeLocalization('no feedback');
    }
}
