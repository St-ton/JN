<?php

namespace JTL\Plugin;

use JTL\Shop;
use stdClass;

/**
 * Class PremiumPlugin
 * @package JTL\Plugin
 */
class PremiumPlugin
{
    public const CERTIFICATION_LOGO = 'https://images.jtl-software.de/servicepartner/cert/jtl_certified_128.png';

    /**
     * @var array
     */
    private $advantages = [];

    /**
     * @var array
     */
    private $howTos = [];

    /**
     * @var stdClass
     */
    private $longDescription;

    /**
     * @var stdClass
     */
    private $shortDescription;

    /**
     * @var string
     */
    private $author;

    /**
     * @var array
     */
    private $badges = [];

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var array
     */
    private $buttons = [];

    /**
     * @var bool
     */
    private $isInstalled;

    /**
     * @var bool
     */
    private $isActivated;

    /**
     * @var bool
     */
    private $exists;

    /**
     * @var string
     */
    private $pluginID;

    /**
     * @var int
     */
    private $kPlugin;

    /**
     * @var stdClass
     */
    private $servicePartner;

    /**
     * @var array
     */
    private $screenShots = [];

    /**
     * @var string
     */
    private $headerColor = '#313131';

    /**
     * @var string
     */
    private $downloadLink;

    /**
     * PremiumPlugin constructor.
     *
     * @param string $pluginID
     */
    public function __construct(string $pluginID)
    {
        $plugin            = Helper::getPluginById($pluginID);
        $this->pluginID    = $pluginID;
        $this->exists      = \file_exists(\PFAD_ROOT . \PFAD_PLUGIN . $pluginID . '/' . \PLUGIN_INFO_FILE);
        $this->isInstalled = $plugin !== null && $plugin->getID() > 0;
        $this->isActivated = $this->isInstalled && $plugin->getState() === State::ACTIVATED;
        $this->kPlugin     = $this->isInstalled ? $plugin->getID() : 0;
    }

    /**
     * @param null|string $id
     * @return $this
     */
    public function setPluginID(?string $id): self
    {
        $this->pluginID = $id;

        return $this;
    }

    /**
     * @param string $link
     * @return $this
     */
    public function setDownloadLink(string $link): self
    {
        $this->downloadLink = $link;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDownloadLink(): ?string
    {
        return $this->downloadLink;
    }

    /**
     * @param string $color
     * @return $this
     */
    public function setHeaderColor(string $color): self
    {
        $this->headerColor = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeaderColor(): string
    {
        return $this->headerColor;
    }

    /**
     * @return int
     */
    public function getKPlugin(): int
    {
        return $this->kPlugin;
    }

    /**
     * @return string|null
     */
    public function getPluginID(): ?string
    {
        return $this->pluginID;
    }

    /**
     * @return bool
     */
    public function getExists(): bool
    {
        return $this->exists;
    }

    /**
     * @return bool
     */
    public function getIsActivated(): bool
    {
        return $this->isActivated;
    }

    /**
     * @return bool
     */
    public function getIsInstalled(): bool
    {
        return $this->isInstalled;
    }

    /**
     * @param stdClass $sp
     * @return $this
     */
    public function setServicePartner(stdClass $sp): self
    {
        $this->servicePartner = $sp;

        return $this;
    }

    /**
     * @return null|stdClass
     */
    public function getservicePartner(): ?stdClass
    {
        return $this->servicePartner;
    }

    /**
     * @param array $screenShots
     * @return $this
     */
    public function setScreenshots(array $screenShots): self
    {
        $this->screenShots = $screenShots;

        return $this;
    }

    /**
     * @param stdClass $screenShot
     * @return $this
     */
    public function addScreenShot(stdClass $screenShot): self
    {
        $this->screenShots[] = $screenShot;

        return $this;
    }

    /**
     * @return array
     */
    public function getScreenShots(): array
    {
        return $this->screenShots;
    }

    /**
     * @return null|string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return $this
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $title
     * @param string $description
     * @return $this
     */
    public function setLongDescription(string $title, string $description): self
    {
        $this->longDescription        = new stdClass();
        $this->longDescription->title = $title;
        $this->longDescription->html  = $description;

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getLongDescription(): stdClass
    {
        return $this->longDescription;
    }

    /**
     * @param string $title
     * @param string $description
     * @return $this
     */
    public function setShortDescription(string $title, string $description): self
    {
        $this->shortDescription        = new stdClass();
        $this->shortDescription->title = $title;
        $this->shortDescription->html  = $description;

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getShortDescription(): stdClass
    {
        return $this->shortDescription;
    }

    /**
     * @param string $advantage
     * @return $this
     */
    public function addAdvantage(string $advantage): self
    {
        $this->advantages[] = $advantage;

        return $this;
    }

    /**
     * @param array $advantages
     * @return $this
     */
    public function setAdvantages(array $advantages): self
    {
        $this->advantages = $advantages;

        return $this;
    }

    /**
     * @return array
     */
    public function getAdvantages(): array
    {
        return $this->advantages;
    }

    /**
     * @param string $howTo
     * @return $this
     */
    public function addHowTo(string $howTo): self
    {
        $this->howTos[] = $howTo;

        return $this;
    }

    /**
     * @param array $howTos
     * @return $this
     */
    public function setHowTos(array $howTos): self
    {
        $this->howTos = $howTos;

        return $this;
    }

    /**
     * @return array
     */
    public function getHowTos(): array
    {
        return $this->howTos;
    }

    /**
     * @param string $url
     * @param bool   $relative
     * @return $this
     */
    public function addBadge(string $url, bool $relative = true): self
    {
        $this->badges[] = $relative
            ? (Shop::getURL() . '/' . \PFAD_ADMIN . \PFAD_GFX . 'PremiumPlugins/' . $url)
            : $url;

        return $this;
    }

    /**
     * @param array $badges
     * @return $this
     */
    public function setBadges(array $badges): self
    {
        $this->badges = $badges;

        return $this;
    }

    /**
     * @return array
     */
    public function getBadges(): array
    {
        return $this->badges;
    }

    /**
     * @return string
     */
    public function getCertifcationLogo(): string
    {
        return self::CERTIFICATION_LOGO;
    }

    /**
     * @return array
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @param string      $caption
     * @param string      $link
     * @param string      $class
     * @param null|string $fa
     * @param bool        $external
     * @return $this
     */
    public function addButton(
        string $caption,
        string $link,
        string $class = 'btn btn-default',
        string $fa = null,
        bool $external = false
    ): self {
        $btn           = new stdClass();
        $btn->link     = $link;
        $btn->caption  = $caption;
        $btn->class    = $class;
        $btn->fa       = $fa;
        $btn->external = $external;
        if ($external === true) {
            $btn->fa .= ' fa-external-link';
        }
        $this->buttons[] = $btn;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCertifcates(): bool
    {
        return isset($this->servicePartner->oZertifizierungen_arr)
            && \count($this->servicePartner->oZertifizierungen_arr) > 0;
    }
}
