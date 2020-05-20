<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use Illuminate\Support\Collection;
use JTL\Backend\Wizard\Steps\EmailSettings;
use JTL\Backend\Wizard\Steps\GeneralSettings;
use JTL\Backend\Wizard\Steps\LegalPlugins;
use JTL\Backend\Wizard\Steps\PaymentPlugins;
use JTL\DB\DbInterface;
use JTL\L10n\GetText;

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
     * @param GetText $getText
     */
    public function __construct(DbInterface $db, GetText $getText)
    {
        $getText->loadConfigLocales();

        $this->steps = new Collection();
        $this->steps->push(new GeneralSettings($db));
        $this->steps->push(new LegalPlugins($db));
        $this->steps->push(new PaymentPlugins($db));
        $this->steps->push(new EmailSettings($db));
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
