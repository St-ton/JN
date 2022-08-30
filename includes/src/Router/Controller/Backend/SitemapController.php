<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SitemapController
 * @package JTL\Router\Controller\Backend
 */
class SitemapController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SETTINGS_SITEMAP_VIEW);
        $this->getText->loadAdminLocale('pages/shopsitemap');
        if (isset($_POST['einstellungen']) && Form::validateToken()) {
            $this->saveAdminSectionSettings(\CONF_SITEMAP, $_POST);
            if (GeneralObject::hasCount('nVon', $_POST) && GeneralObject::hasCount('nBis', $_POST)) {
                $this->db->query('TRUNCATE TABLE tpreisspannenfilter');
                for ($i = 0; $i < 10; $i++) {
                    if ((int)$_POST['nVon'][$i] >= 0 && (int)$_POST['nBis'][$i] > 0) {
                        $filter = (object)[
                            'nVon' => (int)$_POST['nVon'][$i],
                            'nBis' => (int)$_POST['nBis'][$i]
                        ];
                        $this->db->insert('tpreisspannenfilter', $filter);
                    }
                }
            }
        }
        $this->getAdminSectionSettings(\CONF_SITEMAP);

        return $smarty->assign('route', $this->route)
            ->getResponse('shopsitemap.tpl');
    }
}
