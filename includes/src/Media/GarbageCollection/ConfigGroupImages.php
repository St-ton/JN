<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

/**
 * Class ConfigGroupImages
 * @package JTL\Media\GarbageCollection
 */
class ConfigGroupImages extends AbstractCollector
{
    protected $baseDir = \STORAGE_CONFIGGROUPS;

    /**
     * @inheritdoc
     */
    public function fileIsUsed(string $filename): bool
    {
        return \strpos($filename, '.') === 0 || $this->db->getSingleObject(
            'SELECT kKonfiggruppe 
                FROM tkonfiggruppe
                WHERE cBildPfad = :path',
            ['path' => $filename]
        ) !== null;
    }
}
