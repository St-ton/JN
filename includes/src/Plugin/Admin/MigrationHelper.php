<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

/**
 * Class MigrationHelper
 */
final class MigrationHelper
{
    /**
     * @var string
     */
    private const MIGRATION_FILE_NAME_PATTERN = '/^Migration(\d+).php$/i';

    /**
     * @var string
     */
    private $path;

    /**
     * MigrationHelper constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Gets the migration path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Get the id from a file name.
     *
     * @param string $fileName File Name
     * @return string|null
     */
    public function getIdFromFileName($fileName): ?string
    {
        $matches = [];
        if (\preg_match(self::MIGRATION_FILE_NAME_PATTERN, \basename($fileName), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Returns names like 'Migration12345678901234'.
     *
     * @param \DirectoryIterator $file
     * @param string             $pluginID
     * @return string
     */
    public function mapFileNameToClassName(\DirectoryIterator $file, string $pluginID): string
    {
        return \sprintf(
            '%s\migrations\%s',
            $pluginID,
            \str_replace('.' . $file->getExtension(), '', $file->getFilename())
        );
    }

    /**
     * Check if a migration file name is valid.
     *
     * @param string $fileName File Name
     * @return bool|int
     */
    public function isValidMigrationFileName($fileName)
    {
        $matches = [];

        return \preg_match(self::MIGRATION_FILE_NAME_PATTERN, $fileName, $matches);
    }

    /**
     * Check database integrity
     */
    public function verifyIntegrity(): void
    {
        \Shop::Container()->getDB()->query(
            "CREATE TABLE IF NOT EXISTS tpluginmigration 
            (
                kMigration bigint(14) NOT NULL, 
                nVersion int(3) NOT NULL, 
                dExecuted datetime NOT NULL,
                PRIMARY KEY (kMigration)
            ) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'",
            \DB\ReturnType::DEFAULT
        );
        \Shop::Container()->getDB()->query(
            "CREATE TABLE IF NOT EXISTS tmigrationlog 
            (
                kMigrationlog int(10) NOT NULL AUTO_INCREMENT, 
                kMigration bigint(20) NOT NULL, 
                cDir enum('up','down') NOT NULL, 
                cState varchar(6) NOT NULL, 
                cLog text NOT NULL, 
                dCreated datetime NOT NULL, 
                PRIMARY KEY (kMigrationlog)
            ) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'",
            \DB\ReturnType::DEFAULT
        );
    }

    /**
     * @param string $idxTable
     * @param string $idxName
     * @return array
     */
    public function indexColumns(string $idxTable, string $idxName): array
    {
        return \Shop::Container()->getDB()->queryPrepared(
            "SHOW INDEXES FROM `$idxTable` WHERE Key_name = :idxName",
            ['idxName' => $idxName],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string      $idxTable
     * @param array       $idxColumns
     * @param string|null $idxName
     * @param bool        $idxUnique
     * @return bool
     */
    public function createIndex(string $idxTable, array $idxColumns, $idxName = null, $idxUnique = false): bool
    {
        if (empty($idxName)) {
            $idxName = \implode('_', $idxColumns) . '_' . ($idxUnique ? 'UQ' : 'IDX');
        }

        if (\count($this->indexColumns($idxTable, $idxName)) === 0 || $this->dropIndex($idxTable, $idxName)) {
            $ddl = 'CREATE' . ($idxUnique ? ' UNIQUE' : '')
                . ' INDEX `' . $idxName . '` ON `' . $idxTable . '` '
                . '(`' . \implode('`, `', $idxColumns) . '`)';

            return !\Shop::Container()->getDB()->executeQuery($ddl, \DB\ReturnType::DEFAULT) ? false : true;
        }

        return false;
    }

    /**
     * @param string $idxTable
     * @param string $idxName
     * @return bool
     */
    public function dropIndex(string $idxTable, string $idxName): bool
    {
        if (\count($this->indexColumns($idxTable, $idxName)) > 0) {
            return !\Shop::Container()->getDB()->executeQuery(
                'DROP INDEX `' . $idxName . '` ON `' . $idxTable . '` ',
                \DB\ReturnType::DEFAULT
            ) ? false : true;
        }

        return true;
    }
}
