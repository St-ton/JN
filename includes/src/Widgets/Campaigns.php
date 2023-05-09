<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Helpers\Request;
use JTL\Router\Controller\Backend\AbstractBackendController;
use JTL\Router\Controller\Backend\CampaignController;
use JTL\Shop;
use JTL\Widgets\AbstractWidget;
use stdClass;

/**
 * Class Campaigns
 * @package Plugin\jtl_widgets
 */
class Campaigns extends AbstractWidget
{
    /**
     * @inheritDoc
     */
    public function init(): void
    {
        if (\method_exists($this, 'setPermission')) {
            $this->setPermission('STATS_CAMPAIGN_VIEW');
        }

        $_SESSION['Kampagne']                 = new stdClass();
        $_SESSION['Kampagne']->nAnsicht       = 2;
        $_SESSION['Kampagne']->nSort          = 0;
        $_SESSION['Kampagne']->cSort          = 'DESC';
        $_SESSION['Kampagne']->nDetailAnsicht = 2;
        $_SESSION['Kampagne']->cFromDate_arr  = ['nJahr' => (int)\date('Y'), 'nMonat' => (int)\date('n'), 'nTag' => 1];
        $_SESSION['Kampagne']->cToDate_arr    = ['nJahr' => (int)\date('Y'),
                                                 'nMonat' => (int)\date('n'),
                                                 'nTag' => (int)\date('j')];
        $_SESSION['Kampagne']->cFromDate      = \date('Y-n-1');
        $_SESSION['Kampagne']->cToDate        = \date('Y-n-j');

        $controller          = new CampaignController(
            $this->getDB(),
            Shop::Container()->getCache(),
            Shop::Container()->getAlertService(),
            Shop::Container()->getAdminAccount(),
            Shop::Container()->getGetText()
        );
        $campaigns           = AbstractBackendController::getCampaigns(true, false, $this->getDB());
        $campaignDefinitions = $controller->getDefinitions();
        $first               = \array_keys($campaigns);
        $first               = $first[0];
        $campaignID          = (int)$campaigns[$first]->kKampagne;

        if (isset($_SESSION['jtl_widget_kampagnen']['kKampagne'])
            && $_SESSION['jtl_widget_kampagnen']['kKampagne'] > 0
        ) {
            $campaignID = (int)$_SESSION['jtl_widget_kampagnen']['kKampagne'];
        }
        if (Request::getInt('kKampagne') > 0) {
            $campaignID = Request::getInt('kKampagne');
        }
        $_SESSION['jtl_widget_kampagnen']['kKampagne'] = $campaignID;

        $stats = $controller->getDetailStats($campaignID, $campaignDefinitions);

        $this->getSmarty()->assign('kKampagne', $campaignID)
            ->assign('types', \array_keys($stats))
            ->assign('campaigns', $campaigns)
            ->assign('campaignDefinitions', $campaignDefinitions)
            ->assign('campaignStats', $stats);
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->getSmarty()->fetch('tpl_inc/widgets/widgetCampaigns.tpl');
    }
}
