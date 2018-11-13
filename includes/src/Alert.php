<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Alert
 */
class Alert
{
    /**
     * @var string
     */
    private $variant;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $key;

    /**
     * @var bool
     */
    private $dismissable = false;

    /**
     * @var int
     */
    private $fadeOut = 0;

    //Todo: von bootstrap holen?
    public const VARIANT_PRIMARY   = 'primary';
    public const VARIANT_SECONDARY = 'secondary';
    public const VARIANT_SUCCESS   = 'success';
    public const VARIANT_DANGER    = 'danger';
    public const VARIANT_WARNING   = 'warning';
    public const VARIANT_INFO      = 'info';
    public const VARIANT_LIGHT     = 'light';
    public const VARIANT_DARK      = 'dark';

    public const TYPE_ERROR  = 'Error';
    public const TYPE_NOTICE = 'Notice';
    public const TYPE_CUSTOM = 'Custom';

    /**
     * @param string $variant
     * @param string $message
     * @param string $type
     * @param string $key
     * constructor
     */
    public function __construct(string $variant, string $message, string $type, string $key)
    {
        $this->setVariant($variant)
             ->setMessage($message)
             ->setType($type)
             ->setKey($key);
    }


    public function getVariant(): string
    {
        return $this->variant;
    }

    public function setVariant($variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage($message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey($key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getDismissable(): bool
    {
        return $this->dismissable;
    }

    public function setDismissable($dismissable): self
    {
        $this->dismissable = $dismissable;

        return $this;
    }

    public function getFadeOut(): int
    {
        return $this->fadeOut;
    }

    public function setFadeOut($fadeOut): self
    {
        $this->fadeOut = $fadeOut;

        return $this;
    }
}
