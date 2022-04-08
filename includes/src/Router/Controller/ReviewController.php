<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\Review\ReviewController as BaseController;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ReviewController
 * @package JTL\Router\Controller
 */
class ReviewController extends PageController
{
    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_BEWERTUNG);
        $controller = new BaseController(
            $this->db,
            Shop::Container()->getCache(),
            $this->alertService,
            $this->smarty
        );
        if ($controller->handleRequest() === true) {
            $this->preRender();

            return $this->smarty->getResponse('productdetails/review_form.tpl');
        }

        try {
            $product = (new Artikel($this->db))->fuelleArtikel($this->state->productID);
            \header('Location: ' . ($product !== null ? $product->cURLFull : Shop::getURL()));
        } catch (Exception) {
            \header('Location: ' . Shop::getURL());
        }

        exit;
    }
}
