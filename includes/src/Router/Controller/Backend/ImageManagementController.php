<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Media\Image;
use JTL\Media\Manager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ImageManagementController
 * @package JTL\Router\Controller\Backend
 */
class ImageManagementController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::DISPLAY_IMAGES_VIEW);
        $this->getText->loadAdminLocale('pages/bilderverwaltung');

        $manager = new Manager($this->db, $this->getText);

        return $this->smarty->assign('items', $manager->getItems())
            ->assign('corruptedImagesByType', $manager->getCorruptedImages(Image::TYPE_PRODUCT, \MAX_CORRUPTED_IMAGES))
            ->getResponse('bilderverwaltung.tpl');
    }
}
