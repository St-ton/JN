<?php declare(strict_types=1);

namespace JTL\IO;

use JsonSerializable;

/**
 * Class IOError
 * @package JTL\IO
 */
class IOError implements JsonSerializable
{
    /**
     * @var array
     */
    public array $errors = [];

    /**
     * IOError constructor.
     *
     * @param string     $message
     * @param int        $code
     * @param array|null $errors
     */
    public function __construct(public string $message, public int $code = 500, array $errors = null)
    {
        $this->errors = $errors ?? [];
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'error' => [
                'message' => $this->message,
                'code'    => $this->code,
                'errors'  => $this->errors
            ]
        ];
    }
}
