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
     * @var string
     */
    public $filename = '';

    /**
     * @var string
     */
    public $mimetype = '';

    /**
     * IOFile constructor.
     *
     * @param string $filename
     * @param string $mimetype
     */
    public function __construct(string $filename, string $mimetype)
    {
        $this->filename = $filename;
        $this->mimetype = $mimetype;
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
