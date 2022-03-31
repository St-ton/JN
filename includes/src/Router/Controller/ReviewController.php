<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\Review\ReviewController as BaseController;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ReviewController
 * @package JTL\Router\Controller
 */
class ReviewController extends PageController
{
    public function init(): bool
    {
        parent::init();
        Shop::setPageType(\PAGE_BEWERTUNG);

        return true;
    }

    public function handleState(JTLSmarty $smarty): void
    {
        echo $this->getResponse($smarty);
    }

    public function getResponse(JTLSmarty $smarty): ResponseInterface
    {
        $controller = new BaseController(
            $this->db,
            Shop::Container()->getCache(),
            $this->alertService,
            $smarty
        );
        if ($controller->handleRequest() === true) {
            $this->preRender($smarty);

            return $smarty->getResponse('productdetails/review_form.tpl');
        }

        try {
            $product = (new Artikel($this->db))->fuelleArtikel($this->state->productID);
            \header('Location: ' . ($product !== null ? $product->cURLFull : Shop::getURL()));
        } catch (Exception $e) {
            \header('Location: ' . Shop::getURL());
        }

        exit;
    }
}
