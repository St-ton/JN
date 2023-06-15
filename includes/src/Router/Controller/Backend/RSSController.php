<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Alert\Alert;
use JTL\Backend\Permissions;
use JTL\Export\RSS;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RSSController
 * @package JTL\Router\Controller\Backend
 */
class RSSController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::EXPORT_RSSFEED_VIEW);
        $this->getText->loadAdminLocale('pages/rss');
        if (Request::getInt('f') === 1 && Form::validateToken()) {
            $rss = new RSS($this->db, Shop::Container()->getLogService());
            if ($rss->generateXML()) {
                $this->alertService->addSuccess(\__('successRSSCreate'), 'successRSSCreate');
            } else {
                $this->alertService->addError(\__('errorRSSCreate'), 'errorRSSCreate');
            }
        }
        if (Request::postInt('einstellungen') > 0) {
            $this->saveAdminSectionSettings(\CONF_RSS, $_POST);
        }
        $rssDir = \PFAD_ROOT . \FILE_RSS_FEED;
        if (!\file_exists($rssDir)) {
            @\touch($rssDir);
        }
        if (!\is_writable($rssDir)) {
            $this->alertService->addError(
                \sprintf(\__('errorRSSCreatePermissions'), $rssDir),
                'errorRSSCreatePermissions'
            );
        }
        $this->getAdminSectionSettings(\CONF_RSS);

        return $smarty->assign('alertError', $this->alertService->alertTypeExists(Alert::TYPE_ERROR))
            ->assign('route', $this->route)
            ->getResponse('rss.tpl');
    }
}
