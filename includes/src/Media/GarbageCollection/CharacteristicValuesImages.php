<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

/**
 * Class CharacteristicsImages
 * @package JTL\Media\GarbageCollection
 */
class CharacteristicValuesImages extends AbstractCollector
{
    protected $baseDir = \STORAGE_CHARACTERISTIC_VALUES;

    /**
     * @inheritdoc
     */
    public function fileIsUsed(string $filename): bool
    {
        return \strpos($filename, '.') === 0 || $this->db->getSingleObject(
            'SELECT kMerkmalWert 
                FROM tmerkmalwertbild
                WHERE cBildpfad = :path',
            ['path' => $filename]
        ) !== null;
    }
}
