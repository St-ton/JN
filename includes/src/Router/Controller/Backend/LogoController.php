<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class LogoController
 * @package JTL\Router\Controller\Backend
 */
class LogoController extends AbstractBackendController
{
    private const ERR_PERMISSIONS       = 4;
    private const ERR_INVALID_FILE_TYPE = 3;
    private const ERR_EMPTY_FILE_NAME   = 2;
    private const OK                    = 1;

    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('DISPLAY_OWN_LOGO_VIEW');
        $this->getText->loadAdminLocale('pages/shoplogouploader');

        if (isset($_POST['action'], $_POST['logo']) && $_POST['action'] === 'deleteLogo') {
            $this->actionDelete();
        }
        if (!empty($_FILES) && Form::validateToken()) {
            $response         = new stdClass();
            $response->status = ($this->saveShopLogo($_FILES) === self::OK) ? 'OK' : 'FAILED';
            echo \json_encode($response);
            die();
        }
        if (Request::verifyGPCDataInt('upload') === 1 && Form::validateToken()) {
            $this->actionUpload();
        }

        return $smarty->assign('ShopLogo', Shop::getLogo(false))
            ->assign('ShopLogoURL', Shop::getLogo(true))
            ->assign('step', 'shoplogouploader_uebersicht')
            ->assign('route', $route->getPath())
            ->getResponse('shoplogouploader.tpl');
    }

    /**
     * Speichert das aktuelle ShopLogo
     *
     * @param array $files
     * @return int
     */
    private function saveShopLogo(array $files): int
    {
        if (!\file_exists(PFAD_ROOT . \PFAD_SHOPLOGO)
            && !\mkdir($concurrentDirectory = PFAD_ROOT . \PFAD_SHOPLOGO)
            && !\is_dir($concurrentDirectory)
        ) {
            return self::ERR_PERMISSIONS;
        }
        if (empty($files['shopLogo']['name'])) {
            return self::ERR_EMPTY_FILE_NAME;
        }
        $allowedTypes = [
            'image/jpeg',
            'image/pjpeg',
            'image/gif',
            'image/png',
            'image/x-png',
            'image/bmp',
            'image/jpg',
            'image/svg+xml',
            'image/svg',
            'image/webp'
        ];
        if (!\in_array($files['shopLogo']['type'], $allowedTypes, true)
            || (\extension_loaded('fileinfo')
                && !\in_array(\mime_content_type($files['shopLogo']['tmp_name']), $allowedTypes, true))
        ) {
            return self::ERR_INVALID_FILE_TYPE;
        }
        $file = PFAD_ROOT . \PFAD_SHOPLOGO . \basename($files['shopLogo']['name']);
        if ($files['shopLogo']['error'] === \UPLOAD_ERR_OK && \move_uploaded_file($files['shopLogo']['tmp_name'], $file)) {
            $option                        = new stdClass();
            $option->kEinstellungenSektion = \CONF_LOGO;
            $option->cName                 = 'shop_logo';
            $option->cWert                 = $files['shopLogo']['name'];
            $this->db->update('teinstellungen', 'cName', 'shop_logo', $option);
            $this->cache->flushTags([\CACHING_GROUP_OPTION]);

            return self::OK;
        }

        return self::ERR_PERMISSIONS;
    }

    /**
     * @return bool
     * @var string $logo
     */
    private function deleteShopLogo(string $logo): bool
    {
        return \is_file(PFAD_ROOT . $logo) && \unlink(PFAD_ROOT . $logo);
    }

    private function actionDelete(): void
    {
        $currentLogo = Shop::getLogo();
        $response    = new stdClass();
        if ($currentLogo === $_POST['logo'] && Form::validateToken()) {
            $delete                        = $this->deleteShopLogo($currentLogo);
            $response->status              = ($delete === true) ? 'OK' : 'FAILED';
            $option                        = new stdClass();
            $option->kEinstellungenSektion = \CONF_LOGO;
            $option->cName                 = 'shop_logo';
            $option->cWert                 = null;
            $this->db->update('teinstellungen', 'cName', 'shop_logo', $option);
            $this->cache->flushTags([\CACHING_GROUP_OPTION]);
        } else {
            $response->status = 'FAILED';
        }
        die(\json_encode($response));
    }

    private function actionUpload(): void
    {
        if (isset($_POST['delete'])) {
            $delete = $this->deleteShopLogo(Shop::getLogo());
            if ($delete === true) {
                $this->alertService->addSuccess(\__('successLogoDelete'), 'successLogoDelete');
            } else {
                $this->alertService->addError(\sprintf(\__('errorLogoDelete'), PFAD_ROOT . Shop::getLogo()), 'errorLogoDelete');
            }
        }
        $saved = $this->saveShopLogo($_FILES);
        if ($saved === self::OK) {
            $this->alertService->addSuccess(\__('successLogoUpload'), 'successLogoUpload');
        } else {
            switch ($saved) {
                case self::ERR_EMPTY_FILE_NAME:
                    $this->alertService->addError(\__('errorFileName'), 'errorFileName');
                    break;
                case self::ERR_INVALID_FILE_TYPE:
                    $this->alertService->addError(\__('errorFileType'), 'errorFileType');
                    break;
                case self::ERR_PERMISSIONS:
                    $this->alertService->addError(
                        \sprintf(\__('errorFileMove'), PFAD_ROOT . \PFAD_SHOPLOGO . \basename($_FILES['shopLogo']['name'])),
                        'errorFileMove'
                    );
                    break;
                default:
                    break;
            }
        }
    }
}
