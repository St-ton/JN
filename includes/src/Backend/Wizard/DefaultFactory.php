<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard;

use Illuminate\Support\Collection;
use JTL\Backend\Wizard\Steps\ShopConfig;
use JTL\Backend\Wizard\Steps\TaxConfig;
use JTL\DB\DbInterface;

/**
 * Class DefaultFactory
 * @package JTL\Backend\Wizard
 */
final class DefaultFactory
{
    /**
     * @var Collection
     */
    private $steps;

    /**
     * DefaultFactory constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->steps = new Collection();
        $this->steps->push(new ShopConfig($db));
        $this->steps->push(new TaxConfig($db));
    }

    /**
     * @return Collection
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    /**
     * @param Collection $steps
     */
    public function setSteps(Collection $steps): void
    {
        $this->steps = $steps;
    }
}
