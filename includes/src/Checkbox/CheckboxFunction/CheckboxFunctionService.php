<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxFunction;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;

/**
 * Class CheckboxFunctionService
 * @package JTL\Checkbox\CheckboxFunction
 */
class CheckboxFunctionService extends AbstractService
{
    /**
     * @var CheckboxFunctionRepository
     */
    public function __construct(protected ?CheckboxFunctionRepository $repository = null)
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function initDependencies(): void
    {
        $this->repository = new CheckboxFunctionRepository();
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    public function get(int $ID): ?\stdClass
    {
        return $this->getRepository()->get($ID);
    }
}
