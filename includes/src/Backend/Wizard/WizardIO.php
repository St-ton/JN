<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\L10n\GetText;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;

/**
 * Class WizardIO
 * @package JTL\Backend\Wizard
 */
class WizardIO
{
    /**
     * @var Controller|null
     */
    private ?Controller $wizardController = null;

    /**
     * WizardIO constructor.
     * @param DbInterface $db
     * @param JTLCacheInterface $cache
     * @param AlertServiceInterface $alertService
     * @param GetText $getText
     */
    public function __construct(
        protected DbInterface $db,
        protected JTLCacheInterface $cache,
        protected AlertServiceInterface $alertService,
        protected GetText $getText
    ) {
    }

    /**
     * @param array $post
     * @return array
     */
    public function validateStep(array $post): array
    {
        $this->init();

        return $this->wizardController->validateStep($post);
    }

    /**
     * @param array $post
     * @return array
     */
    public function answerQuestions(array $post): array
    {
        $this->init();

        return $this->wizardController->answerQuestions($post);
    }

    private function init(): void
    {
        $wizardFactory          = new DefaultFactory(
            $this->db,
            $this->getText,
            $this->alertService,
            Shop::Container()->getAdminAccount()
        );
        $this->wizardController = new Controller($wizardFactory, $this->db, $this->cache, $this->getText);
    }
}
