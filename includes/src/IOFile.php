<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class IOFile
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
    public function __construct($filename, $mimetype)
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
