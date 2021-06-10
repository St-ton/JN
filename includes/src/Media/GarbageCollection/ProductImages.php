<?php declare(strict_types=1);

namespace JTL\Media\GarbageCollection;

/**
 * Class ProductImages
 * @package JTL\Media\GarbageCollection
 */
class ProductImages extends AbstractCollector
{
    protected $baseDir = \PFAD_MEDIA_IMAGE_STORAGE;

    /**
     * @inheritdoc
     */
    public function fileIsUsed(string $filename): bool
    {
        return \strpos($filename, '.') === 0 || $this->db->getSingleObject(
            'SELECT kArtikelPict 
                FROM tartikelpict
                WHERE cPfad = :path',
            ['path' => $filename]
        ) !== null;
    }
}
