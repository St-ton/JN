<?php
/**
 * adds lang var for rating
 *
 * @author ms
 * @created Tue, 12 Jun 2018 12:40:00 +0200
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
class Migration_20180612124000 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds lang var for rating';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'reviewsInCurrLang', 'Bewertungen in der aktuellen Sprache:');
        $this->setLocalization('eng', 'product rating', 'reviewsInCurrLang', 'Reviews in current language:');

        $this->setLocalization('ger', 'product rating', 'noReviewsInCurrLang', 'In der aktuellen Sprache gibt es keine Bewertungen.');
        $this->setLocalization('eng', 'product rating', 'noReviewsInCurrLang', 'There are no reviews in the current language.');
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    public function down()
    {
        $this->removeLocalization('ratingsInCurrLang');
        $this->removeLocalization('noRatingsInCurrLang');
    }
}
