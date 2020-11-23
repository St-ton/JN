<?php declare(strict_types=1);

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201119133615
 */
class Migration_20201119133615 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Recreate missing autoincrement attributes';

    /**
     * @inheritDoc
     */
    public function up()
    {
        foreach ([
            'tkontaktbetreff' => 'kKontaktBetreff',
            'tsprachiso'      => 'kSprachISO',
            'ttext'           => 'kText',
        ] as $table => $keyColumn) {
            $lastValue = $this->db->query(
                'SELECT COALESCE(MAX('. $keyColumn . '), 0) + 1 AS value FROM ' . $table,
                ReturnType::SINGLE_OBJECT
            );
            $this->execute(
                'ALTER TABLE ' . $table
                . ' CHANGE COLUMN ' . $keyColumn . ' ' . $keyColumn
                . ' INT(10) UNSIGNED NOT NULL AUTO_INCREMENT'
            );
            $this->execute(
                'ALTER TABLE ' . $table . ' AUTO_INCREMENT ' . $lastValue->value
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
