<?php declare(strict_types=1);

namespace JTL\License;

use JsonSerializable;

/**
 * Class AjaxResponse
 * @package JTL\License
 */
class AjaxResponse implements JsonSerializable
{
    /**
     * @var string
     */
    public $html = '';

    /**
     * @var string
     */
    public $id = '';

    /**
     * @var string
     */
    public $status = 'OK';

    /**
     * @var string
     */
    public $action = '';

    /**
     * @var string
     */
    public $error = '';

    /**
     * @var mixed
     */
    public $additional;

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'error'  => $this->error,
            'status' => $this->status,
            'action' => $this->action,
            'id'     => $this->id,
            'html'   => \trim($this->html)
        ];
    }
}
