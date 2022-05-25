<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Catalog\Hersteller;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Helpers\CMS;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Router\ControllerFactory;
use JTL\Router\State;
use JTL\Shop;
use JTL\Sitemap\Sitemap;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PageController
 * @package JTL\Router\Controller
 */
class PageController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function getStateFromSlug(array $args): State
    {
        $linkID   = (int)($args['id'] ?? 0);
        $linkName = $args['name'] ?? null;
        if ($linkID < 1 && $linkName === null) {
            return $this->state;
        }
        $languageID = $this->parseLanguageFromArgs($args, $this->languageID ?? Shop::getLanguageID());

        $seo = $linkID > 0
            ? $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                      AND kKey = :kid
                      AND kSprache = :lid',
                ['key' => 'kLink', 'kid' => $linkID, 'lid' => $languageID]
            )
            : $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key AND cSeo = :seo',
                ['key' => 'kLink', 'seo' => $linkName]
            );
        if ($seo === null) {
            $this->state->is404 = true;

            return $this->state;
        }
        $slug          = $seo->cSeo;
        $seo->kSprache = (int)$seo->kSprache;
        $seo->kKey     = (int)$seo->kKey;

        return $this->updateState($seo, $slug);
    }

    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();
        $this->currentLink = Shop::Container()->getLinkService()->getLinkByID($this->state->linkID);

        return $this->currentLink !== null;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        if (isset($args['id']) || isset($args['name'])) {
            $this->getStateFromSlug($args);
            if (!$this->init()) {
                return $this->notFoundResponse($request, $args, $smarty);
            }
        }
        $this->smarty = $smarty;
        Shop::setPageType($this->state->pageType);
        if (!$this->currentLink->isVisible()) {
            $this->currentLink = Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_STARTSEITE);
            $this->currentLink->setRedirectCode(301);
        }
        $requestURL = URL::buildURL($this->currentLink, \URLART_SEITE);
        if (!str_contains($requestURL, '.php')) {
            $this->canonicalURL = $this->currentLink->getURL();
        }
        $mapped = ControllerFactory::getControllerClassByLinkType($this->currentLink->getLinkType());
        if ($mapped !== null && $mapped !== __CLASS__) {
            return $this->delegateResponse($mapped, $request, $args, $smarty);
        }
        if ($this->currentLink->getLinkType() === \LINKTYP_STARTSEITE) {
            $this->canonicalURL = Shop::getHomeURL();
            if ($this->currentLink->getRedirectCode() > 0) {
                return new RedirectResponse($this->canonicalURL, $this->currentLink->getRedirectCode());
            }
            $this->smarty->assign('StartseiteBoxen', CMS::getHomeBoxes())
                ->assign('oNews_arr', $this->config['news']['news_benutzen'] === 'Y'
                    ? CMS::getHomeNews($this->config)
                    : []);
            Wizard::startIfRequired(\AUSWAHLASSISTENT_ORT_STARTSEITE, 1, $this->languageID, $this->smarty);
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_AGB) {
            $this->smarty->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
                $this->languageID,
                $this->customerGroupID
            ));
        } elseif (\in_array(
            $this->currentLink->getLinkType(),
            [\LINKTYP_WRB, \LINKTYP_WRB_FORMULAR, \LINKTYP_DATENSCHUTZ],
            true
        )) {
            $this->smarty->assign('WRB', Shop::Container()->getLinkService()->getAGBWRB(
                $this->languageID,
                $this->customerGroupID
            ));
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_VERSAND) {
            $error = '';
            if (isset($_POST['land'], $_POST['plz'])
                && !ShippingMethod::getShippingCosts($_POST['land'], $_POST['plz'], $error)
            ) {
                $this->alertService->addError(
                    Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages'),
                    'missingParamShippingDetermination'
                );
            }
            if ($error !== '') {
                $this->alertService->addError($error, 'shippingCostError');
            }
            $this->smarty->assign('laender', ShippingMethod::getPossibleShippingCountries($this->customerGroupID));
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_LIVESUCHE) {
            $liveSearchTop  = CMS::getLiveSearchTop($this->config);
            $liveSearchLast = CMS::getLiveSearchLast($this->config);
            if (\count($liveSearchTop) === 0 && \count($liveSearchLast) === 0) {
                $this->alertService->addWarning(Shop::Lang()->get('noDataAvailable'), 'noDataAvailable');
            }
            $this->smarty->assign('LivesucheTop', $liveSearchTop)
                ->assign('LivesucheLast', $liveSearchLast);
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_HERSTELLER) {
            $this->smarty->assign(
                'oHersteller_arr',
                Hersteller::getAll(true, $this->languageID, $this->customerGroupID)
            );
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_NEWSLETTERARCHIV) {
            $this->smarty->assign('oNewsletterHistory_arr', CMS::getNewsletterHistory());
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_SITEMAP) {
            Shop::setPageType(\PAGE_SITEMAP);
            $sitemap = new Sitemap($this->db, $this->cache, $this->config);
            $sitemap->assignData($this->smarty);
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_404) {
            $sitemap = new Sitemap($this->db, $this->cache, $this->config);
            $sitemap->assignData($this->smarty);
            Shop::setPageType(\PAGE_404);
            $this->alertService->addDanger(Shop::Lang()->get('pageNotFound'), 'pageNotFound');
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_GRATISGESCHENK) {
            if ($this->config['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
                $freeGifts = CMS::getFreeGifts($this->config);
                if (\count($freeGifts) > 0) {
                    $this->smarty->assign('oArtikelGeschenk_arr', $freeGifts);
                } else {
                    $this->alertService->addError(
                        Shop::Lang()->get('freegiftsNogifts', 'errorMessages'),
                        'freegiftsNogifts'
                    );
                }
            }
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_AUSWAHLASSISTENT) {
            Wizard::startIfRequired(
                \AUSWAHLASSISTENT_ORT_LINK,
                $this->currentLink->getID(),
                $this->languageID,
                $this->smarty
            );
        }
        if (($pluginID = $this->currentLink->getPluginID()) > 0 && $this->currentLink->getPluginEnabled() === true) {
            Shop::setPageType(\PAGE_PLUGIN);
            $loader = PluginHelper::getLoaderByPluginID($pluginID, $this->db, $this->cache);
            $boot   = PluginHelper::bootstrap($pluginID, $loader);
            if ($boot === null || !$boot->prepareFrontend($this->currentLink, $this->smarty)) {
                $this->getPluginPage();
            }
        }
        $this->preRender();
        $this->smarty->assign('Link', $this->currentLink)
            ->assign('bSeiteNichtGefunden', Shop::getPageType() === \PAGE_404)
            ->assign('cFehler', null)
            ->assign('meta_language', Text::convertISO2ISO639(Shop::getLanguageCode()));

        \executeHook(\HOOK_SEITE_PAGE);
        if ($this->state->is404) {
            return $this->smarty->getResponse('layout/index.tpl')->withStatus(404);
        }

        return $this->smarty->getResponse('layout/index.tpl');
    }

    /**
     * @return void
     */
    protected function getPluginPage(): void
    {
        $linkID = $this->currentLink->getID();
        if ($linkID <= 0) {
            return;
        }
        $linkFile = $this->db->select('tpluginlinkdatei', 'kLink', $linkID);
        if ($linkFile === null || empty($linkFile->cDatei)) {
            return;
        }
        global $oPlugin, $plugin;
        $pluginID = (int)$linkFile->kPlugin;
        $plugin   = PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID);
        $oPlugin  = $plugin;
        $this->smarty->assign('oPlugin', $plugin)
            ->assign('plugin', $plugin)
            ->assign('Link', $this->currentLink);
        if ($linkFile->cTemplate !== null && \mb_strlen($linkFile->cTemplate) > 0) {
            $this->smarty->assign('cPluginTemplate', $plugin->getPaths()->getFrontendPath() .
                \PFAD_PLUGIN_TEMPLATE . $linkFile->cTemplate)
                ->assign('nFullscreenTemplate', 0);
        } else {
            $this->smarty->assign('cPluginTemplate', $plugin->getPaths()->getFrontendPath() .
                \PFAD_PLUGIN_TEMPLATE . $linkFile->cFullscreenTemplate)
                ->assign('nFullscreenTemplate', 1);
        }
        include $plugin->getPaths()->getFrontendPath() . $linkFile->cDatei;
    }

    /**
     * @param string                 $class
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @param JTLSmarty              $smarty
     * @return ResponseInterface
     */
    protected function delegateResponse(
        string $class,
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        $controller = new $class(
            $this->db,
            $this->cache,
            $this->state,
            $this->customerGroupID,
            $this->config,
            $this->alertService
        );
        $controller->init();

        return $controller->getResponse($request, $args, $smarty);
    }
}
