<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

/**
 * Class CategoryImages
 * @package JTL\Media\GarbageCollection
 */
class CategoryImages extends AbstractCollector
{
    protected $baseDir = \STORAGE_CATEGORIES;

    /**
     * @inheritdoc
     */
    public function fileIsUsed(string $filename): bool
    {
        return \strpos($filename, '.') === 0 || $this->db->getSingleObject(
            'SELECT kKategoriePict 
                FROM tkategoriepict
                WHERE cPfad = :path',
            ['path' => $filename]
        ) !== null;
    }
}
