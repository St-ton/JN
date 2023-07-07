<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\GeneralObject;
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
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::SETTINGS_NAVIGATION_FILTER_VIEW);
        $this->getText->loadAdminLocale('pages/navigationsfilter');
        if ($this->tokenIsValid && $this->request->post('speichern') !== null) {
            $this->saveAdminSectionSettings(\CONF_NAVIGATIONSFILTER, $this->request->getBody());
            $this->cache->flushTags([\CACHING_GROUP_CATEGORY]);
            if (GeneralObject::hasCount('nVon', $this->request->getBody())
                && GeneralObject::hasCount('nBis', $this->request->getBody())
            ) {
                $this->db->query('TRUNCATE TABLE tpreisspannenfilter');
                foreach ($this->request->post('nVon') as $i => $nVon) {
                    $nVon = (float)$nVon;
                    $nBis = (float)$this->request->post('nBis')[$i];
                    if ($nVon >= 0 && $nBis >= 0) {
                        $this->db->insert('tpreisspannenfilter', (object)['nVon' => $nVon, 'nBis' => $nBis]);
                    }
                }
            }
        }

        $priceRangeFilters = $this->db->getObjects('SELECT * FROM tpreisspannenfilter');
        $this->getAdminSectionSettings(\CONF_NAVIGATIONSFILTER);

        return $this->smarty->assign('oPreisspannenfilter_arr', $priceRangeFilters)
            ->getResponse('navigationsfilter.tpl');
    }
}
