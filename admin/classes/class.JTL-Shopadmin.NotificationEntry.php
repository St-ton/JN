<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class NotificationEntry
 */
class NotificationEntry
{
    /**
     * Information type
     */
    const TYPE_INFO = 0;

    /**
     * Warning type
     */
    const TYPE_WARNING = 1;

    /**
     * Error type
     */
    const TYPE_ERROR = 2;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * NotificationEntry constructor.
     * @param int $type
     * @param string $title
     * @param null|string $description
     */
    public function __construct($type, $title, $description = null)
    {
        $this->setType($type);
        $this->setTitle($title);
        $this->setDescription($description);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function hasDescription()
    {
        return $this->description !== null && strlen($this->description) > 0;
    }
}