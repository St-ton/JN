<?php declare(strict_types=1);

namespace JTL\Widgets;

use JsonException;
use JTL\Backend\AdminAccount;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\IO\IOResponse;
use JTL\L10n\GetText;
use JTL\Network\JTLApi;
use JTL\Plugin\Helper;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use SmartyException;
use stdClass;

/**
 * Class Controller
 * @package JTL\Widgets
 */
class Controller
{
    /**
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param GetText           $getText
     * @param JTLSmarty         $smarty
     * @param AdminAccount      $account
     */
    public function __construct(
        private DbInterface $db,
        private JTLCacheInterface $cache,
        private GetText $getText,
        private JTLSmarty $smarty,
        private AdminAccount $account
    ) {
    }

    /**
     * @param bool $active
     * @param bool $getAll
     * @return array
     */
    public function getWidgets(bool $active = true, bool $getAll = false): array
    {
        if (!$getAll || !$this->account->permission('DASHBOARD_VIEW')) {
            return [];
        }
        $loaderLegacy = Helper::getLoader(false, $this->db, $this->cache);
        $loaderExt    = Helper::getLoader(true, $this->db, $this->cache);
        $plugins      = [];

        $widgets = $this->db->getObjects(
            'SELECT tadminwidgets.*, tplugin.cPluginID, tplugin.bExtension
                FROM tadminwidgets
                LEFT JOIN tplugin 
                    ON tplugin.kPlugin = tadminwidgets.kPlugin
                WHERE bActive = :active
                    AND (tplugin.nStatus IS NULL OR tplugin.nStatus = :activated)
                ORDER BY eContainer ASC, nPos ASC',
            ['active' => (int)$active, 'activated' => State::ACTIVATED]
        );

        foreach ($widgets as $widget) {
            $widget->kWidget    = (int)$widget->kWidget;
            $widget->kPlugin    = (int)$widget->kPlugin;
            $widget->nPos       = (int)$widget->nPos;
            $widget->bExpanded  = (int)$widget->bExpanded;
            $widget->bActive    = (int)$widget->bActive;
            $widget->bExtension = (int)$widget->bExtension;
            $widget->plugin     = null;
            if ($widget->cPluginID !== null && \SAFE_MODE === false) {
                if (\array_key_exists($widget->cPluginID, $plugins)) {
                    $widget->plugin = $plugins[$widget->cPluginID];
                } else {
                    if ($widget->bExtension === 1) {
                        $widget->plugin = $loaderExt->init((int)$widget->kPlugin);
                    } else {
                        $widget->plugin = $loaderLegacy->init((int)$widget->kPlugin);
                    }

                    $plugins[$widget->cPluginID] = $widget->plugin;
                }

                if ($widget->bExtension) {
                    $this->getText->loadPluginLocale('widgets/' . $widget->cClass, $widget->plugin);
                }
            } else {
                $this->getText->loadAdminLocale('widgets/' . $widget->cClass);
                $widget->plugin = null;
            }
            $msgid  = $widget->cClass . '_title';
            $msgstr = \__($msgid);
            if ($msgid !== $msgstr) {
                $widget->cTitle = $msgstr;
            }
            $msgid  = $widget->cClass . '_desc';
            $msgstr = \__($msgid);
            if ($msgid !== $msgstr) {
                $widget->cDescription = $msgstr;
            }
        }
        if (!$active) {
            return $widgets;
        }
        foreach ($widgets as $key => $widget) {
            $widget->cContent = '';
            $className        = '\JTL\Widgets\\' . $widget->cClass;
            $classPath        = null;
            if ($widget->plugin !== null) {
                $hit = $widget->plugin->getWidgets()->getWidgetByID($widget->kWidget);
                if ($hit !== null) {
                    $className = $hit->className;
                    $classPath = $hit->classFile;
                    if (\file_exists($classPath)) {
                        require_once $classPath;
                    }
                }
            }
            if (\class_exists($className)) {
                /** @var AbstractWidget $instance */
                $instance = new $className($this->smarty, $this->db, $widget->plugin);
                if ($getAll
                    || \in_array($instance->getPermission(), ['DASHBOARD_ALL', ''], true)
                    || $this->account->permission($instance->getPermission())
                ) {
                    $widget->cContent = $instance->getContent();
                    $widget->hasBody  = $instance->hasBody;
                } else {
                    unset($widgets[$key]);
                }
            }
        }

        return $widgets;
    }

    /**
     * @param int    $id
     * @param string $container
     * @param int    $pos
     */
    public function setWidgetPosition(int $id, string $container, int $pos): void
    {
        $upd             = new stdClass();
        $upd->eContainer = $container;
        $upd->nPos       = $pos;

        $current = $this->db->select('tadminwidgets', 'kWidget', $id);
        if ($current->eContainer === $container) {
            if ($current->nPos < $pos) {
                $this->db->queryPrepared(
                    'UPDATE tadminwidgets
                    SET nPos = nPos - 1
                    WHERE eContainer = :currentContainer
                      AND nPos > :currentPos
                      AND nPos <= :newPos',
                    [
                        'currentPos'       => $current->nPos,
                        'newPos'           => $pos,
                        'currentContainer' => $current->eContainer
                    ]
                );
            } else {
                $this->db->queryPrepared(
                    'UPDATE tadminwidgets
                        SET nPos = nPos + 1
                        WHERE eContainer = :currentContainer
                          AND nPos < :currentPos
                          AND nPos >= :newPos',
                    [
                        'currentPos'       => $current->nPos,
                        'newPos'           => $pos,
                        'currentContainer' => $current->eContainer
                    ]
                );
            }
        } else {
            $this->db->queryPrepared(
                'UPDATE tadminwidgets
                    SET nPos = nPos - 1
                    WHERE eContainer = :currentContainer
                      AND nPos > :currentPos',
                [
                    'currentPos'       => $current->nPos,
                    'currentContainer' => $current->eContainer
                ]
            );
            $this->db->queryPrepared(
                'UPDATE tadminwidgets
                    SET nPos = nPos + 1
                    WHERE eContainer = :newContainer
                      AND nPos >= :newPos',
                [
                    'newPos'       => $pos,
                    'newContainer' => $container
                ]
            );
        }

        $this->db->update('tadminwidgets', 'kWidget', $id, $upd);
    }

    /**
     * @param int $id
     */
    public function closeWidget(int $id): void
    {
        $this->db->update('tadminwidgets', 'kWidget', $id, (object)['bActive' => 0]);
    }

    /**
     * @param int $id
     */
    public function addWidget(int $id): void
    {
        $this->db->update('tadminwidgets', 'kWidget', $id, (object)['bActive' => 1]);
    }

    /**
     * @param int $id
     * @param int $expand
     */
    public function expandWidget(int $id, int $expand): void
    {
        $this->db->update('tadminwidgets', 'kWidget', $id, (object)['bExpanded' => $expand]);
    }

    /**
     * @param string      $url
     * @param string      $dataName
     * @param string      $tpl
     * @param string      $wrapperID
     * @param string|null $post
     * @return IOResponse
     * @throws SmartyException
     */
    public function getRemoteDataIO(
        string $url,
        string $dataName,
        string $tpl,
        string $wrapperID,
        $post = null
    ): IOResponse {
        $this->getText->loadAdminLocale('widgets');
        $response    = new IOResponse();
        $urlsToCache = ['oNews_arr', 'oMarketplace_arr', 'oMarketplaceUpdates_arr', 'oPatch_arr', 'oHelp_arr'];
        if (\in_array($dataName, $urlsToCache, true)) {
            $cacheID = \str_replace('/', '_', $dataName . '_' . $tpl . '_' . \md5($wrapperID . $url));
            if (($remoteData = $this->cache->get($cacheID)) === false) {
                $remoteData = Request::http_get_contents($url, 15, $post);
                $this->cache->set($cacheID, $remoteData, [\CACHING_GROUP_OBJECT], 3600);
            }
        } else {
            $remoteData = Request::http_get_contents($url, 15, $post);
        }

        if (\str_starts_with($remoteData, '<?xml')) {
            $data = \simplexml_load_string($remoteData);
        } else {
            try {
                $data = \json_decode($remoteData, false, 512, \JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $data = null;
            }
        }
        $wrapper = $this->smarty->assign($dataName, $data)->fetch('tpl_inc/' . $tpl);
        $response->assignDom($wrapperID, 'innerHTML', $wrapper);

        return $response;
    }

    /**
     * @param string $tpl
     * @param string $wrapperID
     * @return IOResponse
     * @throws SmartyException
     */
    public function getShopInfoIO(string $tpl, string $wrapperID): IOResponse
    {
        $this->getText->loadAdminLocale('widgets');
        $response = new IOResponse();
        /** @var JTLApi $api */
        $api           = Shop::Container()->get(JTLApi::class);
        $latestVersion = $api->getLatestVersion();
        $wrapper       = $this->smarty->assign('oSubscription', $api->getSubscription())
            ->assign('oVersion', $latestVersion)
            ->assign('strLatestVersion', $latestVersion->getOriginalVersion())
            ->assign('bUpdateAvailable', $api->hasNewerVersion())
            ->fetch('tpl_inc/' . $tpl);

        return $response->assignDom($wrapperID, 'innerHTML', $wrapper);
    }

    /**
     * @return IOResponse
     * @throws SmartyException
     */
    public function getAvailableWidgetsIO(): IOResponse
    {
        $response = new IOResponse();
        $wrapper  = $this->smarty->assign('oAvailableWidget_arr', $this->getWidgets(false))
            ->fetch('tpl_inc/widget_selector.tpl');

        return $response->assignDom('available-widgets', 'innerHTML', $wrapper);
    }
}
