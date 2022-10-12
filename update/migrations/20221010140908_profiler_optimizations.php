<?php declare(strict_types=1);
/**
 * Profiler optimizations
 *
 * @author fp
 * @created Mon, 10 Oct 2022 14:09:08 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221010140908
 */
class Migration_20221010140908 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Profiler optimizations';

    /**
     * @inheritDoc
     */
    public function up()
    {
        if ($this->db->getSingleObject("SHOW INDEX FROM tkonfigitem WHERE COLUMN_NAME = 'kKonfigGruppe'") === null) {
            $this->db->executeQuery('ALTER TABLE tkonfigitem ADD INDEX kKonfigGruppeSort (kKonfigGruppe, nSort)');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tstueckliste WHERE KEY_NAME = 'kStuecklisteArtikel'") === null) {
            $this->db->executeQuery('ALTER TABLE tstueckliste ADD INDEX kStuecklisteArtikel (kStueckliste, kArtikel)');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tsprache WHERE KEY_NAME = 'activeISO'") === null) {
            $this->db->executeQuery('ALTER TABLE tsprache ADD INDEX activeISO (`active`, cISO)');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tsprachiso WHERE KEY_NAME = 'kSprachISOcISO'") === null) {
            $this->db->executeQuery('ALTER TABLE tsprachiso ADD UNIQUE KEY kSprachISOcISO (kSprachISO, cISO)');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tkonfigitem WHERE COLUMN_NAME = 'kKonfiggruppe'") === null) {
            $this->db->executeQuery('ALTER TABLE tkonfigitem ADD INDEX kKonfigGruppe(kKonfiggruppe)');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tartikelpict WHERE KEY_NAME = 'kArtikelNr'") === null) {
            $this->db->executeQuery('ALTER TABLE tartikelpict ADD INDEX kArtikelNr(kArtikel, nNr)');
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        if ($this->db->getSingleObject("SHOW INDEX FROM tkonfigitem WHERE KEY_NAME = 'kKonfigGruppeSort'") !== null) {
            $this->db->executeQuery('ALTER TABLE tkonfigitem DROP INDEX kKonfigGruppeSort');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tstueckliste WHERE KEY_NAME = 'kStuecklisteArtikel'") !== null) {
            $this->db->executeQuery('ALTER TABLE tstueckliste DROP INDEX kStuecklisteArtikel');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tsprache WHERE KEY_NAME = 'activeISO'") !== null) {
            $this->db->executeQuery('ALTER TABLE tsprache DROP INDEX activeISO');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tsprachiso WHERE KEY_NAME = 'kSprachISOcISO'") !== null) {
            $this->db->executeQuery('ALTER TABLE tsprachiso DROP INDEX kSprachISOcISO');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tsprachiso WHERE KEY_NAME = 'kKonfigGruppe'") !== null) {
            $this->db->executeQuery('ALTER TABLE tkonfigitem DROP INDEX kKonfigGruppe');
        }
        if ($this->db->getSingleObject("SHOW INDEX FROM tartikelpict WHERE KEY_NAME = 'kArtikelNr'") !== null) {
            $this->db->executeQuery('ALTER TABLE tartikelpict DROP INDEX kArtikelNr');
        }
    }
}
