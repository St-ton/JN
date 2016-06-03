<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Migration
 */
class Migration implements JsonSerializable
{
    use MigrationTrait,
        MigrationTableTrait;

    /**
     * @var string
     */
    protected $info;

    /**
     * @var DateTime
     */
    protected $created;

    /**
     * Migration constructor.
     *
     * @param null|string   $info
     * @param DateTime|null $created
     */
    public function __construct($info = null, DateTime $created = null)
    {
        $this->info    = ucfirst(strtolower($info));
        $this->created = $created;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return MigrationHelper::mapClassNameToId($this->getName());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * @return null
     */
    public function getAuthor()
    {
        return (isset($this->author) && $this->author !== null)
            ? $this->author : null;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->info;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'author'      => $this->getAuthor(),
            'description' => $this->getDescription(),
            'created'     => $this->getCreated()
        ];
    }
}
