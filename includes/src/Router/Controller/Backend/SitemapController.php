<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\GeneralObject;
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
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::SETTINGS_SITEMAP_VIEW);
        $this->getText->loadAdminLocale('pages/shopsitemap');
        if ($this->tokenIsValid && $this->request->post('einstellungen') !== null) {
            $this->saveAdminSectionSettings(\CONF_SITEMAP, $this->request->getBody());
            if (GeneralObject::hasCount('nVon', $this->request->getBody())
                && GeneralObject::hasCount('nBis', $this->request->getBody())
            ) {
                $this->db->query('TRUNCATE TABLE tpreisspannenfilter');
                for ($i = 0; $i < 10; $i++) {
                    if ((int)$this->request->post('nVon')[$i] >= 0 && (int)$this->request->post('nBis')[$i] > 0) {
                        $filter = (object)[
                            'nVon' => (int)$this->request->post('nVon')[$i],
                            'nBis' => (int)$this->request->post('nBis')[$i]
                        ];
                        $this->db->insert('tpreisspannenfilter', $filter);
                    }
                }
            }
        }
        $this->getAdminSectionSettings(\CONF_SITEMAP);

        return $this->smarty->getResponse('shopsitemap.tpl');
    }
}
