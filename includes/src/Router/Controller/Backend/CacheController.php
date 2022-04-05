<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Backend\DirManager;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Minify\MinifyService;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class CacheController
 * @package JTL\Router\Controller\Backend
 */
class CacheController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('OBJECTCACHE_VIEW');
        $this->getText->loadAdminLocale('pages/cache');

        $notice         = '';
        $error          = '';
        $step           = 'uebersicht';
        $tab            = Request::postVar('tab', 'uebersicht');
        $cache          = null;
        $opcacheStats   = null;
        $options        = null;
        $action         = Form::validateToken() ? Request::postVar('a') : null;
        $cacheAction    = Request::postVar('cache-action', '');
        $settingManager = new Manager($this->db, $smarty, $this->account, $this->getText, $this->alertService);
        $postData       = Text::filterXSS($_POST);
        $sectionFactory = new SectionFactory();
        $cacheSection   = $sectionFactory->getSection(\CONF_CACHING, $settingManager);
        $this->getText->loadConfigLocales();
        if (0 < mb_strlen(Request::verifyGPDataString('tab'))) {
            $smarty->assign('tab', Request::verifyGPDataString('tab'));
        }

        try {
            $cache = Shop::Container()->getCache();
            $cache->setJtlCacheConfig($this->db->selectAll('teinstellungen', 'kEinstellungenSektion', \CONF_CACHING));
        } catch (Exception $exc) {
            $this->alertService->addError(\__('exception') . ': ' . $exc->getMessage(), 'errorException');
        }
        // get disabled cache types
        $deactivated       = $this->db->select(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [\CONF_CACHING, 'caching_types_disabled']
        );
        $currentlyDisabled = [];
        if (\is_object($deactivated) && isset($deactivated->cWert)) {
            $currentlyDisabled = ($deactivated->cWert !== '')
                ? \unserialize($deactivated->cWert)
                : [];
        }
        switch ($action) {
            case 'cacheMassAction':
                //mass action cache flush
                $tab = 'massaction';
                switch ($cacheAction) {
                    case 'flush':
                        if (GeneralObject::isCountable('cache-types', $postData)) {
                            $okCount = 0;
                            foreach ($postData['cache-types'] as $cacheType) {
                                $hookInfo = ['type' => $cacheType, 'key' => null, 'isTag' => true];
                                $flush    = $cache->flushTags([$cacheType], $hookInfo);
                                if ($flush === false) {
                                    $this->alertService->addError(
                                        \sprintf(\__('errorCacheTypeDelete'), $cacheType),
                                        'errorCacheTypeDelete'
                                    );
                                } else {
                                    $okCount++;
                                }
                            }
                            if ($okCount > 0) {
                                $this->alertService->addSuccess($okCount . \__('successCacheEmptied'), 'successCacheEmptied');
                            }
                        } else {
                            $this->alertService->addError(\__('errorNoCacheType'), 'errorNoCacheType');
                        }
                        break;
                    case 'activate':
                        if (\is_array(Request::postVar('cache-types'))) {
                            foreach ($postData['cache-types'] as $cacheType) {
                                $index = \array_search($cacheType, $currentlyDisabled, true);
                                if (\is_int($index)) {
                                    unset($currentlyDisabled[$index]);
                                }
                            }
                            $upd        = new stdClass();
                            $upd->cWert = \serialize($currentlyDisabled);
                            $res        = $this->db->update(
                                'teinstellungen',
                                ['kEinstellungenSektion', 'cName'],
                                [\CONF_CACHING, 'caching_types_disabled'],
                                $upd
                            );
                            if ($res > 0) {
                                $this->alertService->addSuccess(\__('successCacheTypeActivate'), 'successCacheTypeActivate');
                            }
                        } else {
                            $this->alertService->addError(\__('errorNoCacheType'), 'errorNoCacheType');
                        }
                        break;
                    case 'deactivate':
                        if (GeneralObject::isCountable('cache-types', $postData)) {
                            foreach ($postData['cache-types'] as $cacheType) {
                                $cache->flushTags([$cacheType]);
                                $currentlyDisabled[] = $cacheType;
                            }
                            $currentlyDisabled = \array_unique($currentlyDisabled);
                            $upd               = new stdClass();
                            $upd->cWert        = \serialize($currentlyDisabled);
                            $res               = $this->db->update(
                                'teinstellungen',
                                ['kEinstellungenSektion', 'cName'],
                                [\CONF_CACHING, 'caching_types_disabled'],
                                $upd
                            );
                            if ($res > 0) {
                                $this->alertService->addSuccess(\__('successCacheTypeDeactivate'), 'successCacheTypeDeactivate');
                            }
                        } else {
                            $this->alertService->addError(\__('errorNoCacheType'), 'errorNoCacheType');
                        }
                        break;
                    default:
                        break;
                }
                break;
            case 'flush_object_cache':
                $tab = 'massaction';
                if ($cache !== null && $cache->flushAll() !== false) {
                    $this->alertService->addSuccess(\__('successCacheDelete'), 'successCacheDelete');
                } else {
                    $this->alertService->addError(\__('errorCacheDelete'), 'errorCacheDelete');
                }
                break;
            case 'settings':
                if (Request::postVar('resetSetting') !== null) {
                    $settingManager->resetSetting(Request::postVar('resetSetting'));
                    break;
                }
                foreach ($cacheSection->update($_POST) as $item) {
                    if ($item['id'] !== 'caching_method') {
                        continue;
                    }
                    if ($item['value'] === 'auto') {
                        $value            = new stdClass();
                        $availableMethods = [];
                        $allMethods       = $cache->checkAvailability();
                        foreach ($allMethods as $name => $state) {
                            if (isset($state['available'], $state['functional'])
                                && $state['available'] === true
                                && $state['functional'] === true
                            ) {
                                $availableMethods[] = $name;
                            }
                        }
                        if (count($availableMethods) > 0) {
                            $value->cWert = 'null';
                            if (\in_array('redis', $availableMethods, true)) {
                                $value->cWert = 'redis';
                            } elseif (\in_array('memcache', $availableMethods, true)) {
                                $value->cWert = 'memcache';
                            } elseif (\in_array('memcached', $availableMethods, true)) {
                                $value->cWert = 'memcached';
                            } elseif (\in_array('apc', $availableMethods, true)) {
                                $value->cWert = 'apc';
                            } elseif (\in_array('advancedfile', $availableMethods, true)) {
                                $value->cWert = 'advancedfile';
                            } elseif (\in_array('file', $availableMethods, true)) {
                                $value->cWert = 'file';
                            }
                        } else {
                            $value->cWert = 'null';
                        }
                        $this->db->update(
                            'teinstellungen',
                            ['kEinstellungenSektion', 'cName'],
                            [\CONF_CACHING, 'caching_method'],
                            $value
                        );
                        if ($value->cWert !== 'null') {
                            $this->alertService->addSuccess(
                                '<strong>' . $value->cWert . '</strong>' . \__('successCacheMethodSave'),
                                'successCacheDelete'
                            );
                        } else {
                            $this->alertService->addError(\__('errorCacheMethodSelect'), 'errorCacheMethodSelect');
                        }
                    }
                }
                $cache->flushAll();
                $cache->setJtlCacheConfig($this->db->selectAll('teinstellungen', 'kEinstellungenSektion', \CONF_CACHING));
                $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
                $cache->flushTags([\CACHING_GROUP_OPTION]);
                $tab = 'settings';
                break;
            case 'benchmark':
                // do benchmarks
                $tab      = 'benchmark';
                $methods  = 'all';
                $repeat   = Request::postInt('repeat', 1);
                $runCount = Request::postInt($postData['runcount'], 1000);
                switch ($postData['testdata'] ?? 'string') {
                    case 'array':
                        $testData = ['test1' => 'string number one', 'test2' => 'string number two', 'test3' => 333];
                        break;
                    case 'object':
                        $testData        = new stdClass();
                        $testData->test1 = 'string number one';
                        $testData->test2 = 'string number two';
                        $testData->test3 = 333;
                        break;
                    case 'string':
                    default:
                        $testData = 'simple short string';
                        break;
                }
                if (\is_array(Request::postVar('methods'))) {
                    $methods = $postData['methods'];
                }
                if ($cache !== null) {
                    $benchResults = $cache->benchmark($methods, $testData, $runCount, $repeat, false, true);
                    $smarty->assign('bench_results', $benchResults);
                }
                break;
            case 'flush_template_cache':
                // delete all template cachefiles
                $callback     = static function (array $pParameters) {
                    if (\strpos($pParameters['filename'], '.') === 0) {
                        return;
                    }
                    if (!$pParameters['isdir']) {
                        if (@\unlink($pParameters['path'] . $pParameters['filename'])) {
                            $pParameters['count']++;
                        } else {
                            $pParameters['error'] .= \sprintf(
                                \__('errorFileDelete'),
                                '<strong>' . $pParameters['path'] . $pParameters['filename'] . '</strong>'
                            ) . '<br/>';
                        }
                    } elseif (!@\rmdir($pParameters['path'] . $pParameters['filename'])) {
                        $pParameters['error'] .= \sprintf(
                            \__('errorDirDelete'),
                            '<strong>' . $pParameters['path'] . $pParameters['filename'] . '</strong>'
                        ) . '<br/>';
                    }
                };
                $deleteCount  = 0;
                $cbParameters = [
                    'count'  => &$deleteCount,
                    'notice' => &$notice,
                    'error'  => &$error
                ];
                $template     = Shop::Container()->getTemplateService()->getActiveTemplate();
                $dirMan       = new DirManager();
                $dirMan->getData(PFAD_ROOT . \PFAD_COMPILEDIR . $template->getDir(), $callback, $cbParameters);
                $dirMan->getData(PFAD_ROOT . \PFAD_ADMIN . \PFAD_COMPILEDIR, $callback, $cbParameters);
                $ms = new MinifyService();
                $ms->flushCache();
                $this->alertService->addError($error, 'errorCache');
                $this->alertService->addNotice($notice, 'noticeCache');
                $this->alertService->addSuccess(
                    \sprintf(
                        \__('successTemplateCacheDelete'),
                        \number_format($cbParameters['count'])
                    ),
                    'successTemplateCacheDelete'
                );
                break;
            default:
                break;
        }
        if ($cache !== null) {
            $options = $cache->getOptions();
            $smarty->assign('method', \ucfirst($options['method']))
                ->assign('all_methods', $cache->getAllMethods())
                ->assign('stats', $cache->getStats());
        }

        $cacheSection = $sectionFactory->getSection(\CONF_CACHING, $settingManager);
        $cacheSection->load();
        $advancedSettings = [];
        $settings         = [];
        foreach ($cacheSection->getSubsections() as $subsection) {
            foreach ($subsection->getItems() as $item) {
                if ($item->getShowDefault() === 0 || $item->getShowDefault() === 2) {
                    $advancedSettings[] = $item;
                } elseif ($item->getShowDefault() === 1) {
                    $settings[] = $item;
                }
            }
        }
        if (\function_exists('opcache_get_status')) {
            $data                       = \opcache_get_status();
            $opcacheStats               = new stdClass();
            $opcacheStats->enabled      = isset($data['opcache_enabled'])
                && $data['opcache_enabled'] === true;
            $opcacheStats->memoryFree   = isset($data['memory_usage']['free_memory'])
                ? \round($data['memory_usage']['free_memory'] / 1024 / 1024, 2)
                : -1;
            $opcacheStats->memoryUsed   = isset($data['memory_usage']['used_memory'])
                ? \round($data['memory_usage']['used_memory'] / 1024 / 1024, 2)
                : -1;
            $opcacheStats->numberScrips = $data['opcache_statistics']['num_cached_scripts'] ?? -1;
            $opcacheStats->numberKeys   = $data['opcache_statistics']['num_cached_keys'] ?? -1;
            $opcacheStats->hits         = $data['opcache_statistics']['hits'] ?? -1;
            $opcacheStats->misses       = $data['opcache_statistics']['misses'] ?? -1;
            $opcacheStats->hitRate      = isset($data['opcache_statistics']['opcache_hit_rate'])
                ? \round($data['opcache_statistics']['opcache_hit_rate'], 2)
                : -1;
            $opcacheStats->scripts      = GeneralObject::isCountable('scripts', $data)
                ? $data['scripts']
                : [];
        }

        $tplcacheStats           = new stdClass();
        $tplcacheStats->frontend = [];
        $tplcacheStats->backend  = [];

        $callback = static function (array $pParameters) {
            if (!$pParameters['isdir']) {
                $fileObj           = new stdClass();
                $fileObj->filename = $pParameters['filename'];
                $fileObj->path     = $pParameters['path'];
                $fileObj->fullname = $pParameters['path'] . $pParameters['filename'];

                $pParameters['files'][] = $fileObj;
            }
        };

        $template = Shop::Container()->getTemplateService()->getActiveTemplate();
        $dirMan   = new DirManager();
        $dirMan->getData(\PFAD_ROOT . \PFAD_COMPILEDIR . $template->getDir(), $callback, ['files' => &$tplcacheStats->frontend])
            ->getData(\PFAD_ROOT . \PFAD_ADMIN . \PFAD_COMPILEDIR, $callback, ['files' => &$tplcacheStats->backend]);
        $allMethods           = $cache->checkAvailability();
        $availableMethods     = [];
        $disFunctionalMethods = [];
        $nonAvailableMethods  = [];
        foreach ($allMethods as $name => $state) {
            if ($name === 'null') {
                continue;
            }
            if ($state['functional'] === true) {
                $availableMethods[] = $name;
            } elseif ($state['available'] === true) {
                $disFunctionalMethods[] = $name;
            } else {
                $nonAvailableMethods[] = $name;
            }
        }
        $cachingGroups = $cache->getCachingGroups();
        foreach ($cachingGroups as &$cachingGroup) {
            $cachingGroup['key_count'] = count($cache->getKeysByTag([\constant($cachingGroup['name'])]));
        }
        unset($cachingGroup);
        if (!empty($cache->getError())) {
            $this->alertService->addError($cache->getError(), 'errorCache');
        }

        return $smarty->assign('settings', $settings)
            ->assign('caching_groups', $cachingGroups)
            ->assign('cache_enabled', isset($options['activated']) && $options['activated'] === true)
            ->assign('options', $options)
            ->assign('opcache_stats', $opcacheStats)
            ->assign('tplcacheStats', $tplcacheStats)
            ->assign('functional_methods', \json_encode($availableMethods))
            ->assign('disfunctional_methods', \json_encode($disFunctionalMethods))
            ->assign('non_available_methods', \json_encode($nonAvailableMethods))
            ->assign('advanced_settings', $advancedSettings)
            ->assign('disabled_caches', $currentlyDisabled)
            ->assign('step', $step)
            ->assign('tab', $tab)
            ->assign('route', $route->getPath())
            ->getResponse('cache.tpl');
    }
}
