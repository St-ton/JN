<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Overlay as Helper;
use JTL\Media\Image\Overlay;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SearchSpecialOverlayController
 * @package JTL\Router\Controller\Backend
 */
class SearchSpecialOverlayController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::DISPLAY_ARTICLEOVERLAYS_VIEW);
        $this->getText->loadAdminLocale('pages/suchspecialoverlay');

        $this->setLanguage();
        $step    = 'suchspecialoverlay_uebersicht';
        $overlay = $this->getOverlayInstance(1);
        if ($this->request->requestInt('suchspecialoverlay') === 1) {
            $helper = new Helper($this->db);
            $step   = 'suchspecialoverlay_detail';
            $oID    = $this->request->requestInt('kSuchspecialOverlay');
            if ($this->tokenIsValid
                && $this->request->postInt('speicher_einstellung') === 1
                && $helper->saveConfig($oID, $this->request->getBody(), $_FILES['cSuchspecialOverlayBild'])
            ) {
                $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);
                $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
            }
            if ($oID > 0) {
                $overlay = $this->getOverlayInstance($oID);
            }
        }
        $overlays = $this->getAll();
        $template = Shop::Container()->getTemplateService()->getActiveTemplate();
        if ($template->getName() === 'Evo'
            && $template->getAuthor() === 'JTL-Software-GmbH'
            && (int)$template->getVersion() >= 4
        ) {
            $this->smarty->assign('isDeprecated', true);
        }

        return $this->smarty->assign('cRnd', \time())
            ->assign('oSuchspecialOverlay', $overlay)
            ->assign('nMaxFileSize', self::getMaxFileSize(\ini_get('upload_max_filesize')))
            ->assign('oSuchspecialOverlay_arr', $overlays)
            ->assign('nSuchspecialOverlayAnzahl', \count($overlays) + 1)
            ->assign('step', $step)
            ->getResponse('suchspecialoverlay.tpl');
    }

    /**
     * @return Overlay[]
     * @former gibAlleSuchspecialOverlays()
     */
    private function getAll(): array
    {
        $overlays = [];
        foreach ($this->db->getInts(
            'SELECT kSuchspecialOverlay FROM tsuchspecialoverlay',
            'kSuchspecialOverlay'
        ) as $type) {
            $overlays[] = Overlay::getInstance($type, $this->currentLanguageID);
        }

        return $overlays;
    }

    /**
     * @param int $overlayID
     * @return Overlay
     */
    private function getOverlayInstance(int $overlayID): Overlay
    {
        return Overlay::getInstance($overlayID, $this->currentLanguageID);
    }
}
