<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin;

use JTL\Mapper\PluginValidation;
use JTL\Plugin\InstallCode;

/**
 * Class ListingItem
 * @package JTL\Plugin\Admin
 */
class ListingItem
{
    /**
     * @var bool
     */
    private $isShop4Compatible = false;

    /**
     * @var bool
     */
    private $isShop5Compatible = false;

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     */
    private $dir = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $version = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $author = '';

    /**
     * @var string
     */
    private $icon = '';

    /**
     * @var string
     */
    private $id = '';

    /**
     * @var int
     */
    private $errorCode = 0;

    /**
     * @var string
     */
    private $errorMessage = '';

    /**
     * @var bool
     */
    private $hasError = false;

    /**
     * @var bool
     */
    private $available = false;

    /**
     * @var bool
     */
    private $installed = false;

    /**
     * @var int
     */
    private $state = 0;

    /**
     * @param array $xml
     * @return ListingItem
     */
    public function parseXML(array $xml): self
    {
        $node       = null;
        $this->name = $xml['cVerzeichnis'];
        $this->dir  = $xml['cVerzeichnis'];
        if (isset($xml['jtlshopplugin']) && \is_array($xml['jtlshopplugin'])) {
            $node                    = $xml['jtlshopplugin'][0];
            $this->isShop5Compatible = true;
        } elseif (isset($xml['jtlshop3plugin']) && \is_array($xml['jtlshop3plugin'])) {
            $node = $xml['jtlshop3plugin'][0];
        }
        if ($node !== null) {
            if ($this->isShop5Compatible) {
                if (!isset($node['Version'])) {
                    return $this;
                }
            } elseif (!isset($node['Install'][0]['Version'])) {
                return $this;
            }
            if (!isset($node['Name'])) {
                return $this;
            }
            $this->name        = $node['Name'] ?? '';
            $this->description = $node['Description'] ?? '';
            $this->author      = $node['Author'] ?? '';
            $this->id          = $node['PluginID'] ?? '';
            $this->icon        = $node['Icon'] ?? null;
            if (isset($node['Install'][0]['Version']) && \is_array($node['Install'][0]['Version'])) {
                $lastVersion   = \count($node['Install'][0]['Version']) / 2 - 1;
                $version       = $lastVersion >= 0
                && isset($node['Install'][0]['Version'][$lastVersion . ' attr']['nr'])
                    ? (int)$node['Install'][0]['Version'][$lastVersion . ' attr']['nr']
                    : 0;
                $this->version = \number_format($version / 100, 2);
            } else {
                $this->version = $node['Version'];
            }
        }
        if ($xml['cFehlercode'] !== InstallCode::OK) {
            $mapper             = new PluginValidation();
            $this->hasError     = true;
            $this->errorCode    = $xml['cFehlercode'];
            $this->errorMessage = $mapper->map($xml['cFehlercode'], $this->getID());
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isShop4Compatible(): bool
    {
        return $this->isShop4Compatible;
    }

    /**
     * @param bool $isShop4Compatible
     */
    public function setIsShop4Compatible(bool $isShop4Compatible): void
    {
        $this->isShop4Compatible = $isShop4Compatible;
    }

    /**
     * @return bool
     */
    public function isShop5Compatible(): bool
    {
        return $this->isShop5Compatible;
    }

    /**
     * @param bool $isShop5Compatible
     */
    public function setIsShop5Compatible(bool $isShop5Compatible): void
    {
        $this->isShop5Compatible = $isShop5Compatible;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setID(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     */
    public function setErrorCode(int $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return bool
     */
    public function isHasError(): bool
    {
        return $this->hasError;
    }

    /**
     * @param bool $hasError
     */
    public function setHasError(bool $hasError): void
    {
        $this->hasError = $hasError;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     */
    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * @param bool $installed
     */
    public function setInstalled(bool $installed): void
    {
        $this->installed = $installed;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }
}
