<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class NavFilterController
 * @package JTL\Router\Controller\Backend
 */
class NavFilterController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SETTINGS_NAVIGATION_FILTER_VIEW);
        $this->getText->loadAdminLocale('pages/navigationsfilter');

        if (isset($_POST['speichern']) && Form::validateToken()) {
            $this->saveAdminSectionSettings(\CONF_NAVIGATIONSFILTER, $_POST);
            $this->cache->flushTags([\CACHING_GROUP_CATEGORY]);
            if (GeneralObject::hasCount('nVon', $_POST) && GeneralObject::hasCount('nBis', $_POST)) {
                $this->db->query('TRUNCATE TABLE tpreisspannenfilter');
                foreach ($_POST['nVon'] as $i => $nVon) {
                    $nVon = (float)$nVon;
                    $nBis = (float)$_POST['nBis'][$i];
                    if ($nVon >= 0 && $nBis >= 0) {
                        $this->db->insert('tpreisspannenfilter', (object)['nVon' => $nVon, 'nBis' => $nBis]);
                    }
                }
            }
        }

        $priceRangeFilters = $this->db->getObjects('SELECT * FROM tpreisspannenfilter');
        $this->getAdminSectionSettings(\CONF_NAVIGATIONSFILTER);

        return $smarty->assign('oPreisspannenfilter_arr', $priceRangeFilters)
            ->assign('route', $this->route)
            ->getResponse('navigationsfilter.tpl');
    }
}
