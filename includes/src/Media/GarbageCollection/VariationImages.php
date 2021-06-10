<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

/**
 * Class VariationImages
 * @package JTL\Media\GarbageCollection
 */
class VariationImages extends AbstractCollector
{
    protected $baseDir = \STORAGE_VARIATIONS;

    /**
     * @inheritdoc
     */
    public function fileIsUsed(string $filename): bool
    {
        return \strpos($filename, '.') === 0 || $this->db->getSingleObject(
            'SELECT kEigenschaftWertPict 
                FROM teigenschaftwertpict
                WHERE cPfad = :path',
            ['path' => $filename]
        ) !== null;
    }
}
