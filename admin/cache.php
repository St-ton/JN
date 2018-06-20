<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
/** @global JTLSmarty $smarty */
setzeSprache();
$oAccount->permission('OBJECTCACHE_VIEW', true, true);
$notice       = '';
$error        = '';
$cacheAction  = '';
$step         = 'uebersicht';
$tab          = 'uebersicht';
$action       = (isset($_POST['a']) && FormHelper::validateToken()) ? $_POST['a'] : null;
$cache        = null;
$opcacheStats = null;
if (0 < strlen(RequestHelper::verifyGPDataString('tab'))) {
    $smarty->assign('tab', RequestHelper::verifyGPDataString('tab'));
}
try {
    $cache = Shop::Container()->getCache();
    $cache->setJtlCacheConfig();
} catch (Exception $exc) {
    $error = 'Ausnahme: ' . $exc->getMessage();
}
//get disabled cache types
$deactivated       = Shop::Container()->getDB()->select(
    'teinstellungen',
    ['kEinstellungenSektion', 'cName'],
    [CONF_CACHING, 'caching_types_disabled']
);
$currentlyDisabled = [];
if (is_object($deactivated) && isset($deactivated->cWert)) {
    $currentlyDisabled = ($deactivated->cWert !== '')
        ? unserialize($deactivated->cWert)
        : [];
}
if ($action !== null && isset($_POST['cache-action'])) {
    $cacheAction = $_POST['cache-action'];
}
switch ($action) {
    case 'cacheMassAction' :
        //mass action cache flush
        $tab = 'massaction';
        switch ($cacheAction) {
            case 'flush' :
                if (isset($_POST['cache-types']) && is_array($_POST['cache-types'])) {
                    $okCount = 0;
                    foreach ($_POST['cache-types'] as $cacheType) {
                        $hookInfo = ['type' => $cacheType, 'key' => null, 'isTag' => true];
                        $flush    = $cache->flushTags([$cacheType], $hookInfo);
                        if ($flush === false) {
                            $error .= '<br />Konnte Cache "' . $cacheType . '" nicht l&ouml;schen (evtl. bereits leer).';
                        } else {
                            $okCount++;
                        }
                    }
                    if ($okCount > 0) {
                        $notice .= $okCount . ' Caches erfolgreich geleert.';
                    }
                } else {
                    $error .= 'Kein Cache-Typ ausgew&auml;hlt.';
                }
                break;
            case 'activate' :
                if (isset($_POST['cache-types']) && is_array($_POST['cache-types'])) {
                    foreach ($_POST['cache-types'] as $cacheType) {
                        $index = array_search($cacheType, $currentlyDisabled, true);
                        if (is_int($index)) {
                            unset($currentlyDisabled[$index]);
                        }
                    }
                    $upd        = new stdClass();
                    $upd->cWert = serialize($currentlyDisabled);
                    $res        = Shop::Container()->getDB()->update(
                        'teinstellungen',
                        ['kEinstellungenSektion', 'cName'],
                        [CONF_CACHING, 'caching_types_disabled'],
                        $upd
                    );
                    if ($res > 0) {
                        $notice .= 'Ausgew&auml;hlte Typen erfolgreich aktiviert.';
                    }
                } else {
                    $error .= 'Kein Cache-Typ ausgew&auml;hlt.';
                }
                break;
            case 'deactivate' :
                if (isset($_POST['cache-types']) && is_array($_POST['cache-types'])) {
                    foreach ($_POST['cache-types'] as $cacheType) {
                        $cache->flushTags([$cacheType]);
                        $currentlyDisabled[] = $cacheType;
                    }
                    $currentlyDisabled = array_unique($currentlyDisabled);
                    $upd               = new stdClass();
                    $upd->cWert        = serialize($currentlyDisabled);
                    $res               = Shop::Container()->getDB()->update(
                        'teinstellungen',
                        ['kEinstellungenSektion', 'cName'],
                        [CONF_CACHING, 'caching_types_disabled'],
                        $upd
                    );
                    if ($res > 0) {
                        $notice .= 'Ausgew&auml;hlte Typen erfolgreich deaktiviert.';
                    }
                } else {
                    $error .= 'Kein Cache-Typ ausgew&auml;hlt.';
                }
                break;
            default :
                break;
        }
        break;
    case 'flush_object_cache' :
        $tab = 'massaction';
        if ($cache !== null && $cache->flushAll() !== false) {
            $notice = 'Object Cache wurde erfolgreich gelöscht.';
        } else {
            if (0 < strlen($error)) {
                $error .= '<br />';
            }
            $error .= 'Der Cache konnte nicht gelöscht werden.';
        }
        break;
    case 'settings' :
        $settings = Shop::Container()->getDB()->selectAll(
            'teinstellungenconf',
            ['kEinstellungenSektion', 'cConf'],
            [CONF_CACHING, 'Y'],
            '*',
            'nSort'
        );
        $i             = 0;
        $settingsCount = count($settings);
        while ($i < $settingsCount) {
            if (isset($_POST[$settings[$i]->cWertName])) {
                $value                        = new stdClass();
                $value->cWert                 = $_POST[$settings[$i]->cWertName];
                $value->cName                 = $settings[$i]->cWertName;
                $value->kEinstellungenSektion = CONF_CACHING;
                switch ($settings[$i]->cInputTyp) {
                    case 'kommazahl' :
                        $value->cWert = (float)$value->cWert;
                        break;
                    case 'zahl' :
                    case 'number':
                        $value->cWert = (int)$value->cWert;
                        break;
                    case 'text' :
                        $value->cWert = (strlen($value->cWert) > 0) ? substr($value->cWert, 0, 255) : $value->cWert;
                        break;
                    case 'listbox' :
                        bearbeiteListBox($value->cWert, $settings[$i]->cWertName, CONF_CACHING);
                        break;
                }
                if ($value->cName === 'caching_method' && $value->cWert === 'auto') {
                    $availableMethods = [];
                    $allMethods       = $cache->checkAvailability();
                    foreach ($allMethods as $_name => $_status) {
                        if (isset($_status['available'], $_status['functional'])
                            && $_status['available'] === true
                            && $_status['functional'] === true
                        ) {
                            $availableMethods[] = $_name;
                        }
                    }
                    if (count($availableMethods) > 0) {
                        $value->cWert = 'null';
                        if (in_array('redis', $availableMethods, true)) {
                            $value->cWert = 'redis';
                        } elseif (in_array('memcache', $availableMethods, true)) {
                            $value->cWert = 'memcache';
                        } elseif (in_array('memcached', $availableMethods, true)) {
                            $value->cWert = 'memcached';
                        } elseif (in_array('apc', $availableMethods, true)) {
                            $value->cWert = 'apc';
                        } elseif (in_array('xcache', $availableMethods, true)) {
                            $value->cWert = 'xcache';
                        } elseif (in_array('advancedfile', $availableMethods, true)) {
                            $value->cWert = 'advancedfile';
                        }  elseif (in_array('file', $availableMethods, true)) {
                            $value->cWert = 'file';
                        }
                    } else {
                        $value->cWert = 'null';
                    }
                    if ($value->cWert !== 'null') {
                        $notice .= '<strong>' . $value->cWert . '</strong> wurde als Cache-Methode gespeichert.<br />';
                    } else {
                        $notice .= 'Konnte keine funktionierende Cache-Methode ausw&auml;hlen.';
                    }
                }
                Shop::Container()->getDB()->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [CONF_CACHING, $settings[$i]->cWertName]
                );
                Shop::Container()->getDB()->insert('teinstellungen', $value);
            }
            ++$i;
        }
        $cache->flushAll();
        $cache->setJtlCacheConfig();
        $notice .= 'Ihre Einstellungen wurden &uuml;bernommen.<br />';
        $tab = 'settings';
        break;
    case 'benchmark' :
        //do benchmarks
        $tab      = 'benchmark';
        $testData = 'simple short string';
        $runCount = 1000;
        $repeat   = 1;
        $methods  = 'all';
        if (isset($_POST['repeat'])) {
            $repeat = (int)$_POST['repeat'];
        }
        if (isset($_POST['runcount'])) {
            $runCount = (int)$_POST['runcount'];
        }
        if (isset($_POST['testdata'])) {
            switch ($_POST['testdata']) {
                case 'array' :
                    $testData = ['test1' => 'string number one', 'test2' => 'string number two', 'test3' => 333];
                    break;
                case 'object' :
                    $testData        = new stdClass();
                    $testData->test1 = 'string number one';
                    $testData->test2 = 'string number two';
                    $testData->test3 = 333;
                    break;
                case 'string' :
                default :
                    $testData = 'simple short string';
                    break;
            }
        }
        if (isset($_POST['methods']) && is_array($_POST['methods'])) {
            $methods = $_POST['methods'];
        }
        if ($cache !== null) {
            $benchResults = $cache->benchmark($methods, $testData, $runCount, $repeat, false, true);
            $smarty->assign('bench_results', $benchResults);
        }
        break;
    case 'flush_template_cache' :
        // delete all template cachefiles
        $callback = function (array $pParameters) {
            if (!$pParameters['isdir']) {
                if (@unlink($pParameters['path'] . $pParameters['filename'])) {
                    $pParameters['count']++;
                } else {
                    $pParameters['error'] .= 'Datei <strong>' . $pParameters['path'] . $pParameters['filename'] .
                        '</strong> konnte nicht gelöscht werden!<br/>';
                }
            } elseif (!@rmdir($pParameters['path'] . $pParameters['filename'])) {
                $pParameters['error'] .= 'Verzeichnis <strong>' . $pParameters['path'] . $pParameters['filename'] .
                    '</strong> konnte nicht gelöscht werden!<br/>';
            }
        };
        $deleteCount  = 0;
        $cbParameters = [
            'count'  => &$deleteCount,
            'notice' => &$notice,
            'error'  => &$error
        ];
        $template    = Template::getInstance();
        $templateDir = $template->getDir();
        $dirMan      = new DirManager();
        $dirMan->getData(PFAD_ROOT . PFAD_COMPILEDIR . $templateDir, $callback, $cbParameters);
        $dirMan->getData(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR, $callback, $cbParameters);
        $notice .= 'Es wurden <strong>' .
            number_format($cbParameters['count']) .
            '</strong> Dateien im Templatecache gelöscht!';
        break;
    default:
        break;
}
if ($cache !== null) {
    $options = $cache->getOptions();
    $smarty->assign('method', ucfirst($options['method']))
           ->assign('all_methods', $cache->getAllMethods())
           ->assign('stats', $cache->getStats());
}
$settings      = Shop::Container()->getDB()->selectAll(
    'teinstellungenconf',
    ['nStandardAnzeigen', 'kEinstellungenSektion'],
    [1, CONF_CACHING],
    '*',
    'nSort'
);
foreach ($settings as $i => $setting) {
    if ($setting->cName === 'caching_types_disabled') {
        unset($settings[$i]);
        continue;
    }
    if ($setting->cInputTyp === 'selectbox') {
        $setting->ConfWerte = Shop::Container()->getDB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$setting->kEinstellungenConf,
            '*',
            'nSort'
        );
    }
    $oSetValue = Shop::Container()->getDB()->select(
        'teinstellungen',
        ['kEinstellungenSektion', 'cName'],
        [CONF_CACHING, $setting->cWertName]
    );
    $setting->gesetzterWert = $oSetValue->cWert ?? null;
}
$advancedSettings = Shop::Container()->getDB()->query(
    "SELECT * 
        FROM teinstellungenconf 
        WHERE (nStandardAnzeigen = 0 OR nStandardAnzeigen = 2)
            AND kEinstellungenSektion = " . CONF_CACHING . "
        ORDER BY nSort",
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
$settingsCount    = count($advancedSettings);
for ($i = 0; $i < $settingsCount; ++$i) {
    if ($advancedSettings[$i]->cInputTyp === 'selectbox') {
        $advancedSettings[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$advancedSettings[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
    }
    $oSetValue = Shop::Container()->getDB()->select(
        'teinstellungen',
        ['kEinstellungenSektion', 'cName'],
        [CONF_CACHING, $advancedSettings[$i]->cWertName]
    );
    $advancedSettings[$i]->gesetzterWert = $oSetValue->cWert ?? null;
}
if (function_exists('opcache_get_status')) {
    $_opcacheStatus             = opcache_get_status();
    $opcacheStats               = new stdClass();
    $opcacheStats->enabled      = isset($_opcacheStatus['opcache_enabled']) && $_opcacheStatus['opcache_enabled'] === true;
    $opcacheStats->memoryFree   = isset($_opcacheStatus['memory_usage']['free_memory'])
        ? round($_opcacheStatus['memory_usage']['free_memory'] / 1024 / 1024, 2)
        : -1;
    $opcacheStats->memoryUsed   = isset($_opcacheStatus['memory_usage']['used_memory'])
        ? round($_opcacheStatus['memory_usage']['used_memory'] / 1024 / 1024, 2)
        : -1;
    $opcacheStats->numberScrips = $_opcacheStatus['opcache_statistics']['num_cached_scripts'] ?? -1;
    $opcacheStats->numberKeys   = $_opcacheStatus['opcache_statistics']['num_cached_keys'] ?? -1;
    $opcacheStats->hits         = $_opcacheStatus['opcache_statistics']['hits'] ?? -1;
    $opcacheStats->misses       = $_opcacheStatus['opcache_statistics']['misses'] ?? -1;
    $opcacheStats->hitRate      = isset($_opcacheStatus['opcache_statistics']['opcache_hit_rate'])
        ? round($_opcacheStatus['opcache_statistics']['opcache_hit_rate'], 2)
        : -1;
    $opcacheStats->scripts      = (isset($_opcacheStatus['scripts']) && is_array($_opcacheStatus['scripts']))
        ? $_opcacheStatus['scripts']
        : [];
}

$tplcacheStats           = new stdClass();
$tplcacheStats->frontend = [];
$tplcacheStats->backend  = [];

$callback = function (array $pParameters) {
    if (!$pParameters['isdir']) {
        $fileObj           = new stdClass();
        $fileObj->filename = $pParameters['filename'];
        $fileObj->path     = $pParameters['path'];
        $fileObj->fullname = $pParameters['path'] . $pParameters['filename'];

        $pParameters['files'][] = $fileObj;
    }
};

$template    = Template::getInstance();
$templateDir = $template->getDir();
$dirMan      = new DirManager();
$dirMan->getData(PFAD_ROOT . PFAD_COMPILEDIR . $template->getDir(), $callback, ['files' => &$tplcacheStats->frontend])
       ->getData(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR, $callback, ['files' => &$tplcacheStats->backend]);

$allMethods          = $cache->checkAvailability();
$availableMethods    = [];
$nonAvailableMethods = [];
foreach ($allMethods as $_name => $_status) {
    if (isset($_status['available'], $_status['functional'])
        && $_status['available'] === true
        && $_status['functional'] === true
    ) {
        $availableMethods[] = $_name;
    } elseif ($_name !== 'null') {
        $nonAvailableMethods[] = $_name;
    }
}
$smarty->assign('settings', $settings)
       ->assign('caching_groups', (($cache !== null) ? $cache->getCachingGroups() : []))
       ->assign('cache_enabled', isset($options['activated']) && $options['activated'] === true)
       ->assign('show_page_cache', $settings)
       ->assign('options', $options)
       ->assign('opcache_stats', $opcacheStats)
       ->assign('tplcacheStats', $tplcacheStats)
       ->assign('available_methods', json_encode($availableMethods))
       ->assign('non_available_methods', json_encode($nonAvailableMethods))
       ->assign('advanced_settings', $advancedSettings)
       ->assign('disabled_caches', $currentlyDisabled)
       ->assign('cHinweis', $notice)
       ->assign('cFehler', $error)
       ->assign('step', $step)
       ->assign('tab', $tab)
       ->display('cache.tpl');
