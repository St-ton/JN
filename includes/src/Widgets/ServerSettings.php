<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

/**
 * Class ServerSettings
 * @package Widgets
 */
class ServerSettings extends AbstractWidget
{
    /**
     * @var \Helpers\PHPSettingsHelper
     */
    private $helper;

    /**
     *
     */
    public function init()
    {
        $this->helper = \Helpers\PHPSettingsHelper::getInstance();
        $this->oSmarty->assign('maxExecutionTime', \ini_get('max_execution_time'))
                      ->assign('bMaxExecutionTime', $this->checkMaxExecutionTime())
                      ->assign('maxFilesize', \ini_get('upload_max_filesize'))
                      ->assign('bMaxFilesize', $this->checkMaxFilesize())
                      ->assign('memoryLimit', \ini_get('memory_limit'))
                      ->assign('bMemoryLimit', $this->checkMemoryLimit())
                      ->assign('postMaxSize', \ini_get('post_max_size'))
                      ->assign('bPostMaxSize', $this->checkPostMaxSize())
                      ->assign('bAllowUrlFopen', $this->checkAllowUrlFopen())
                      ->assign('SOAPCheck', $this->SOAPcheck());
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
    public function checkImageMagick(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function SOAPcheck(): bool
    {
        if (\class_exists('Systemcheck_Environment')) {
            $oSystemCheck  = new \Systemcheck_Environment();
            $vCheckResults = $oSystemCheck->executeTestGroup('Shop4');
            if (\in_array('recommendations', \array_keys($vCheckResults), true)) {
                foreach ($vCheckResults['recommendations'] as $object) {
                    if ($object instanceof \Systemcheck_Tests_Shop4_PhpSoapExtension) {
                        // SOAP is OFF
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function checkMaxExecutionTime(): bool
    {
        return $this->helper->hasMinExecutionTime(60);
    }

    /**
     * @return bool
     */
    public function checkMaxFilesize(): bool
    {
        return $this->helper->hasMinUploadSize(5 * 1024 * 1024);
    }

    /**
     * @return bool
     */
    public function checkMemoryLimit(): bool
    {
        return $this->helper->hasMinLimit(64 * 1024 * 1024);
    }

    /**
     * @return bool
     */
    public function checkPostMaxSize(): bool
    {
        return $this->helper->hasMinPostSize(8 * 1024 * 1024);
    }

    /**
     * @return bool
     */
    public function checkAllowUrlFopen(): bool
    {
        return $this->helper->fopenWrapper();
    }
}
