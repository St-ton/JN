<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Alert\Alert;
use JTL\Backend\Permissions;
use JTL\Export\RSS;
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
        $this->checkPermissions(Permissions::EXPORT_RSSFEED_VIEW);
        $this->getText->loadAdminLocale('pages/rss');
        if ($this->tokenIsValid && $this->request->getInt('f') === 1) {
            $rss = new RSS($this->db, Shop::Container()->getLogService());
            if ($rss->generateXML()) {
                $this->alertService->addSuccess(\__('successRSSCreate'), 'successRSSCreate');
            } else {
                $this->alertService->addError(\__('errorRSSCreate'), 'errorRSSCreate');
            }
        }
        if ($this->request->postInt('einstellungen') > 0) {
            $this->saveAdminSectionSettings(\CONF_RSS, $this->request->getBody());
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

        return $this->smarty->assign('alertError', $this->alertService->alertTypeExists(Alert::TYPE_ERROR))
            ->getResponse('rss.tpl');
    }
}
