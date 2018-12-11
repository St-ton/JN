<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

use DB\DbInterface;
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
     * @param DbInterface  $db
     * @param \Plugin|\Plugin\Extension $oPlugin
     */
    public function __construct($smarty = null, DbInterface $db = null, $oPlugin = null);

    /**
     * @return JTLSmarty
     */
    public function getSmarty(): JTLSmarty;

    /**
     * @param JTLSmarty $oSmarty
     */
    public function setSmarty(JTLSmarty $oSmarty): void;

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @param DbInterface $oDB
     */
    public function setDB(DbInterface $oDB): void;

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
