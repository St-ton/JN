<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\State;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CharacteristicValueController
 * @package JTL\Router\Controller
 */
class CharacteristicValueController extends ProductListController
{
    /**
     * @var string
     */
    protected string $tseoSelector = 'kMerkmalWert';

    /**
     * @inheritdoc
     */
    protected function handleSeoError(int $id, int $languageID): State
    {
        if ($id > 0) {
            $exists = $this->db->getSingleObject(
                'SELECT kMerkmalWert
                    FROM tmerkmalwert
                    WHERE kMerkmalWert = :pid',
                ['pid' => $id]
            );
            if ($exists !== null) {
                $seo = (object)[
                    'cSeo'     => '',
                    'cKey'     => 'kMerkmalWert',
                    'kKey'     => $id,
                    'kSprache' => $languageID
                ];

                return $this->updateState($seo, $seo->cSeo);
            }
        }
        $this->state->is404 = true;

        return $this->updateProductFilter();
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getStateFromSlug($args);
        if (!$this->init()) {
            return $this->notFoundResponse($request, $args, $smarty);
        }

        return parent::getResponse($request, $args, $smarty);
    }
}
