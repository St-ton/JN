<?php
/**
 * Create status table for or-filtered attributes
 *
 * @author fp
 * @created Wed, 19 Sep 2018 13:05:19 +0200
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
class Migration_20180919130519 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Create indices for or-filtered attributes';

    public function up()
    {
        $this->execute(
            'ALTER TABLE tartikelmerkmal ADD UNIQUE KEY kArtikelMerkmalWert_UQ (kArtikel, kMerkmalWert, kMerkmal)'
        );
        $this->execute(
            'ALTER TABLE tartikel ADD UNIQUE KEY kVaterArtikel_UQ (kArtikel, nIstVater, kVaterArtikel)'
        );
        $this->execute(
            'ALTER TABLE tkategorieartikel ADD UNIQUE KEY kKategorieArtikel_UQ (kArtikel, kKategorie)'
        );
    }

    public function down()
    {
        $this->execute('ALTER TABLE tartikelmerkmal DROP INDEX kArtikelMerkmalWert_UQ');
        $this->execute('ALTER TABLE tartikel DROP INDEX kVaterArtikel_UQ');
        $this->execute('ALTER TABLE tkategorieartikel DROP INDEX kKategorieArtikel_UQ');
    }
}
