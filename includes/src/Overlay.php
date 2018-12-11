<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Overlay
 */
class Overlay
{
    /**
     * @var Overlay
     */
    private static $instance;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $pathSizes;

    /**
     * @var int
     */
    private $position;

    /**
     * @var int
     */
    private $active;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var int
     */
    private $margin;

    /**
     * @var int
     */
    private $transparence;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $language;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $imageName;

    /**
     * @var string
     */
    private $templateName;

    public const BESTSELLER       = 1;
    public const SPECIALOFFERS    = 2;
    public const NEWPRODUCTS      = 3;
    public const TOPOFFERS        = 4;
    public const UPCOMINGPRODUCTS = 5;
    public const TOPREVIEWS       = 6;
    public const OUTOFSTOCK       = 7;
    public const ONSTOCK          = 8;
    public const PREORDER         = 9;

    public function __construct(int $type, int $language)
    {
        $this->setType($type)
             ->setLanguage($language)
             ->setTemplateName()
             ->setPath(PFAD_TEMPLATES . $this->getTemplateName() . PFAD_OVERLAY_TEMPLATE)
             ->setPathSizes();
    }

    /**
     * @param int $type
     * @param int $language
     * @return Overlay
     */
    public static function getInstance(int $type, int $language): self
    {
        return self::$instance ?? (new self($type, $language))->loadFromDB();
    }

    /**
     * @return Overlay
     */
    public function loadFromDB(): self
    {
        $overlay = Shop::Container()->getDB()->queryPrepared("
            SELECT ssos.*, sso.cSuchspecial
              FROM tsuchspecialoverlaysprache ssos
              LEFT JOIN tsuchspecialoverlay sso
                ON ssos.kSuchspecialOverlay = sso.kSuchspecialOverlay
              WHERE ssos.kSprache = :languageID
                AND ssos.kSuchspecialOverlay = :overlayID
                AND ssos.cTemplate IN (:templateName, 'default')
              ORDER BY FIELD(ssos.cTemplate, :templateName, 'default')
              LIMIT 1",
            [
                'languageID'   => $this->getLanguage(),
                'overlayID'    => $this->getType(),
                'templateName' => $this->getTemplateName()
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (!empty($overlay)) {
            $this->setActive($overlay->nAktiv)
                 ->setMargin($overlay->nMargin)
                 ->setPosition($overlay->nPosition)
                 ->setPriority($overlay->nPrio)
                 ->setTransparence($overlay->nTransparenz)
                 ->setSize($overlay->nGroesse)
                 ->setImageName($overlay->cBildPfad)
                 ->setName($overlay->cSuchspecial);

            if ($overlay->cTemplate === 'default') {
                $this->setPath(PFAD_SUCHSPECIALOVERLAY)->setPathSizes();
            }
        }

        return $this;
    }

    /**
     * save overlay to db
     */
    public function save(): void
    {
        $overlayData =(object)[
            'nAktiv'       => $this->getActive(),
            'nPrio'        => $this->getPriority(),
            'nTransparenz' => $this->getTransparance(),
            'nGroesse'     => $this->getSize(),
            'nPosition'    => 1,
            'cTemplate'    => $this->getTemplateName(),
            'cBildPfad'    => $this->getImageName(),
            'nMargin'      => 5
        ];

        $check = Shop::Container()->getDB()->queryPrepared('
            SELECT * FROM tsuchspecialoverlaysprache
              WHERE kSprache = :languageID
                AND kSuchspecialOverlay = :overlayID
                AND cTemplate = :templateName',
            [
                'languageID'   => $this->getLanguage(),
                'overlayID'    => $this->getType(),
                'templateName' => $this->getTemplateName()
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($check) {
            Shop::Container()->getDB()->update(
                'tsuchspecialoverlaysprache',
                ['kSuchspecialOverlay', 'kSprache', 'cTemplate'],
                [$this->getType(), $this->getLanguage(), $this->getTemplateName()],
                $overlayData
            );
        } else {
            $overlayData->kSuchspecialOverlay = $this->getType();
            $overlayData->kSprache            = $this->getLanguage();
            Shop::Container()->getDB()->insert('tsuchspecialoverlaysprache', $overlayData);
        }
    }

    /**
     * @param string $imageName
     * @return Overlay
     */
    public function setImageName(string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageName(): string
    {
        return $this->imageName;
    }

    /**
     * @param string $name
     * @return Overlay
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Overlay
     */
    public function setTemplateName(): self
    {
        $template           = Template::getInstance();
        $this->templateName = $template->getName();

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * @return array
     */
    public function getPathSizes(): array
    {
        return $this->pathSizes;
    }

    /**
     * @param string $size
     * @return string
     */
    public function getPathSize(string $size): string
    {
        return $this->pathSizes[$size];
    }

    /**
     * @return Overlay
     */
    public function setPathSizes(): self
    {
        $this->pathSizes = [
            'klein'  => $this->getPath() . 'klein/',
            'normal' => $this->getPath() . 'normal/',
            'gross'  => $this->getPath() . 'gross/',
            'retina' => $this->getPath() . 'retina/',
        ];

        return $this;
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
     * @return Overlay
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param int $type
     * @return Overlay
     */
    private function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param int $language
     * @return Overlay
     */
    private function setLanguage(int $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param int $position
     * @return Overlay
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @param int $active
     * @return Overlay
     */
    public function setActive(int $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @param int $priority
     * @return Overlay
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @param int $margin
     * @return Overlay
     */
    public function setMargin(int $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    /**
     * @param int $transparance
     * @return Overlay
     */
    public function setTransparence(int $transparance): self
    {
        $this->transparence = $transparance;

        return $this;
    }

    /**
     * @param int $size
     * @return Overlay
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getTransparance(): int
    {
        return $this->transparence;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }
}
