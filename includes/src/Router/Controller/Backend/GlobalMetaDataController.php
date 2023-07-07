<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Text;
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
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::SETTINGS_GLOBAL_META_VIEW);
        $this->getText->loadAdminLocale('pages/globalemetaangaben');
        $this->setLanguage();
        if ($this->tokenIsValid && $this->request->postInt('einstellungen') === 1) {
            $this->actionSaveConfig(Text::filterXSS($this->request->getBody()));
        }
        $meta     = $this->db->selectAll(
            'tglobalemetaangaben',
            ['kSprache', 'kEinstellungenSektion'],
            [$this->currentLanguageID, \CONF_METAANGABEN]
        );
        $metaData = [];
        foreach ($meta as $item) {
            $metaData[$item->cName] = $item->cWertName;
        }
        $this->getAdminSectionSettings(\CONF_METAANGABEN);

        return $this->smarty->assign('oMetaangaben_arr', $metaData)
            ->getResponse('globalemetaangaben.tpl');
    }

    /**
     * @param array $postData
     * @return void
     */
    private function actionSaveConfig(array $postData): void
    {
        $this->saveAdminSectionSettings(\CONF_METAANGABEN, $this->request->getBody());
        $title     = $postData['Title'];
        $desc      = $postData['Meta_Description'];
        $metaDescr = $postData['Meta_Description_Praefix'];
        $this->db->delete(
            'tglobalemetaangaben',
            ['kSprache', 'kEinstellungenSektion'],
            [$this->currentLanguageID, \CONF_METAANGABEN]
        );
        $ins                        = new stdClass();
        $ins->kEinstellungenSektion = \CONF_METAANGABEN;
        $ins->kSprache              = $this->currentLanguageID;
        $ins->cName                 = 'Title';
        $ins->cWertName             = $title;
        $this->db->insert('tglobalemetaangaben', $ins);
        $ins->cName     = 'Meta_Description';
        $ins->cWertName = $desc;
        $this->db->insert('tglobalemetaangaben', $ins);
        $ins->cName     = 'Meta_Description_Praefix';
        $ins->cWertName = $metaDescr;
        $this->db->insert('tglobalemetaangaben', $ins);
        $this->cache->flushAll();
        $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
    }
}
