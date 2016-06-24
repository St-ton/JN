<?php
/**
 * BackendAccountHelper
 *
 * @package     jtl_backenduser_extension
 * @copyright   2015 JTL-Software-GmbH
 */

/**
 * Class BackendAccountHelper
 */
class BackendAccountHelper
{
    private $plugin;

    private static $_instance;

    /**
     * @param Plugin $oPlugin
     * @return BackendAccountHelper
     */
    public static function getInstance(Plugin $oPlugin)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($oPlugin);
        }

        return static::$_instance;
    }

    /**
     * BackendAccountHelper constructor.
     * @param Plugin $oPlugin
     */
    private function __construct(Plugin $oPlugin)
    {
        $this->plugin = $oPlugin;
    }

    /**
     * @param array $tmpFile
     * @param string $attribName
     * @return mixed bool|string
     */
    private function uploadImage(array $tmpFile, $attribName)
    {
        $imgType = array_search($tmpFile['type'][$attribName], array(
            IMAGETYPE_JPEG => image_type_to_mime_type(IMAGETYPE_JPEG),
            IMAGETYPE_PNG  => image_type_to_mime_type(IMAGETYPE_PNG),
            IMAGETYPE_BMP  => image_type_to_mime_type(IMAGETYPE_BMP),
            IMAGETYPE_GIF  => image_type_to_mime_type(IMAGETYPE_GIF),
        ));

        if ($imgType !== false) {
            $imagePath = PFAD_BILDER . 'avatare/';
            $imageName = pathinfo($tmpFile['name'][$attribName], PATHINFO_FILENAME) . image_type_to_extension($imgType);

            if (is_dir(PFAD_ROOT . $imagePath) || mkdir(PFAD_ROOT . $imagePath, 0755)) {
                if (move_uploaded_file($tmpFile['tmp_name'][$attribName], PFAD_ROOT . $imagePath . $imageName)) {
                    return '/' . $imagePath . $imageName;
                }
            }
        }

        return false;
    }

    /**
     * @param $imagePath
     */
    private function deleteImage($imagePath)
    {
        if (is_file(PFAD_ROOT . $imagePath)) {
            unlink(PFAD_ROOT . $imagePath);
        }
    }

    /**
     * @param string $paramName
     * @param string|null $defaultValue
     * @return string
     */
    public function getConfigParam($paramName, $defaultValue = null)
    {
        return isset($this->plugin->oPluginEinstellungAssoc_arr[$paramName]) ? $this->plugin->oPluginEinstellungAssoc_arr[$paramName] : $defaultValue;
    }

    /**
     * @return void
     */
    public function getFrontend()
    {
        $smarty  = $GLOBALS['smarty'];
        $newsArr = $smarty->getVariable('oNewsUebersicht_arr');
    }

    /**
     * HOOK_BACKEND_ACCOUNT_PREPARE_EDIT
     * @param stdClass $oAccount
     * @param JTLSmarty $smarty
     * @param array $attribs
     * @return string
     */
    public function getContent(stdClass $oAccount, JTLSmarty $smarty, array $attribs)
    {
        $showAvatar          = $this->getConfigParam('use_avatar', 'N') === 'Y' ? true : false;
        $showVita            = $this->getConfigParam('use_vita', 'N') === 'Y' ? true : false;
        $showSectionPersonal = $showAvatar || $showVita;
        $gravatarEmail       = !empty($attribs['useGravatarEmail']->cAttribValue) ? $attribs['useGravatarEmail']->cAttribValue : $oAccount->cMail;
        $uploadImage         = $attribs['useAvatar']->cAttribValue === 'U' && !empty($attribs['useAvatarUpload']->cAttribValue) ? $attribs['useAvatarUpload']->cAttribValue : '/' . BILD_UPLOAD_ZUGRIFF_VERWEIGERT;

        $result = $smarty
            ->assign('oAccount', $oAccount)
            ->assign('showAvatar', $showAvatar)
            ->assign('showVita', $showVita)
            ->assign('sectionPersonal', $showSectionPersonal)
            ->assign('gravatarEmail', $gravatarEmail)
            ->assign('uploadImage', $uploadImage)
            ->assign('attribValues', $attribs)
            ->assign('sprachen', gibAlleSprachen())
            ->fetch($this->plugin->cAdminmenuPfad . 'templates/userextension_index.tpl');

        return $result;
    }

    /**
     * HOOK_BACKEND_ACCOUNT_EDIT - VALIDATE
     * @param stdClass $oAccount
     * @param array $attribs
     * @param array $messages
     * @return mixed bool|array - true if success otherwise errormap
     */
    public function validateAccount(stdClass $oAccount, array &$attribs, array &$messages)
    {
        if ($this->getConfigParam('use_avatar', 'N') === 'Y') {
            if (!$attribs['useAvatar']) {
                $attribs['useAvatar'] = 'N';
            }

            switch ($attribs['useAvatar']) {
                case 'G':
                    if (!empty($attribs['useAvatarUpload'])) {
                        $this->deleteImage($attribs['useAvatarUpload']);
                        $attribs['useAvatarUpload'] = '';
                    }
                    break;
                case 'U':
                    $attribs['useGravatarEmail'] = '';

                    if (isset($_FILES['extAttribs']) && !empty($_FILES['extAttribs']['name']['useAvatarUpload'])) {
                        $attribs['useAvatarUpload'] = $this->uploadImage($_FILES['extAttribs'], 'useAvatarUpload');

                        if ($attribs['useAvatarUpload'] !== false) {
                            return true;
                        }
                    } else {
                        if (!empty($attribs['useAvatarUpload'])) {
                            return true;
                        }
                    }

                    $messages['error'] .= 'Bitte geben Sie ein Bild an.';
                    return array('useAvatarUpload' => 1);
                    break;
                default:
                    $attribs['useGravatarEmail'] = '';

                    if (!empty($attribs['useAvatarUpload'])) {
                        $this->deleteImage($attribs['useAvatarUpload']);
                        $attribs['useAvatarUpload'] = '';
                    }
            }
        }

        if ($this->getConfigParam('use_vita', 'N') === 'Y') {
            foreach (gibAlleSprachen() as $sprache) {
                $useVita_ISO = 'useVita_' . $sprache->cISO;

                if (!empty($attribs[$useVita_ISO])) {
                    $shortText = StringHandler::filterXSS($attribs[$useVita_ISO]);
                    $longtText = $attribs[$useVita_ISO];

                    if (strlen($shortText) > 255) {
                        $shortText = substr($shortText, 0, 250) . '...';
                    }

                    $attribs[$useVita_ISO] = [$shortText, $longtText];
                }
            }
        }

        return true;
    }
}
