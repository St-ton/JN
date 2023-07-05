<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Catalog\Warehouse;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Text;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class WarehousesController
 * @package JTL\Router\Controller\Backend
 */
class WarehousesController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::WAREHOUSE_VIEW);
        $this->getText->loadAdminLocale('pages/warenlager');

        $step     = 'uebersicht';
        $postData = Text::filterXSS($_POST);
        $action   = (isset($postData['a']) && Form::validateToken()) ? $postData['a'] : null;
        if ($action === 'update') {
            $this->db->query('UPDATE twarenlager SET nAktiv = 0');
            if (GeneralObject::hasCount('kWarenlager', $postData)) {
                $wl = \array_map('\intval', $postData['kWarenlager']);
                $this->db->query(
                    'UPDATE twarenlager 
                        SET nAktiv = 1
                        WHERE kWarenlager IN (' . \implode(', ', $wl) . ')'
                );
            }
            if (GeneralObject::hasCount('cNameSprache', $postData)) {
                foreach ($postData['cNameSprache'] as $id => $assocLang) {
                    $this->db->delete('twarenlagersprache', 'kWarenlager', (int)$id);
                    foreach ($assocLang as $languageID => $name) {
                        if (\mb_strlen(\trim($name)) < 2) {
                            continue;
                        }
                        $data = (object)[
                            'kWarenlager' => (int)$id,
                            'kSprache'    => (int)$languageID,
                            'cName'       => \htmlspecialchars(
                                \trim($name),
                                \ENT_COMPAT | \ENT_HTML401,
                                \JTL_CHARSET
                            )
                        ];
                        $this->db->insert('twarenlagersprache', $data);
                    }
                }
            }
            $this->cache->flushTags([\CACHING_GROUP_ARTICLE]);
            $this->alertService->addSuccess(\__('successStoreRefresh'), 'successStoreRefresh');
        }

        return $smarty->assign('step', $step)
            ->assign('warehouses', Warehouse::getAll(false, true))
            ->assign('route', $this->route)
            ->getResponse('warenlager.tpl');
    }
}
