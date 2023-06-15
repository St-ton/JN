<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Update\Updater;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OPCController
 * @package JTL\Router\Controller\Backend
 */
class OPCController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::OPC_VIEW);
        $this->getText->loadAdminLocale('pages/opc');

        $pageKey   = Request::verifyGPCDataInt('pageKey');
        $pageId    = Request::verifyGPDataString('pageId');
        $pageUrl   = Request::verifyGPDataString('pageUrl');
        $pageName  = Request::verifyGPDataString('pageName');
        $action    = Request::verifyGPDataString('action');
        $draftKeys = \array_map('\intval', $_POST['draftKeys'] ?? []);
        $shopURL   = Shop::getURL();
        $error     = null;
        $opc       = Shop::Container()->getOPC();
        $opcPage   = Shop::Container()->getOPCPageService();
        $opcPageDB = Shop::Container()->getOPCPageDB();

        $templateUrl = $this->baseURL . '/' . $smarty->getTemplateUrlPath();

        $smarty->assign('shopUrl', $shopURL)
            ->assign('adminUrl', $this->baseURL)
            ->assign('templateUrl', $templateUrl)
            ->assign('pageKey', $pageKey)
            ->assign('route', $this->route)
            ->assign('opc', $opc);

        $updater    = new Updater($this->db);
        $hasUpdates = $updater->hasPendingUpdates();

        if ($hasUpdates) {
            // Database update needed
            $this->getText->loadAdminLocale('pages/dbupdater');

            return $smarty->assign('error', [
                'heading' => \__('dbUpdate') . ' ' . \__('required'),
                'desc'    => \sprintf(\__('dbUpdateNeeded'), $this->baseURL),
            ])
                ->getResponse(\PFAD_ROOT . \PFAD_ADMIN . '/opc/tpl/editor.tpl');
        }
        if ($action === 'edit') {
            // Enter OPC to edit a page
            try {
                $page = $opcPage->getDraft($pageKey);
            } catch (Exception $e) {
                $error = $e->getMessage();
                $page  = null;
            }

            $this->getText->loadAdminLocale('pages/opc/tutorials');

            return $smarty->assign('error', $error)
                ->assign('page', $page)
                ->getResponse(\PFAD_ROOT . \PFAD_ADMIN . '/opc/tpl/editor.tpl');
        }
        if ($action !== '' && Form::validateToken() === false) {
            // OPC action while XSRF validation failed
            $error = \__('Wrong XSRF token.');
        } elseif ($action === 'extend') {
            // Create a new OPC page draft
            try {
                $newName = \__('draft') . ' ' . ($opcPageDB->getDraftCount($pageId) + 1);
                $page    = $opcPage
                    ->createDraft($pageId)
                    ->setUrl($pageUrl)
                    ->setName($newName);
                $opcPageDB->saveDraft($page);
                $pageKey = $page->getKey();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            return new RedirectResponse($this->baseURL . $this->route . '?pageKey=' . $pageKey . '&action=edit');
        } elseif ($action === 'adopt') {
            // Adopt new draft from another draft
            try {
                $adoptFromDraft = $opcPage->getDraft($pageKey);
                $page           = $opcPage
                    ->createDraft($pageId)
                    ->setUrl($pageUrl)
                    ->setName($pageName)
                    ->setPublishFrom($adoptFromDraft->getPublishFrom())
                    ->setPublishTo($adoptFromDraft->getPublishTo())
                    ->setAreaList($adoptFromDraft->getAreaList());
                $opcPageDB->saveDraft($page);
                $pageKey = $page->getKey();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            return new RedirectResponse($this->baseURL . $this->route . '?pageKey=' . $pageKey . '&action=edit');
        } elseif ($action === 'duplicate-bulk') {
            // duplicate multiple drafts from existing drafts
            try {
                foreach ($draftKeys as $draftKey) {
                    $adoptFromDraft = $opcPage->getDraft($draftKey);
                    $newName        = $adoptFromDraft->getName() . ' (Copy)';
                    $curPageId      = $adoptFromDraft->getId();
                    $page           = $opcPage
                        ->createDraft($adoptFromDraft->getId())
                        ->setUrl($adoptFromDraft->getUrl())
                        ->setName($newName)
                        ->setAreaList($adoptFromDraft->getAreaList());
                    $opcPageDB->saveDraft($page);
                    $pageKey = $page->getKey();
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            exit('ok');
        } elseif ($action === 'discard') {
            // Discard a OPC page draft
            $opcPage->deleteDraft($pageKey);
            exit('ok');
        } elseif ($action === 'discard-bulk') {
            // Discard multiple OPC page drafts
            foreach ($draftKeys as $draftKey) {
                $opcPage->deleteDraft($draftKey);
            }
            exit('ok');
        }
        exit('ok');
    }
}
