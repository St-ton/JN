<?php
/**
 * Create new Varkombi index
 *
 * @author fp
 * @created Tue, 19 Feb 2019 09:01:54 +0100
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
class Migration_20190219090154 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Create new Varkombi index';

    public function up()
    {
        $this->execute(
            'CREATE UNIQUE INDEX idx_eigenschaftwert_uq
                ON teigenschaftkombiwert (kEigenschaft, kEigenschaftWert, kEigenschaftKombi)'
        );
    }

    public function down()
    {
        $this->execute(
            'DROP INDEX idx_eigenschaftwert_uq ON teigenschaftkombiwert'
        );
    }
}
