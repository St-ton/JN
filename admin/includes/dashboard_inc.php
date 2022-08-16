<?php declare(strict_types=1);

use JTL\IO\IOResponse;
use JTL\Shop;

/**
 * @param bool $bActive
 * @param bool $getAll
 * @return array
 * @deprecated since 5.2.0
 */
function getWidgets(bool $bActive = true, bool $getAll = false): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int    $widgetId
 * @param string $container
 * @param int    $pos
 * @deprecated since 5.2.0
 */
function setWidgetPosition(int $widgetId, string $container, int $pos): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}

/**
 * @param int $kWidget
 * @deprecated since 5.2.0
 */
function closeWidget(int $kWidget): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', $kWidget, (object)['bActive' => 0]);
}

/**
 * @param int $kWidget
 * @deprecated since 5.2.0
 */
function addWidget(int $kWidget): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', $kWidget, (object)['bActive' => 1]);
}

/**
 * @param int $kWidget
 * @param int $bExpand
 * @deprecated since 5.2.0
 */
function expandWidget(int $kWidget, int $bExpand): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    Shop::Container()->getDB()->update('tadminwidgets', 'kWidget', $kWidget, (object)['bExpanded' => $bExpand]);
}

/**
 * @param string      $url
 * @param string      $dataName
 * @param string      $tpl
 * @param string      $wrapperID
 * @param string|null $post
 * @return IOResponse
 * @deprecated since 5.2.0
 */
function getRemoteDataIO(string $url, string $dataName, string $tpl, string $wrapperID, $post = null): IOResponse
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new IOResponse();
}

/**
 * @param string $tpl
 * @param string $wrapperID
 * @return IOResponse
 * @deprecated since 5.2.0
 */
function getShopInfoIO(string $tpl, string $wrapperID): IOResponse
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new IOResponse();
}

/**
 * @return IOResponse
 * @deprecated since 5.2.0
 */
function getAvailableWidgetsIO(): IOResponse
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new IOResponse();
}
