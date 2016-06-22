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
     * @param string $paramName
     * @param string|null $defaultValue
     * @return string
     */
    public function getConfigParam($paramName, $defaultValue = null)
    {
        return isset($this->plugin->oPluginEinstellungAssoc_arr[$paramName]) ? $this->plugin->oPluginEinstellungAssoc_arr[$paramName] : $defaultValue;
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
        $showSectionPersonal = $showAvatar;
        $gravatarEmail       = !empty($attribs['useGravatarEmail']->cAttribValue) ? $attribs['useGravatarEmail']->cAttribValue : $oAccount->cMail;

        $result = $smarty
            ->assign('showAvatar', $showAvatar)
            ->assign('sectionPersonal', $showSectionPersonal)
            ->assign('gravatarEmail', $gravatarEmail)
            ->assign('attribValues', $attribs)
            ->fetch($this->plugin->cAdminmenuPfad . 'templates/userextension_index.tpl');

        return $result;
    }
}