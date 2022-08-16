<?php declare(strict_types=1);

namespace JTL\IO;

use JsonSerializable;

/**
 * Class IOFile
 * @package JTL\IO
 */
class IOFile implements JsonSerializable
{
    /**
     * IOFile constructor.
     *
     * @param string $filename
     * @param string $mimetype
     */
    public function __construct(public string $filename, public string $mimetype)
    {
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'filename' => $this->filename,
            'mimetype' => $this->mimetype
        ];
    }
}
