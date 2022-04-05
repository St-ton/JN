<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Media\Image;
use JTL\Media\Manager;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ImageManagementController
 * @package JTL\Router\Controller\Backend
 */
class ImageManagementController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('DISPLAY_IMAGES_VIEW');
        $this->getText->loadAdminLocale('pages/bilderverwaltung');

        $manager = new Manager($this->db, $this->getText);

        return $smarty->assign('items', $manager->getItems())
            ->assign('corruptedImagesByType', $manager->getCorruptedImages(Image::TYPE_PRODUCT, \MAX_CORRUPTED_IMAGES))
            ->assign('route', $route->getPath())
            ->getResponse('bilderverwaltung.tpl');
    }
}
