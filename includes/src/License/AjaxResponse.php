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
    public string $html = '';

    /**
     * @var string
     */
    public string $notification = '';

    /**
     * @var string
     */
    public string $id = '';

    /**
     * @var string
     */
    public string $status = 'OK';

    /**
     * @var string|null
     */
    public ?string $redirect = null;

    /**
     * @var string
     */
    public string $action = '';

    /**
     * @var string
     */
    public string $error = '';

    /**
     * @var mixed
     */
    public mixed $additional = null;

    /**
     * @var array
     */
    public array $replaceWith = [];

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'error'        => $this->error,
            'status'       => $this->status,
            'action'       => $this->action,
            'id'           => $this->id,
            'notification' => \trim($this->notification),
            'html'         => \trim($this->html),
            'replaceWith'  => $this->replaceWith,
            'redirect'     => $this->redirect
        ];
    }
}
