<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\JsonResponse;
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

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::DISPLAY_OWN_LOGO_VIEW);
        $this->getText->loadAdminLocale('pages/shoplogouploader');

        if ($this->tokenIsValid) {
            if ($this->request->post('logo') && $this->request->post('action') === 'deleteLogo') {
                return $this->actionDelete();
            }
            if (!empty($_FILES)) {
                $response = (object)['status' => ($this->saveShopLogo($_FILES) === self::OK) ? 'OK' : 'FAILED'];

                return new JsonResponse($response);
            }
            if ($this->request->requestInt('upload') === 1) {
                $this->actionUpload();
            }
        }

        return $this->smarty->assign('ShopLogo', Shop::getLogo())
            ->assign('ShopLogoURL', Shop::getLogo(true))
            ->assign('step', 'shoplogouploader_uebersicht')
            ->getResponse('shoplogouploader.tpl');
    }

    /**
     * @param array $files
     * @return int
     */
    private function saveShopLogo(array $files): int
    {
        if (!\file_exists(\PFAD_ROOT . \PFAD_SHOPLOGO)
            && !\mkdir($concurrentDirectory = \PFAD_ROOT . \PFAD_SHOPLOGO)
            && !\is_dir($concurrentDirectory)
        ) {
            return self::ERR_PERMISSIONS;
        }
        if (empty($files['shopLogo']['name'])) {
            return self::ERR_EMPTY_FILE_NAME;
        }
        $tmp          = $files['shopLogo']['tmp_name'];
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
            || (\extension_loaded('fileinfo') && !\in_array(\mime_content_type($tmp), $allowedTypes, true))
        ) {
            return self::ERR_INVALID_FILE_TYPE;
        }
        $file = \PFAD_ROOT . \PFAD_SHOPLOGO . \basename($files['shopLogo']['name']);
        if ($files['shopLogo']['error'] === \UPLOAD_ERR_OK && \move_uploaded_file($tmp, $file)) {
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
        return \is_file(\PFAD_ROOT . $logo) && \unlink(\PFAD_ROOT . $logo);
    }

    /**
     * @return ResponseInterface
     */
    private function actionDelete(): ResponseInterface
    {
        $currentLogo = Shop::getLogo();
        $response    = (object)['status' => 'FAILED'];
        if ($this->tokenIsValid && $currentLogo === $this->request->post('logo')) {
            $delete                        = $this->deleteShopLogo($currentLogo);
            $response->status              = $delete === true ? 'OK' : 'FAILED';
            $option                        = new stdClass();
            $option->kEinstellungenSektion = \CONF_LOGO;
            $option->cName                 = 'shop_logo';
            $option->cWert                 = null;
            $this->db->update('teinstellungen', 'cName', 'shop_logo', $option);
            $this->cache->flushTags([\CACHING_GROUP_OPTION]);
        }

        return new JsonResponse($response);
    }

    /**
     * @return void
     */
    private function actionUpload(): void
    {
        if ($this->request->post('delete') !== null) {
            $delete = $this->deleteShopLogo(Shop::getLogo());
            if ($delete === true) {
                $this->alertService->addSuccess(\__('successLogoDelete'), 'successLogoDelete');
            } else {
                $this->alertService->addError(
                    \sprintf(\__('errorLogoDelete'), \PFAD_ROOT . Shop::getLogo()),
                    'errorLogoDelete'
                );
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
                        \sprintf(
                            \__('errorFileMove'),
                            \PFAD_ROOT . \PFAD_SHOPLOGO . \basename($_FILES['shopLogo']['name'])
                        ),
                        'errorFileMove'
                    );
                    break;
                default:
                    break;
            }
        }
    }
}
