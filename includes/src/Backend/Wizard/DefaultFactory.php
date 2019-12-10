<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard;

use Illuminate\Support\Collection;
use JTL\Backend\Wizard\Steps\GlobalSettings;
use JTL\Backend\Wizard\Steps\LegalPlugins;
use JTL\Backend\Wizard\Steps\PaymentPlugins;
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
        $this->steps->push(new GlobalSettings($db));
        $this->steps->push(new LegalPlugins($db));
        $this->steps->push(new PaymentPlugins($db));
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
