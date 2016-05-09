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
        $this->oSmarty->assign('maxExecutionTime', ini_get('max_execution_time'));
        $this->oSmarty->assign('bMaxExecutionTime', $this->checkMaxExecutionTime());
        $this->oSmarty->assign('maxFilesize', ini_get('upload_max_filesize'));
        $this->oSmarty->assign('bMaxFilesize', $this->checkMaxFilesize());
        $this->oSmarty->assign('memoryLimit', ini_get('memory_limit'));
        $this->oSmarty->assign('bMemoryLimit', $this->checkMemoryLimit());
        $this->oSmarty->assign('postMaxSize', ini_get('post_max_size'));
        $this->oSmarty->assign('bPostMaxSize', $this->checkPostMaxSize());
        $this->oSmarty->assign('bAllowUrlFopen', $this->checkAllowUrlFopen());
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
        return (ini_get('max_execution_time') >= 60 || ini_get('max_execution_time') <= 0);
    }

    /**
     * @return bool
     */
    public function checkMaxFilesize()
    {
        $upload_max_filesize = ini_get('upload_max_filesize');
        $cLast               = substr($upload_max_filesize, -1);

        return !(substr($upload_max_filesize, 0, strrpos($upload_max_filesize, $cLast)) < 5 && (strtolower($cLast) === 'm' || strtolower($cLast) === 'g'));
    }

    /**
     * @return bool
     */
    public function checkMemoryLimit()
    {
        $memory_limit = ini_get('memory_limit');
        $cLast        = substr($memory_limit, -1);

        return ($memory_limit == -1 || substr($memory_limit, 0, strrpos($memory_limit, $cLast)) >= 64);
    }

    /**
     * @return bool
     */
    public function checkPostMaxSize()
    {
        $post_max_size = ini_get('post_max_size');
        $cLast         = substr($post_max_size, -1);

        return !(substr($post_max_size, 0, strrpos($post_max_size, $cLast)) < 8 && (strtolower($cLast) === 'm' || strtolower($cLast) === 'g'));
    }

    /**
     * @return bool
     */
    public function checkAllowUrlFopen()
    {
        return (ini_get('allow_url_fopen') > 0);
    }
}
