<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

/**
 * Class ManufacturerImages
 * @package JTL\Media\GarbageCollection
 */
class ManufacturerImages extends AbstractCollector
{
    protected $baseDir = \STORAGE_MANUFACTURERS;

    /**
     * @inheritdoc
     */
    public function fileIsUsed(string $filename): bool
    {
        return \strpos($filename, '.') === 0 || $this->db->getSingleObject(
            'SELECT kHersteller 
                FROM thersteller
                WHERE cBildpfad = :path',
            ['path' => $filename]
        ) !== null;
    }
}
