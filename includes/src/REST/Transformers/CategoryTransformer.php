<?php declare(strict_types=1);

namespace JTL\REST\Transformers;

use JTL\Model\DataModelInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class CategoryTransformer
 * @package JAPI\Transformers
 */
class CategoryTransformer extends TransformerAbstract
{
    /**
     * @param DataModelInterface $category
     * @return array
     */
    public function transform(DataModelInterface $category): array
    {
        return $category->rawArray(true);
    }
}
