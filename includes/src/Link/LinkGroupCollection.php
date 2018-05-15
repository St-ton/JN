<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;


use Tightenco\Collect\Support\Collection;

/**
 * Class LinkGroupCollection
 *
 * this allows calls like LinkHelper::getLinkgroups()->Fuss to access a link group by its template name
 * for compatability reasons only
 *
 * @package Link
 */
final class LinkGroupCollection extends Collection
{
    /**
     * @var array
     */
    public $Link_Datenschutz;

    /**
     * @var array
     */
    public $Link_Versandseite;

    /**
     * @var array
     */
    public $Link_AGB;

    /**
     * @param string $name
     * @return LinkGroupInterface|null
     */
    public function getLinkgroupByTemplate(string $name)
    {
        return $this->filter(function (LinkGroupInterface $e) use ($name) {
            return $e->getTemplate() === $name;
        })->first();
    }

    /**
     * @param int $id
     * @return LinkGroupInterface|null
     */
    public function getLinkgroupByID(int $id)
    {
        return $this->filter(function (LinkGroupInterface $e) use ($id) {
            return $e->getID() === $id;
        })->first();
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function __get($key)
    {
        return $this->getLinkgroupByTemplate($key) ?? parent::__get($key);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return property_exists($this, $name) || $this->getLinkgroupByTemplate($name) !== null;
    }
}
