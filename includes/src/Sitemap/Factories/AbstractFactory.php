<?php declare(strict_types=1);

namespace JTL\Sitemap\Factories;

use JTL\DB\DbInterface;

/**
 * Class AbstractFactory
 * @package JTL\Sitemap\Factories
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * AbstractFactory constructor.
     * @param DbInterface $db
     * @param array       $config
     * @param string      $baseURL
     * @param string      $baseImageURL
     */
    public function __construct(
        protected DbInterface $db,
        protected array $config,
        protected string $baseURL,
        protected string $baseImageURL
    ) {
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        $res           = \get_object_vars($this);
        $res['db']     = '*truncated*';
        $res['config'] = '*truncated*';

        return $res;
    }
}
