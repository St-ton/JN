<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use InvalidArgumentException;
use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Overlay;
use JTL\Helpers\Request;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Template\Admin\Extractor;
use JTL\Template\Admin\Listing;
use JTL\Template\Admin\Validation\TemplateValidator;
use JTL\Template\BootChecker;
use JTL\Template\Compiler;
use JTL\Template\Config;
use JTL\Template\XMLReader;
use JTLShop\SemVer\Version;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Functional\first;

/**
 * Class TemplateController
 * @package JTL\Router\Controller\Backend
 */
class TemplateController extends AbstractBackendController
{
    /**
     * @var string|null
     */
    private ?string $currentTemplateDir = null;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::DISPLAY_TEMPLATE_VIEW);
        $this->getText->loadAdminLocale('pages/shoptemplate');
        $this->smarty->assign('route', $this->route);

        return $this->handleAction();
    }

    /**
     * @return ResponseInterface
     */
    public function handleAction(): ResponseInterface
    {
        $action                   = Request::verifyGPDataString('action');
        $valid                    = Form::validateToken();
        $this->currentTemplateDir = \basename(Request::verifyGPDataString('dir'));
        if (!\is_dir(\PFAD_ROOT . \PFAD_TEMPLATES . $this->currentTemplateDir)) {
            $this->currentTemplateDir = null;
            $valid                    = false;
        }
        $this->smarty->assign('action', $action);
        $this->config = new Config($this->currentTemplateDir, $this->db);
        if (!empty($_FILES['template-install-upload'])) {
            $action = 'upload';
            if (!$valid) {
                return $this->failResponse();
            }
        }
        if (!$valid) {
            return $this->displayOverview();
        }
        if (Request::postVar('saveAndContinue')) {
            $this->saveConfig();
            return $this->displayTemplateSettings();
        }
        switch ($action) {
            case 'config':
                return $this->displayTemplateSettings();
            case 'switch':
                $this->switch();
                return Request::verifyGPCDataInt('config') === 1
                    ? $this->displayTemplateSettings()
                    : $this->displayOverview();
            case 'save-config':
                $this->saveConfig();
                return $this->displayOverview();
            case 'unsetPreview':
                $this->unsetPreview();
                return $this->displayOverview();
            case 'setPreview':
                $this->switch('test');
                return Request::verifyGPCDataInt('config') === 1
                    ? $this->displayTemplateSettings()
                    : $this->displayOverview();
            case 'upload':
                return $this->upload($_FILES['template-install-upload']);
            default:
                return $this->displayOverview();
        }
    }

    /**
     * @return ResponseInterface
     */
    private function failResponse(): ResponseInterface
    {
        $response = new InstallationResponse();
        $response->setStatus(InstallationResponse::STATUS_FAILED);
        $response->setError(\__('errorCSRF'));

        $data = (new Response())->withStatus(200)->withAddedHeader('content-type', 'application/json');
        $data->getBody()->write($response->toJson());

        return $data;
    }

    /**
     * @param array $files
     * @return ResponseInterface
     */
    private function upload(array $files): ResponseInterface
    {
        $extractor = new Extractor();
        $response  = $extractor->extractTemplate($files['tmp_name']);
        if ($response->getStatus() === InstallationResponse::STATUS_OK
            && $response->getDirName()
            && ($bootstrapper = BootChecker::bootstrap(\rtrim($response->getDirName(), '/'))) !== null
        ) {
            $bootstrapper->installed();
        }
        $lstng = new Listing($this->db, new TemplateValidator($this->db));
        $html  = (object)[
            'id'      => '#shoptemplate-overview',
            'content' => $this->smarty->assign('listingItems', $lstng->getAll())
                ->assign('shopVersion', Version::parse(\APPLICATION_VERSION))
                ->fetch('tpl_inc/shoptemplate_overview.tpl')
        ];
        $response->setHtml($html);

        $data = (new Response())->withStatus(200)->withAddedHeader('content-type', 'application/json');
        $data->getBody()->write($response->toJson());

        return $data;
    }

    private function unsetPreview(): void
    {
        $this->db->delete('ttemplate', 'eTyp', 'test');
    }

    private function saveConfig(): void
    {
        $parentFolder = null;
        $reader       = new XMLReader();
        $tplXML       = $reader->getXML($this->currentTemplateDir);
        if ($tplXML !== null && !empty($tplXML->Parent)) {
            $parentFolder = (string)$tplXML->Parent;
        }
        $service      = Shop::Container()->getTemplateService();
        $current      = $service->getActiveTemplate();
        $updated      = $current->getFileVersion() !== $current->getVersion();
        $tplConfXML   = $this->config->getConfigXML($reader, $parentFolder);
        $oldConfig    = $this->config->loadConfigFromDB();
        $oldColorConf = $oldConfig['colors'] ?? null;
        $oldSassConf  = $oldConfig['customsass'] ?? null;
        foreach ($tplConfXML as $config) {
            foreach ($config->settings as $setting) {
                if ($setting->cType === 'checkbox') {
                    $value = isset($_POST[$setting->elementID]) ? '1' : '0';
                } else {
                    $value = $_POST[$setting->elementID] ?? null;
                }
                if ($value === null) {
                    continue;
                }
                if (\is_array($value)) {
                    $value = first($value);
                }
                // for uploads, the value of an input field is the $_FILES index of the uploaded file
                if ($setting->cType === 'upload') {
                    try {
                        $value = $this->handleUpload($tplConfXML, $value, $setting->key);
                    } catch (InvalidArgumentException) {
                        continue;
                    }
                }
                $this->config->updateConfigInDB($setting->section, $setting->key, $value);
            }
        }
        $check = $service->setActiveTemplate($this->currentTemplateDir, Request::postVar('eTyp', 'standard'));
        $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);
        if ($check) {
            $this->alertService->addSuccess(\__('successTemplateSave'), 'successTemplateSave');
        } else {
            $this->alertService->addError(\__('errorTemplateSave'), 'errorTemplateSave');
        }
        if (Request::verifyGPCDataInt('activate') === 1) {
            $overlayHelper = new Overlay($this->db);
            $overlayHelper->loadOverlaysFromTemplateFolder($this->currentTemplateDir);
        }
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
        $config = $this->config->loadConfigFromDB();
        if (!isset($config['colors']) && !isset($config['customsass'])) {
            return;
        }
        $newColorConf = $config['colors'] ?? null;
        $newSassConf  = $config['customsass'] ?? null;
        if ($updated === false && $newColorConf === $oldColorConf && $newSassConf === $oldSassConf) {
            return;
        }
        $vars          = \trim($config['customsass']['customVariables'] ?? '');
        $customContent = \trim($config['customsass']['customContent'] ?? '');
        foreach ($config['colors'] ?? [] as $name => $color) {
            if (!empty($color)) {
                $vars .= "\n" . '$' . $name . ': ' . $color . ';';
            }
        }
        $paths    = $current->getPaths();
        $compiler = new Compiler();
        $compiler->setCustomVariables($vars);
        $compiler->setCustomContent($customContent);
        if ($compiler->compileSass($paths->getThemeDirName(), $paths->getBaseRelDir() . 'themes/')) {
            $this->alertService->addSuccess(\__('Successfully compiled CSS.'), 'successCompile');
        }
        foreach ($compiler->getErrors() as $idx => $error) {
            $this->alertService->addError(
                \sprintf(\__('An error occured while compiling the CSS: %s'), $error),
                'errorCompile' . $idx
            );
        }
    }

    /**
     * @param array  $tplConfXML
     * @param string $value
     * @param string $name
     * @return string
     */
    private function handleUpload(array $tplConfXML, string $value, string $name): string
    {
        if (empty($_FILES[$value]['name']) || $_FILES[$value]['error'] !== \UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No file provided or upload error');
        }
        $file  = $_FILES[$value];
        $value = \basename($_FILES[$value]['name']);
        foreach ($tplConfXML as $section) {
            if (!isset($section->settings)) {
                continue;
            }
            foreach ($section->settings as $setting) {
                if (!isset($setting->key, $setting->rawAttributes['target']) || $setting->key !== $name) {
                    continue;
                }
                $templatePath = \PFAD_TEMPLATES . $this->currentTemplateDir . '/' . $setting->rawAttributes['target'];
                $base         = \PFAD_ROOT . $templatePath;
                // optional target file name + extension
                if (isset($setting->rawAttributes['targetFileName'])) {
                    $value = $setting->rawAttributes['targetFileName'];
                }
                $targetFile = $base . $value;
                if (!\is_writable($base)) {
                    $this->alertService->addError(
                        \sprintf(\__('errorFileUpload'), $templatePath),
                        'errorFileUpload',
                        ['saveInSession' => true]
                    );
                } elseif (!\move_uploaded_file($file['tmp_name'], $targetFile)) {
                    $this->alertService->addError(
                        \__('errorFileUploadGeneral'),
                        'errorFileUploadGeneral',
                        ['saveInSession' => true]
                    );
                }

                return $value;
            }
        }

        return $value;
    }

    /**
     * @return ResponseInterface
     */
    private function displayOverview(): ResponseInterface
    {
        $lstng = new Listing($this->db, new TemplateValidator($this->db));

        return $this->smarty->assign('listingItems', $lstng->getAll())
            ->assign('shopVersion', Version::parse(\APPLICATION_VERSION))
            ->getResponse('shoptemplate.tpl');
    }

    /**
     * @return string|null
     */
    private function getPreviousTemplate(): ?string
    {
        return $this->db->select('ttemplate', 'eTyp', 'standard')->cTemplate ?? null;
    }

    /**
     * @param string $type
     */
    private function switch(string $type = 'standard'): void
    {
        BootChecker::bootstrap($this->getPreviousTemplate())?->disabled();
        if (Shop::Container()->getTemplateService()->setActiveTemplate($this->currentTemplateDir, $type)) {
            BootChecker::bootstrap($this->currentTemplateDir)?->enabled();
            $this->alertService->addSuccess(\__('successTemplateSave'), 'successTemplateSave');
        } else {
            $this->alertService->addError(\__('errorTemplateSave'), 'errorTemplateSave');
        }
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
        $this->cache->flushTags([\CACHING_GROUP_LICENSES]);
    }

    /**
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    private function displayTemplateSettings(): ResponseInterface
    {
        $reader = new XMLReader();
        $tplXML = $reader->getXML($this->currentTemplateDir);
        if ($tplXML === null) {
            throw new InvalidArgumentException('Cannot display template settings');
        }
        $this->assignScrollPosition();
        $service      = Shop::Container()->getTemplateService();
        $current      = $service->loadFull(['cTemplate' => $this->currentTemplateDir]);
        $parentFolder = null;
        $getText      = $this->getGetText();
        if (!empty($tplXML->Parent)) {
            $parentFolder = (string)$tplXML->Parent;
            $getText->loadLocaleFile($getText->getMoPath(\PFAD_ROOT . \PFAD_TEMPLATES . $parentFolder . '/', 'base'));
        }
        $getText->loadTemplateLocale('base', $current);
        $templateConfig = $this->config->getConfigXML($reader, $parentFolder);
        $preview        = $this->getPreview($templateConfig);

        return $this->smarty->assign('template', $current)
            ->assign('themePreviews', (\count($preview) > 0) ? $preview : null)
            ->assign('themePreviewsJSON', \json_encode($preview, \JSON_THROW_ON_ERROR))
            ->assign('templateConfig', $templateConfig)
            ->getResponse('shoptemplate.tpl');
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
            foreach ($_conf->settings as $_setting) {
                if ($_setting->cType === 'upload'
                    && isset($_setting->rawAttributes['target'], $_setting->rawAttributes['targetFileName'])
                    && !\file_exists($tplPath . $_setting->rawAttributes['target']
                        . $_setting->rawAttributes['targetFileName'])
                ) {
                    $_setting->value = null;
                }
            }
            if (isset($_conf->key, $_conf->settings) && $_conf->key === 'theme' && \count($_conf->settings) > 0) {
                foreach ($_conf->settings as $_themeConf) {
                    if (!isset($_themeConf->key, $_themeConf->options)
                        || $_themeConf->key !== 'theme_default'
                        || \count($_themeConf->options) === 0
                    ) {
                        continue;
                    }
                    foreach ($_themeConf->options as $_theme) {
                        $previewImage = isset($_theme->dir)
                            ? $tplBase . $_theme->dir . '/themes/' .
                            $_theme->value . '/preview.png'
                            : $tplBase . $this->currentTemplateDir . '/themes/' . $_theme->value . '/preview.png';
                        if (\file_exists($previewImage)) {
                            $base                    = $shopURL . \PFAD_TEMPLATES;
                            $preview[$_theme->value] = isset($_theme->dir)
                                ? $base . $_theme->dir . '/themes/' . $_theme->value . '/preview.png'
                                : $base . $this->currentTemplateDir . '/themes/' . $_theme->value . '/preview.png';
                        }
                    }
                    break;
                }
            }
        }

        return $preview;
    }
}
