<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use InvalidArgumentException;
use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Overlay;
use JTL\Helpers\Request;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Template\Admin\Validation\TemplateValidator;
use JTL\Template\Model;
use JTL\Template\XMLReader;
use stdClass;

/**
 * Class Controller
 * @package JTL\Template\Admin
 */
class Controller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var AlertServiceInterface
     */
    private $alertService;

    /**
     * @var string|null
     */
    private $currentTemplateDir;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * @var Config
     */
    private $config;

    /**
     * Controller constructor.
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param AlertServiceInterface $alertService
     * @param JTLSmarty             $smarty
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        AlertServiceInterface $alertService,
        JTLSmarty $smarty
    )
    {
        $this->db           = $db;
        $this->cache        = $cache;
        $this->alertService = $alertService;
        $this->smarty       = $smarty;
    }

    public function handleAction(): void
    {
        $action                   = Request::verifyGPDataString('action');
        $valid                    = Form::validateToken();
        $this->currentTemplateDir = \basename(Request::verifyGPDataString('dir'));
        if (!\is_dir(\PFAD_ROOT . \PFAD_TEMPLATES . $this->currentTemplateDir)) {
            $this->currentTemplateDir = null;
            $valid                    = false;
        }
        $this->config = new Config($this->currentTemplateDir, $this->db);
        if (!$valid) {
            $this->displayOverview();
            return;
        }
        switch ($action) {
            case 'config':
                $this->displayTemplateSettings();
                break;
            case 'switch':
                $this->switch();
                if (Request::verifyGPCDataInt('config') === 1) {
                    $this->displayTemplateSettings();
                } else {
                    $this->displayOverview();
                }
                break;
            case 'save-config':
                $this->saveConfig();
                $this->displayOverview();
                break;
            default:
                $this->displayOverview();
                break;
        }
    }

    /**
     * @param string $dir
     * @param string $type
     * @return bool
     * @throws \Exception
     */
    private function setActiveTemplate(string $dir, string $type = 'standard'): bool
    {
        $this->db->delete('ttemplate', 'eTyp', $type);
        $this->db->delete('ttemplate', 'cTemplate', $dir);
        $reader    = new XMLReader();
        $tplConfig = $reader->getXML($dir);
        if ($tplConfig !== null && !empty($tplConfig->Parent)) {
            if (!\is_dir(\PFAD_ROOT . \PFAD_TEMPLATES . (string)$tplConfig->Parent)) {
                return false;
            }
            $parent       = (string)$tplConfig->Parent;
            $parentConfig = $reader->getXML($parent);
        } else {
            $parentConfig = false;
        }
        $model = new Model($this->db);
        $model->setCTemplate($dir);
        $model->setType($type);
        if (!empty($tplConfig->Parent)) {
            $model->setParent((string)$tplConfig->Parent);
        }
        $model->setName((string)$tplConfig->Name);
        $model->setAuthor((string)$tplConfig->Author);
        $model->setUrl((string)$tplConfig->URL);
        $model->setPreview((string)$tplConfig->Preview);
        $version = empty($tplConfig->Version) && $parentConfig
            ? (string)$parentConfig->Version
            : (string)$tplConfig->Version;
        if (empty($version)) {
            $version = !empty($tplConfig->ShopVersion)
                ? (string)$tplConfig->ShopVersion
                : (string)$parentConfig->ShopVersion;
        }
        $model->setVersion($version);
        $save = $model->save();
        if ($save === true) {
            if (!$dh = \opendir(\PFAD_ROOT . \PFAD_COMPILEDIR)) {
                return false;
            }
            while (($obj = \readdir($dh)) !== false) {
                if (\mb_strpos($obj, '.') === 0) {
                    continue;

                }
                if (!\is_dir(\PFAD_ROOT . \PFAD_COMPILEDIR . $obj)) {
                    \unlink(\PFAD_ROOT . \PFAD_COMPILEDIR . $obj);
                }
            }
        }
        $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);

        return $save;
    }

    private function saveConfig(): void
    {
        $parentFolder = null;
        $reader       = new XMLReader();
        $tplXML       = $reader->getXML($this->currentTemplateDir);

        if ($tplXML !== null && !empty($tplXML->Parent)) {
            $parentFolder = (string)$tplXML->Parent;
        }
        $tplConfXML   = $this->config->getTemplateConfig($reader, $parentFolder);
        $sectionCount = \count($_POST['cSektion']);
        for ($i = 0; $i < $sectionCount; $i++) {
            $section = $_POST['cSektion'][$i];
            $name    = $_POST['cName'][$i];
            $value   = $_POST['cWert'][$i];
            // for uploads, the value of an input field is the $_FILES index of the uploaded file
            if (\mb_strpos($value, 'upload-') === 0) {
                // all upload fields have to start with "upload-" - so check for that
                if (!empty($_FILES[$value]['name']) && $_FILES[$value]['error'] === UPLOAD_ERR_OK) {
                    // we have an upload field and the file is set in $_FILES array
                    $file  = $_FILES[$value];
                    $value = \basename($_FILES[$value]['name']);
                    $break = false;
                    foreach ($tplConfXML as $_section) {
                        if (!isset($_section->oSettings_arr)) {
                            continue;
                        }
                        foreach ($_section->oSettings_arr as $_setting) {
                            if (!isset($_setting->cKey, $_setting->rawAttributes['target']) || $_setting->cKey !== $name) {
                                continue;
                            }
                            $templatePath = PFAD_TEMPLATES . $this->currentTemplateDir . '/' . $_setting->rawAttributes['target'];
                            $base         = PFAD_ROOT . $templatePath;
                            // optional target file name + extension
                            if (isset($_setting->rawAttributes['targetFileName'])) {
                                $value = $_setting->rawAttributes['targetFileName'];
                            }
                            $targetFile = $base . $value;
                            if (!\is_writable($base)) {
                                $this->alertService->addAlert(
                                    Alert::TYPE_ERROR,
                                    \sprintf(__('errorFileUpload'), $templatePath),
                                    'errorFileUpload',
                                    ['saveInSession' => true]
                                );
                            } elseif (!\move_uploaded_file($file['tmp_name'], $targetFile)) {
                                $this->alertService->addAlert(
                                    Alert::TYPE_ERROR,
                                    __('errorFileUploadGeneral'),
                                    'errorFileUploadGeneral',
                                    ['saveInSession' => true]
                                );
                            }
                            $break = true;
                            break;
                        }
                        if ($break === true) {
                            break;
                        }
                    }
                } else {
                    // no file uploaded, ignore
                    continue;
                }
            }
            $this->config->updateConfigInDB($section, $name, $value);
            $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);
        }
        $check = $this->setActiveTemplate($this->currentTemplateDir);
        if ($check) {
            $this->alertService->addAlert(Alert::TYPE_SUCCESS, __('successTemplateSave'), 'successTemplateSave');
        } else {
            $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorTemplateSave'), 'errorTemplateSave');
        }

        if (Request::verifyGPCDataInt('activate') === 1) {
            $overlayHelper = new Overlay($this->db);
            $overlayHelper->loadOverlaysFromTemplateFolder($this->currentTemplateDir);
        }

        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
    }

    private function displayOverview(): void
    {
        $lstng = new Listing($this->db, new TemplateValidator($this->db));
        $this->smarty->assign('listingItems', $lstng->getAll())
            ->display('shoptemplate.tpl');
    }

    private function switch(): void
    {
        if ($this->setActiveTemplate($this->currentTemplateDir)) {
            $this->alertService->addAlert(Alert::TYPE_SUCCESS, __('successTemplateSave'), 'successTemplateSave');
        } else {
            $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorTemplateSave'), 'errorTemplateSave');
        }
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
    }

    private function displayTemplateSettings(): void
    {
        $reader    = new XMLReader();
        $tplXML    = $reader->getXML($this->currentTemplateDir);
        $parentXML = ($tplXML !== null || empty($tplXML->Parent)) ? null : $reader->getXML((string)$tplXML->Parent);
        if ($tplXML === null) {
            throw new InvalidArgumentException('Cannot display template settings');
        }
        $model        = new Model($this->db);
        $current      = $model->mergeWithXML($this->currentTemplateDir, $tplXML, $parentXML);
        $parentFolder = null;
        if ($tplXML !== null && !empty($tplXML->Parent)) {
            $parentFolder = (string)$tplXML->Parent;
        }
        $tplConfXML = $this->config->getTemplateConfig($reader, $parentFolder);
        $preview    = $this->getPreview($tplConfXML);

        $this->smarty->assign('template', $current)
            ->assign('themePreviews', (\count($preview) > 0) ? $preview : null)
            ->assign('themePreviewsJSON', \json_encode($preview))
            ->assign('oEinstellungenXML', $tplConfXML)
            ->display('shoptemplate.tpl');
    }

    /**
     * @param array $tplConfXML
     * @return array
     */
    private function getPreview(array $tplConfXML): array
    {
        $shopURL = Shop::getURL() . '/';
        $preview = [];
        $tplBase = \PFAD_ROOT . \PFAD_TEMPLATES;
        $tplPath = $tplBase . $this->currentTemplateDir . '/';
        foreach ($tplConfXML as $_conf) {
            // iterate over each "Setting" in this "Section"
            foreach ($_conf->oSettings_arr as $_setting) {
                if ($_setting->cType === 'upload'
                    && isset($_setting->rawAttributes['target'], $_setting->rawAttributes['targetFileName'])
                    && !\file_exists($tplPath . $_setting->rawAttributes['target']
                        . $_setting->rawAttributes['targetFileName'])
                ) {
                    $_setting->cValue = null;
                }
            }
            if (isset($_conf->cKey, $_conf->oSettings_arr)
                && $_conf->cKey === 'theme'
                && \count($_conf->oSettings_arr) > 0
            ) {
                foreach ($_conf->oSettings_arr as $_themeConf) {
                    if (isset($_themeConf->cKey, $_themeConf->oOptions_arr)
                        && $_themeConf->cKey === 'theme_default'
                        && \count($_themeConf->oOptions_arr) > 0
                    ) {
                        foreach ($_themeConf->oOptions_arr as $_theme) {
                            $previewImage = isset($_theme->cOrdner)
                                ? $tplBase . $_theme->cOrdner . '/themes/' .
                                $_theme->cValue . '/preview.png'
                                : $tplBase . $this->currentTemplateDir . '/themes/' . $_theme->cValue . '/preview.png';
                            if (\file_exists($previewImage)) {
                                $base                     = $shopURL . PFAD_TEMPLATES;
                                $preview[$_theme->cValue] = isset($_theme->cOrdner)
                                    ? $base . $_theme->cOrdner . '/themes/' . $_theme->cValue . '/preview.png'
                                    : $base . $this->currentTemplateDir . '/themes/' . $_theme->cValue . '/preview.png';
                            }
                        }
                        break;
                    }
                }
            }
        }

        return $preview;
    }
}
