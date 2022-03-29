<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Catalog\Hersteller;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Helpers\CMS;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Shop;
use JTL\Sitemap\Sitemap;
use JTL\Smarty\JTLSmarty;

/**
 * Class PageController
 * @package JTL\Router\Controller
 */
class PageController extends AbstractController
{
    public function init(): bool
    {
        parent::init();
        $link = Shop::Container()->getLinkService()->getLinkByID($this->state->linkID);
        if ($link === null) {
            return false;
        }
        $this->currentLink = $link;
        Shop::setPageType($this->state->pageType);

        return true;
    }

    public function handleState(JTLSmarty $smarty): void
    {
        echo $this->getResponse($smarty);
    }

    public function getResponse(JTLSmarty $smarty): string
    {
        $linkHelper = Shop::Container()->getLinkService();
        $cache      = Shop::Container()->getCache();
        if (!$this->currentLink->isVisible()) {
            $this->currentLink = $linkHelper->getSpecialPage(\LINKTYP_STARTSEITE);
            if ($this->currentLink === null) {
                die('Fatal.'); // @todo
            }
            $this->currentLink->setRedirectCode(301);
        }
        $requestURL = URL::buildURL($this->currentLink, \URLART_SEITE);
        if (!str_contains($requestURL, '.php')) {
            $this->canonicalURL = $this->currentLink->getURL();
        }
        if ($this->currentLink->getLinkType() === \LINKTYP_STARTSEITE) {
            $this->canonicalURL = Shop::getHomeURL();
            if ($this->currentLink->getRedirectCode() > 0) {
                \header('Location: ' . $this->canonicalURL, true, $this->currentLink->getRedirectCode());
                exit();
            }
            $smarty->assign('StartseiteBoxen', CMS::getHomeBoxes())
                ->assign('oNews_arr', $this->config['news']['news_benutzen'] === 'Y'
                    ? CMS::getHomeNews($this->config)
                    : []);
            Wizard::startIfRequired(\AUSWAHLASSISTENT_ORT_STARTSEITE, 1, $this->languageID, $smarty);
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_AGB) {
            $smarty->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
                $this->languageID,
                $this->customerGroupID
            ));
        } elseif (\in_array(
            $this->currentLink->getLinkType(),
            [\LINKTYP_WRB, \LINKTYP_WRB_FORMULAR, \LINKTYP_DATENSCHUTZ],
            true
        )) {
            $smarty->assign('WRB', Shop::Container()->getLinkService()->getAGBWRB(
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
            $smarty->assign('laender', ShippingMethod::getPossibleShippingCountries($this->customerGroupID));
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_LIVESUCHE) {
            $liveSearchTop  = CMS::getLiveSearchTop($this->config);
            $liveSearchLast = CMS::getLiveSearchLast($this->config);
            if (\count($liveSearchTop) === 0 && count($liveSearchLast) === 0) {
                $this->alertService->addWarning(Shop::Lang()->get('noDataAvailable'), 'noDataAvailable');
            }
            $smarty->assign('LivesucheTop', $liveSearchTop)
                ->assign('LivesucheLast', $liveSearchLast);
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_HERSTELLER) {
            $smarty->assign('oHersteller_arr', Hersteller::getAll());
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_NEWSLETTERARCHIV) {
            $smarty->assign('oNewsletterHistory_arr', CMS::getNewsletterHistory());
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_SITEMAP) {
            Shop::setPageType(\PAGE_SITEMAP);
            $sitemap = new Sitemap($this->db, $cache, $this->config);
            $sitemap->assignData($smarty);
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_404) {
            $sitemap = new Sitemap($this->db, $cache, $this->config);
            $sitemap->assignData($smarty);
            Shop::setPageType(\PAGE_404);
            $this->alertService->addDanger(Shop::Lang()->get('pageNotFound'), 'pageNotFound');
        } elseif ($this->currentLink->getLinkType() === \LINKTYP_GRATISGESCHENK) {
            if ($this->config['sonstiges']['sonstiges_gratisgeschenk_nutzen'] === 'Y') {
                $freeGifts = CMS::getFreeGifts($this->config);
                if (count($freeGifts) > 0) {
                    $smarty->assign('oArtikelGeschenk_arr', $freeGifts);
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
                $smarty
            );
        }
        if (($pluginID = $this->currentLink->getPluginID()) > 0 && $this->currentLink->getPluginEnabled() === true) {
            Shop::setPageType(\PAGE_PLUGIN);
            $loader = PluginHelper::getLoaderByPluginID($pluginID, $this->db, $cache);
            $boot   = PluginHelper::bootstrap($pluginID, $loader);
            if ($boot === null || !$boot->prepareFrontend($this->currentLink, $smarty)) {
                \executeHook(\HOOK_SEITE_PAGE_IF_LINKART);
            }
        }
        $this->preRender($smarty);
        $smarty->assign('Link', $this->currentLink)
            ->assign('bSeiteNichtGefunden', Shop::getPageType() === \PAGE_404)
            ->assign('cFehler', null)
            ->assign('meta_language', Text::convertISO2ISO639(Shop::getLanguageCode()));

        \executeHook(\HOOK_SEITE_PAGE);

        return $smarty->getResponse('layout/index.tpl');
    }
}
