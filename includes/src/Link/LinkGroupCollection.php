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
 *
 * @package Link
 */
class LinkGroupCollection extends Collection
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
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function __get($key)
    {
        $group = $this->filter(function (LinkGroupInterface $e) use ($key) {
            return $e->getTemplate() === $key;
        })->first();
        return $group ?? parent::__get($key);
    }
}
