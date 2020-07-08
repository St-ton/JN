<?php

namespace JTL\Recommendation;

use stdClass;

/**
 * Class Manager
 * @package JTL\Recommendation
 */
class Recommendation
{
    /**
     * @var int
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
    private $descriptionShort = '';

    /**
     * @var array
     */
    private $benefits;

    /**
     * @var string
     */
    private $setupDescription = '';

    /**
     * Recommendation constructor.
     * @param \stdClass $recommendation
     */
    public function __construct(stdClass $recommendation)
    {
        $this->setId($recommendation->id);
        $this->setDescription($recommendation->product_description_a);
        $this->setTitle($recommendation->offer_title);
        $this->setPreviewImage($recommendation->preview_image);
        $this->setBenefits($recommendation->benefits);
        $this->setSetupDescription($recommendation->installation_setup_description);
        $this->setImages($recommendation->images);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
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
    public function getDescriptionShort(): string
    {
        return $this->descriptionShort;
    }

    /**
     * @param string $descriptionShort
     */
    public function setDescriptionShort(string $descriptionShort): void
    {
        $this->descriptionShort = $descriptionShort;
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
}
