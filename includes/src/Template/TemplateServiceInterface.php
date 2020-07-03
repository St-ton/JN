<?php declare(strict_types=1);

namespace JTL\Template;

use Exception;

/**
 * Interface TemplateServiceInterface
 * @package JTL\Template
 */
interface TemplateServiceInterface
{
    /**
     *
     */
    public function save(): void;

    /**
     * @return Model
     * @throws Exception
     */
    public function getActiveTemplate(): Model;

    /**
     * @param array $attributes
     * @return Model
     * @throws Exception
     */
    public function loadFull(array $attributes): Model;
}
