<?php
/**
 * add_lang_rating_range_error
 *
 * @author mh
 * @created Thu, 07 Feb 2019 15:15:43 +0100
 */

use JTL\Update\Migration;
use JTL\Update\IMigration;

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
class Migration_20190207151543 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'add lang rating range error';

    public function up()
    {
        $this->setLocalization(
            'ger',
            'errorMessages',
            'ratingRange',
            'Die Bewertung muss eine Zahl zwischen 1 und 5 sein.'
        );
        $this->setLocalization(
            'eng',
            'errorMessages',
            'ratingRange',
            'The rating needs to be a value between 1 and 5.'
        );
    }

    public function down()
    {
        $this->removeLocalization('ratingRange');
    }
}
