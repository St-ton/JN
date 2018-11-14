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
    private $fadeOut = self::FADE_NEVER;

    /**
     * @var bool
     */
    private $showInAlertListTemplate = true;

    /**
     * @var bool
     */
    private $removeFromSession = true;

    /**
     * @var string
     */
    private $linkHref;

    /**
     * @var string
     */
    private $linkText;

    /**
     * @var string
     */
    private $icon;

    public const TYPE_PRIMARY   = 'primary';
    public const TYPE_SECONDARY = 'secondary';
    public const TYPE_SUCCESS   = 'success';
    public const TYPE_DANGER    = 'danger';
    public const TYPE_WARNING   = 'warning';
    public const TYPE_INFO      = 'info';
    public const TYPE_LIGHT     = 'light';
    public const TYPE_DARK      = 'dark';

    public const FADE_FAST   = 3000;
    public const FADE_SLOW   = 9000;
    public const FADE_MEDIUM = 5000;
    public const FADE_NEVER  = 0;


    /**
     * @param string $message
     * @param string $type
     * @param string $key
     * constructor
     */
    public function __construct(string $type, string $message, string $key)
    {
        $this->initAlert($type, $message, $key);
    }

    /**
     * @param string $message
     * @param string $type
     * @param string $key
     */
    public function initAlert(string $type, string $message, string $key): void
    {
        $this->setType($type)
             ->setMessage($message)
             ->setKey($key);

        switch ($type) {
            case self::TYPE_DANGER:
                $this->setDismissable(true)
                     ->setIcon('warning');
                break;
            case self::TYPE_WARNING:
                $this->setDismissable(true)
                     ->setIcon('warning');
                break;
            case self::TYPE_INFO:
                $this->setFadeOut(self::FADE_SLOW)
                     ->setIcon('info');
                break;
            case self::TYPE_SUCCESS:
                $this->setFadeOut(self::FADE_MEDIUM)
                     ->setIcon('check');
                break;
            default;
                break;
        }
    }

    /**
     * @return void
     */
    public function display(): void
    {
        Shop::Smarty()->assign('alert', $this)
            ->display('snippets/alert.tpl');

        if ($this->removeFromSession) {
            Shop::Container()->getAlertService()->unsetAlert($this->getKey());
        }
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDismissable(): bool
    {
        return $this->dismissable;
    }

    /**
     * @param bool $dismissable
     * @return $this
     */
    public function setDismissable(bool $dismissable): self
    {
        $this->dismissable = $dismissable;

        return $this;
    }

    /**
     * @return int
     */
    public function getFadeOut(): int
    {
        return $this->fadeOut;
    }

    /**
     * @param int $fadeOut
     * @return $this
     */
    public function setFadeOut(int $fadeOut): self
    {
        $this->fadeOut = $fadeOut;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRemoveFromSession(): bool
    {
        return $this->removeFromSession;
    }

    /**
     * @param bool $removeFromSession
     * @return $this
     */
    public function setRemoveFromSession(bool $removeFromSession): self
    {
        $this->removeFromSession = $removeFromSession;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowInAlertListTemplate(): bool
    {
        return $this->showInAlertListTemplate;
    }

    /**
     * @param bool $showInAlertListTemplate
     * @return $this
     */
    public function setShowInAlertListTemplate(bool $showInAlertListTemplate): self
    {
        $this->showInAlertListTemplate = $showInAlertListTemplate;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLinkHref(): ?string
    {
        return $this->linkHref;
    }

    /**
     * @param string $linkHref
     * @return $this
     */
    public function setLinkHref(string $linkHref): self
    {
        $this->linkHref = $linkHref;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLinkText(): ?string
    {
        return $this->linkText;
    }

    /**
     * @param string $linkText
     * @return $this
     */
    public function setLinkText(string $linkText): self
    {
        $this->linkText = $linkText;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
