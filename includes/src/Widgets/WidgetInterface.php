<?php
/**
 * Created by PhpStorm.
 * User: MO
 * Date: 2018-12-05
 * Time: 15:30
 */

namespace Widgets;

use Plugin\AbstractExtension;
use Smarty\JTLSmarty;

/**
 * Class AbstractWidget
 *
 * @package Widgets
 */
interface WidgetInterface
{
    /**
     * @param \Smarty\JTLSmarty $smarty
     * @param \DB\DbInterface  $db
     * @param \Plugin|\Plugin\Extension $oPlugin
     */
    public function __construct($smarty = null, $db = null, $oPlugin = null);

    /**
     * @return JTLSmarty
     */
    public function getSmarty(): JTLSmarty;

    /**
     * @param JTLSmarty $oSmarty
     */
    public function setSmarty(JTLSmarty $oSmarty): void;

    /**
     * @return \DB\DbInterface
     */
    public function getDB(): \DB\DbInterface;

    /**
     * @param \DB\DbInterface $oDB
     */
    public function setDB(\DB\DbInterface $oDB): void;

    /**
     * @return AbstractExtension
     */
    public function getPlugin(): AbstractExtension;

    /**
     * @param AbstractExtension $oPlugin
     */
    public function setPlugin(AbstractExtension $oPlugin): void;

    /**
     *
     */
    public function init();

    /**
     * @return string
     */
    public function getContent();
}