<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class GlobalMetaDataController
 * @package JTL\Router\Controller\Backend
 */
class GlobalMetaDataController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('SETTINGS_GLOBAL_META_VIEW');
        $this->getText->loadAdminLocale('pages/globalemetaangaben');
        $this->setzeSprache();
        $languageID = (int)$_SESSION['editLanguageID'];
        if (Request::postInt('einstellungen') === 1 && Form::validateToken()) {
            $this->actionSaveConfig(Text::filterXSS($_POST));
        }

        $meta     = $this->db->selectAll(
            'tglobalemetaangaben',
            ['kSprache', 'kEinstellungenSektion'],
            [$languageID, \CONF_METAANGABEN]
        );
        $metaData = [];
        foreach ($meta as $item) {
            $metaData[$item->cName] = $item->cWertName;
        }
        $this->getAdminSectionSettings(\CONF_METAANGABEN);

        return $smarty->assign('oMetaangaben_arr', $metaData)
            ->assign('route', $this->route)
            ->getResponse('globalemetaangaben.tpl');
    }

    /**
     * @param array $postData
     * @return void
     */
    private function actionSaveConfig(array $postData): void
    {
        $this->saveAdminSectionSettings(\CONF_METAANGABEN, $_POST);
        $languageID = (int)$_SESSION['editLanguageID'];
        $title      = $postData['Title'];
        $desc       = $postData['Meta_Description'];
        $metaDescr  = $postData['Meta_Description_Praefix'];
        $this->db->delete(
            'tglobalemetaangaben',
            ['kSprache', 'kEinstellungenSektion'],
            [$languageID, \CONF_METAANGABEN]
        );
        $globalMetaData                        = new stdClass();
        $globalMetaData->kEinstellungenSektion = \CONF_METAANGABEN;
        $globalMetaData->kSprache              = $languageID;
        $globalMetaData->cName                 = 'Title';
        $globalMetaData->cWertName             = $title;
        $this->db->insert('tglobalemetaangaben', $globalMetaData);
        $globalMetaData                        = new stdClass();
        $globalMetaData->kEinstellungenSektion = \CONF_METAANGABEN;
        $globalMetaData->kSprache              = $languageID;
        $globalMetaData->cName                 = 'Meta_Description';
        $globalMetaData->cWertName             = $desc;
        $this->db->insert('tglobalemetaangaben', $globalMetaData);
        $globalMetaData                        = new stdClass();
        $globalMetaData->kEinstellungenSektion = \CONF_METAANGABEN;
        $globalMetaData->kSprache              = $languageID;
        $globalMetaData->cName                 = 'Meta_Description_Praefix';
        $globalMetaData->cWertName             = $metaDescr;
        $this->db->insert('tglobalemetaangaben', $globalMetaData);
        $this->cache->flushAll();
        $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
    }
}
