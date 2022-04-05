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

        $step = 'shoplogouploader_uebersicht';
        // Upload
        if (!empty($_FILES) && Form::validateToken()) {
            $status           = $this->saveShopLogo($_FILES);
            $response         = new stdClass();
            $response->status = ($status === 1) ? 'OK' : 'FAILED';
            echo \json_encode($response);
            die();
        }
        if (Request::verifyGPCDataInt('upload') === 1 && Form::validateToken()) {
            if (isset($_POST['delete'])) {
                $delete = $this->deleteShopLogo(Shop::getLogo());
                if ($delete === true) {
                    $this->alertService->addSuccess(\__('successLogoDelete'), 'successLogoDelete');
                } else {
                    $this->alertService->addError(\sprintf(\__('errorLogoDelete'), PFAD_ROOT . Shop::getLogo()), 'errorLogoDelete');
                }
            }
            $saved = $this->saveShopLogo($_FILES);
            if ($saved === 1) {
                $this->alertService->addSuccess(\__('successLogoUpload'), 'successLogoUpload');
            } else {
                // 2 = Dateiname entspricht nicht der Konvention oder fehlt
                // 3 = Dateityp entspricht nicht der (Nur jpg/gif/png/bmp/ Bilder) Konvention oder fehlt
                switch ($saved) {
                    case 2:
                        $this->alertService->addError(\__('errorFileName'), 'errorFileName');
                        break;
                    case 3:
                        $this->alertService->addError(\__('errorFileType'), 'errorFileType');
                        break;
                    case 4:
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

        return $smarty->assign('ShopLogo', Shop::getLogo(false))
            ->assign('ShopLogoURL', Shop::getLogo(true))
            ->assign('step', $step)
            ->assign('route', $route->getPath())
            ->getResponse('shoplogouploader.tpl');
    }

    /**
     * Speichert das aktuelle ShopLogo
     *
     * @param array $files
     * @return int
     * 1 = Alles O.K.
     * 2 = Dateiname leer
     * 3 = Dateityp entspricht nicht der Konvention (Nur jpg/gif/png/bmp/ Bilder) oder fehlt
     * 4 = Konnte nicht bewegen
     */
    private function saveShopLogo(array $files): int
    {
        if (!\file_exists(PFAD_ROOT . \PFAD_SHOPLOGO)
            && !\mkdir($concurrentDirectory = PFAD_ROOT . \PFAD_SHOPLOGO)
            && !\is_dir($concurrentDirectory)
        ) {
            return 4;
        }
        if (empty($files['shopLogo']['name'])) {
            return 2;
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
            return 3;
        }
        $file = PFAD_ROOT . \PFAD_SHOPLOGO . \basename($files['shopLogo']['name']);
        if ($files['shopLogo']['error'] === \UPLOAD_ERR_OK && \move_uploaded_file($files['shopLogo']['tmp_name'], $file)) {
            $option                        = new stdClass();
            $option->kEinstellungenSektion = \CONF_LOGO;
            $option->cName                 = 'shop_logo';
            $option->cWert                 = $files['shopLogo']['name'];
            $this->db->update('teinstellungen', 'cName', 'shop_logo', $option);
            $this->cache->flushTags([\CACHING_GROUP_OPTION]);

            return 1;
        }

        return 4;
    }

    /**
     * @return bool
     * @var string $logo
     */
    private function deleteShopLogo(string $logo): bool
    {
        return \is_file(PFAD_ROOT . $logo) && \unlink(PFAD_ROOT . $logo);
    }
}
