<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class SitemapController
 * @package JTL\Router\Controller\Backend
 */
class SitemapController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('SETTINGS_SITEMAP_VIEW');
        $this->getText->loadAdminLocale('pages/shopsitemap');
        if (isset($_POST['einstellungen']) && Form::validateToken()) {
            \saveAdminSectionSettings(\CONF_SITEMAP, $_POST);
            if (GeneralObject::hasCount('nVon', $_POST) && GeneralObject::hasCount('nBis', $_POST)) {
                $this->db->query('TRUNCATE TABLE tpreisspannenfilter');
                for ($i = 0; $i < 10; $i++) {
                    if ((int)$_POST['nVon'][$i] >= 0 && (int)$_POST['nBis'][$i] > 0) {
                        $filter       = new stdClass();
                        $filter->nVon = (int)$_POST['nVon'][$i];
                        $filter->nBis = (int)$_POST['nBis'][$i];

                        $this->db->insert('tpreisspannenfilter', $filter);
                    }
                }
            }
        }
        \getAdminSectionSettings(\CONF_SITEMAP);

        return $smarty->assign('route', $route->getPath())
            ->getResponse('shopsitemap.tpl');
    }
}
