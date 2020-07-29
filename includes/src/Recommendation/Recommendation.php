<?php

namespace JTL\Recommendation;

use stdClass;

/**
 * Class Recommendation
 * @package JTL\Recommendation
 */
class Recommendation
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $previewImage;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var array
     */
    private $images;

    /**
     * @var string
     */
    private $teaser = '';

    /**
     * @var array
     */
    private $benefits;

    /**
     * @var string
     */
    private $setupDescription = '';

    /**
     * @var Manufacturer
     */
    private $manufacturer;

    /**
     * @var string
     */
    private $url;

    /**
     * Recommendation constructor.
     * @param \stdClass $recommendation
     */
    public function __construct(stdClass $recommendation)
    {
        $this->setId($recommendation->id);
        $this->setDescription($recommendation->description);
        $this->setTitle($recommendation->name);
        $this->setPreviewImage($recommendation->preview_url);
        $this->setBenefits($recommendation->benefits);
        $this->setSetupDescription($recommendation->installation_description);
        $this->setImages($recommendation->images);
        $this->setTeaser($recommendation->teaser);
        $this->setManufacturer(new Manufacturer($recommendation->seller));
        $this->setUrl($recommendation->url);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPreviewImage(): string
    {
        return $this->previewImage;
    }

    /**
     * @param string $previewImage
     */
    public function setPreviewImage(string $previewImage): void
    {
        $this->previewImage = $previewImage;
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
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param array $images
     */
    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    /**
     * @return string
     */
    public function getTeaser(): string
    {
        return $this->teaser;
    }

    /**
     * @param string $teaser
     */
    public function setTeaser(string $teaser): void
    {
        $this->teaser = $teaser;
    }

    /**
     * @return array
     */
    public function getBenefits(): array
    {
        return $this->benefits;
    }

    /**
     * @param array $benefits
     */
    public function setBenefits(array $benefits): void
    {
        $this->benefits = $benefits;
    }

    /**
     * @return string
     */
    public function getSetupDescription(): string
    {
        return $this->setupDescription;
    }

    /**
     * @param string $setupDescription
     */
    public function setSetupDescription(string $setupDescription): void
    {
        $this->setupDescription = $setupDescription;
    }

    /**
     * @return Manufacturer
     */
    public function getManufacturer(): Manufacturer
    {
        return $this->manufacturer;
    }

    /**
     * @param Manufacturer $manufacturer
     */
    public function setManufacturer(Manufacturer $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
