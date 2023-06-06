<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use elFinder;
use elFinderConnector;
use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ElfinderController
 * @package JTL\Router\Controller\Backend
 */
class ElfinderController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::IMAGE_UPLOAD);
        if (!Form::validateToken()) {
            $response = (new Response())->withStatus(200)->withAddedHeader('content-type', 'text/html');
            $response->getBody()->write('Invalid token.');

            return $response;
        }
        $mediafilesSubdir = \STORAGE_OPC;
        $mediafilesType   = Request::verifyGPDataString('mediafilesType');
        if ($mediafilesType === 'video') {
            $mediafilesSubdir = \PFAD_MEDIA_VIDEO;
        }
        $mediafilesBaseUrlPath = Shop::getURL() . '/' . $mediafilesSubdir;
        if (!empty(Request::verifyGPDataString('cmd'))) {
            $this->runConnector($mediafilesSubdir, $mediafilesBaseUrlPath);
        }

        return $smarty->assign('mediafilesType', $mediafilesType)
            ->assign('mediafilesSubdir', $mediafilesSubdir)
            ->assign('isCKEditor', Request::verifyGPDataString('ckeditor') === '1')
            ->assign('route', $this->route)
            ->assign('CKEditorFuncNum', Request::verifyGPDataString('CKEditorFuncNum'))
            ->assign('templateUrl', $this->baseURL . '/' . $smarty->getTemplateUrlPath())
            ->assign('mediafilesBaseUrlPath', $mediafilesBaseUrlPath)
            ->getResponse('elfinder.tpl');
    }

    /**
     * @param string $mediafilesSubdir
     * @param string $mediafilesBaseUrlPath
     * @return void
     * @see https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
     */
    private function runConnector(string $mediafilesSubdir, string $mediafilesBaseUrlPath): void
    {
        $connector = new elFinderConnector(new elFinder([
            'bind'  => [
                'rm rename'      => static function ($cmd, &$result, $args, $elfinder, $volume) {
                    $sizes     = ['xs', 'sm', 'md', 'lg', 'xl'];
                    $fileTypes = ['jpeg', 'jpg', 'webp', 'png'];

                    foreach ($result['added'] as &$item) {
                        $item['name'] = \mb_strtolower($item['name']);
                    }
                    unset($item);
                    foreach ($result['removed'] as $filename) {
                        foreach ($sizes as $size) {
                            $filePath   = \str_replace(
                                \PFAD_ROOT . PFAD_MEDIA_IMAGE . 'storage/opc/',
                                '',
                                $filename['realpath']
                            );
                            $scaledFile = \PFAD_ROOT . PFAD_MEDIA_IMAGE . 'opc/' . $size . '/' . $filePath;
                            if (\is_dir($scaledFile)) {
                                @\rmdir($scaledFile);
                                continue;
                            }
                            $fileExtension = \pathinfo($scaledFile, \PATHINFO_EXTENSION);
                            $fileBaseName  = \basename($scaledFile, '.' . $fileExtension);

                            foreach ($fileTypes as $fileType) {
                                $fileTemp = \str_replace(
                                    $fileBaseName . '.' . $fileExtension,
                                    $fileBaseName . '.' . $fileType,
                                    $scaledFile
                                );
                                if (\file_exists($fileTemp)) {
                                    @\unlink($fileTemp);
                                }
                            }
                        }
                    }
                },
                'upload.presave' => static function (&$path, &$name, $tmpname, $_this, $volume) {
                    $name = \mb_strtolower($name);
                },
            ],
            'roots' => [
                [
                    'tmbSize'       => 120,
                    'driver'        => 'LocalFileSystem',
                    'path'          => \PFAD_ROOT . $mediafilesSubdir,
                    'URL'           => $mediafilesBaseUrlPath,
                    'winHashFix'    => \DIRECTORY_SEPARATOR !== '/',
                    'uploadDeny'    => ['all'],
                    'uploadAllow'   => ['image',
                                        'video',
                                        'text/plain',
                                        'application/pdf',
                                        'application/msword',
                                        'application/excel',
                                        'application/vnd.ms-excel',
                                        'application/x-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ],
                    'uploadOrder'   => ['deny', 'allow'],
                    'accessControl' => 'access',
                ],
            ],
        ]));

        $connector->run();
        exit();
    }
}
