<?php declare(strict_types=1);
/**
 * Patch tinyint section in settings.
 *
 * @author fp
 * @created Mon, 06 Mar 2023 16:02:50 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230306160250
 */
class Migration_20230306160250 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Patch tinyint section in settings.';

    /**
     * @inheritdoc
     */
    public function up()
    {
        foreach ($this->fetchAll(
            "SELECT `TABLE_NAME`, `DATA_TYPE`
                FROM information_schema.COLUMNS
                WHERE `TABLE_SCHEMA` = '" . DB_NAME . "'
                    AND `TABLE_NAME` IN ('teinstellungen', 'teinstellungenconf', 'teinstellungensektion')
                    AND `COLUMN_NAME` = 'kEinstellungenSektion'"
        ) as $colDef) {
            if (\strtoupper($colDef->DATA_TYPE) === 'INT') {
                continue;
            }

            $this->execute(
                'ALTER TABLE `' . $colDef->TABLE_NAME . '` MODIFY COLUMN
                    kEinstellungenSektion INT UNSIGNED NOT NULL DEFAULT 0'
            );
        }

        foreach ([
            'teinstellungen'     => 'cName',
            'teinstellungenconf' => 'cWertName'
        ] as $settingTable => $settingColumn) {
            $this->execute(
                'UPDATE ' . $settingTable . '
                    SET kEinstellungenSektion = 128
                    WHERE kEinstellungenSektion != 128
                        AND ' . $settingColumn . " IN (
                            'configgroup_128_cron',
                            'cron_freq',
                            'cron_type'
                        )"
            );
            $this->execute(
                'UPDATE ' . $settingTable . '
                    SET kEinstellungenSektion = 129
                    WHERE kEinstellungenSektion != 129
                      AND ' . $settingColumn . " IN (
                        'configgroup_129_consentmanager',
                        'consent_manager_active',
                        'consent_manager_show_banner'
                    )");
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // not revokable
    }
}
