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
     * @var bool
     */
    private $dismissable = false;

    private const VARIANT_PRIMARY   = 'primary';
    private const VARIANT_SECONDARY = 'secondary';
    private const VARIANT_SUCCESS   = 'success';
    private const VARIANT_DANGER    = 'danger';
    private const VARIANT_WARNING   = 'warning';
    private const VARIANT_INFO      = 'info';
    private const VARIANT_LIGHT     = 'light';
    private const VARIANT_DARK      = 'dark';

    /**
     * constructor
     */
    public function __construct(string $variant, string $message)
    {
        $this->setVariant(constant("self::$variant"))
             ->setMessage($message);
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

    //TODO: use dismissable
    public function getDismissable(): string
    {
        return $this->dismissable;
    }

    public function setDismissable($dismissable): self
    {
        $this->dismissable = $dismissable;
        return $this;
    }
}
