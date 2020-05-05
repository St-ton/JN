<?php declare(strict_types=1);

namespace JTL\Crawler;

use JTL\MagicCompatibilityTrait;

/**
 * Class Crawler
 * @package JTL\Crawler
 */
class Crawler
{

    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'kBesucherBot'  => 'ID',
        'cName'         => 'Name',
        'cUserAgent'    => 'UserAgent',
        'cBeschreibung' => 'Description',
        'cLink'         => 'Link',
    ];

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $useragent = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $link;

    /**
     * @param array $crawler
     * @return object
     */
    public function map(array $crawler) :object
    {
        foreach ($crawler as $cr) {
            $this->setID((int)$cr->kBesucherBot);
            $this->setDescription($cr->cBeschreibung);
            $this->setUserAgent($cr->cUserAgent);
            $this->setName($cr->cName);
            $this->setLink($cr->cLink);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setID(int $id): void
    {
        $this->id = $id;
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
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return void
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->useragent;
    }

    /**
     * @param string $useragent
     * @return void
     */
    public function setUserAgent(string $useragent): void
    {
        $this->useragent = $useragent;
    }

    /**
     * @param string $link
     * @return string
     */
    public function getLink(string $link): string
    {
        return $this->link;
    }

    /**
     * @param string|null $link
     * @return void
     */
    public function setLink($link): void
    {
        $this->link = $link;
    }


}
