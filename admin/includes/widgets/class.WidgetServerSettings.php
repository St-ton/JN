<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetServerSettings
 */
class WidgetServerSettings extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('maxExecutionTime', ini_get('max_execution_time'))
                      ->assign('bMaxExecutionTime', $this->checkMaxExecutionTime())
                      ->assign('maxFilesize', ini_get('upload_max_filesize'))
                      ->assign('bMaxFilesize', $this->checkMaxFilesize())
                      ->assign('memoryLimit', ini_get('memory_limit'))
                      ->assign('bMemoryLimit', $this->checkMemoryLimit())
                      ->assign('postMaxSize', ini_get('post_max_size'))
                      ->assign('bPostMaxSize', $this->checkPostMaxSize())
                      ->assign('bAllowUrlFopen', $this->checkAllowUrlFopen());
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/serversettings.tpl');
    }

    /**
     * @return bool
     * @deprecated - ImageMagick is not required anymore
     */
    public function checkImageMagick()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function checkMaxExecutionTime()
    {
        return Shop()->PHPSettingsHelper()->hasMinExecutionTime(60);
    }

    /**
     * @return bool
     */
    public function checkMaxFilesize()
    {
        return Shop()->PHPSettingsHelper()->hasMinUploadSize(5 * 1024 * 1024);
    }

    /**
     * @return bool
     */
    public function checkMemoryLimit()
    {
        return Shop()->PHPSettingsHelper()->hasMinLimit(64 * 1024 * 1024);
    }

    /**
     * @return bool
     */
    public function checkPostMaxSize()
    {
        return Shop()->PHPSettingsHelper()->hasMinPostSize(8 * 1024 * 1024);
    }

    /**
     * @return bool
     */
    public function checkAllowUrlFopen()
    {
        return Shop()->PHPSettingsHelper()->fopenWrapper();
    }
}
