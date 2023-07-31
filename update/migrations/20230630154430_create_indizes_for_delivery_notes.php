<?php declare(strict_types=1);
/**
 * Create indizes for delivery notes
 *
 * @author fp
 * @created Fri, 30 Jun 2023 15:44:30 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230630154430
 */
class Migration_20230630154430 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Create indizes for delivery notes';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $singleColIndizes = [
            'tversand'             => 'kLieferschein',
            'tlieferschein'        => 'kInetBestellung',
            'tlieferscheinposinfo' => 'kLieferscheinPos',
        ];
        foreach ($singleColIndizes as $table => $column) {
            $idxExists = $this->db->getSingleObject(
                'SHOW INDEX FROM `' . $table . '` WHERE Column_name = :colName',
                ['colName' => $column]
            );
            if ($idxExists !== null) {
                $idxAll = $this->db->getObjects(
                    'SHOW INDEX FROM `' . $table . '` WHERE Key_name = :keyName',
                    ['keyName' => $idxExists->Key_name]
                );
                if (count($idxAll) === 1) {
                    $this->execute('ALTER TABLE `' . $table . '` DROP INDEX `' . $idxExists->Key_name . '`');
                }
            }
        }
        $idxExists = $this->db->getSingleObject(
            "SHOW INDEX FROM tlieferscheinpos WHERE Column_name IN ('kLieferschein', 'kBestellPos')"
        );
        if ($idxExists !== null) {
            $idxAll = $this->db->getObjects(
                'SHOW INDEX FROM tlieferscheinpos WHERE Key_name = :keyName',
                ['keyName' => $idxExists->Key_name]
            );
            if (count($idxAll) === 2) {
                $this->execute('ALTER TABLE tlieferscheinpos DROP INDEX `' . $idxExists->Key_name . '`');
            }
        }

        $this->execute('ALTER TABLE tversand ADD INDEX idx_kLieferschein (kLieferschein)');
        $this->execute('ALTER TABLE tlieferschein ADD INDEX idx_inetBestellung (kInetBestellung)');
        $this->execute('ALTER TABLE tlieferscheinpos ADD INDEX idx_kLieferschein (kLieferschein, kBestellPos)');
        $this->execute('ALTER TABLE tlieferscheinposinfo ADD INDEX idx_lieferscheinpos (kLieferscheinPos)');
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute('ALTER TABLE tversand DROP INDEX idx_kLieferschein');
        $this->execute('ALTER TABLE tlieferschein DROP INDEX idx_inetBestellung');
        $this->execute('ALTER TABLE tlieferscheinpos DROP INDEX idx_kLieferschein');
        $this->execute('ALTER TABLE tlieferscheinposinfo DROP INDEX idx_lieferscheinpos');
    }
}
