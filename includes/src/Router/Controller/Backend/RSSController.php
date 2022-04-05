<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RSSController
 * @package JTL\Router\Controller\Backend
 */
class RSSController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('EXPORT_RSSFEED_VIEW');
        $this->getText->loadAdminLocale('pages/rss');
        if (Request::getInt('f') === 1 && Form::validateToken()) {
            if (\generiereRSSXML()) {
                $this->alertService->addSuccess(\__('successRSSCreate'), 'successRSSCreate');
            } else {
                $this->alertService->addError(\__('errorRSSCreate'), 'errorRSSCreate');
            }
        }
        if (Request::postInt('einstellungen') > 0) {
            \saveAdminSectionSettings(\CONF_RSS, $_POST);
        }
        if (!\file_exists(PFAD_ROOT . \FILE_RSS_FEED)) {
            @\touch(PFAD_ROOT . \FILE_RSS_FEED);
        }
        if (!\is_writable(PFAD_ROOT . \FILE_RSS_FEED)) {
            $this->alertService->addError(
                \sprintf(\__('errorRSSCreatePermissions'), PFAD_ROOT . \FILE_RSS_FEED),
                'errorRSSCreatePermissions'
            );
        }
        \getAdminSectionSettings(\CONF_RSS);

        return $smarty->assign('alertError', $this->alertService->alertTypeExists(Alert::TYPE_ERROR))
            ->getResponse('rss.tpl');
    }
}
