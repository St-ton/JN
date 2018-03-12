<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_DBES . 'xml_tools.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

define('PLUGIN_CODE_OK', 1);
define('PLUGIN_CODE_WRONG_PARAM', 2);
define('PLUGIN_CODE_DIR_DOES_NOT_EXIST', 3);
define('PLUGIN_CODE_INFO_XML_MISSING', 4);
define('PLUGIN_CODE_NO_PLUGIN_FOUND', 5);
define('PLUGIN_CODE_INVALID_NAME', 6);
define('PLUGIN_CODE_INVALID_PLUGIN_ID', 7);
define('PLUGIN_CODE_INSTALL_NODE_MISSING', 8);
define('PLUGIN_CODE_INVALID_XML_VERSION_NUMBER', 9);
define('PLUGIN_CODE_INVALID_VERSION_NUMBER', 10);
define('PLUGIN_CODE_INVALID_DATE', 11);
define('PLUGIN_CODE_MISSING_SQL_FILE', 12);
define('PLUGIN_CODE_MISSING_HOOKS', 13);
define('PLUGIN_CODE_INVALID_HOOK', 14);
define('PLUGIN_CODE_INVALID_CUSTOM_LINK_NAME', 15);
define('PLUGIN_CODE_INVALID_CUSTOM_LINK_FILE_NAME', 16);
define('PLUGIN_CODE_MISSING_CUSTOM_LINK_FILE', 17);
define('PLUGIN_CODE_INVALID_CONFIG_LINK_NAME', 18);
define('PLUGIN_CODE_MISSING_CONFIG', 19);
define('PLUGIN_CODE_INVALID_CONFIG_TYPE', 20);
define('PLUGIN_CODE_INVALID_CONFIG_INITIAL_VALUE', 21);
define('PLUGIN_CODE_INVALID_CONFIG_SORT_VALUE', 22);
define('PLUGIN_CODE_INVALID_CONFIG_NAME', 23);
define('PLUGIN_CODE_MISSING_CONFIG_SELECTBOX_OPTIONS', 24);
define('PLUGIN_CODE_INVALID_CONFIG_OPTION', 25);
define('PLUGIN_CODE_MISSING_LANG_VARS', 26);
define('PLUGIN_CODE_INVALID_LANG_VAR_NAME', 27);
define('PLUGIN_CODE_MISSING_LOCALIZED_LANG_VAR', 28);
define('PLUGIN_CODE_INVALID_LANG_VAR_ISO', 29);
define('PLUGIN_CODE_INVALID_LOCALIZED_LANG_VAR_NAME', 30);
define('PLUGIN_CODE_MISSING_HOOK_FILE', 31);
define('PLUGIN_CODE_MISSING_VERSION_DIR', 32);
define('PLUGIN_CODE_INVALID_CONF', 33);
define('PLUGIN_CODE_INVALID_CONF_VALUE_NAME', 34);
define('PLUGIN_CODE_INVALID_XML_VERSION', 35);
//@tod: 35 vs 9
define('PLUGIN_CODE_INVALID_SHOP_VERSION', 36);
define('PLUGIN_CODE_SHOP_VERSION_COMPATIBILITY', 37);
define('PLUGIN_CODE_MISSING_FRONTEND_LINKS', 38);
define('PLUGIN_CODE_INVALID_FRONTEND_LINK_FILENAME', 39);
define('PLUGIN_CODE_INVALID_FRONTEND_LINK_NAME', 40);
define('PLUGIN_CODE_INVALID_FRONEND_LINK_VISIBILITY', 41);
define('PLUGIN_CODE_INVALID_FRONEND_LINK_PRINT', 42);
define('PLUGIN_CODE_INVALID_FRONEND_LINK_ISO', 43);
define('PLUGIN_CODE_INVALID_FRONEND_LINK_SEO', 44);
define('PLUGIN_CODE_INVALID_FRONEND_LINK_NAME', 45);
define('PLUGIN_CODE_INVALID_FRONEND_LINK_TITLE', 46);
define('PLUGIN_CODE_INVALID_FRONEND_LINK_META_TITLE', 47);
define('PLUGIN_CODE_INVALID_FRONEND_LINK_META_KEYWORDS', 48);
define('PLUGIN_CODE_INVALID_FRONEND_LINK_META_DESCRIPTION', 49);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_NAME', 50);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_MAIL', 51);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_TSCODE', 52);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_PRE_ORDER', 53);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_CLASS_FILE', 54);
define('PLUGIN_CODE_MISSING_PAYMENT_METHOD_FILE', 55);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_TEMPLATE', 56);
define('PLUGIN_CODE_MISSING_PAYMENT_METHOD_TEMPLATE', 57);
define('PLUGIN_CODE_MISSING_PAYMENT_METHOD_LANGUAGES',58 );
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_LANGUAGE_ISO', 59);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_NAME_LOCALIZED', 60);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_CHARGE_NAME', 61);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_INFO_TEXT', 62);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_TYPE', 63);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_INITITAL_VALUE', 64);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_SORT', 65);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_CONF', 66);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_NAME', 67);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_VALUE_NAME', 68);
define('PLUGIN_CODE_MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS', 69); //@todo
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION', 70);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_SORT', 71);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_SOAP', 72);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_CURL', 73);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_SOCKETS', 74);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_CLASS_NAME', 75);
define('PLUGIN_CODE_INVALID_FULLSCREEN_TEMPLATE', 76);
define('PLUGIN_CODE_MISSING_FRONTEND_LINK_TEMPLATE', 77);
define('PLUGIN_CODE_TOO_MANY_FULLSCREEN_TEMPLATE_NAMES', 78);
define('PLUGIN_CODE_INVALID_FULLSCREEN_TEMPLATE_NAME', 79);
define('PLUGIN_CODE_MISSING_FULLSCREEN_TEMPLATE_FILE', 80);
define('PLUGIN_CODE_INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE', 81);
define('PLUGIN_CODE_MISSING_BOX', 82);
define('PLUGIN_CODE_INVALID_BOX_NAME', 83);
define('PLUGIN_CODE_INVALID_BOX_TEMPLATE', 84);
define('PLUGIN_CODE_MISSING_BOX_TEMPLATE_FILE', 85);
define('PLUGIN_CODE_MISSING_LICENCE_FILE', 86);
define('PLUGIN_CODE_INVALID_LICENCE_FILE_NAME', 87);
define('PLUGIN_CODE_MISSING_LICENCE', 88);
define('PLUGIN_CODE_MISSING_LICENCE_CHECKLICENCE_METHOD', 89);
define('PLUGIN_CODE_DUPLICATE_PLUGIN_ID', 90);
define('PLUGIN_CODE_MISSING_EMAIL_TEMPLATES', 91);
define('PLUGIN_CODE_INVALID_TEMPLATE_NAME', 92);
define('PLUGIN_CODE_INVALID_TEMPLATE_TYPE', 93);
define('PLUGIN_CODE_INVALID_TEMPLATE_MODULE_ID', 94);
define('PLUGIN_CODE_INVALID_TEMPLATE_ACTIVE', 95);
define('PLUGIN_CODE_INVALID_TEMPLATE_AKZ', 96);
define('PLUGIN_CODE_INVALID_TEMPLATE_AGB', 97);
define('PLUGIN_CODE_INVALID_TEMPLATE_WRB', 98);
define('PLUGIN_CODE_INVALID_EMAIL_TEMPLATE_ISO', 99);
define('PLUGIN_CODE_INVALID_EMAIL_TEMPLATE_SUBJECT', 100);
define('PLUGIN_CODE_MISSING_EMAIL_TEMPLATE_LANGUAGE', 101);
define('PLUGIN_CODE_INVALID_CHECKBOX_FUNCTION_NAME', 102);
define('PLUGIN_CODE_INVALID_CHECKBOX_FUNCTION_ID', 103);
define('PLUGIN_CODE_INVALID_FRONTEND_LINK_NO_FOLLOW', 104);
define('PLUGIN_CODE_MISSING_WIDGETS', 105);
define('PLUGIN_CODE_INVALID_WIDGET_TITLE', 106);
define('PLUGIN_CODE_INVALID_WIDGET_CLASS', 107);
define('PLUGIN_CODE_MISSING_WIDGET_CLASS_FILE', 108);
define('PLUGIN_CODE_INVALID_WIDGET_CONTAINER', 109);
define('PLUGIN_CODE_INVALID_WIDGET_POS', 110);
define('PLUGIN_CODE_INVALID_WIDGET_EXPANDED', 111);
define('PLUGIN_CODE_INVALID_WIDGET_ACTIVE', 112);
define('PLUGIN_CODE_INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE', 113);
define('PLUGIN_CODE_MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE', 114);
define('PLUGIN_CODE_MISSING_FORMATS', 115);
define('PLUGIN_CODE_INVALID_FORMAT_NAME', 116);
define('PLUGIN_CODE_INVALID_FORMAT_FILE_NAME', 117);
define('PLUGIN_CODE_MISSING_FORMAT_CONTENT', 118);
define('PLUGIN_CODE_INVALID_FORMAT_ENCODING', 119);
define('PLUGIN_CODE_INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY', 120);
define('PLUGIN_CODE_INVALID_FORMAT_CONTENT_FILE', 121);
define('PLUGIN_CODE_MISSING_EXTENDED_TEMPLATE', 122);
define('PLUGIN_CODE_INVALID_EXTENDED_TEMPLATE_FILE_NAME', 123);
define('PLUGIN_CODE_MISSING_EXTENDED_TEMPLATE_FILE', 124);
define('PLUGIN_CODE_MISSING_UNINSTALL_FILE', 125);
define('PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE', 126);
define('PLUGIN_CODE_IONCUBE_REQUIRED', 127);
define('PLUGIN_CODE_INVALID_OPTIONS_SOURE_FILE', 128);
define('PLUGIN_CODE_MISSING_OPTIONS_SOURE_FILE', 129);
define('PLUGIN_CODE_MISSING_BOOTSTRAP_CLASS', 130);
define('PLUGIN_CODE_INVALID_BOOTSTRAP_IMPLEMENTATION', 131);
define('PLUGIN_CODE_INVALID_AUTHOR', 132);

define('PLUGIN_CODE_SQL_MISSING_DATA', 2);
define('PLUGIN_CODE_SQL_ERROR', 3);
define('PLUGIN_CODE_SQL_WRONG_TABLE_NAME_DELETE', 4);
define('PLUGIN_CODE_SQL_WRONG_TABLE_NAME_CREATE', 5);
define('PLUGIN_CODE_SQL_INVALID_FILE_CONTENT', 6);


/**
 * sanitize names from plugins downloaded via gitlab
 *
 * @param array $p_event
 * @param array $p_header
 * @return int
 */
function pluginPreExtractCallBack ($p_event, &$p_header) {
    //plugins downloaded from gitlab have -[BRANCHNAME]-[COMMIT_ID] in their name.
    //COMMIT_ID should be 40 characters
    preg_match('/(.*)-master-([a-zA-Z0-9]{40})\/(.*)/', $p_header['filename'], $hits);
    if (count($hits) >= 3) {
        $p_header['filename'] = str_replace('-master-' . $hits[2], '', $p_header['filename']);
    }

    return 1;
}

/**
 * @param string $zipFile
 * @return stdClass
 */
function extractPlugin($zipFile)
{
    $response                 = new stdClass();
    $response->status         = 'OK';
    $response->error          = null;
    $response->files_unpacked = [];
    $response->files_failed   = [];
    $response->messages       = [];

    $unzipPath = PFAD_ROOT . PFAD_PLUGIN;

    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if (!$zip->open($zipFile) || $zip->numFiles === 0) {
            $response->status     = 'FAILED';
            $response->messages[] = 'Cannot open archive';
        } else {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if ($i === 0 && strpos($zip->getNameIndex($i), '.') !== false) {
                    $response->status     = 'FAILED';
                    $response->messages[] = 'Invalid archive';

                    return $response;
                }
                $filename = $zip->getNameIndex($i);
                preg_match('/(.*)-master-([a-zA-Z0-9]{40})\/(.*)/', $filename, $hits);
                if (count($hits) >= 3) {
                    $zip->renameIndex($i, str_replace('-master-' . $hits[2], '', $filename));
                }
                $filename = $zip->getNameIndex($i);
                if ($zip->extractTo($unzipPath, $filename)) {
                    $response->files_unpacked[] = $filename;
                } else {
                    $response->files_failed = $filename;
                }
            }
            $zip->close();
        }

        return $response;
    }

    $zip     = new PclZip($zipFile);
    $content = $zip->listContent();
    if (!is_array($content) || !isset($content[0]['filename']) || strpos($content[0]['filename'], '.') !== false) {
        $response->status     = 'FAILED';
        $response->messages[] = 'Invalid archive';
    } else {
        $res     = $zip->extract(PCLZIP_OPT_PATH, $unzipPath, PCLZIP_CB_PRE_EXTRACT, 'pluginPreExtractCallBack');
        $success = [];
        $fail    = [];
        if ($res !== 0) {
            foreach ($res as $_file) {
                if ($_file['status'] === 'ok' || $_file['status'] === 'already_a_directory') {
                    $response->files_unpacked[] = $_file;
                } else {
                    $response->files_failed[] = $_file;
                }
            }
        } else {
            $response->status   = 'FAILED';
            $response->errors[] = 'Got unzip error code: ' . $zip->errorCode();
        }
    }

    return $response;
}


/**
 * @return array
 */
function gibInstalliertePlugins()
{
    $oPlugin_arr    = [];
    $oPluginTMP_arr = Shop::DB()->selectAll('tplugin', [], [], 'kPlugin', 'cName, cAutor, nPrio');
    if (count($oPluginTMP_arr) > 0) {
        foreach ($oPluginTMP_arr as $oPluginTMP) {
            $oPlugin_arr[] = new Plugin($oPluginTMP->kPlugin);
        }
    }

    return $oPlugin_arr;
}

/**
 * @see gibAllePlugins
 *
 * @param array $PluginInstalliert_arr
 * @param bool  $bFehlerhaft - Falls bFehlerhaft = true => gib nur fehlerhafte Plugins zurück
 * @return array - array von Plugins
 * @deprecated since 4.06 - use gibAllePlugins instead
 */
function gibVerfuegbarePlugins($PluginInstalliert_arr, $bFehlerhaft = false)
{
    static $allPlugins = null;

    if (!isset($allPlugins)) {
        $allPlugins = gibAllePlugins($PluginInstalliert_arr);
    }

    return $bFehlerhaft ? $allPlugins->fehlerhaft : $allPlugins->verfuegbar;
}

/**
 * Läuft im Ordner PFAD_ROOT/includes/plugins/ alle Verzeichnisse durch und gibt korrekte Plugins zurück
 *
 * @param array $PluginInstalliert_arr
 * @return object - {installiert[], verfuegbar[], fehlerhaft[]}
 */
function gibAllePlugins($PluginInstalliert_arr)
{
    $cPfad   = PFAD_ROOT . PFAD_PLUGIN;
    $Plugins = (object)[
        'index'       => [],
        'installiert' => [],
        'verfuegbar'  => [],
        'fehlerhaft'  => [],
    ];

    if (is_dir($cPfad)) {
        $Dir               = opendir($cPfad);
        $cInstalledPlugins = array_map(function ($item) {
            return $item->cVerzeichnis;
        }, $PluginInstalliert_arr);

        while (($cVerzeichnis = readdir($Dir)) !== false) {
            if ($cVerzeichnis !== '.' && $cVerzeichnis !== '..') {
                $cXML = $cPfad . $cVerzeichnis . '/' . PLUGIN_INFO_FILE;
                // Ist eine info.xml Datei vorhanden? Wenn nicht, ist das Plugin fehlerhaft und wird nicht angezeigt
                if (file_exists($cXML)) {
                    $xml          = file_get_contents($cXML);
                    $XML_arr      = XML_unserialize($xml);
                    $XML_arr      = getArrangedArray($XML_arr);
                    $nReturnValue = pluginPlausi(0, $cPfad . $cVerzeichnis);
                    if ($nReturnValue === PLUGIN_CODE_DUPLICATE_PLUGIN_ID && in_array($cVerzeichnis, $cInstalledPlugins, true)) {
                        $XML_arr['cVerzeichnis']       = $cVerzeichnis;
                        $XML_arr['shop4compatible']    = isset($XML_arr['jtlshop3plugin'][0]['Shop4Version']);
                        $Plugins->index[$cVerzeichnis] = makeXMLToObj($XML_arr);
                        $Plugins->installiert[]        =& $Plugins->index[$cVerzeichnis];
                    } elseif ($nReturnValue === PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE
                        || $nReturnValue === PLUGIN_CODE_OK
                    ) {
                        $XML_arr['cVerzeichnis']       = $cVerzeichnis;
                        $XML_arr['shop4compatible']    = ($nReturnValue === 1);
                        $Plugins->index[$cVerzeichnis] = makeXMLToObj($XML_arr);
                        $Plugins->verfuegbar[]         =& $Plugins->index[$cVerzeichnis];
                    } elseif ($nReturnValue !== PLUGIN_CODE_OK
                        && $nReturnValue !== PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE
                    ) {
                        $XML_arr['cVerzeichnis']       = $cVerzeichnis;
                        $XML_arr['cFehlercode']        = $nReturnValue;
                        $Plugins->index[$cVerzeichnis] = makeXMLToObj($XML_arr);
                        $Plugins->fehlerhaft[]         = & $Plugins->index[$cVerzeichnis];
                    }
                }
            }
        }
        // Pluginsortierung nach Name
        usort($Plugins->installiert, function ($left, $right) {
            return strcasecmp($left->cName, $right->cName);
        });
        usort($Plugins->verfuegbar, function ($left, $right) {
            return strcasecmp($left->cName, $right->cName);
        });
        usort($Plugins->fehlerhaft, function ($left, $right) {
            return strcasecmp($left->cName, $right->cName);
        });
    }

    return $Plugins;
}

/**
 * @param int    $kPlugin
 * @param string $cVerzeichnis
 * @return int
 */
function pluginPlausi($kPlugin, $cVerzeichnis = '')
{
    // Plugin kommt aus der Datenbank
    $kPlugin = (int)$kPlugin;
    if ($kPlugin > 0) {
        // Plugin aus der DB holen
        $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
        if (empty($oPlugin->kPlugin)) {
            return PLUGIN_CODE_NO_PLUGIN_FOUND; // Kein Plugin in der DB anhand von kPlugin gefunden
        }
        $basePath = PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis;
        if (!is_dir($basePath)) {
            return PLUGIN_CODE_DIR_DOES_NOT_EXIST;
        }
        $cInfofile = $basePath . '/' . PLUGIN_INFO_FILE;
        if (!file_exists($cInfofile)) {
            return PLUGIN_CODE_INFO_XML_MISSING;
        }
        $xml     = file_get_contents($cInfofile);
        $XML_arr = XML_unserialize($xml);
        $XML_arr = getArrangedArray($XML_arr);
        // Interne Plugin Plausi
        return pluginPlausiIntern($XML_arr, $basePath);
    }
    if (empty($cVerzeichnis)) {
        return PLUGIN_CODE_WRONG_PARAM;
    }
    // Plugin wird anhand des Verzeichnisses geprüft
    if (!is_dir($cVerzeichnis)) {
        return PLUGIN_CODE_DIR_DOES_NOT_EXIST;
    }
    $cInfofile = "{$cVerzeichnis}/" . PLUGIN_INFO_FILE;
    if (!file_exists($cInfofile)) {
        return PLUGIN_CODE_INFO_XML_MISSING;
    }
    $xml     = file_get_contents($cInfofile);
    $XML_arr = XML_unserialize($xml);
    $XML_arr = getArrangedArray($XML_arr);
    // Interne Plugin Plausi
    return pluginPlausiIntern($XML_arr, $cVerzeichnis);
}

/**
 * @param array  $XML_arr
 * @param string $cVerzeichnis
 * @return int
 */
function pluginPlausiIntern($XML_arr, $cVerzeichnis)
{
    $cVersionsnummer        = '';
    $isShop4Compatible      = false;
    $requiresMissingIoncube = false;
    $nXMLShopVersion        = 0; // Shop-Version die das Plugin braucht um zu laufen
    $nShopVersion           = 0; // Aktuelle Shop-Version
    $baseNode               = $XML_arr['jtlshop3plugin'][0];
    // Shopversion holen
    $oVersion = Shop::DB()->query("SELECT nVersion FROM tversion LIMIT 1", 1);

    if ($oVersion->nVersion > 0) {
        $nShopVersion = (int)$oVersion->nVersion;
    }
    // XML-Versionsnummer
    if (!isset($baseNode['XMLVersion'])) {
        return PLUGIN_CODE_INVALID_XML_VERSION;
    }
    preg_match('/[0-9]{3}/', $baseNode['XMLVersion'], $cTreffer_arr);
    if (count($cTreffer_arr) === 0
        || (strlen($cTreffer_arr[0]) !== strlen($baseNode['XMLVersion']) && (int)$baseNode['XMLVersion'] >= 100)
    ) {
        return PLUGIN_CODE_INVALID_XML_VERSION;
    }
    $nXMLVersion = (int)$XML_arr['jtlshop3plugin'][0]['XMLVersion'];
    if (empty($baseNode['ShopVersion']) && empty($baseNode['Shop4Version'])) {
        return PLUGIN_CODE_INVALID_SHOP_VERSION;
    }
    if ((isset($baseNode['ShopVersion'])
            && strlen($cTreffer_arr[0]) !== strlen($baseNode['ShopVersion'])
            && (int)$baseNode['ShopVersion'] >= 300
        )
        || (isset($baseNode['Shop4Version'])
            && strlen($cTreffer_arr[0]) !== strlen($baseNode['Shop4Version'])
            && (int)$baseNode['Shop4Version'] >= 300)
    ) {
        return PLUGIN_CODE_INVALID_SHOP_VERSION; //Shop-Version entspricht nicht der Konvention
    }
    if (isset($baseNode['Shop4Version'])) {
        $nXMLShopVersion   = (int)$baseNode['Shop4Version'];
        $isShop4Compatible = true;
    } else {
        $nXMLShopVersion = (int)$baseNode['ShopVersion'];
    }
    $installNode = $baseNode['Install'][0];
    //check if plugin need ioncube loader but extension is not loaded
    if (isset($baseNode['LicenceClassFile']) && !extension_loaded('ionCube Loader')) {
        //ioncube is not loaded
        $nLastVersionKey    = count($installNode['Version']) / 2 - 1;
        $nLastPluginVersion = (int)$installNode['Version'][$nLastVersionKey . ' attr']['nr'];
        //try to read license file
        if (file_exists($cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
            PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile'])
        ) {
            $content = file_get_contents($cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile']);
            //ioncube encoded files usually have a header that checks loaded extions itself
            //but it can also be in short form, where there are no opening php tags
            $requiresMissingIoncube = ((strpos($content, 'ionCube') !== false
                    && strpos($content, 'extension_loaded') !== false)
                || strpos($content, '<?php') === false);
        }
    }
    // Shop-Version ausreichend?
    if (!$nShopVersion || !$nXMLShopVersion || $nXMLShopVersion > $nShopVersion) {
        return PLUGIN_CODE_SHOP_VERSION_COMPATIBILITY; //Shop-Version ist zu niedrig
    }
    if (!isset($baseNode['Author'])) {
        return PLUGIN_CODE_INVALID_AUTHOR;
    }
    // Prüfe Pluginname
    if (!isset($baseNode['Name'])) {
        return PLUGIN_CODE_INVALID_NAME;
    }
    preg_match("/[a-zA-Z0-9äÄüÜöÖß" . "\(\)_ -]+/",
        $baseNode['Name'],
        $cTreffer_arr
    );
    if (!isset($cTreffer_arr[0]) || strlen($cTreffer_arr[0]) !== strlen($baseNode['Name'])) {
        return PLUGIN_CODE_INVALID_NAME;
    }
    // Prüfe PluginID
    preg_match("/[a-zA-Z0-9_]+/", $baseNode['PluginID'], $cTreffer_arr);
    if (empty($baseNode['PluginID']) || strlen($cTreffer_arr[0]) !== strlen($baseNode['PluginID'])) {
        return PLUGIN_CODE_INVALID_PLUGIN_ID;
    }
    // Existiert die PluginID bereits?
    $oPluginTMP = Shop::DB()->select('tplugin', 'cPluginID', $baseNode['PluginID']);

    if (isset($oPluginTMP->kPlugin) && $oPluginTMP->kPlugin > 0) {
        return PLUGIN_CODE_DUPLICATE_PLUGIN_ID;
    }
    if (!isset($installNode['Version']) || !is_array($installNode['Version'])) {
        return PLUGIN_CODE_INVALID_VERSION_NUMBER;
    }

    //Finde aktuelle Version
    $nLastVersionKey    = count($installNode['Version']) / 2 - 1;
    $nLastPluginVersion = (int)$installNode['Version'][$nLastVersionKey . ' attr']['nr'];

    if (isset($baseNode['LicenceClassFile'])
        && strlen($baseNode['LicenceClassFile']) > 0
    ) {
        //Existiert die Lizenzdatei?
        if (!file_exists($cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
            PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile'])
        ) {
            return PLUGIN_CODE_MISSING_LICENCE_FILE;
        }
        //Klassenname gesetzt?
        if (empty($baseNode['LicenceClass'])
            || $baseNode['LicenceClass'] !== $baseNode['PluginID'] . PLUGIN_LICENCE_CLASS
        ) {
            return PLUGIN_CODE_INVALID_LICENCE_FILE_NAME;
        }
        if ($requiresMissingIoncube) {
            return PLUGIN_CODE_IONCUBE_REQUIRED;
        }
        require_once $cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile'];
        // Existiert die Klasse?
        if (!class_exists($baseNode['LicenceClass'])) {
            return PLUGIN_CODE_MISSING_LICENCE;
        }
        //Methode checkLicence defininiert?
        $cClassMethod_arr = get_class_methods($baseNode['LicenceClass']);
        $bClassMethod     = (is_array($cClassMethod_arr)
            && count($cClassMethod_arr) > 0
            && in_array(PLUGIN_LICENCE_METHODE, $cClassMethod_arr, true));
        if (!$bClassMethod) {
            return PLUGIN_CODE_MISSING_LICENCE_CHECKLICENCE_METHOD;
        }
    }

    //Prüfe Bootstrapper
    $cBootstrapNamespace = $baseNode['PluginID'];
    $cBootstrapClassFile = $cVerzeichnis . '/' . PFAD_PLUGIN_VERSION .
        $nLastPluginVersion . '/' . PLUGIN_BOOTSTRAPPER;

    if (is_file($cBootstrapClassFile)) {
        $cClass = sprintf('%s\\%s', $cBootstrapNamespace, 'Bootstrap');

        require_once $cBootstrapClassFile;

        if (!class_exists($cClass)) {
            return PLUGIN_CODE_MISSING_BOOTSTRAP_CLASS;
        }

        $bootstrapper = new $cClass((object)['cPluginID' => $cBootstrapNamespace]);

        if (!is_subclass_of($bootstrapper, 'AbstractPlugin')) {
            return PLUGIN_CODE_INVALID_BOOTSTRAP_IMPLEMENTATION;
        }
    }

    //Prüfe Install Knoten
    if (!isset($baseNode['Install']) || !is_array($baseNode['Install'])) {
        return PLUGIN_CODE_INSTALL_NODE_MISSING;
    }
    // Versionen definiert?
    if (isset($installNode['Version'])
        && is_array($installNode['Version'])
        && count($installNode['Version']) > 0
    ) {
        //Ist die 1. Versionsnummer korrekt?
        if ((int)$installNode['Version']['0 attr']['nr'] !== 100) {
            return PLUGIN_CODE_INVALID_XML_VERSION_NUMBER;
        }
        //Laufe alle Versionen durch
        foreach ($installNode['Version'] as $i => $Version) {
            preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
            preg_match("/[0-9]+/", $i, $cTreffer2_arr);
            if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                $cVersionsnummer = $Version['nr'];
                // Entpricht die Versionsnummer
                preg_match("/[0-9]+/", $Version['nr'], $cTreffer_arr);
                if (strlen($cTreffer_arr[0]) !== strlen($Version['nr'])) {
                    return PLUGIN_CODE_INVALID_VERSION_NUMBER;
                }
            } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                // Prüfe SQL und CreateDate
                if (isset($Version['SQL']) &&
                    strlen($Version['SQL']) > 0 &&
                    !file_exists($cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                        PFAD_PLUGIN_SQL . $Version['SQL'])
                 ) {
                    return PLUGIN_CODE_MISSING_SQL_FILE;
                }
                // Prüfe Versionsordner
                if (!is_dir($cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $cVersionsnummer)) {
                    return PLUGIN_CODE_MISSING_VERSION_DIR;
                }
                preg_match('/[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}/',
                    $Version['CreateDate'],
                    $cTreffer_arr
                );
                if (!isset($cTreffer_arr[0]) || strlen($cTreffer_arr[0]) !== strlen($Version['CreateDate'])) {
                    return PLUGIN_CODE_INVALID_DATE;
                }
            }
        }
    }
    //Auf Hooks prüfen
    if (isset($installNode['Hooks'])
        && is_array($installNode['Hooks'])
    ) {
        if (count($installNode['Hooks'][0]) === 1) {
            //Es gibt mehr als einen Hook
            foreach ($installNode['Hooks'][0]['Hook'] as $i => $Hook_arr) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    if (strlen($Hook_arr['id']) === 0) {
                        return PLUGIN_CODE_INVALID_HOOK;
                    }
                } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($i)) {
                    if (strlen($Hook_arr) === 0) {
                        return PLUGIN_CODE_INVALID_HOOK;
                    }
                    //Hook include Datei vorhanden?
                    if (!file_exists($cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                        PFAD_PLUGIN_FRONTEND . $Hook_arr)
                    ) {
                        return PLUGIN_CODE_MISSING_HOOK_FILE;
                    }
                }
            }
        } elseif (count($installNode['Hooks'][0]) > 1) {
            //Es gibt nur einen Hook
            $Hook_arr = $installNode['Hooks'][0];
            //Hook-Name und ID prüfen
            if ((int)$Hook_arr['Hook attr']['id'] === 0 || strlen($Hook_arr['Hook']) === 0) {
                return PLUGIN_CODE_INVALID_HOOK;
            }
            //Hook include Datei vorhanden?
            if (!file_exists($cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                PFAD_PLUGIN_FRONTEND . $Hook_arr['Hook'])
            ) {
                return PLUGIN_CODE_MISSING_HOOK_FILE;
            }
        }
    }
    //Plausi Adminmenü & Einstellungen (falls vorhanden)
    if (isset($installNode['Adminmenu'])
        && is_array($installNode['Adminmenu'])
    ) {
        //Adminsmenüs vorhanden?
        if (isset($installNode['Adminmenu'][0]['Customlink'])
            && is_array($installNode['Adminmenu'][0]['Customlink'])
            && count($installNode['Adminmenu'][0]['Customlink']) > 0
        ) {
            $nSort = 0;
            foreach ($installNode['Adminmenu'][0]['Customlink'] as $i => $Customlink_arr) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    $nSort = (int)$Customlink_arr['sort'];
                } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                    // Name prüfen
                    preg_match("/[a-zA-Z0-9äÄüÜöÖß" . "\_\- ]+/",
                        $Customlink_arr['Name'],
                        $cTreffer_arr
                    );
                    if (strlen($cTreffer_arr[0]) !== strlen($Customlink_arr['Name']) || empty($Customlink_arr['Name'])) {
                        return PLUGIN_CODE_INVALID_CUSTOM_LINK_NAME;
                    }
                    if (empty($Customlink_arr['Filename'])) {
                        return PLUGIN_CODE_INVALID_CUSTOM_LINK_FILE_NAME;
                    }
                    if (!file_exists($cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                        PFAD_PLUGIN_ADMINMENU . $Customlink_arr['Filename'])
                    ) {
                        return PLUGIN_CODE_MISSING_CUSTOM_LINK_FILE;
                    }
                }
            }
        }
        // Einstellungen vorhanden?
        if (isset($installNode['Adminmenu'][0]['Settingslink'])
            && is_array($installNode['Adminmenu'][0]['Settingslink'])
            && count($installNode['Adminmenu'][0]['Settingslink']) > 0
        ) {
            $nSort = 0;
            foreach ($installNode['Adminmenu'][0]['Settingslink'] as $i => $Settingslink_arr) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                preg_match("/[0-9]+/", $i, $cTreffer2_arr);

                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    $nSort = (int)$Settingslink_arr['sort'];
                } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                    // EinstellungsLink Name prüfen
                    if (empty($Settingslink_arr['Name'])) {
                        return PLUGIN_CODE_INVALID_CONFIG_LINK_NAME;
                    }
                    // Einstellungen prüfen
                    $cTyp = '';
                    if (!isset($Settingslink_arr['Setting'])
                        || !is_array($Settingslink_arr['Setting'])
                        || count($Settingslink_arr['Setting']) === 0
                    ) {
                        return PLUGIN_CODE_MISSING_CONFIG;
                    }
                    foreach ($Settingslink_arr['Setting'] as $j => $Setting_arr) {
                        preg_match("/[0-9]+\sattr/", $j, $cTreffer3_arr);
                        preg_match("/[0-9]+/", $j, $cTreffer4_arr);

                        if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) === strlen($j)) {
                            $cTyp = $Setting_arr['type'];

                            // Einstellungen type prüfen
                            if (strlen($Setting_arr['type']) === 0) {
                                return PLUGIN_CODE_INVALID_CONFIG_TYPE;
                            }
                            // Einstellungen initialValue prüfen
                            //if(strlen($Setting_arr['initialValue']) == 0)
                            //return 21;  // Einstellungen initialValue entspricht nicht der Konvention

                            // Einstellungen sort prüfen
                            if (strlen($Setting_arr['sort']) === 0) {
                                return PLUGIN_CODE_INVALID_CONFIG_SORT_VALUE;
                            }
                            // Einstellungen conf prüfen
                            if (strlen($Setting_arr['conf']) === 0) {
                                return PLUGIN_CODE_INVALID_CONF;
                            }
                        } elseif (strlen($cTreffer4_arr[0]) === strlen($j)) {
                            // Einstellungen Name prüfen
                            if (strlen($Setting_arr['Name']) === 0) {
                                return PLUGIN_CODE_INVALID_CONFIG_NAME;
                            }
                            // Einstellungen ValueName prüfen
                            if (!isset($Setting_arr['ValueName'])
                                || !is_string($Setting_arr['ValueName'])
                                || strlen($Setting_arr['ValueName']) === 0
                            ) {
                                return PLUGIN_CODE_INVALID_CONF_VALUE_NAME;//Einstellungen ValueName entspricht nicht der Konvention
                            }
                            // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                            if ($cTyp === 'selectbox') {
                                // SelectboxOptions prüfen
                                if (isset($Setting_arr['OptionsSource'])
                                    && is_array($Setting_arr['OptionsSource'])
                                    && count($Setting_arr['OptionsSource']) > 0
                                ) {
                                    if (empty($Setting_arr['OptionsSource'][0]['File'])) {
                                        return PLUGIN_CODE_INVALID_OPTIONS_SOURE_FILE;
                                    }
                                    if (!file_exists($cVerzeichnis . '/' .
                                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                        PFAD_PLUGIN_ADMINMENU . $Setting_arr['OptionsSource'][0]['File'])
                                    ) {
                                        return PLUGIN_CODE_MISSING_OPTIONS_SOURE_FILE;
                                    }
                                } elseif (isset($Setting_arr['SelectboxOptions'])
                                    && is_array($Setting_arr['SelectboxOptions'])
                                    && count($Setting_arr['SelectboxOptions']) > 0
                                ) {
                                    // Es gibt mehr als 1 Option
                                    if (count($Setting_arr['SelectboxOptions'][0]) === 1) {
                                        foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                            preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);
                                            preg_match("/[0-9]+/", $y, $cTreffer7_arr);

                                            if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                if (strlen($Option_arr['value']) === 0) {
                                                    return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                                }
                                                if (strlen($Option_arr['sort']) === 0) {
                                                    return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                                }
                                            } elseif (strlen($cTreffer7_arr[0]) === strlen($y)) {
                                                // Name prüfen
                                                if (strlen($Option_arr) === 0) {
                                                    return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                                }
                                            }
                                        }
                                    } elseif (count($Setting_arr['SelectboxOptions'][0]) === 2) {
                                        //Es gibt nur 1 Option
                                        if (strlen($Setting_arr['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                                            return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                        }
                                        if (strlen($Setting_arr['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                                            return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                        }
                                        if (strlen($Setting_arr['SelectboxOptions'][0]['Option']) === 0) {
                                            return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                        }
                                    }
                                } else {
                                    return PLUGIN_CODE_MISSING_CONFIG_SELECTBOX_OPTIONS;
                                }
                            } elseif ($cTyp === 'radio') {
                                //radioOptions prüfen
                                if (isset($Setting_arr['OptionsSource'])
                                    && is_array($Setting_arr['OptionsSource'])
                                    && count($Setting_arr['OptionsSource']) > 0
                                ) {
                                    //do nothing for now
                                } elseif (isset($Setting_arr['RadioOptions'])
                                    && is_array($Setting_arr['RadioOptions'])
                                    && count($Setting_arr['RadioOptions']) > 0
                                ) {
                                    // Es gibt mehr als 1 Option
                                    if (count($Setting_arr['RadioOptions'][0]) === 1) {
                                        foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                            preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);
                                            preg_match("/[0-9]+/", $y, $cTreffer7_arr);
                                            if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                if (strlen($Option_arr['value']) === 0) {
                                                    return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                                }
                                                if (strlen($Option_arr['sort']) === 0) {
                                                    return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                                }
                                            } elseif (strlen($cTreffer7_arr[0]) === strlen($y)) {
                                                if (strlen($Option_arr) === 0) {
                                                    return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                                }
                                            }
                                        }
                                    } elseif (count($Setting_arr['RadioOptions'][0]) === 2) {
                                        // Es gibt nur 1 Option
                                        if (strlen($Setting_arr['RadioOptions'][0]['Option attr']['value']) === 0) {
                                            return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                        }
                                        if (strlen($Setting_arr['RadioOptions'][0]['Option attr']['sort']) === 0) {
                                            return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                        }
                                        if (strlen($Setting_arr['RadioOptions'][0]['Option']) === 0) {
                                            return PLUGIN_CODE_INVALID_CONFIG_OPTION;
                                        }
                                    }
                                } else {
                                    return PLUGIN_CODE_MISSING_CONFIG_SELECTBOX_OPTIONS;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    // Plausi FrontendLinks (falls vorhanden)
    if (isset($installNode['FrontendLink'])
        && is_array($installNode['FrontendLink'])
    ) {
        // Links prüfen
        if (!isset($installNode['FrontendLink'][0]['Link'])
            || !is_array($installNode['FrontendLink'][0]['Link'])
            || count($installNode['FrontendLink'][0]['Link']) === 0
        ) {
            return PLUGIN_CODE_MISSING_FRONTEND_LINKS;
        }
        foreach ($installNode['FrontendLink'][0]['Link'] as $u => $Link_arr) {
            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            preg_match("/[0-9]+/", $u, $cTreffer2_arr);

            if (strlen($cTreffer2_arr[0]) !== strlen($u)) {
                continue;
            }
            // Filename prüfen
            if (strlen($Link_arr['Filename']) === 0) {
                return PLUGIN_CODE_INVALID_FRONTEND_LINK_FILENAME;
            }
            // LinkName prüfen
            preg_match("/[a-zA-Z0-9äÄöÖüÜß" . "\_\- ]+/",
                $Link_arr['Name'],
                $cTreffer1_arr
            );
            if (strlen($cTreffer1_arr[0]) !== strlen($Link_arr['Name'])) {
                return PLUGIN_CODE_INVALID_FRONTEND_LINK_NAME;
            }
            // Templatename UND Fullscreen Templatename vorhanden?
            // Es darf nur entweder oder geben
            if (isset($Link_arr['Template'], $Link_arr['FullscreenTemplate'])
                && strlen($Link_arr['Template']) > 0
                && strlen($Link_arr['FullscreenTemplate']) > 0
            ) {
                return PLUGIN_CODE_TOO_MANY_FULLSCREEN_TEMPLATE_NAMES;
            }
            // Templatename prüfen
            if (!isset($Link_arr['FullscreenTemplate'])
                || strlen($Link_arr['FullscreenTemplate']) === 0
            ) {
                if (strlen($Link_arr['Template']) === 0) {
                    return PLUGIN_CODE_INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                }
                preg_match("/[a-zA-Z0-9\/_\-.]+.tpl/", $Link_arr['Template'], $cTreffer1_arr);
                if (strlen($cTreffer1_arr[0]) === strlen($Link_arr['Template'])) {
                    if (!file_exists($cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                        PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_TEMPLATE . $Link_arr['Template'])
                    ) {
                        return PLUGIN_CODE_MISSING_FRONTEND_LINK_TEMPLATE;
                    }
                } else {
                    return PLUGIN_CODE_INVALID_FULLSCREEN_TEMPLATE;
                }
            }

            // Fullscreen Templatename prüfen
            if (!isset($Link_arr['Template']) || strlen($Link_arr['Template']) === 0) {
                if (strlen($Link_arr['FullscreenTemplate']) === 0) {
                    return PLUGIN_CODE_INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                }
                preg_match("/[a-zA-Z0-9\/_\-.]+.tpl/", $Link_arr['FullscreenTemplate'], $cTreffer1_arr);
                if (strlen($cTreffer1_arr[0]) === strlen($Link_arr['FullscreenTemplate'])) {
                    if (!file_exists($cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                        PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_TEMPLATE . $Link_arr['FullscreenTemplate'])
                    ) {
                        return PLUGIN_CODE_MISSING_FULLSCREEN_TEMPLATE_FILE;
                    }
                } else {
                    return PLUGIN_CODE_INVALID_FULLSCREEN_TEMPLATE_NAME;
                }
            }
            // Angabe ob erst Sichtbar nach Login prüfen
            preg_match("/[NY]{1,1}/", $Link_arr['VisibleAfterLogin'], $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) !== strlen($Link_arr['VisibleAfterLogin'])) {
                return PLUGIN_CODE_INVALID_FRONEND_LINK_VISIBILITY;
            }
            // Abgabe ob ein Druckbutton gezeigt werden soll prüfen
            preg_match("/[NY]{1,1}/", $Link_arr['PrintButton'], $cTreffer3_arr);
            if (strlen($cTreffer3_arr[0]) !== strlen($Link_arr['PrintButton'])) {
                return PLUGIN_CODE_INVALID_FRONEND_LINK_PRINT;
            }
            // Abgabe ob NoFollow Attribut gezeigt werden soll prüfen
            if (isset($Link_arr['NoFollow'])) {
                preg_match("/[NY]{1,1}/", $Link_arr['NoFollow'], $cTreffer3_arr);
            } else {
                $cTreffer3_arr = [];
            }
            if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) !== strlen($Link_arr['NoFollow'])) {
                return PLUGIN_CODE_INVALID_FRONTEND_LINK_NO_FOLLOW;
            }
            // LinkSprachen prüfen
            if (!isset($Link_arr['LinkLanguage'])
                || !is_array($Link_arr['LinkLanguage'])
                || count($Link_arr['LinkLanguage']) === 0
            ) {
                return PLUGIN_CODE_INVALID_FRONEND_LINK_ISO;
            }
            foreach ($Link_arr['LinkLanguage'] as $l => $LinkLanguage_arr) {
                preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                preg_match("/[0-9]+/", $l, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                    // ISO prüfen
                    preg_match("/[A-Z]{3}/", $LinkLanguage_arr['iso'], $cTreffer_arr);
                    if (strlen($LinkLanguage_arr['iso']) === 0
                        || strlen($cTreffer_arr[0]) !== strlen($LinkLanguage_arr['iso'])
                    ) {
                        return PLUGIN_CODE_INVALID_FRONEND_LINK_ISO;
                    }
                } elseif (strlen($cTreffer2_arr[0]) === strlen($l)) {
                    // Seo prüfen
                    preg_match("/[a-zA-Z0-9- ]+/", $LinkLanguage_arr['Seo'], $cTreffer1_arr);
                    if (strlen($LinkLanguage_arr['Seo']) === 0
                        || strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['Seo'])
                    ) {
                        return PLUGIN_CODE_INVALID_FRONEND_LINK_SEO;
                    }
                    // Name prüfen
                    preg_match("/[a-zA-Z0-9äÄüÜöÖß" . "\- ]+/",
                        $LinkLanguage_arr['Name'],
                        $cTreffer1_arr
                    );
                    if (strlen($LinkLanguage_arr['Name']) === 0
                        || strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['Name'])
                    ) {
                        return PLUGIN_CODE_INVALID_FRONEND_LINK_NAME;
                    }
                    // Title prüfen
                    preg_match("/[a-zA-Z0-9äÄüÜöÖß" . "\- ]+/",
                        $LinkLanguage_arr['Title'],
                        $cTreffer1_arr
                    );
                    if (strlen($LinkLanguage_arr['Title']) === 0
                        || strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['Title'])
                    ) {
                        return PLUGIN_CODE_INVALID_FRONEND_LINK_TITLE;
                    }
                    // MetaTitle prüfen
                    preg_match("/[a-zA-Z0-9äÄüÜöÖß" . "\,\.\- ]+/",
                        $LinkLanguage_arr['MetaTitle'],
                        $cTreffer1_arr
                    );
                    if ((strlen($LinkLanguage_arr['MetaTitle']) === 0
                            || strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['MetaTitle']))
                        && strlen($LinkLanguage_arr['MetaTitle']) === 0
                    ) {
                        return PLUGIN_CODE_INVALID_FRONEND_LINK_META_TITLE;
                    }
                    // MetaKeywords prüfen
                    preg_match("/[a-zA-Z0-9äÄüÜöÖß" . "\,\- ]+/",
                        $LinkLanguage_arr['MetaKeywords'],
                        $cTreffer1_arr
                    );
                    if (strlen($LinkLanguage_arr['MetaKeywords']) === 0
                        || strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['MetaKeywords'])
                    ) {
                        return PLUGIN_CODE_INVALID_FRONEND_LINK_META_KEYWORDS;
                    }
                    // MetaDescription prüfen
                    preg_match("/[a-zA-Z0-9äÄüÜöÖß" . "\,\.\- ]+/",
                        $LinkLanguage_arr['MetaDescription'],
                        $cTreffer1_arr
                    );
                    if (strlen($LinkLanguage_arr['MetaDescription']) === 0
                        || strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['MetaDescription'])
                    ) {
                        return PLUGIN_CODE_INVALID_FRONEND_LINK_META_DESCRIPTION;
                    }
                }
            }
        }
    }
    // Plausi Zahlungsmethode (PaymentMethod) (falls vorhanden)
    if (isset($installNode['PaymentMethod'][0]['Method'])
        && is_array($installNode['PaymentMethod'])
        && is_array($installNode['PaymentMethod'][0]['Method'])
        && count($installNode['PaymentMethod'][0]['Method']) > 0
    ) {
        foreach ($installNode['PaymentMethod'][0]['Method'] as $u => $Method_arr) {
            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            preg_match("/[0-9]+/", $u, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                // Name prüfen
                preg_match("/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                    $Method_arr['Name'],
                    $cTreffer1_arr
                );
                if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Name'])) {
                    return PLUGIN_CODE_INVALID_PAYMENT_METHOD_NAME;
                }
                // Sort prüfen
                preg_match("/[0-9]+/", $Method_arr['Sort'], $cTreffer1_arr);
                if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Sort'])) {
                    return PLUGIN_CODE_INVALID_PAYMENT_METHOD_SORT;
                }
                // SendMail prüfen
                preg_match("/[0-1]{1}/", $Method_arr['SendMail'], $cTreffer1_arr);
                if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['SendMail'])) {
                    return PLUGIN_CODE_INVALID_PAYMENT_METHOD_MAIL;
                }
                // TSCode prüfen
                preg_match('/[A-Z_]+/', $Method_arr['TSCode'], $cTreffer1_arr);
                if (strlen($cTreffer1_arr[0]) === strlen($Method_arr['TSCode'])) {
                    $cTSCode_arr = [
                        'DIRECT_DEBIT',
                        'CREDIT_CARD',
                        'INVOICE',
                        'CASH_ON_DELIVERY',
                        'PREPAYMENT',
                        'CHEQUE',
                        'PAYBOX',
                        'PAYPAL',
                        'CASH_ON_PICKUP',
                        'FINANCING',
                        'LEASING',
                        'T_PAY',
                        'GIROPAY',
                        'GOOGLE_CHECKOUT',
                        'SHOP_CARD',
                        'DIRECT_E_BANKING',
                        'OTHER'
                    ];
                    if (!in_array($Method_arr['TSCode'], $cTSCode_arr, true)) {
                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_TSCODE;
                    }
                } else {
                    return PLUGIN_CODE_INVALID_PAYMENT_METHOD_TSCODE;
                }
                // PreOrder (nWaehrendbestellung) prüfen
                preg_match("/[0-1]{1}/", $Method_arr['PreOrder'], $cTreffer1_arr);
                if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['PreOrder'])) {
                    return PLUGIN_CODE_INVALID_PAYMENT_METHOD_PRE_ORDER;
                }
                // Soap prüfen
                preg_match("/[0-1]{1}/", $Method_arr['Soap'], $cTreffer1_arr);
                if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Soap'])) {
                    return PLUGIN_CODE_INVALID_PAYMENT_METHOD_SOAP;
                }
                // Curl prüfen
                preg_match("/[0-1]{1}/", $Method_arr['Curl'], $cTreffer1_arr);
                if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Curl'])) {
                    return PLUGIN_CODE_INVALID_PAYMENT_METHOD_CURL;
                }
                // Sockets prüfen
                preg_match('/[0-1]{1}/', $Method_arr['Sockets'], $cTreffer1_arr);
                if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Sockets'])) {
                    return PLUGIN_CODE_INVALID_PAYMENT_METHOD_SOCKETS;
                }
                // ClassFile prüfen
                if (isset($Method_arr['ClassFile'])) {
                    preg_match('/[a-zA-Z0-9\/_\-.]+.php/', $Method_arr['ClassFile'], $cTreffer1_arr);
                    if (strlen($cTreffer1_arr[0]) === strlen($Method_arr['ClassFile'])) {
                        if (!file_exists($cVerzeichnis . '/' .
                            PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                            PFAD_PLUGIN_PAYMENTMETHOD . $Method_arr['ClassFile'])
                        ) {
                            return PLUGIN_CODE_MISSING_PAYMENT_METHOD_FILE;
                        }
                    } else {
                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_CLASS_FILE;
                    }
                }
                // ClassName prüfen
                if (isset($Method_arr['ClassName'])) {
                    preg_match("/[a-zA-Z0-9\/_\-]+/", $Method_arr['ClassName'], $cTreffer1_arr);
                    if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['ClassName'])) {
                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_CLASS_NAME;
                    }
                }
                // TemplateFile prüfen
                if (isset($Method_arr['TemplateFile']) && strlen($Method_arr['TemplateFile']) > 0) {
                    preg_match('/[a-zA-Z0-9\/_\-.]+.tpl/',
                        $Method_arr['TemplateFile'],
                        $cTreffer1_arr
                    );
                    if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['TemplateFile'])) {
                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_TEMPLATE;
                    }
                    if (!file_exists($cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                        PFAD_PLUGIN_PAYMENTMETHOD . $Method_arr['TemplateFile'])
                    ) {
                        return PLUGIN_CODE_MISSING_PAYMENT_METHOD_TEMPLATE;
                    }
                }
                // Zusatzschritt-TemplateFile prüfen
                if (isset($Method_arr['AdditionalTemplateFile']) && strlen($Method_arr['AdditionalTemplateFile']) > 0) {
                    preg_match('/[a-zA-Z0-9\/_\-.]+.tpl/',
                        $Method_arr['AdditionalTemplateFile'],
                        $cTreffer1_arr
                    );
                    if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['AdditionalTemplateFile'])) {
                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE;
                    }
                    if (!file_exists($cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                        PFAD_PLUGIN_PAYMENTMETHOD . $Method_arr['AdditionalTemplateFile'])
                    ) {
                        return PLUGIN_CODE_MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE;
                    }
                }
                // ZahlungsmethodeSprachen prüfen
                if (!isset($Method_arr['MethodLanguage'])
                    || !is_array($Method_arr['MethodLanguage'])
                    || count($Method_arr['MethodLanguage']) === 0
                ) {
                    return PLUGIN_CODE_MISSING_PAYMENT_METHOD_LANGUAGES;
                }
                foreach ($Method_arr['MethodLanguage'] as $l => $MethodLanguage_arr) {
                    preg_match('/[0-9]+\sattr/', $l, $cTreffer1_arr);
                    preg_match('/[0-9]+/', $l, $cTreffer2_arr);
                    if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                        // ISO prüfen
                        preg_match("/[A-Z]{3}/", $MethodLanguage_arr['iso'], $cTreffer_arr);
                        if (strlen($MethodLanguage_arr['iso']) === 0
                            || strlen($cTreffer_arr[0]) !== strlen($MethodLanguage_arr['iso'])
                        ) {
                            return PLUGIN_CODE_INVALID_PAYMENT_METHOD_LANGUAGE_ISO;
                        }
                    } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($l)) {
                        // Name prüfen
                        if (!isset($MethodLanguage_arr['Name'])) {
                            return PLUGIN_CODE_INVALID_PAYMENT_METHOD_NAME_LOCALIZED;
                        }
                        preg_match("/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                            $MethodLanguage_arr['Name'],
                            $cTreffer1_arr
                        );
                        if (strlen($cTreffer1_arr[0]) !== strlen($MethodLanguage_arr['Name'])) {
                            return PLUGIN_CODE_INVALID_PAYMENT_METHOD_NAME_LOCALIZED;
                        }
                        // ChargeName prüfen
                        if (!isset($MethodLanguage_arr['ChargeName'])) {
                            return PLUGIN_CODE_INVALID_PAYMENT_METHOD_CHARGE_NAME;
                        }
                        preg_match("/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                            $MethodLanguage_arr['ChargeName'],
                            $cTreffer1_arr
                        );
                        if (strlen($cTreffer1_arr[0]) !== strlen($MethodLanguage_arr['ChargeName'])) {
                            return PLUGIN_CODE_INVALID_PAYMENT_METHOD_CHARGE_NAME;
                        }
                        // InfoText prüfen
                        if (!isset($MethodLanguage_arr['InfoText'])) {
                            return PLUGIN_CODE_INVALID_PAYMENT_METHOD_INFO_TEXT;
                        }
                        preg_match("/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                            $MethodLanguage_arr['InfoText'],
                            $cTreffer1_arr
                        );
                        if (isset($cTreffer1_arr[0])
                            && strlen($cTreffer1_arr[0]) !== strlen($MethodLanguage_arr['InfoText'])
                        ) {
                            return PLUGIN_CODE_INVALID_PAYMENT_METHOD_INFO_TEXT;
                        }
                    }
                }
                // Zahlungsmethode Einstellungen prüfen
                $cTyp = '';
                if (isset($Method_arr['Setting'])
                    && is_array($Method_arr['Setting'])
                    && count($Method_arr['Setting']) > 0
                ) {
                    foreach ($Method_arr['Setting'] as $j => $Setting_arr) {
                        preg_match('/[0-9]+\sattr/', $j, $cTreffer3_arr);
                        preg_match('/[0-9]+/', $j, $cTreffer4_arr);
                        if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) === strlen($j)) {
                            $cTyp = $Setting_arr['type'];
                            // Einstellungen type prüfen
                            if (strlen($Setting_arr['type']) === 0) {
                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_TYPE;
                            }
                            // Einstellungen initialValue prüfen
                            //if(strlen($Setting_arr['initialValue']) == 0)
                            //return 64;  // Einstellungen initialValue entspricht nicht der Konvention

                            // Einstellungen sort prüfen
                            if (strlen($Setting_arr['sort']) === 0) {
                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_SORT;
                            }
                            // Einstellungen conf prüfen
                            if (strlen($Setting_arr['conf']) === 0) {
                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_CONF;
                            }
                        } elseif (isset($cTreffer4_arr[0]) && strlen($cTreffer4_arr[0]) === strlen($j)) {
                            // Einstellungen Name prüfen
                            if (strlen($Setting_arr['Name']) === 0) {
                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_NAME;
                            }
                            // Einstellungen ValueName prüfen
                            if (strlen($Setting_arr['ValueName']) === 0) {
                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_VALUE_NAME;
                            }
                            // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                            if ($cTyp === 'selectbox') {
                                // SelectboxOptions prüfen
                                if (!isset($Setting_arr['SelectboxOptions'])
                                    || !is_array($Setting_arr['SelectboxOptions'])
                                    || count($Setting_arr['SelectboxOptions']) === 0
                                ) {
                                    return PLUGIN_CODE_MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;
                                }
                                // Es gibt mehr als 1 Option
                                if (count($Setting_arr['SelectboxOptions'][0]) === 1) {
                                    foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                        preg_match('/[0-9]+\sattr/', $y, $cTreffer6_arr);
                                        preg_match('/[0-9]+/', $y, $cTreffer7_arr);
                                        if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                            // Value prüfen
                                            if (strlen($Option_arr['value']) === 0) {
                                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                            }
                                            // Sort prüfen
                                            if (strlen($Option_arr['sort']) === 0) {
                                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                            }
                                        } elseif (isset($cTreffer7_arr[0]) && strlen($cTreffer7_arr[0]) === strlen($y)) {
                                            // Name prüfen
                                            if (strlen($Option_arr) === 0) {
                                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                            }
                                        }
                                    }
                                } elseif (count($Setting_arr['SelectboxOptions'][0]) === 2) { //Es gibt nur 1 Option
                                    // Value prüfen
                                    if (strlen($Setting_arr['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                    // Sort prüfen
                                    if (strlen($Setting_arr['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                    // Name prüfen
                                    if (strlen($Setting_arr['SelectboxOptions'][0]['Option']) === 0) {
                                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                }
                            } elseif ($cTyp === 'radio') {
                                // SelectboxOptions prüfen
                                if (!isset($Setting_arr['RadioOptions'])
                                    || !is_array($Setting_arr['RadioOptions'])
                                    || count($Setting_arr['RadioOptions']) === 0
                                ) {
                                    return PLUGIN_CODE_MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;// Keine SelectboxOptionen vorhanden
                                }
                                // Es gibt mehr als 1 Option
                                if (count($Setting_arr['RadioOptions'][0]) === 1) {
                                    foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                        preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);
                                        preg_match("/[0-9]+/", $y, $cTreffer7_arr);
                                        if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                            // Value prüfen
                                            if (strlen($Option_arr['value']) === 0) {
                                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                            }
                                            // Sort prüfen
                                            if (strlen($Option_arr['sort']) === 0) {
                                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                            }
                                        } elseif (isset($cTreffer7_arr[0]) && strlen($cTreffer7_arr[0]) === strlen($y)) {
                                            // Name prüfen
                                            if (strlen($Option_arr) === 0) {
                                                return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                            }
                                        }
                                    }
                                } elseif (count($Setting_arr['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                                    // Value prüfen
                                    if (strlen($Setting_arr['RadioOptions'][0]['Option attr']['value']) === 0) {
                                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                    // Sort prüfen
                                    if (strlen($Setting_arr['RadioOptions'][0]['Option attr']['sort']) === 0) {
                                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                    // Name prüfen
                                    if (strlen($Setting_arr['RadioOptions'][0]['Option']) === 0) {
                                        return PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Plausi Boxenvorlagen (falls vorhanden)
    if (isset($installNode['Boxes'])
        && is_array($installNode['Boxes'])
    ) {
        // Boxen prüfen
        if (!isset($installNode['Boxes'][0]['Box'])
            || !is_array($installNode['Boxes'][0]['Box'])
            || count($installNode['Boxes'][0]['Box']) === 0
        ) {
            return PLUGIN_CODE_MISSING_BOX;
        }
        foreach ($installNode['Boxes'][0]['Box'] as $h => $Box_arr) {
            preg_match("/[0-9]+/", $h, $cTreffer3_arr);
            if (strlen($cTreffer3_arr[0]) !== strlen($h)) {
                continue;
            }
            // Box Name prüfen
            if (empty($Box_arr['Name'])) {
                return PLUGIN_CODE_INVALID_BOX_NAME;
            }
            // Box TemplateFile prüfen
            if (empty($Box_arr['TemplateFile'])) {
                return PLUGIN_CODE_INVALID_BOX_TEMPLATE;
            }
            if (!file_exists($cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_BOXEN . $Box_arr['TemplateFile'])
            ) {
                return PLUGIN_CODE_MISSING_BOX_TEMPLATE_FILE;
            }
        }
    }

    // Plausi Emailvorlagen (falls vorhanden)
    if (isset($installNode['Emailtemplate'])
        && is_array($installNode['Emailtemplate'])
    ) {
        // EmailTemplates prüfen
        if (!isset($installNode['Emailtemplate'][0]['Template'])
            || !is_array($installNode['Emailtemplate'][0]['Template'])
            || count($installNode['Emailtemplate'][0]['Template']) === 0
        ) {
            return PLUGIN_CODE_MISSING_EMAIL_TEMPLATES;
        }
        foreach ($installNode['Emailtemplate'][0]['Template'] as $u => $Template_arr) {
            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            preg_match("/[0-9]+/", $u, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) !== strlen($u)) {
                continue;
            }
            // Template Name prüfen
            preg_match("/[a-zA-Z0-9\/_\-äÄüÜöÖß" . " ]+/",
                $Template_arr['Name'],
                $cTreffer1_arr
            );
            if (strlen($cTreffer1_arr[0]) !== strlen($Template_arr['Name'])) {
                return PLUGIN_CODE_INVALID_TEMPLATE_NAME;
            }
            // Template Typ prüfen
            if ($Template_arr['Type'] !== 'text/html' && $Template_arr['Type'] !== 'text') {
                return PLUGIN_CODE_INVALID_TEMPLATE_TYPE;
            }
            // Template ModulId prüfen
            if (strlen($Template_arr['ModulId']) === 0) {
                return PLUGIN_CODE_INVALID_TEMPLATE_MODULE_ID;
            }
            // Template Active prüfen
            if (strlen($Template_arr['Active']) === 0) {
                return PLUGIN_CODE_INVALID_TEMPLATE_ACTIVE;
            }
            // Template AKZ prüfen
            if (strlen($Template_arr['AKZ']) === 0) {
                return PLUGIN_CODE_INVALID_TEMPLATE_AKZ;
            }
            // Template AGB prüfen
            if (strlen($Template_arr['AGB']) === 0) {
                return PLUGIN_CODE_INVALID_TEMPLATE_AGB;
            }
            // Template WRB prüfen
            if (strlen($Template_arr['WRB']) === 0) {
                return PLUGIN_CODE_INVALID_TEMPLATE_WRB;
            }
            // Template Sprachen prüfen
            if (!isset($Template_arr['TemplateLanguage'])
                || !is_array($Template_arr['TemplateLanguage'])
                || count($Template_arr['TemplateLanguage']) === 0
            ) {
                return PLUGIN_CODE_MISSING_EMAIL_TEMPLATE_LANGUAGE;
            }
            foreach ($Template_arr['TemplateLanguage'] as $l => $TemplateLanguage_arr) {
                preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                preg_match("/[0-9]+/", $l, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                    // ISO prüfen
                    preg_match("/[A-Z]{3}/", $TemplateLanguage_arr['iso'], $cTreffer_arr);
                    if (strlen($TemplateLanguage_arr['iso']) === 0
                        || strlen($cTreffer_arr[0]) !== strlen($TemplateLanguage_arr['iso'])
                    ) {
                        return PLUGIN_CODE_INVALID_EMAIL_TEMPLATE_ISO;
                    }
                } elseif (strlen($cTreffer2_arr[0]) === strlen($l)) {
                    // Subject prüfen
                    preg_match("/[a-zA-Z0-9\/_\-.#: ]+/", $TemplateLanguage_arr['Subject'], $cTreffer1_arr);
                    if (strlen($TemplateLanguage_arr['Subject']) === 0
                        || strlen($cTreffer1_arr[0]) !== strlen($TemplateLanguage_arr['Subject'])
                    ) {
                        return PLUGIN_CODE_INVALID_EMAIL_TEMPLATE_SUBJECT;
                    }
                }
            }
        }
    }
    // Plausi Locales (falls vorhanden)
    if (isset($installNode['Locales'])
        && is_array($installNode['Locales'])
    ) {
        // Variablen prüfen
        if (!isset($installNode['Locales'][0]['Variable'])
            || !is_array($installNode['Locales'][0]['Variable'])
            || count($installNode['Locales'][0]['Variable']) === 0
        ) {
            return PLUGIN_CODE_MISSING_LANG_VARS;
        }
        foreach ($installNode['Locales'][0]['Variable'] as $t => $Variable_arr) {
            preg_match("/[0-9]+/", $t, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) !== strlen($t)) {
                continue;
            }
            // Variablen Name prüfen
            if (strlen($Variable_arr['Name']) === 0) {
                return PLUGIN_CODE_INVALID_LANG_VAR_NAME;
            }
            // Variable Localized prüfen
            // Nur eine Sprache vorhanden
            if (isset($Variable_arr['VariableLocalized attr'])
                && is_array($Variable_arr['VariableLocalized attr'])
                && count($Variable_arr['VariableLocalized attr']) > 0
            ) {
                if (!isset($Variable_arr['VariableLocalized attr']['iso'])) {
                    return PLUGIN_CODE_MISSING_LOCALIZED_LANG_VAR;
                }
                // ISO prüfen
                preg_match("/[A-Z]{3}/", $Variable_arr['VariableLocalized attr']['iso'], $cTreffer_arr);
                if (strlen($cTreffer_arr[0]) !== strlen($Variable_arr['VariableLocalized attr']['iso'])) {
                    return PLUGIN_CODE_INVALID_LANG_VAR_ISO;
                }
                // Name prüfen
                if (strlen($Variable_arr['VariableLocalized']) === 0) {
                    return PLUGIN_CODE_INVALID_LOCALIZED_LANG_VAR_NAME;
                }
            } elseif (isset($Variable_arr['VariableLocalized'])
                && is_array($Variable_arr['VariableLocalized'])
                && count($Variable_arr['VariableLocalized']) > 0
            ) {
                // Mehr als eine Sprache vorhanden
                foreach ($Variable_arr['VariableLocalized'] as $i => $VariableLocalized_arr) {
                    preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                    preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                    if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                        // ISO prüfen
                        preg_match("/[A-Z]{3}/", $VariableLocalized_arr['iso'], $cTreffer_arr);
                        if (strlen($VariableLocalized_arr['iso']) === 0 ||
                            strlen($cTreffer_arr[0]) !== strlen($VariableLocalized_arr['iso'])
                        ) {
                            return PLUGIN_CODE_INVALID_LANG_VAR_ISO;
                        }
                    } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($i)) {
                        // Name prüfen
                        if (strlen($VariableLocalized_arr) === 0) {
                            return PLUGIN_CODE_INVALID_LOCALIZED_LANG_VAR_NAME;
                        }
                    }
                }
            } else {
                return PLUGIN_CODE_MISSING_LOCALIZED_LANG_VAR;
            }
        }
    }

    // Plausi CheckBoxFunction (falls vorhanden)
    if (isset($installNode['CheckBoxFunction'][0]['Function'])
        && is_array($installNode['CheckBoxFunction'])
        && is_array($installNode['CheckBoxFunction'][0]['Function'])
        && count($installNode['CheckBoxFunction'][0]['Function']) > 0
    ) {
        foreach ($installNode['CheckBoxFunction'][0]['Function'] as $t => $Function_arr) {
            preg_match("/[0-9]+/", $t, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) === strlen($t)) {
                // Function Name prüfen
                if (strlen($Function_arr['Name']) === 0) {
                    return PLUGIN_CODE_INVALID_CHECKBOX_FUNCTION_NAME;
                }
                // Function ID prüfen
                if (strlen($Function_arr['ID']) === 0) {
                    return PLUGIN_CODE_INVALID_CHECKBOX_FUNCTION_ID;
                }
            }
        }
    }
    // Plausi AdminWidgets (falls vorhanden)
    if (isset($installNode['AdminWidget'])
        && is_array($installNode['AdminWidget'])
    ) {
        if (!isset($installNode['AdminWidget'][0]['Widget'])
            || !is_array($installNode['AdminWidget'][0]['Widget'])
            || count($installNode['AdminWidget'][0]['Widget']) === 0
        ) {
            return PLUGIN_CODE_MISSING_WIDGETS;
        }
        foreach ($installNode['AdminWidget'][0]['Widget'] as $u => $Widget_arr) {
            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            preg_match("/[0-9]+/", $u, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) !== strlen($u)) {
                continue;
            }
            // Widget Title prüfen
            preg_match("/[a-zA-Z0-9\/_\-äÄüÜöÖß" . "\(\) ]+/",
                $Widget_arr['Title'],
                $cTreffer1_arr
            );
            if (strlen($cTreffer1_arr[0]) !== strlen($Widget_arr['Title'])) {
                return PLUGIN_CODE_INVALID_WIDGET_TITLE;
            }
            // Widget Class prüfen
            preg_match("/[a-zA-Z0-9\/_\-.]+/", $Widget_arr['Class'], $cTreffer1_arr);
            if (strlen($cTreffer1_arr[0]) !== strlen($Widget_arr['Class'])) {
                return PLUGIN_CODE_INVALID_WIDGET_CLASS;
            }
            if (!file_exists(
                $cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                PFAD_PLUGIN_ADMINMENU . PFAD_PLUGIN_WIDGET .
                'class.Widget' . $Widget_arr['Class'] . '_' .
                $baseNode['PluginID'] . '.php'
            )) {
                return PLUGIN_CODE_MISSING_WIDGET_CLASS_FILE;
            }
            // Widget Container prüfen
            if ($Widget_arr['Container'] !== 'center' &&
                $Widget_arr['Container'] !== 'left' &&
                $Widget_arr['Container'] !== 'right'
            ) {
                return PLUGIN_CODE_INVALID_WIDGET_CONTAINER;
            }
            // Widget Pos prüfen
            preg_match("/[0-9]+/", $Widget_arr['Pos'], $cTreffer1_arr);
            if (strlen($cTreffer1_arr[0]) !== strlen($Widget_arr['Pos'])) {
                return PLUGIN_CODE_INVALID_WIDGET_POS;
            }
            // Widget Expanded prüfen
            preg_match("/[0-1]{1}/", $Widget_arr['Expanded'], $cTreffer1_arr);
            if (strlen($cTreffer1_arr[0]) !== strlen($Widget_arr['Expanded'])) {
                return PLUGIN_CODE_INVALID_WIDGET_EXPANDED;
            }
            // Widget Active prüfen
            preg_match("/[0-1]{1}/", $Widget_arr['Active'], $cTreffer1_arr);
            if (strlen($cTreffer1_arr[0]) !== strlen($Widget_arr['Active'])) {
                return PLUGIN_CODE_INVALID_WIDGET_ACTIVE;
            }
        }
    }

    // Plausi Exportformate (falls vorhanden)
    if (isset($installNode['ExportFormat'])
        && is_array($installNode['ExportFormat'])
    ) {
        // Formate prüfen
        if (!isset($installNode['ExportFormat'][0]['Format'])
            || !is_array($installNode['ExportFormat'][0]['Format'])
            || count($installNode['ExportFormat'][0]['Format']) === 0
        ) {
            return PLUGIN_CODE_MISSING_FORMATS;
        }
        foreach ($installNode['ExportFormat'][0]['Format'] as $h => $Format_arr) {
            preg_match("/[0-9]+\sattr/", $h, $cTreffer1_arr);
            preg_match("/[0-9]+/", $h, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) !== strlen($h)) {
                continue;
            }
            // Name prüfen
            if (strlen($Format_arr['Name']) === 0) {
                return PLUGIN_CODE_INVALID_FORMAT_NAME;
            }
            // Filename prüfen
            if (strlen($Format_arr['FileName']) === 0) {
                return PLUGIN_CODE_INVALID_FORMAT_FILE_NAME;
            }
            // Content prüfen
            if ((!isset($Format_arr['Content']) || strlen($Format_arr['Content']) === 0) &&
                (!isset($Format_arr['ContentFile']) || strlen($Format_arr['ContentFile']) === 0)
            ) {
                return PLUGIN_CODE_MISSING_FORMAT_CONTENT;
            }
            // Encoding prüfen
            if (strlen($Format_arr['Encoding']) === 0 || ($Format_arr['Encoding'] !== 'ASCII' && $Format_arr['Encoding'] !== 'UTF-8')) {
                return PLUGIN_CODE_INVALID_FORMAT_ENCODING;
            }
            // Encoding prüfen
            if (strlen($Format_arr['ShippingCostsDeliveryCountry']) === 0) {
                return PLUGIN_CODE_INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY;
            }
            // Encoding prüfen
            if (strlen($Format_arr['ContentFile']) > 0 && !file_exists(
                $cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                PFAD_PLUGIN_ADMINMENU . PFAD_PLUGIN_EXPORTFORMAT . $Format_arr['ContentFile']
            )) {
                return PLUGIN_CODE_INVALID_FORMAT_CONTENT_FILE;
            }
        }
    }
    // Plausi ExtendedTemplate (falls vorhanden)
    if (isset($installNode['ExtendedTemplates'])
        && is_array($installNode['ExtendedTemplates'])
    ) {
        // Template prüfen
        if (!isset($installNode['ExtendedTemplates'][0]['Template'])) {
            return PLUGIN_CODE_MISSING_EXTENDED_TEMPLATE;
        }
        $cTemplate_arr = (array)$installNode['ExtendedTemplates'][0]['Template'];
        foreach ($cTemplate_arr as $cTemplate) {
            preg_match('/[a-zA-Z0-9\/_\-]+\.tpl/', $cTemplate, $cTreffer3_arr);
            if (strlen($cTreffer3_arr[0]) !== strlen($cTemplate)) {
                return PLUGIN_CODE_INVALID_EXTENDED_TEMPLATE_FILE_NAME;
            }
            if (!file_exists($cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_TEMPLATE . $cTemplate)
            ) {
                return PLUGIN_CODE_MISSING_EXTENDED_TEMPLATE_FILE;
            }
        }
    }

    // Plausi Uninstall (falls vorhanden)
    if (isset($baseNode['Uninstall'])
        && strlen($baseNode['Uninstall']) > 0
        && !file_exists($cVerzeichnis . '/' .
            PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
            PFAD_PLUGIN_UNINSTALL . $baseNode['Uninstall'])
    ) {
        return PLUGIN_CODE_MISSING_UNINSTALL_FILE;
    }
    // Interne XML prüfung mit höheren XML Versionen
    if ($nXMLVersion > 100) {
        return $isShop4Compatible ? PLUGIN_CODE_OK : PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE;
    }

    return $isShop4Compatible ? PLUGIN_CODE_OK : PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE;
}

/**
 * Versucht ein ausgewähltes Plugin zu updaten
 *
 * @param int $kPlugin
 * @return int
 */
function updatePlugin($kPlugin)
{
    $kPlugin = (int)$kPlugin;
    if ($kPlugin <= 0) {
        return PLUGIN_CODE_WRONG_PARAM;
    }
    $oPluginTMP = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
    if (isset($oPluginTMP->kPlugin) && $oPluginTMP->kPlugin > 0) {
        $oPlugin = new Plugin($oPluginTMP->kPlugin);

        return installierePluginVorbereitung($oPlugin->cVerzeichnis, $oPlugin);
    }

    return PLUGIN_CODE_NO_PLUGIN_FOUND;
}

/**
 * Versucht ein ausgewähltes Plugin zu vorzubereiten und danach zu installieren
 *
 * @param string     $cVerzeichnis
 * @param int|Plugin $oPluginOld
 * @return int
 */
function installierePluginVorbereitung($cVerzeichnis, $oPluginOld = 0)
{
    if (empty($cVerzeichnis)) {
        return PLUGIN_CODE_WRONG_PARAM;
    }
    // Plugin wurde schon installiert?
    $oPluginTMP = new stdClass();
    if (!isset($oPluginOld->kPlugin) || !$oPluginOld->kPlugin) {
        $oPluginTMP = Shop::DB()->select('tplugin', 'cVerzeichnis', $cVerzeichnis);
    }
    if (!empty($oPluginTMP->kPlugin)) {
        return 4;// Plugin wurde schon installiert
    }
    $cPfad = PFAD_ROOT . PFAD_PLUGIN . basename($cVerzeichnis);
    if (!file_exists($cPfad . '/' . PLUGIN_INFO_FILE)) {
        return 3;// info.xml existiert nicht
    }
    $xml     = file_get_contents($cPfad . '/' . PLUGIN_INFO_FILE);
    $XML_arr = XML_unserialize($xml);
    $XML_arr = getArrangedArray($XML_arr);
    // Interne Plugin Plausi
    $nReturnValue = pluginPlausiIntern($XML_arr, $cPfad);
    // Work Around
    if (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin > 0 && $nReturnValue === 90) {
        $nReturnValue = PLUGIN_CODE_OK;
    }
    // Alles O.K. => installieren
    if ($nReturnValue === PLUGIN_CODE_OK || $nReturnValue === 126) {
        // Plugin wird installiert
        $nReturnValue = installierePlugin($XML_arr, $cVerzeichnis, $oPluginOld);

        if ($nReturnValue === PLUGIN_CODE_OK) {
            return PLUGIN_CODE_OK;
        }
        $nSQLFehlerCode_arr = [
            2  => 152,
            3  => 153,
            4  => 154,
            5  => 155,
            6  => 156,
            7  => 157,
            8  => 158,
            9  => 159,
            10 => 160,
            11 => 161,
            12 => 162,
            13 => 163,
            14 => 164,
            15 => 165,
            16 => 166,
            22 => 202,
            23 => 203,
            24 => 204,
            25 => 205,
            26 => 206,
            27 => 207,
            28 => 208
        ];

        return $nSQLFehlerCode_arr[$nReturnValue];
    }

    return $nReturnValue;
}

/*
// Return:
// 1 = Alles O.K.
// 2 = Main Plugindaten nicht korrekt
// 3 = Ein Hook konnte nicht in die Datenbank gespeichert werden
// 4 = Ein Adminmenü Customlink konnte nicht in die Datenbank gespeichert werden
// 5 = Ein Adminmenü Settingslink konnte nicht in die Datenbank gespeichert werden
// 6 = Eine Einstellung konnte nicht in die Datenbank gespeichert werden
// 7 = Eine Sprachvariable konnte nicht in die Datenbank gespeichert werden
// 8 = Ein Link konnte nicht in die Datenbank gespeichert werden
// 9 = Eine Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
// 10 = Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
// 11 = Eine Einstellung der Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
// 12 = Es konnte keine Linkgruppe im Shop gefunden werden
// 13 = Eine Boxvorlage konnte nicht in die Datenbank gespeichert werden
// 14 = Eine Emailvorlage konnte nicht in die Datenbank gespeichert werden
// 15 = Ein AdminWidget konnte nicht in die Datenbank gespeichert werden
// 16 = Ein Exportformat konnte nicht in die Datenbank gespeichert werden
// 17 = Ein Template konnte nicht in die Datenbank gespeichert werden
// 18 = Eine Uninstall Datei konnte nicht in die Datenbank gespeichert werden

// ### logikSQLDatei
// 22 = Plugindaten fehlen
// 23 = SQL hat einen Fehler verursacht
// 24 = Versuch eine nicht Plugintabelle zu löschen
// 25 = Versuch eine nicht Plugintabelle anzulegen
// 26 = SQL Datei ist leer oder konnte nicht geparsed werden
// 27 = Sync Übergabeparameter nicht korrekt
// 28 = Update konnte nicht gesynct werden
*/

/**
 * Installiert ein Plugin
 *
 * @param array  $XML_arr
 * @param string $cVerzeichnis
 * @param Plugin $oPluginOld
 * @return int
 */
function installierePlugin($XML_arr, $cVerzeichnis, $oPluginOld)
{
    $baseNode          = $XML_arr['jtlshop3plugin'][0];
    $versionNode       = $baseNode['Install'][0]['Version'];
    $nLastVersionKey   = count($versionNode) / 2 - 1; // Finde aktuelle Version
    $nXMLVersion       = (int)$baseNode['XMLVersion']; // XML Version
    $cLizenzKlasse     = '';
    $cLizenzKlasseName = '';
    $nStatus           = 2;
    $tagsToFlush       = [];
    $basePath          = PFAD_ROOT . PFAD_PLUGIN . $cVerzeichnis . '/';
    if (isset($baseNode['LicenceClass'], $baseNode['LicenceClassFile'])
        && strlen($baseNode['LicenceClass']) > 0
        && strlen($baseNode['LicenceClassFile']) > 0
    ) {
        $cLizenzKlasse     = $baseNode['LicenceClass'];
        $cLizenzKlasseName = $baseNode['LicenceClassFile'];
        $nStatus           = 5;
    }
    // tplugin füllen
    $oPlugin                       = new stdClass();
    $oPlugin->cName                = $baseNode['Name'];
    $oPlugin->cBeschreibung        = $baseNode['Description'];
    $oPlugin->cAutor               = $baseNode['Author'];
    $oPlugin->cURL                 = $baseNode['URL'];
    $oPlugin->cIcon                = $baseNode['Icon'] ?? null;
    $oPlugin->cVerzeichnis         = $cVerzeichnis;
    $oPlugin->cPluginID            = $baseNode['PluginID'];
    $oPlugin->cFehler              = '';
    $oPlugin->cLizenz              = '';
    $oPlugin->cLizenzKlasse        = $cLizenzKlasse;
    $oPlugin->cLizenzKlasseName    = $cLizenzKlasseName;
    $oPlugin->nStatus              = $nStatus;
    $oPlugin->nVersion             = (int)$versionNode[$nLastVersionKey . ' attr']['nr'];
    $oPlugin->nXMLVersion          = $nXMLVersion;
    $oPlugin->nPrio                = 0;
    $oPlugin->dZuletztAktualisiert = 'now()';
    $oPlugin->dErstellt            = $versionNode[$nLastVersionKey]['CreateDate'];
    $oPlugin->bBootstrap           = is_file($basePath . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . 'bootstrap.php')
        ? 1
        : 0;

    $_tags = empty($baseNode['Install'][0]['FlushTags'])
        ? []
        : explode(',', $baseNode['Install'][0]['FlushTags']);
    foreach ($_tags as $_tag) {
        if (defined(trim($_tag))) {
            $tagsToFlush[] = constant(trim($_tag));
        }
    }
    if (count($tagsToFlush) > 0) {
        Shop::Cache()->flushTags($tagsToFlush);
    }
    $licenceClassFile = $basePath .
        PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' .
        PFAD_PLUGIN_LICENCE . $oPlugin->cLizenzKlasseName;
    if (isset($oPluginOld->cLizenz, $oPluginOld->nStatus)
        && (int)$oPluginOld->nStatus > 0
        && strlen($oPluginOld->cLizenz) > 0
        && is_file($licenceClassFile)
    ) {
        require_once $licenceClassFile;
        $oPluginLicence = new $oPlugin->cLizenzKlasse();
        $cLicenceMethod = PLUGIN_LICENCE_METHODE;
        if ($oPluginLicence->$cLicenceMethod($oPluginOld->cLizenz)) {
            $oPlugin->cLizenz = $oPluginOld->cLizenz;
            $oPlugin->nStatus = $oPluginOld->nStatus;
        }
    }
    $oPlugin->dInstalliert = (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin > 0)
        ? $oPluginOld->dInstalliert
        : 'now()';
    $kPlugin               = Shop::DB()->insert('tplugin', $oPlugin);
    $nVersion              = (int)$versionNode[$nLastVersionKey . ' attr']['nr'];
    $oPlugin->kPlugin      = $kPlugin;

    if ($kPlugin <= 0) {
        return PLUGIN_CODE_WRONG_PARAM;
    }
    $res = installPluginTables($XML_arr, $oPlugin, $oPluginOld);

    if ($res > 0) {
        deinstallierePlugin($kPlugin, $nXMLVersion);

        return $res;
    }
    // SQL installieren
    $bSQLFehler   = false;
    $nReturnValue = 1;
    foreach ($versionNode as $i => $Version_arr) {
        if ($nVersion > 0
            && isset($oPluginOld->kPlugin, $Version_arr['nr'])
            && $oPluginOld->nVersion >= (int)$Version_arr['nr']
        ) {
            continue;
        }
        preg_match('/[0-9]+\sattr/', $i, $cTreffer1_arr);

        if (!isset($cTreffer1_arr[0]) || strlen($cTreffer1_arr[0]) !== strlen($i)) {
            continue;
        }
        $nVersionTMP = (int)$Version_arr['nr'];
        $xy          = trim(str_replace('attr', '', $i));
        $cSQLDatei   = $versionNode[$xy]['SQL'] ?? '';
        if ($cSQLDatei === '') {
            continue;
        }
        $nReturnValue       = logikSQLDatei($cSQLDatei, $nVersionTMP, $oPlugin);
        $nSQLFehlerCode_arr = [1 => 1, 2 => 22, 3 => 23, 4 => 24, 5 => 25, 6 => 26];
        $nReturnValue       = $nSQLFehlerCode_arr[$nReturnValue];

        if ($nReturnValue !== PLUGIN_CODE_OK) {
            Jtllog::writeLog(
                'SQL-Fehler bei der Plugin-Installation von kPlugin ' . $oPlugin->kPlugin . ', Fehlercode: ' .
                $nReturnValue,
                JTLLOG_LEVEL_ERROR,
                false,
                'kPlugin',
                $kPlugin
            );
            $bSQLFehler = true;
            break;
        }
    }
    // Ist ein SQL Fehler aufgetreten? Wenn ja, deinstalliere wieder alles
    if ($bSQLFehler) {
        deinstallierePlugin($oPlugin->kPlugin, $nXMLVersion);
    }
    if ($nReturnValue === PLUGIN_CODE_OK && ($p = Plugin::bootstrapper($oPlugin->kPlugin)) !== null) {
        $p->installed();
    }
    // Installation von höheren XML Versionen
    if ($nXMLVersion > 100
        && ($nReturnValue === PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE || $nReturnValue ===  PLUGIN_CODE_OK)
    ) {
        $nReturnValue = PLUGIN_CODE_OK;
        // Update
        if (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin > 0 && $nReturnValue === 1) {
            // Update erfolgreich => sync neue Version auf altes Plugin
            $nReturnValue       = syncPluginUpdate($oPlugin->kPlugin, $oPluginOld, $nXMLVersion);
            $nSQLFehlerCode_arr = [1 => 1, 2 => 27, 3 => 28];
            $nReturnValue       = $nSQLFehlerCode_arr[$nReturnValue];
        }
    } elseif (isset($oPluginOld->kPlugin)
        && $oPluginOld->kPlugin
        && ($nReturnValue === PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE || $nReturnValue === PLUGIN_CODE_OK)
    ) {
        // Update erfolgreich => sync neue Version auf altes Plugin
        $nReturnValue       = syncPluginUpdate($oPlugin->kPlugin, $oPluginOld, $nXMLVersion);
        $nSQLFehlerCode_arr = [1 => 1, 2 => 27, 3 => 28];
        $nReturnValue       = $nSQLFehlerCode_arr[$nReturnValue];
    }

    return $nReturnValue;
}

/**
 * Installiert die tplugin* Tabellen für ein Plugin in der Datenbank
 *
 * @param array  $XML_arr
 * @param object $oPlugin
 * @param object $oPluginOld
 * @return int
 */
function installPluginTables($XML_arr, $oPlugin, $oPluginOld)
{
    $kPlugin      = $oPlugin->kPlugin;
    $cVerzeichnis = $oPlugin->cVerzeichnis;
    $nVersion     = $oPlugin->nVersion;

    // used in ExportFormate
    $kKundengruppeStd = Kundengruppe::getDefaultGroupID();
    $oSprache         = gibStandardsprache(true);
    $kSpracheStd      = $oSprache->kSprache;
    $kWaehrungStd     = gibStandardWaehrung();

    $hooksNode      = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'])
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks']
        : null;
    $uninstallNode  = !empty($XML_arr['jtlshop3plugin'][0]['Uninstall'])
        ? $XML_arr['jtlshop3plugin'][0]['Uninstall']
        : null;
    $adminNode      = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'])
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu']
        : null;
    $frontendNode   = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link'])
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link']
        : [];
    $paymentNode    = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'],
        $XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method'])
    && count($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']) > 0
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']
        : [];
    $boxesNode      = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'])
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'][0]['Box']
        : [];
    $checkboxesNode = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function'])
    && count($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']) > 0
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']
        : [];
    $cTemplate_arr  = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'])
        ? (array)$XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'][0]['Template']
        : [];
    $mailNode       = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template'])
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template']
        : [];
    $localeNode     = $XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales'][0]['Variable'] ?? [];
    $widgetsNode    = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget'])
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget']
        : [];
    $exportNode     = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format'])
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format']
        : [];
    $cssNode        = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file'])
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file']
        : [];
    $jsNode         = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file'])
    && is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file'])
        ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file']
        : [];
    // tpluginhook füllen
    if ($hooksNode !== null) {
        if (count($hooksNode[0]) === 1) {
            // Es gibt mehr als einen Hook
            $nHookID   = 0;
            $nPriority = 5;
            foreach ($hooksNode[0]['Hook'] as $i => $hook) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    $nHookID   = (int)$hook['id'];
                    $nPriority = isset($hook['priority']) ? (int)$hook['priority'] : 5;
                } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($i)) {
                    $oPluginHook             = new stdClass();
                    $oPluginHook->kPlugin    = $kPlugin;
                    $oPluginHook->nHook      = $nHookID;
                    $oPluginHook->nPriority  = $nPriority;
                    $oPluginHook->cDateiname = $hook;

                    $kPluginHook = Shop::DB()->insert('tpluginhook', $oPluginHook);

                    if (!$kPluginHook) {
                        return 3;//Ein Hook konnte nicht in die Datenbank gespeichert werden
                    }
                }
            }
        } elseif (count($hooksNode[0]) > 1) {
            // Es gibt nur einen Hook
            $hook = $hooksNode[0];

            $oPluginHook             = new stdClass();
            $oPluginHook->kPlugin    = $kPlugin;
            $oPluginHook->nHook      = (int)$hook['Hook attr']['id'];
            $oPluginHook->nPriority  = isset($hook['Hook attr']['priority'])
                ? (int)$hook['Hook attr']['priority']
                : 5;
            $oPluginHook->cDateiname = $hook['Hook'];

            $kPluginHook = Shop::DB()->insert('tpluginhook', $oPluginHook);

            if (!$kPluginHook) {
                return 3;//Ein Hook konnte nicht in die Datenbank gespeichert werden
            }
        }
    }
    // tpluginuninstall füllen
    if ($uninstallNode !== null) {
        $oPluginUninstall             = new stdClass();
        $oPluginUninstall->kPlugin    = $kPlugin;
        $oPluginUninstall->cDateiname = $uninstallNode;

        $kPluginUninstall = Shop::DB()->insert('tpluginuninstall', $oPluginUninstall);

        if (!$kPluginUninstall) {
            return 18;//Eine Uninstall-Datei konnte nicht in die Datenbank gespeichert werden
        }
    }
    // tpluginadminmenu füllen
    if ($adminNode !== null) {
        // Adminsmenüs vorhanden?
        if (isset($adminNode[0]['Customlink'])
            && is_array($adminNode[0]['Customlink'])
            && count($adminNode[0]['Customlink']) > 0
        ) {
            $nSort = 0;
            foreach ($adminNode[0]['Customlink'] as $i => $customLink) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                preg_match("/[0-9]+/", $i, $cTreffer2_arr);

                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    $nSort = (int)$customLink['sort'];
                } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                    $oAdminMenu             = new stdClass();
                    $oAdminMenu->kPlugin    = $kPlugin;
                    $oAdminMenu->cName      = $customLink['Name'];
                    $oAdminMenu->cDateiname = $customLink['Filename'];
                    $oAdminMenu->nSort      = $nSort;
                    $oAdminMenu->nConf      = 0;

                    $kPluginAdminMenu = Shop::DB()->insert('tpluginadminmenu', $oAdminMenu);

                    if (!$kPluginAdminMenu) {
                        return 4;//Ein Adminmenü-Customlink konnte nicht in die Datenbank gespeichert werden
                    }
                }
            }
        }
        // Einstellungen vorhanden?
        if (isset($adminNode[0]['Settingslink'])
            && is_array($adminNode[0]['Settingslink'])
            && count($adminNode[0]['Settingslink']) > 0
        ) {
            $nSort = 0;
            foreach ($adminNode[0]['Settingslink'] as $i => $Settingslink_arr) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    $nSort = (int)$Settingslink_arr['sort'];
                } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                    // tpluginadminmenu füllen
                    $oAdminMenu             = new stdClass();
                    $oAdminMenu->kPlugin    = $kPlugin;
                    $oAdminMenu->cName      = $Settingslink_arr['Name'];
                    $oAdminMenu->cDateiname = '';
                    $oAdminMenu->nSort      = $nSort;
                    $oAdminMenu->nConf      = 1;

                    $kPluginAdminMenu = Shop::DB()->insert('tpluginadminmenu', $oAdminMenu);

                    if ($kPluginAdminMenu <= 0) {
                        return 5;// Ein Adminmenü Settingslink konnte nicht in die Datenbank gespeichert werden
                    }
                    $cTyp          = '';
                    $cInitialValue = '';
                    $nSort         = 0;
                    $cConf         = 'Y';
                    $multiple      = false;
                    foreach ($Settingslink_arr['Setting'] as $j => $Setting_arr) {
                        preg_match("/[0-9]+\sattr/", $j, $cTreffer3_arr);
                        preg_match("/[0-9]+/", $j, $cTreffer4_arr);

                        if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) === strlen($j)) {
                            $cTyp          = $Setting_arr['type'];
                            $multiple      = (isset($Setting_arr['multiple']) && $Setting_arr['multiple'] === 'Y' && $cTyp === 'selectbox');
                            $cInitialValue = ($multiple === true) ?
                                serialize([$Setting_arr['initialValue']])
                                : $Setting_arr['initialValue'];
                            $nSort         = $Setting_arr['sort'];
                            $cConf         = $Setting_arr['conf'];
                        } elseif (strlen($cTreffer4_arr[0]) === strlen($j)) {
                            // tplugineinstellungen füllen
                            $oPluginEinstellungen          = new stdClass();
                            $oPluginEinstellungen->kPlugin = $kPlugin;
                            $oPluginEinstellungen->cName   = is_array($Setting_arr['ValueName'])
                                ? $Setting_arr['ValueName']['0']
                                : $Setting_arr['ValueName'];
                            $oPluginEinstellungen->cWert   = $cInitialValue;

                            Shop::DB()->insert('tplugineinstellungen', $oPluginEinstellungen);
                            // tplugineinstellungenconf füllen
                            $oPluginEinstellungenConf                   = new stdClass();
                            $oPluginEinstellungenConf->kPlugin          = $kPlugin;
                            $oPluginEinstellungenConf->kPluginAdminMenu = $kPluginAdminMenu;
                            $oPluginEinstellungenConf->cName            = $Setting_arr['Name'];
                            $oPluginEinstellungenConf->cBeschreibung    = (!isset($Setting_arr['Description']) || is_array($Setting_arr['Description']))
                                ? ''
                                : $Setting_arr['Description'];
                            $oPluginEinstellungenConf->cWertName = is_array($Setting_arr['ValueName'])
                                ? $Setting_arr['ValueName']['0']
                                : $Setting_arr['ValueName'];
                            $oPluginEinstellungenConf->cInputTyp = $cTyp;
                            $oPluginEinstellungenConf->nSort     = $nSort;
                            $oPluginEinstellungenConf->cConf     = $cConf;
                            //dynamic data source for selectbox/radio
                            if ($cTyp === 'selectbox' || $cTyp === 'radio') {
                                if (isset($Setting_arr['OptionsSource'][0]['File'])) {
                                    $oPluginEinstellungenConf->cSourceFile = $Setting_arr['OptionsSource'][0]['File'];
                                }
                                if ($multiple === true) {
                                    $oPluginEinstellungenConf->cConf = 'M';
                                }
                            }
                            $kPluginEinstellungenConf = Shop::DB()->insert('tplugineinstellungenconf', $oPluginEinstellungenConf);
                            // tplugineinstellungenconfwerte füllen
                            if ($kPluginEinstellungenConf > 0) {
                                $nSort = 0;
                                // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                                if ($cTyp === 'selectbox') {
                                    if (isset($Setting_arr['OptionsSource']) && is_array($Setting_arr['OptionsSource']) && count($Setting_arr['OptionsSource']) > 0) {
                                        //do nothing for now
                                    } elseif (count($Setting_arr['SelectboxOptions'][0]) === 1) { // Es gibt mehr als 1 Option
                                        foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                            preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);

                                            if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                $cWert = $Option_arr['value'];
                                                $nSort = $Option_arr['sort'];
                                                $yx    = substr($y, 0, strpos($y, ' '));
                                                $cName = $Setting_arr['SelectboxOptions'][0]['Option'][$yx];

                                                $oPluginEinstellungenConfWerte                           = new stdClass();
                                                $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                                $oPluginEinstellungenConfWerte->cName                    = $cName;
                                                $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                                $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                                Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                            }
                                        }
                                    } elseif (count($Setting_arr['SelectboxOptions'][0]) === 2) {
                                        // Es gibt nur eine Option
                                        $oPluginEinstellungenConfWerte                           = new stdClass();
                                        $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                        $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['SelectboxOptions'][0]['Option'];
                                        $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['value'];
                                        $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['sort'];

                                        Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                    }
                                } elseif ($cTyp === 'radio') {
                                    if (isset($Setting_arr['OptionsSource']) && is_array($Setting_arr['OptionsSource']) && count($Setting_arr['OptionsSource']) > 0) {

                                    } elseif (count($Setting_arr['RadioOptions'][0]) === 1) { // Es gibt mehr als eine Option
                                        foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                            preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);
                                            if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                $cWert = $Option_arr['value'];
                                                $nSort = $Option_arr['sort'];
                                                $yx    = substr($y, 0, strpos($y, ' '));
                                                $cName = $Setting_arr['RadioOptions'][0]['Option'][$yx];

                                                $oPluginEinstellungenConfWerte                           = new stdClass();
                                                $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                                $oPluginEinstellungenConfWerte->cName                    = $cName;
                                                $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                                $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                                Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                            }
                                        }
                                    } elseif (count($Setting_arr['RadioOptions'][0]) === 2) {
                                        // Es gibt nur eine Option
                                        $oPluginEinstellungenConfWerte                           = new stdClass();
                                        $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                        $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['RadioOptions'][0]['Option'];
                                        $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['RadioOptions'][0]['Option attr']['value'];
                                        $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['RadioOptions'][0]['Option attr']['sort'];

                                        Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                    }
                                }
                            } else {
                                return 6;// Eine Einstellung konnte nicht in die Datenbank gespeichert werden
                            }
                        }
                    }
                }
            }
        }
    }
    // FrontendLinks (falls vorhanden)
    foreach ($frontendNode as $u => $Link_arr) {
        preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
        preg_match("/[0-9]+/", $u, $cTreffer2_arr);
        $oLink = new stdClass();
        if (empty($Link_arr['LinkGroup'])) {
            // linkgroup not set? default to 'hidden'
            $Link_arr['LinkGroup'] = 'hidden';
        }
        $oLinkgruppe = Shop::DB()->select('tlinkgruppe', 'cName', $Link_arr['LinkGroup']);
        if ($oLinkgruppe === null) {
            // linkgroup not in database? create it anew
            $oLinkgruppe                = new stdClass();
            $oLinkgruppe->cName         = $Link_arr['LinkGroup'];
            $oLinkgruppe->cTemplatename = $Link_arr['LinkGroup'];
            $oLinkgruppe->kLinkgruppe   = Shop::DB()->insert('tlinkgruppe', $oLinkgruppe);
        }
        if (!isset($oLinkgruppe->kLinkgruppe) || $oLinkgruppe->kLinkgruppe <= 0) {
            return 12; // Es konnte keine Linkgruppe im Shop gefunden werden
        }
        $kLinkgruppe = $oLinkgruppe->kLinkgruppe;
        if (strlen($cTreffer2_arr[0]) !== strlen($u)) {
            continue;
        }
        $kLinkOld                  = (!empty($oPluginOld->kPlugin))
            ? Shop::DB()->select('tlink', 'kPlugin', $oPluginOld->kPlugin, 'cName', $Link_arr['Name'])
            : null;
        $oLink->kLinkgruppe        = $kLinkgruppe;
        $oLink->kPlugin            = $kPlugin;
        $oLink->cName              = $Link_arr['Name'];
        $oLink->nLinkart           = LINKTYP_PLUGIN;
        $oLink->cSichtbarNachLogin = $Link_arr['VisibleAfterLogin'];
        $oLink->cDruckButton       = $Link_arr['PrintButton'];
        $oLink->cNoFollow          = $Link_arr['NoFollow'] ?? null;
        $oLink->nSort              = LINKTYP_PLUGIN;
        $oLink->bSSL               = isset($Link_arr['SSL'])
            ? (int)$Link_arr['SSL']
            : 0;
        // tlink füllen
        $kLink = Shop::DB()->insert('tlink', $oLink);

        if ($kLink > 0) {
            $oLinkSprache        = new stdClass();
            $oLinkSprache->kLink = $kLink;
            // Hole alle Sprachen des Shops
            // Assoc cISO
            $oSprachAssoc_arr = gibAlleSprachen(2);
            // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
            // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
            // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
            $bLinkStandard   = false;
            $oLinkSpracheStd = new stdClass();

            foreach ($Link_arr['LinkLanguage'] as $l => $LinkLanguage_arr) {
                preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                preg_match("/[0-9]+/", $l, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                    $oLinkSprache->cISOSprache = strtolower($LinkLanguage_arr['iso']);
                } elseif (strlen($cTreffer2_arr[0]) === strlen($l)) {
                    // tlinksprache füllen
                    $oLinkSprache->cSeo             = checkSeo(getSeo($LinkLanguage_arr['Seo']));
                    $oLinkSprache->cName            = $LinkLanguage_arr['Name'];
                    $oLinkSprache->cTitle           = $LinkLanguage_arr['Title'];
                    $oLinkSprache->cContent         = '';
                    $oLinkSprache->cMetaTitle       = $LinkLanguage_arr['MetaTitle'];
                    $oLinkSprache->cMetaKeywords    = $LinkLanguage_arr['MetaKeywords'];
                    $oLinkSprache->cMetaDescription = $LinkLanguage_arr['MetaDescription'];

                    Shop::DB()->insert('tlinksprache', $oLinkSprache);
                    // Erste Linksprache vom Plugin als Standard setzen
                    if (!$bLinkStandard) {
                        $oLinkSpracheStd = $oLinkSprache;
                        $bLinkStandard   = true;
                    }

                    if ($oSprachAssoc_arr[$oLinkSprache->cISOSprache]->kSprache > 0) {
                        $or = isset($kLinkOld->kLink) ? (' OR kKey = ' . (int)$kLinkOld->kLink) : '';
                        Shop::DB()->query(
                            "DELETE FROM tseo
                                    WHERE cKey = 'kLink'
                                        AND (kKey = " . (int)$kLink . $or . ")
                                        AND kSprache = " . (int)$oSprachAssoc_arr[$oLinkSprache->cISOSprache]->kSprache, 4
                        );
                        // tseo füllen
                        $oSeo           = new stdClass();
                        $oSeo->cSeo     = checkSeo(getSeo($LinkLanguage_arr['Seo']));
                        $oSeo->cKey     = 'kLink';
                        $oSeo->kKey     = $kLink;
                        $oSeo->kSprache = $oSprachAssoc_arr[$oLinkSprache->cISOSprache]->kSprache;

                        Shop::DB()->insert('tseo', $oSeo);
                    }

                    if (isset($oSprachAssoc_arr[$oLinkSprache->cISOSprache])) {
                        // Resette aktuelle Sprache
                        unset($oSprachAssoc_arr[$oLinkSprache->cISOSprache]);
                        $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
                    }
                }
            }
            // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
            if (count($oSprachAssoc_arr) > 0) {
                foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                    //$oSprache = $oSprachAssoc;
                    if ($oSprachAssoc->kSprache > 0) {
                        Shop::DB()->delete(
                            'tseo',
                            ['cKey', 'kKey', 'kSprache'],
                            ['kLink', (int)$kLink, (int)$oSprachAssoc->kSprache]
                        );
                        // tseo füllen
                        $oSeo           = new stdClass();
                        $oSeo->cSeo     = checkSeo(getSeo($oLinkSpracheStd->cSeo));
                        $oSeo->cKey     = 'kLink';
                        $oSeo->kKey     = $kLink;
                        $oSeo->kSprache = $oSprachAssoc->kSprache;

                        Shop::DB()->insert('tseo', $oSeo);
                        // tlinksprache füllen
                        $oLinkSpracheStd->cSeo        = $oSeo->cSeo;
                        $oLinkSpracheStd->cISOSprache = $oSprachAssoc->cISO;
                        Shop::DB()->insert('tlinksprache', $oLinkSpracheStd);
                    }
                }
            }
            // tpluginhook füllen (spezieller Ausnahmefall für Frontend Links)
            $oPluginHook             = new stdClass();
            $oPluginHook->kPlugin    = $kPlugin;
            $oPluginHook->nHook      = HOOK_SEITE_PAGE_IF_LINKART;
            $oPluginHook->cDateiname = PLUGIN_SEITENHANDLER;

            $kPluginHook = Shop::DB()->insert('tpluginhook', $oPluginHook);

            if (!$kPluginHook) {
                return 3; // Ein Hook konnte nicht in die Datenbank gespeichert werden
            }
            // tpluginlinkdatei füllen
            $oPluginLinkDatei                      = new stdClass();
            $oPluginLinkDatei->kPlugin             = $kPlugin;
            $oPluginLinkDatei->kLink               = $kLink;
            $oPluginLinkDatei->cDatei              = $Link_arr['Filename'] ?? null;
            $oPluginLinkDatei->cTemplate           = $Link_arr['Template'] ?? null;
            $oPluginLinkDatei->cFullscreenTemplate = $Link_arr['FullscreenTemplate'] ?? null;

            Shop::DB()->insert('tpluginlinkdatei', $oPluginLinkDatei);
        } else {
            return 8; // Ein Link konnte nicht in die Datenbank gespeichert werden
        }
    }
    // Zahlungsmethode (PaymentMethod) (falls vorhanden)
    $shopURL = Shop::getURL(true) . '/';
    foreach ($paymentNode as $u => $Method_arr) {
        preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
        preg_match("/[0-9]+/", $u, $cTreffer2_arr);
        if (strlen($cTreffer2_arr[0]) !== strlen($u)) {
            continue;
        }
        $oZahlungsart                         = new stdClass();
        $oZahlungsart->cName                  = $Method_arr['Name'];
        $oZahlungsart->cModulId               = gibPlugincModulId($kPlugin, $Method_arr['Name']);
        $oZahlungsart->cKundengruppen         = '';
        $oZahlungsart->cPluginTemplate        = $Method_arr['TemplateFile'] ?? null;
        $oZahlungsart->cZusatzschrittTemplate = $Method_arr['AdditionalTemplateFile'] ?? null;
        $oZahlungsart->nSort                  = isset($Method_arr['Sort'])
            ? (int)$Method_arr['Sort']
            : 0;
        $oZahlungsart->nMailSenden            = isset($Method_arr['SendMail'])
            ? (int)$Method_arr['SendMail']
            : 0;
        $oZahlungsart->nActive                = 1;
        $oZahlungsart->cAnbieter              = is_array($Method_arr['Provider'])
            ? ''
            : $Method_arr['Provider'];
        $oZahlungsart->cTSCode                = is_array($Method_arr['TSCode'])
            ? ''
            : $Method_arr['TSCode'];
        $oZahlungsart->nWaehrendBestellung    = (int)$Method_arr['PreOrder'];
        $oZahlungsart->nCURL                  = (int)$Method_arr['Curl'];
        $oZahlungsart->nSOAP                  = (int)$Method_arr['Soap'];
        $oZahlungsart->nSOCKETS               = (int)$Method_arr['Sockets'];
        $oZahlungsart->cBild                  = isset($Method_arr['PictureURL'])
            ? $shopURL . PFAD_PLUGIN . $cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $nVersion . '/' .
            PFAD_PLUGIN_PAYMENTMETHOD . $Method_arr['PictureURL']
            : '';
        $oZahlungsart->nNutzbar = 0;
        $bPruefen               = false;
        if ($oZahlungsart->nCURL == 0 && $oZahlungsart->nSOAP == 0 && $oZahlungsart->nSOCKETS == 0) {
            $oZahlungsart->nNutzbar = 1;
        } else {
            $bPruefen = true;
        }
        $kZahlungsart               = Shop::DB()->insert('tzahlungsart', $oZahlungsart);
        $oZahlungsart->kZahlungsart = $kZahlungsart;

        if ($bPruefen) {
            aktiviereZahlungsart($oZahlungsart);
        }

        $cModulId = $oZahlungsart->cModulId;

        if (!$kZahlungsart) {
            return 9; //Eine Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
        }
        // tpluginzahlungsartklasse füllen
        $oPluginZahlungsartKlasse                         = new stdClass();
        $oPluginZahlungsartKlasse->cModulId               = gibPlugincModulId($kPlugin, $Method_arr['Name']);
        $oPluginZahlungsartKlasse->kPlugin                = $kPlugin;
        $oPluginZahlungsartKlasse->cClassPfad             = $Method_arr['ClassFile'] ?? null;
        $oPluginZahlungsartKlasse->cClassName             = $Method_arr['ClassName'] ?? null;
        $oPluginZahlungsartKlasse->cTemplatePfad          = $Method_arr['TemplateFile'] ?? null;
        $oPluginZahlungsartKlasse->cZusatzschrittTemplate = $Method_arr['AdditionalTemplateFile'] ?? null;

        Shop::DB()->insert('tpluginzahlungsartklasse', $oPluginZahlungsartKlasse);

        $cISOSprache = '';
        // Hole alle Sprachen des Shops
        // Assoc cISO
        $oSprachAssoc_arr = gibAlleSprachen(2);
        // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
        // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
        // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
        $bZahlungsartStandard   = false;
        $oZahlungsartSpracheStd = new stdClass();

        foreach ($Method_arr['MethodLanguage'] as $l => $MethodLanguage_arr) {
            preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
            preg_match("/[0-9]+/", $l, $cTreffer2_arr);
            if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                $cISOSprache = strtolower($MethodLanguage_arr['iso']);
            } elseif (strlen($cTreffer2_arr[0]) === strlen($l)) {
                $oZahlungsartSprache               = new stdClass();
                $oZahlungsartSprache->kZahlungsart = $kZahlungsart;
                $oZahlungsartSprache->cISOSprache  = $cISOSprache;
                $oZahlungsartSprache->cName        = $MethodLanguage_arr['Name'];
                $oZahlungsartSprache->cGebuehrname = $MethodLanguage_arr['ChargeName'];
                $oZahlungsartSprache->cHinweisText = $MethodLanguage_arr['InfoText'];
                // Erste ZahlungsartSprache vom Plugin als Standard setzen
                if (!$bZahlungsartStandard) {
                    $oZahlungsartSpracheStd = $oZahlungsartSprache;
                    $bZahlungsartStandard   = true;
                }
                $kZahlungsartTMP = Shop::DB()->insert('tzahlungsartsprache', $oZahlungsartSprache);
                if (!$kZahlungsartTMP) {
                    return 10; // Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
                }

                if (isset($oSprachAssoc_arr[$oZahlungsartSprache->cISOSprache])) {
                    // Resette aktuelle Sprache
                    unset($oSprachAssoc_arr[$oZahlungsartSprache->cISOSprache]);
                    $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
                }
            }
        }

        // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
        if (count($oSprachAssoc_arr) > 0) {
            foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                $oZahlungsartSpracheStd->cISOSprache = $oSprachAssoc->cISO;
                $kZahlungsartTMP                     = Shop::DB()->insert('tzahlungsartsprache', $oZahlungsartSpracheStd);
                if (!$kZahlungsartTMP) {
                    return 10; // Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
        // Zahlungsmethode Einstellungen
        // Vordefinierte Einstellungen
        $cName_arr         = ['Anzahl Bestellungen n&ouml;tig', 'Mindestbestellwert', 'Maximaler Bestellwert'];
        $cWertName_arr     = ['min_bestellungen', 'min', 'max'];
        $cBeschreibung_arr = [
            'Nur Kunden, die min. soviele Bestellungen bereits durchgef&uuml;hrt haben, k&ouml;nnen diese Zahlungsart nutzen.',
            'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.',
            'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)'];
        $nSort_arr         = [100, 101, 102];

        for ($z = 0; $z < 3; $z++) {
            // tplugineinstellungen füllen
            $oPluginEinstellungen          = new stdClass();
            $oPluginEinstellungen->kPlugin = $kPlugin;
            $oPluginEinstellungen->cName   = $cModulId . '_' . $cWertName_arr[$z];
            $oPluginEinstellungen->cWert   = 0;

            Shop::DB()->insert('tplugineinstellungen', $oPluginEinstellungen);
            // tplugineinstellungenconf füllen
            $oPluginEinstellungenConf                   = new stdClass();
            $oPluginEinstellungenConf->kPlugin          = $kPlugin;
            $oPluginEinstellungenConf->kPluginAdminMenu = 0;
            $oPluginEinstellungenConf->cName            = $cName_arr[$z];
            $oPluginEinstellungenConf->cBeschreibung    = $cBeschreibung_arr[$z];
            $oPluginEinstellungenConf->cWertName        = $cModulId . '_' . $cWertName_arr[$z];
            $oPluginEinstellungenConf->cInputTyp        = 'zahl';
            $oPluginEinstellungenConf->nSort            = $nSort_arr[$z];
            $oPluginEinstellungenConf->cConf            = 'Y';

            Shop::DB()->insert('tplugineinstellungenconf', $oPluginEinstellungenConf);
        }

        if (isset($Method_arr['Setting'])
            && is_array($Method_arr['Setting'])
            && count($Method_arr['Setting']) > 0
        ) {
            $cTyp          = '';
            $cInitialValue = '';
            $nSort         = 0;
            $cConf         = 'Y';
            $multiple      = false;
            foreach ($Method_arr['Setting'] as $j => $Setting_arr) {
                preg_match('/[0-9]+\sattr/', $j, $cTreffer3_arr);
                preg_match('/[0-9]+/', $j, $cTreffer4_arr);

                if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) === strlen($j)) {
                    $cTyp          = $Setting_arr['type'];
                    $multiple      = (isset($Setting_arr['multiple']) && $Setting_arr['multiple'] === 'Y' && $cTyp === 'selectbox');
                    $cInitialValue = ($multiple === true)
                        ? serialize([$Setting_arr['initialValue']])
                        : $Setting_arr['initialValue'];
                    $nSort         = $Setting_arr['sort'];
                    $cConf         = $Setting_arr['conf'];
                } elseif (strlen($cTreffer4_arr[0]) === strlen($j)) {
                    // tplugineinstellungen füllen
                    $oPluginEinstellungen          = new stdClass();
                    $oPluginEinstellungen->kPlugin = $kPlugin;
                    $oPluginEinstellungen->cName   = $cModulId . '_' . $Setting_arr['ValueName'];
                    $oPluginEinstellungen->cWert   = $cInitialValue;

                    Shop::DB()->insert('tplugineinstellungen', $oPluginEinstellungen);

                    // tplugineinstellungenconf füllen
                    $oPluginEinstellungenConf                   = new stdClass();
                    $oPluginEinstellungenConf->kPlugin          = $kPlugin;
                    $oPluginEinstellungenConf->kPluginAdminMenu = 0;
                    $oPluginEinstellungenConf->cName            = $Setting_arr['Name'];
                    $oPluginEinstellungenConf->cBeschreibung    = (!isset($Setting_arr['Description']) || is_array($Setting_arr['Description']))
                        ? ''
                        : $Setting_arr['Description'];
                    $oPluginEinstellungenConf->cWertName = $cModulId . '_' . $Setting_arr['ValueName'];
                    $oPluginEinstellungenConf->cInputTyp = $cTyp;
                    $oPluginEinstellungenConf->nSort     = $nSort;
                    $oPluginEinstellungenConf->cConf     = ($cTyp === 'selectbox' && $multiple === true)
                        ? 'M'
                        : $cConf;

                    $kPluginEinstellungenConf = Shop::DB()->insert('tplugineinstellungenconf', $oPluginEinstellungenConf);
                    // tplugineinstellungenconfwerte füllen
                    if ($kPluginEinstellungenConf <= 0) {
                        return 11; // Eine Einstellung der Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
                    }
                    // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                    if ($cTyp === 'selectbox') {
                        if (isset($Setting_arr['OptionsSource'])
                            && is_array($Setting_arr['OptionsSource'])
                            && count($Setting_arr['OptionsSource']) > 0
                        ) {
                            //do nothing for now
                        } elseif (count($Setting_arr['SelectboxOptions'][0]) === 1) {
                            // Es gibt mehr als eine Option
                            foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                preg_match('/[0-9]+\sattr/', $y, $cTreffer6_arr);

                                if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                    $cWert = $Option_arr['value'];
                                    $nSort = $Option_arr['sort'];
                                    $yx    = substr($y, 0, strpos($y, ' '));
                                    $cName = $Setting_arr['SelectboxOptions'][0]['Option'][$yx];

                                    $oPluginEinstellungenConfWerte                           = new stdClass();
                                    $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                    $oPluginEinstellungenConfWerte->cName                    = $cName;
                                    $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                    $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                    Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                }
                            }
                        } elseif (count($Setting_arr['SelectboxOptions'][0]) === 2) {
                            // Es gibt nur eine Option
                            $oPluginEinstellungenConfWerte                           = new stdClass();
                            $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                            $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['SelectboxOptions'][0]['Option'];
                            $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['value'];
                            $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['sort'];

                            Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                        }
                    } elseif ($cTyp === 'radio') {
                        if (isset($Setting_arr['OptionsSource'])
                            && is_array($Setting_arr['OptionsSource'])
                            && count($Setting_arr['OptionsSource']) > 0
                        ) {
                            //do nothing for now
                        } elseif (count($Setting_arr['RadioOptions'][0]) === 1) { // Es gibt mehr als eine Option
                            foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                preg_match('/[0-9]+\sattr/', $y, $cTreffer6_arr);
                                if (strlen($cTreffer6_arr[0]) === strlen($y)) {
                                    $cWert = $Option_arr['value'];
                                    $nSort = $Option_arr['sort'];
                                    $yx    = substr($y, 0, strpos($y, ' '));
                                    $cName = $Setting_arr['RadioOptions'][0]['Option'][$yx];

                                    $oPluginEinstellungenConfWerte                           = new stdClass();
                                    $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                    $oPluginEinstellungenConfWerte->cName                    = $cName;
                                    $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                    $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                    Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                }
                            }
                        } elseif (count($Setting_arr['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                            $oPluginEinstellungenConfWerte                           = new stdClass();
                            $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                            $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['RadioOptions'][0]['Option'];
                            $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['RadioOptions'][0]['Option attr']['value'];
                            $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['RadioOptions'][0]['Option attr']['sort'];

                            Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                        }
                    }
                }
            }
        }
    }
    // tboxvorlage füllen
    foreach ($boxesNode as $h => $Box_arr) {
        preg_match("/[0-9]+/", $h, $cTreffer3_arr);
        if (strlen($cTreffer3_arr[0]) === strlen($h)) {
            $oBoxvorlage              = new stdClass();
            $oBoxvorlage->kCustomID   = $kPlugin;
            $oBoxvorlage->eTyp        = 'plugin';
            $oBoxvorlage->cName       = $Box_arr['Name'];
            $oBoxvorlage->cVerfuegbar = $Box_arr['Available'];
            $oBoxvorlage->cTemplate   = $Box_arr['TemplateFile'];

            $kBoxvorlage = Shop::DB()->insert('tboxvorlage', $oBoxvorlage);

            if (!$kBoxvorlage) {
                return 13; //Eine Boxvorlage konnte nicht in die Datenbank gespeichert werden
            }
        }
    }
    // tplugintemplate füllen
    foreach ($cTemplate_arr as $cTemplate) {
        preg_match("/[a-zA-Z0-9\/_\-]+\.tpl/", $cTemplate, $cTreffer3_arr);
        if (strlen($cTreffer3_arr[0]) === strlen($cTemplate)) {
            $oPluginTemplate            = new stdClass();
            $oPluginTemplate->kPlugin   = $kPlugin;
            $oPluginTemplate->cTemplate = $cTemplate;

            $kPluginTemplate = Shop::DB()->insert('tplugintemplate', $oPluginTemplate);

            if (!$kPluginTemplate) {
                return 17; //Ein Template konnte nicht in die Datenbank gespeichert werden
            }
        }
    }
    // Emailtemplates (falls vorhanden)
    foreach ($mailNode as $u => $Template_arr) {
        preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
        preg_match("/[0-9]+/", $u, $cTreffer2_arr);

        $oTemplate = new stdClass();
        if (strlen($cTreffer2_arr[0]) !== strlen($u)) {
            continue;
        }
        $oTemplate->kPlugin       = $kPlugin;
        $oTemplate->cName         = $Template_arr['Name'];
        $oTemplate->cBeschreibung = is_array($Template_arr['Description'])
            ? $Template_arr['Description'][0]
            : $Template_arr['Description'];
        $oTemplate->cMailTyp      = $Template_arr['Type'] ?? 'text/html';
        $oTemplate->cModulId      = $Template_arr['ModulId'];
        $oTemplate->cDateiname    = $Template_arr['Filename'] ?? null;
        $oTemplate->cAktiv        = $Template_arr['Active'] ?? 'N';
        $oTemplate->nAKZ          = $Template_arr['AKZ'] ?? 0;
        $oTemplate->nAGB          = $Template_arr['AGB'] ?? 0;
        $oTemplate->nWRB          = $Template_arr['WRB'] ?? 0;
        $oTemplate->nWRBForm      = $Template_arr['WRBForm'] ?? 0;
        // tpluginemailvorlage füllen
        $kEmailvorlage = Shop::DB()->insert('tpluginemailvorlage', $oTemplate);

        if ($kEmailvorlage <= 0) {
            return 14; //Eine Emailvorlage konnte nicht in die Datenbank gespeichert werden
        }
        $oTemplateSprache                = new stdClass();
        $cISOSprache                     = '';
        $oTemplateSprache->kEmailvorlage = $kEmailvorlage;
        // Hole alle Sprachen des Shops
        // Assoc cISO
        $oSprachAssoc_arr = gibAlleSprachen(2);
        // Ist das erste Standard Template gesetzt worden? => wird etwas weiter unten gebraucht
        // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
        // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
        $bTemplateStandard   = false;
        $oTemplateSpracheStd = new stdClass();
        foreach ($Template_arr['TemplateLanguage'] as $l => $TemplateLanguage_arr) {
            preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
            preg_match("/[0-9]+/", $l, $cTreffer2_arr);
            if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                $cISOSprache = strtolower($TemplateLanguage_arr['iso']);
            } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($l)) {
                // tpluginemailvorlagesprache füllen
                $oTemplateSprache->kEmailvorlage = $kEmailvorlage;
                $oTemplateSprache->kSprache      = $oSprachAssoc_arr[$cISOSprache]->kSprache;
                $oTemplateSprache->cBetreff      = $TemplateLanguage_arr['Subject'];
                $oTemplateSprache->cContentHtml  = $TemplateLanguage_arr['ContentHtml'];
                $oTemplateSprache->cContentText  = $TemplateLanguage_arr['ContentText'];
                $oTemplateSprache->cPDFS         = $TemplateLanguage_arr['PDFS'] ?? null;
                $oTemplateSprache->cDateiname    = $TemplateLanguage_arr['Filename'] ?? null;

                if (!isset($oPluginOld->kPlugin) || !$oPluginOld->kPlugin) {
                    Shop::DB()->insert('tpluginemailvorlagesprache', $oTemplateSprache);
                }

                Shop::DB()->insert('tpluginemailvorlagespracheoriginal', $oTemplateSprache);
                // Erste Templatesprache vom Plugin als Standard setzen
                if (!$bTemplateStandard) {
                    $oTemplateSpracheStd = $oTemplateSprache;
                    $bTemplateStandard   = true;
                }

                if (isset($oSprachAssoc_arr[$cISOSprache])) {
                    // Resette aktuelle Sprache
                    unset($oSprachAssoc_arr[$cISOSprache]);
                    $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
                }
            }
        }
        // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
        if (count($oSprachAssoc_arr) > 0) {
            foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                //$oSprache = $oSprachAssoc;
                if ($oSprachAssoc->kSprache > 0) {
                    // tpluginemailvorlagesprache füllen
                    $oTemplateSpracheStd->kSprache = $oSprachAssoc->kSprache;

                    if (!isset($oPluginOld->kPlugin) || !$oPluginOld->kPlugin) {
                        Shop::DB()->insert('tpluginemailvorlagesprache', $oTemplateSpracheStd);
                    }

                    Shop::DB()->insert('tpluginemailvorlagespracheoriginal', $oTemplateSpracheStd);
                }
            }
        }
    }
    // tpluginsprachvariable + tpluginsprachvariablesprache füllen
    $oSprachStandardAssoc_arr = gibAlleSprachen(2);
    foreach ($localeNode as $t => $Variable_arr) {
        $oSprachAssoc_arr = $oSprachStandardAssoc_arr;
        preg_match("/[0-9]+/", $t, $cTreffer1_arr);
        if (strlen($cTreffer1_arr[0]) !== strlen($t)) {
            continue;
        }
        // tpluginsprachvariable füllen
        $oPluginSprachVariable          = new stdClass();
        $oPluginSprachVariable->kPlugin = $kPlugin;
        $oPluginSprachVariable->cName   = $Variable_arr['Name'];
        if (isset($Variable_arr['Description']) && is_array($Variable_arr['Description'])) {
            $oPluginSprachVariable->cBeschreibung = '';
        } else {
            $oPluginSprachVariable->cBeschreibung = preg_replace('/\s+/', ' ', $Variable_arr['Description']);
        }

        $kPluginSprachvariable = Shop::DB()->insert('tpluginsprachvariable', $oPluginSprachVariable);

        if ($kPluginSprachvariable <= 0) {
            return 7; // Eine Sprachvariable konnte nicht in die Datenbank gespeichert werden
        }
        // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
        // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
        // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
        $bVariableStandard   = false;
        $oVariableSpracheStd = new stdClass();
        // Nur eine Sprache vorhanden
        if (isset($Variable_arr['VariableLocalized attr'])
            && is_array($Variable_arr['VariableLocalized attr'])
            && count($Variable_arr['VariableLocalized attr']) > 0
        ) {
            // tpluginsprachvariablesprache füllen
            $oPluginSprachVariableSprache                        = new stdClass();
            $oPluginSprachVariableSprache->kPluginSprachvariable = $kPluginSprachvariable;
            $oPluginSprachVariableSprache->cISO                  = $Variable_arr['VariableLocalized attr']['iso'];
            $oPluginSprachVariableSprache->cName                 = preg_replace('/\s+/', ' ', $Variable_arr['VariableLocalized']);

            Shop::DB()->insert('tpluginsprachvariablesprache', $oPluginSprachVariableSprache);

            // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
            if (!$bVariableStandard) {
                $oVariableSpracheStd = $oPluginSprachVariableSprache;
                $bVariableStandard   = true;
            }

            if (isset($oSprachAssoc_arr[strtolower($oPluginSprachVariableSprache->cISO)])) {
                // Resette aktuelle Sprache
                unset($oSprachAssoc_arr[strtolower($oPluginSprachVariableSprache->cISO)]);
                $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
            }
        } elseif (isset($Variable_arr['VariableLocalized'])
            && is_array($Variable_arr['VariableLocalized'])
            && count($Variable_arr['VariableLocalized']) > 0
        ) {
            // Mehr Sprachen vorhanden
            foreach ($Variable_arr['VariableLocalized'] as $i => $VariableLocalized_arr) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);

                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    $cISO = $VariableLocalized_arr['iso'];
                    //$yx = substr($i, 0, 1);
                    $yx    = substr($i, 0, strpos($i, ' '));
                    $cName = $Variable_arr['VariableLocalized'][$yx];
                    // tpluginsprachvariablesprache füllen
                    $oPluginSprachVariableSprache                        = new stdClass();
                    $oPluginSprachVariableSprache->kPluginSprachvariable = $kPluginSprachvariable;
                    $oPluginSprachVariableSprache->cISO                  = $cISO;
                    $oPluginSprachVariableSprache->cName                 = preg_replace('/\s+/', ' ', $cName);

                    Shop::DB()->insert('tpluginsprachvariablesprache', $oPluginSprachVariableSprache);
                    // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
                    if (!$bVariableStandard) {
                        $oVariableSpracheStd = $oPluginSprachVariableSprache;
                        $bVariableStandard   = true;
                    }

                    if (isset($oSprachAssoc_arr[strtolower($oPluginSprachVariableSprache->cISO)])) {
                        // Resette aktuelle Sprache

                        unset($oSprachAssoc_arr[strtolower($oPluginSprachVariableSprache->cISO)]);
                        $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
                    }
                }
            }
        }
        // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
        if (count($oSprachAssoc_arr) > 0) {
            foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                $oVariableSpracheStd->cISO = strtoupper($oSprachAssoc->cISO);
                $kPluginSprachVariableTMP  = Shop::DB()->insert('tpluginsprachvariablesprache', $oVariableSpracheStd);
                if (!$kPluginSprachVariableTMP) {
                    return 7; // Eine Sprachvariable konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
    }
    // CheckBox tcheckboxfunktion fuellen
    foreach ($checkboxesNode as $t => $Function_arr) {
        preg_match("/[0-9]+/", $t, $cTreffer2_arr);
        if (strlen($cTreffer2_arr[0]) === strlen($t)) {
            $oCheckBoxFunktion          = new stdClass();
            $oCheckBoxFunktion->kPlugin = $kPlugin;
            $oCheckBoxFunktion->cName   = $Function_arr['Name'];
            $oCheckBoxFunktion->cID     = $oPlugin->cPluginID . '_' . $Function_arr['ID'];
            Shop::DB()->insert('tcheckboxfunktion', $oCheckBoxFunktion);
        }
    }
    // AdminWidgets tadminwidgets fuellen
    foreach ($widgetsNode as $u => $Widget_arr) {
        preg_match("/[0-9]+/", $u, $cTreffer2_arr);
        if (strlen($cTreffer2_arr[0]) !== strlen($u)) {
            continue;
        }
        $oAdminWidget               = new stdClass();
        $oAdminWidget->kPlugin      = $kPlugin;
        $oAdminWidget->cTitle       = $Widget_arr['Title'];
        $oAdminWidget->cClass       = $Widget_arr['Class'] . '_' . $oPlugin->cPluginID;
        $oAdminWidget->eContainer   = $Widget_arr['Container'];
        $oAdminWidget->cDescription = $Widget_arr['Description'];
        if (is_array($oAdminWidget->cDescription)) {
            //@todo: when description is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
            $oAdminWidget->cDescription = $oAdminWidget->cDescription[0];
        }
        $oAdminWidget->nPos      = $Widget_arr['Pos'];
        $oAdminWidget->bExpanded = $Widget_arr['Expanded'];
        $oAdminWidget->bActive   = $Widget_arr['Active'];
        $kWidget                 = Shop::DB()->insert('tadminwidgets', $oAdminWidget);

        if (!$kWidget) {
            return 15;// Ein AdminWidget konnte nicht in die Datenbank gespeichert werden
        }
    }
    // ExportFormate in texportformat fuellen
    foreach ($exportNode as $u => $Format_arr) {
        preg_match("/[0-9]+/", $u, $cTreffer2_arr);
        if (strlen($cTreffer2_arr[0]) !== strlen($u)) {
            continue;
        }
        $oExportformat                   = new stdClass();
        $oExportformat->kKundengruppe    = $kKundengruppeStd;
        $oExportformat->kSprache         = $kSpracheStd;
        $oExportformat->kWaehrung        = $kWaehrungStd;
        $oExportformat->kKampagne        = 0;
        $oExportformat->kPlugin          = $kPlugin;
        $oExportformat->cName            = $Format_arr['Name'];
        $oExportformat->cDateiname       = $Format_arr['FileName'];
        $oExportformat->cKopfzeile       = $Format_arr['Header'];
        $oExportformat->cContent         = (isset($Format_arr['Content']) && strlen($Format_arr['Content']) > 0)
            ? $Format_arr['Content']
            : 'PluginContentFile_' . $Format_arr['ContentFile'];
        $oExportformat->cFusszeile       = $Format_arr['Footer'] ?? null;
        $oExportformat->cKodierung       = $Format_arr['Encoding'] ?? 'ASCII';
        $oExportformat->nSpecial         = 0;
        $oExportformat->nVarKombiOption  = $Format_arr['VarCombiOption'] ?? 1;
        $oExportformat->nSplitgroesse    = $Format_arr['SplitSize'] ?? 0;
        $oExportformat->dZuletztErstellt = '0000-00-00 00:00:00';
        if (is_array($oExportformat->cKopfzeile)) {
            //@todo: when cKopfzeile is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
            $oExportformat->cKopfzeile = $oExportformat->cKopfzeile[0];
        }
        if (is_array($oExportformat->cContent)) {
            $oExportformat->cContent = $oExportformat->cContent[0];
        }
        if (is_array($oExportformat->cFusszeile)) {
            $oExportformat->cFusszeile = $oExportformat->cFusszeile[0];
        }
        $kExportformat = Shop::DB()->insert('texportformat', $oExportformat);

        if (!$kExportformat) {
            return 16;// Ein Exportformat konnte nicht in die Datenbank gespeichert werden
        }
        // Einstellungen
        // <OnlyStockGreaterZero>N</OnlyStockGreaterZero> => exportformate_lager_ueber_null
        $oExportformatEinstellungen                = new stdClass();
        $oExportformatEinstellungen->kExportformat = $kExportformat;
        $oExportformatEinstellungen->cName         = 'exportformate_lager_ueber_null';
        $oExportformatEinstellungen->cWert         = strlen($Format_arr['OnlyStockGreaterZero']) !== 0
            ? $Format_arr['OnlyStockGreaterZero']
            : 'N';
        Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
        // <OnlyPriceGreaterZero>N</OnlyPriceGreaterZero> => exportformate_preis_ueber_null
        $oExportformatEinstellungen                = new stdClass();
        $oExportformatEinstellungen->kExportformat = $kExportformat;
        $oExportformatEinstellungen->cName         = 'exportformate_preis_ueber_null';
        $oExportformatEinstellungen->cWert         = $Format_arr['OnlyPriceGreaterZero'] === 'Y'
            ? 'Y'
            : 'N';
        Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
        // <OnlyProductsWithDescription>N</OnlyProductsWithDescription> => exportformate_beschreibung
        $oExportformatEinstellungen                = new stdClass();
        $oExportformatEinstellungen->kExportformat = $kExportformat;
        $oExportformatEinstellungen->cName         = 'exportformate_beschreibung';
        $oExportformatEinstellungen->cWert         = $Format_arr['OnlyProductsWithDescription'] === 'Y'
            ? 'Y'
            : 'N';
        Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
        // <ShippingCostsDeliveryCountry>DE</ShippingCostsDeliveryCountry> => exportformate_lieferland
        $oExportformatEinstellungen                = new stdClass();
        $oExportformatEinstellungen->kExportformat = $kExportformat;
        $oExportformatEinstellungen->cName         = 'exportformate_lieferland';
        $oExportformatEinstellungen->cWert         = $Format_arr['ShippingCostsDeliveryCountry'];
        Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
        // <EncodingQuote>N</EncodingQuote> => exportformate_quot
        $oExportformatEinstellungen                = new stdClass();
        $oExportformatEinstellungen->kExportformat = $kExportformat;
        $oExportformatEinstellungen->cName         = 'exportformate_quot';
        $oExportformatEinstellungen->cWert         = $Format_arr['EncodingQuote'] === 'Y'
            ? 'Y'
            : 'N';
        Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
        // <EncodingDoubleQuote>N</EncodingDoubleQuote> => exportformate_equot
        $oExportformatEinstellungen                = new stdClass();
        $oExportformatEinstellungen->kExportformat = $kExportformat;
        $oExportformatEinstellungen->cName         = 'exportformate_equot';
        $oExportformatEinstellungen->cWert         = $Format_arr['EncodingDoubleQuote'] === 'Y'
            ? 'Y'
            : 'N';
        Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
        // <EncodingSemicolon>N</EncodingSemicolon> => exportformate_semikolon
        $oExportformatEinstellungen                = new stdClass();
        $oExportformatEinstellungen->kExportformat = $kExportformat;
        $oExportformatEinstellungen->cName         = 'exportformate_semikolon';
        $oExportformatEinstellungen->cWert         = $Format_arr['EncodingSemicolon'] === 'Y'
            ? 'Y'
            : 'N';
        Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
    }
    // Resourcen in tplugin_ressources fuellen
    foreach ($cssNode as $file) {
        if (isset($file['name'])) {
            $oFile          = new stdClass();
            $oFile->kPlugin = $kPlugin;
            $oFile->type    = 'css';
            $oFile->path     = $file['name'];
            $oFile->priority = $file['priority'] ?? 5;
            Shop::DB()->insert('tplugin_resources', $oFile);
            unset($oFile);
        }
    }
    foreach ($jsNode as $file) {
        if (isset($file['name'])) {
            $oFile          = new stdClass();
            $oFile->kPlugin = $kPlugin;
            $oFile->type    = 'js';
            $oFile->path     = $file['name'];
            $oFile->priority = $file['priority'] ?? 5;
            $oFile->position = $file['position'] ?? 'head';
            Shop::DB()->insert('tplugin_resources', $oFile);
            unset($oFile);
        }
    }

    return 0;
}

/**
 * Laedt das Plugin neu, d.h. liest die XML Struktur neu ein, fuehrt neue SQLs aus.
 *
 * @param Plugin $oPlugin
 * @param bool   $forceReload
 * @return int
 * 200 = kein Reload nötig, da info file älter als dZuletztAktualisiert
 * siehe return Codes von installierePluginVorbereitung()
 */
function reloadPlugin($oPlugin, $forceReload = false)
{
    $cXMLPath = PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PLUGIN_INFO_FILE;
    if (!file_exists($cXMLPath)) {
        return -1;
    }
    $oLastUpdate    = new DateTimeImmutable($oPlugin->dZuletztAktualisiert);
    $nLastUpdate    = $oLastUpdate->getTimestamp();
    $nLastXMLChange = filemtime($cXMLPath);

    if ($nLastXMLChange > $nLastUpdate || $forceReload === true) {
        return installierePluginVorbereitung($oPlugin->cVerzeichnis, $oPlugin);
    }

    return 200; // kein Reload nötig, da info file älter als dZuletztAktualisiert
}

/**
 * Wenn ein Update erfolgreich mit neuer kPlugin in der Datenbank ist
 * wird der alte kPlugin auf die neue Version übertragen und
 * die alte Plugin-Version deinstalliert.
 *
 * @param int    $kPlugin
 * @param Plugin $oPluginOld
 * @param int    $nXMLVersion
 * @return int
 * 1 = Alles O.K.
 * 2 = Übergabeparameter nicht korrekt
 * 3 = Update konnte nicht installiert werden
 */
function syncPluginUpdate($kPlugin, $oPluginOld, $nXMLVersion)
{
    $kPlugin    = (int)$kPlugin;
    $kPluginOld = (int)$oPluginOld->kPlugin;
    // Altes Plugin deinstallieren
    $nReturnValue = deinstallierePlugin($kPluginOld, $nXMLVersion, true, $kPlugin);

    if ($nReturnValue === 1) {
        // tplugin
        $upd          = new stdClass();
        $upd->kPlugin = $kPluginOld;
        Shop::DB()->update('tplugin', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginhook', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginadminmenu', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginsprachvariable', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tadminwidgets', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginsprachvariablecustomsprache', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tplugin_resources', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tplugincustomtabelle', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tplugintemplate', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginlinkdatei', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginemailvorlage', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('texportformat', 'kPlugin', $kPlugin, $upd);
        // tplugineinstellungen
        $oPluginEinstellung_arr = Shop::DB()->query(
            "SELECT *
                FROM tplugineinstellungen
                WHERE kPlugin IN (" . $kPluginOld . ", " . $kPlugin . ")
                ORDER BY kPlugin", 2
        );
        if (is_array($oPluginEinstellung_arr) && count($oPluginEinstellung_arr) > 0) {
            $oEinstellung_arr = [];
            foreach ($oPluginEinstellung_arr as $oPluginEinstellung) {
                $cName = str_replace(
                    ['kPlugin_' . $kPluginOld . '_', 'kPlugin_' . $kPlugin . '_'],
                    '',
                    $oPluginEinstellung->cName
                );
                if (!isset($oEinstellung_arr[$cName])) {
                    $oEinstellung_arr[$cName] = new stdClass();

                    $oEinstellung_arr[$cName]->kPlugin = $kPluginOld;
                    $oEinstellung_arr[$cName]->cName   = str_replace(
                        'kPlugin_' . $kPlugin . '_',
                        'kPlugin_' . $kPluginOld . '_',
                        $oPluginEinstellung->cName
                    );
                    $oEinstellung_arr[$cName]->cWert   = $oPluginEinstellung->cWert;
                }
            }
            Shop::DB()->query("
                DELETE FROM tplugineinstellungen
                    WHERE kPlugin IN (" . $kPluginOld . ", " . $kPlugin . ")", 3
            );

            foreach ($oEinstellung_arr as $oEinstellung) {
                Shop::DB()->insert('tplugineinstellungen', $oEinstellung);
            }
        }
        Shop::DB()->query(
            "UPDATE tplugineinstellungen
                SET kPlugin = " . $kPluginOld . ",
                    cName = REPLACE(cName, 'kPlugin_" . $kPlugin . "_', 'kPlugin_" . $kPluginOld . "_')
                WHERE kPlugin = " . $kPlugin, 3
        );
        // tplugineinstellungenconf
        Shop::DB()->query(
            "UPDATE tplugineinstellungenconf
                SET kPlugin = " . $kPluginOld . ",
                    cWertName = REPLACE(cWertName, 'kPlugin_" . $kPlugin . "_', 'kPlugin_" . $kPluginOld . "_')
                WHERE kPlugin = " . $kPlugin, 3
        );
        // tboxvorlage
        $upd = new stdClass();
        $upd->kCustomID = $kPluginOld;
        Shop::DB()->update('tboxvorlage', ['kCustomID', 'eTyp'], [$kPlugin, 'plugin'], $upd);
        // tpluginzahlungsartklasse
        Shop::DB()->query(
            "UPDATE tpluginzahlungsartklasse
                SET kPlugin = " . $kPluginOld . ",
                    cModulId = REPLACE(cModulId, 'kPlugin_" . $kPlugin . "_', 'kPlugin_" . $kPluginOld . "_')
                WHERE kPlugin = " . $kPlugin, 3
        );
        // tpluginemailvorlageeinstellungen
        //@todo: this part was really messed up - check.
        $oPluginEmailvorlageAlt = Shop::DB()->select('tpluginemailvorlage', 'kPlugin', $kPluginOld);
        $oEmailvorlage          = Shop::DB()->select('tpluginemailvorlage', 'kPlugin', $kPlugin);
        if (isset($oEmailvorlage->kEmailvorlage, $oPluginEmailvorlageAlt->kEmailvorlage)) {
            $upd = new stdClass();
            $upd->kEmailvorlage = $oEmailvorlage->kEmailvorlage;
            Shop::DB()->update('tpluginemailvorlageeinstellungen', 'kEmailvorlage', $oPluginEmailvorlageAlt->kEmailvorlage, $upd);
        }
        // tpluginemailvorlagesprache
        $kEmailvorlageNeu = 0;
        $kEmailvorlageAlt = 0;
        if (isset($oPluginOld->oPluginEmailvorlageAssoc_arr) && count($oPluginOld->oPluginEmailvorlageAssoc_arr) > 0) {
            foreach ($oPluginOld->oPluginEmailvorlageAssoc_arr as $cModulId => $oPluginEmailvorlageAlt) {
                $oPluginEmailvorlageNeu = Shop::DB()->select(
                    'tpluginemailvorlage',
                    'kPlugin',
                    $kPluginOld,
                    'cModulId',
                    $cModulId,
                    null,
                    null,
                    false,
                    'kEmailvorlage'
                );
                if (isset($oPluginEmailvorlageNeu->kEmailvorlage) && $oPluginEmailvorlageNeu->kEmailvorlage > 0) {
                    if ($kEmailvorlageNeu == 0 || $kEmailvorlageAlt == 0) {
                        $kEmailvorlageNeu = $oPluginEmailvorlageNeu->kEmailvorlage;
                        $kEmailvorlageAlt = $oPluginEmailvorlageAlt->kEmailvorlage;
                    }
                    $upd = new stdClass();
                    $upd->kEmailvorlage = $oPluginEmailvorlageNeu->kEmailvorlage;
                    Shop::DB()->update(
                        'tpluginemailvorlagesprache',
                        'kEmailvorlage',
                        $oPluginEmailvorlageAlt->kEmailvorlage,
                        $upd
                    );
                }
            }
        }
        // tpluginemailvorlageeinstellungen
        $upd = new stdClass();
        $upd->kEmailvorlage = $kEmailvorlageNeu;
        Shop::DB()->update('tpluginemailvorlageeinstellungen', 'kEmailvorlage', $kEmailvorlageAlt, $upd);
        // tlink
        $upd = new stdClass();
        $upd->kPlugin = $kPluginOld;
        Shop::DB()->update('tlink', 'kPlugin', $kPlugin, $upd);
        // tboxen
        // Ausnahme: Gibt es noch eine Boxenvorlage in der Pluginversion?
        // Falls nein -> lösche tboxen mit dem entsprechenden kPlugin
        $oObj = Shop::DB()->select('tboxvorlage', 'kCustomID', $kPluginOld, 'eTyp', 'plugin');
        if (isset($oObj->kBoxvorlage) && (int)$oObj->kBoxvorlage > 0) {
            // tboxen kCustomID
            $upd = new stdClass();
            $upd->kBoxvorlage = $oObj->kBoxvorlage;
            Shop::DB()->update('tboxen', 'kCustomID', $kPluginOld, $upd);
        } else {
            Shop::DB()->delete('tboxen', 'kCustomID', $kPluginOld);
        }
        // tcheckboxfunktion
        $upd = new stdClass();
        $upd->kPlugin = $kPluginOld;
        Shop::DB()->update('tcheckboxfunktion', 'kPlugin', $kPlugin, $upd);
        // tspezialseite
        Shop::DB()->update('tspezialseite', 'kPlugin', $kPlugin, $upd);
        // tzahlungsart
        $oZahlungsartOld_arr = Shop::DB()->query("
            SELECT kZahlungsart, cModulId
                FROM tzahlungsart
                WHERE cModulId LIKE 'kPlugin_{$kPluginOld}_%'", 2
        );
        foreach ($oZahlungsartOld_arr as $oZahlungsartOld) {
            $cModulIdNew     = str_replace("kPlugin_{$kPluginOld}_", "kPlugin_{$kPlugin}_", $oZahlungsartOld->cModulId);
            $oZahlungsartNew = Shop::DB()->query("
                  SELECT kZahlungsart
                      FROM tzahlungsart
                      WHERE cModulId LIKE '{$cModulIdNew}'", 1
            );
            $cNewSetSQL      = '';
            if (isset($oZahlungsartOld->kZahlungsart, $oZahlungsartNew->kZahlungsart)) {
                Shop::DB()->query(
                    "DELETE tzahlungsart, tzahlungsartsprache
                        FROM tzahlungsart
                        JOIN tzahlungsartsprache
                            ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                        WHERE tzahlungsart.kZahlungsart = " . $oZahlungsartOld->kZahlungsart, 3
                );

                $cNewSetSQL = " , kZahlungsart = " . $oZahlungsartOld->kZahlungsart;
                $upd = new stdClass();
                $upd->kZahlungsart = $oZahlungsartOld->kZahlungsart;
                Shop::DB()->update('tzahlungsartsprache', 'kZahlungsart', $oZahlungsartNew->kZahlungsart, $upd);
            }

            Shop::DB()->query(
                "UPDATE tzahlungsart
                    SET cModulId = '{$oZahlungsartOld->cModulId}'
                    " . $cNewSetSQL . "
                    WHERE cModulId LIKE '{$cModulIdNew}'", 3
            );
        }

        return PLUGIN_CODE_OK;
    }
    deinstallierePlugin($kPlugin, $nXMLVersion);

    return 3;
}

/**
 * Versucht, ein ausgewähltes Plugin zu deinstallieren
 *
 * @param int  $kPlugin
 * @param int  $nXMLVersion
 * @param bool $bUpdate
 * @param null $kPluginNew
 * @return int
 * 1 = Alles O.K.
 * 2 = $kPlugin wurde nicht übergeben
 * 3 = SQL-Fehler
 */
function deinstallierePlugin($kPlugin, $nXMLVersion, $bUpdate = false, $kPluginNew = null)
{
    $kPlugin = (int)$kPlugin;
    if ($kPlugin <= 0) {
        return PLUGIN_CODE_WRONG_PARAM; // $kPlugin wurde nicht übergeben
    }
    $oPlugin = new Plugin($kPlugin, false, true); // suppress reload = true um Endlosrekursion zu verhindern
    if (empty($oPlugin->kPlugin)) {
        return PLUGIN_CODE_NO_PLUGIN_FOUND;
    }
    if (!$bUpdate) {
        // Plugin wird vollständig deinstalliert
        if (isset($oPlugin->oPluginUninstall->kPluginUninstall) &&
            (int)$oPlugin->oPluginUninstall->kPluginUninstall > 0
        ) {
            try {
                include $oPlugin->cPluginUninstallPfad;
            } catch (Exception $exc) {
            }
        }
        // Custom Tables löschen
        $oCustomTabelle_arr = Shop::DB()->selectAll('tplugincustomtabelle', 'kPlugin', $kPlugin);
        foreach ($oCustomTabelle_arr as $oCustomTabelle) {
            Shop::DB()->query("DROP TABLE IF EXISTS " . $oCustomTabelle->cTabelle, 4);
        }
        doSQLDelete($kPlugin, $bUpdate, $kPluginNew);
    } else {
        // Plugin wird nur teilweise deinstalliert, weil es danach ein Update gibt
        doSQLDelete($kPlugin, $bUpdate, $kPluginNew);
    }
    Shop::Cache()->flushAll();
    // Deinstallation für eine höhere XML Version
    if ($nXMLVersion > 100) {
        return PLUGIN_CODE_OK;
    }

    if (($p = Plugin::bootstrapper($kPlugin)) !== null) {
        $p->uninstalled();
    }

    return PLUGIN_CODE_OK;
}

/**
 * @param int      $kPlugin
 * @param bool     $bUpdate
 * @param null|int $kPluginNew
 */
function doSQLDelete($kPlugin, $bUpdate, $kPluginNew = null)
{
    $kPlugin = (int)$kPlugin;
    // Kein Update => alles deinstallieren
    if (!$bUpdate) {
        Shop::DB()->query(
            "DELETE tpluginsprachvariablesprache, tpluginsprachvariablecustomsprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache
                    ON tpluginsprachvariablecustomsprache.cSprachvariable = tpluginsprachvariable.cName
                    AND tpluginsprachvariablecustomsprache.kPlugin = tpluginsprachvariable.kPlugin
                WHERE tpluginsprachvariable.kPlugin = " . $kPlugin, 3
        );

        Shop::DB()->delete('tplugineinstellungen', 'kPlugin', $kPlugin);
        Shop::DB()->delete('tplugincustomtabelle', 'kPlugin', $kPlugin);
        Shop::DB()->delete('tpluginlinkdatei', 'kPlugin', $kPlugin);
        Shop::DB()->query(
            "DELETE tzahlungsartsprache, tzahlungsart
                FROM tzahlungsart
                LEFT JOIN tzahlungsartsprache
                    ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tzahlungsart.cModulId LIKE 'kPlugin_" . $kPlugin . "_%'", 3
        );

        Shop::DB()->query(
            "DELETE tboxen, tboxvorlage
                FROM tboxvorlage
                LEFT JOIN tboxen ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE tboxvorlage.kCustomID = " . $kPlugin . "
                    AND tboxvorlage.eTyp = 'plugin'", 3
        );

        Shop::DB()->query(
            "DELETE tpluginemailvorlageeinstellungen, tpluginemailvorlagespracheoriginal,
                tpluginemailvorlage, tpluginemailvorlagesprache
                FROM tpluginemailvorlage
                LEFT JOIN tpluginemailvorlagespracheoriginal
                    ON tpluginemailvorlagespracheoriginal.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                LEFT JOIN tpluginemailvorlageeinstellungen
                    ON tpluginemailvorlageeinstellungen.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                LEFT JOIN tpluginemailvorlagesprache
                    ON tpluginemailvorlagesprache.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                WHERE tpluginemailvorlage.kPlugin = " . $kPlugin, 3
        );
    } else { // Update => nur teilweise deinstallieren
        Shop::DB()->query(
            "DELETE tpluginsprachvariablesprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                WHERE tpluginsprachvariable.kPlugin = " . $kPlugin, 3
        );

        Shop::DB()->delete('tboxvorlage', ['kCustomID', 'eTyp'], [$kPlugin, 'plugin']);
        Shop::DB()->delete('tpluginlinkdatei', 'kPlugin', $kPlugin);
        Shop::DB()->query(
            "DELETE tpluginemailvorlage, tpluginemailvorlagespracheoriginal
                FROM tpluginemailvorlage
                LEFT JOIN tpluginemailvorlagespracheoriginal
                    ON tpluginemailvorlagespracheoriginal.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                WHERE tpluginemailvorlage.kPlugin = " . $kPlugin, 3
        );
    }
    Shop::DB()->query(
        "DELETE tpluginsqlfehler, tpluginhook
            FROM tpluginhook
            LEFT JOIN tpluginsqlfehler
                ON tpluginsqlfehler.kPluginHook = tpluginhook.kPluginHook
            WHERE tpluginhook.kPlugin = " . $kPlugin, 3
    );
    Shop::DB()->delete('tpluginadminmenu', 'kPlugin', $kPlugin);
    Shop::DB()->query(
        "DELETE tplugineinstellungenconfwerte, tplugineinstellungenconf
            FROM tplugineinstellungenconf
            LEFT JOIN tplugineinstellungenconfwerte
                ON tplugineinstellungenconfwerte.kPluginEinstellungenConf = tplugineinstellungenconf.kPluginEinstellungenConf
            WHERE tplugineinstellungenconf.kPlugin = " . $kPlugin, 3
    );

    Shop::DB()->delete('tpluginuninstall', 'kPlugin', $kPlugin);
    //delete ressource entries
    Shop::DB()->delete('tplugin_resources', 'kPlugin', $kPlugin);
    // tlinksprache && tseo
    $oObj_arr = [];
    if ($kPluginNew !== null && $kPluginNew > 0) {
        $kPluginNew = (int)$kPluginNew;
        $oObj_arr   = Shop::DB()->query(
            "SELECT kLink
                FROM tlink
                WHERE kPlugin IN ({$kPlugin}, {$kPluginNew})
                    ORDER BY kLink", 2
        );
    }
    if (is_array($oObj_arr) && count($oObj_arr) === 2) {
        $oLinkspracheOld_arr = Shop::DB()->selectAll('tlinksprache', 'kLink', $oObj_arr[0]->kLink);
        if (is_array($oLinkspracheOld_arr) && count($oLinkspracheOld_arr) > 0) {
            $oSprachAssoc_arr = gibAlleSprachen(2);

            foreach ($oLinkspracheOld_arr as $oLinkspracheOld) {
                $_upd       = new stdClass();
                $_upd->cSeo = $oLinkspracheOld->cSeo;
                Shop::DB()->update(
                    'tlinksprache',
                    ['kLink', 'cISOSprache'],
                    [$oObj_arr[1]->kLink, $oLinkspracheOld->cISOSprache],
                    $_upd
                );
                $kSprache = $oSprachAssoc_arr[$oLinkspracheOld->cISOSprache]->kSprache;
                Shop::DB()->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $oObj_arr[0]->kLink, $kSprache]
                );
                $_upd       = new stdClass();
                $_upd->cSeo = $oLinkspracheOld->cSeo;
                Shop::DB()->update(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $oObj_arr[1]->kLink, $kSprache],
                    $_upd
                );
            }
        }
    }
    Shop::DB()->query(
        "DELETE tlinksprache, tseo, tlink
            FROM tlink
            LEFT JOIN tlinksprache
                ON tlinksprache.kLink = tlink.kLink
            LEFT JOIN tseo
                ON tseo.cKey = 'kLink'
                AND tseo.kKey = tlink.kLink
            WHERE tlink.kPlugin = " . $kPlugin, 3
    );
    Shop::DB()->delete('tpluginzahlungsartklasse', 'kPlugin', $kPlugin);
    Shop::DB()->delete('tplugintemplate', 'kPlugin', $kPlugin);
    Shop::DB()->delete('tcheckboxfunktion', 'kPlugin', $kPlugin);
    Shop::DB()->delete('tadminwidgets', 'kPlugin', $kPlugin);
    Shop::DB()->query(
        "DELETE texportformateinstellungen, texportformatqueuebearbeitet, texportformat
            FROM texportformat
            LEFT JOIN texportformateinstellungen
                ON texportformateinstellungen.kExportformat = texportformat.kExportformat
            LEFT JOIN texportformatqueuebearbeitet
                ON texportformatqueuebearbeitet.kExportformat = texportformat.kExportformat
            WHERE texportformat.kPlugin = " . $kPlugin, 3
    );
    Shop::DB()->delete('tplugin', 'kPlugin', $kPlugin);
}

/**
 * Versucht ein ausgewähltes Plugin zu aktivieren
 *
 * @param int $kPlugin
 * @return int
 */
function aktivierePlugin($kPlugin)
{
    $kPlugin = (int)$kPlugin;
    if ($kPlugin <= 0) {
        return PLUGIN_CODE_WRONG_PARAM;
    }
    $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
    if (empty($oPlugin->kPlugin)) {
        return PLUGIN_CODE_NO_PLUGIN_FOUND;
    }
    $cPfad        = PFAD_ROOT . PFAD_PLUGIN;
    $nReturnValue = pluginPlausi(0, $cPfad . $oPlugin->cVerzeichnis);

    if ($nReturnValue === PLUGIN_CODE_OK
        || $nReturnValue === PLUGIN_CODE_DUPLICATE_PLUGIN_ID
        || $nReturnValue === PLUGIN_CODE_OK_BUT_NOT_SHOP4_COMPATIBLE
    ) {
        $nRow              = Shop::DB()->update('tplugin', 'kPlugin', $kPlugin, (object)['nStatus' => 2]);
        Shop::DB()->update('tadminwidgets', 'kPlugin', $kPlugin, (object)['bActive' => 1]);
        Shop::DB()->update('tlink', 'kPlugin', $kPlugin, (object)['bIsActive' => 1]);

        if (($p = Plugin::bootstrapper($kPlugin)) !== null) {
            $p->enabled();
        }

        return $nRow > 0
            ? PLUGIN_CODE_OK
            : PLUGIN_CODE_NO_PLUGIN_FOUND;
    }

    return $nReturnValue; // Plugin konnte aufgrund eines Fehlers nicht aktiviert werden.
}

/**
 * Versucht ein ausgewähltes Plugin zu deaktivieren
 *
 * @param int $kPlugin
 * @return int
 */
function deaktivierePlugin($kPlugin)
{
    $kPlugin = (int)$kPlugin;
    if ($kPlugin <= 0) {
        return PLUGIN_CODE_WRONG_PARAM;
    }
    if (($p = Plugin::bootstrapper($kPlugin)) !== null) {
        $p->disabled();
    }
    Shop::DB()->update('tplugin', 'kPlugin', $kPlugin, (object)['nStatus' => 1]);
    Shop::DB()->update('tadminwidgets', 'kPlugin', $kPlugin, (object)['bActive' => 0]);
    Shop::DB()->update('tlink', 'kPlugin', $kPlugin, (object)['bIsActive' => 0]);

    Shop::Cache()->flushTags([CACHING_GROUP_PLUGIN . '_' . $kPlugin]);

    return PLUGIN_CODE_OK;
}

/**
 * Baut aus einer XML ein Objekt
 *
 * @param array $XML
 * @return stdClass
 */
function makeXMLToObj($XML)
{
    $oObj = new stdClass();
    if (isset($XML['jtlshop3plugin']) && is_array($XML['jtlshop3plugin'])) {
        if (!isset($XML['jtlshop3plugin'][0]['Install'][0]['Version'])) {
            return $oObj;
        }
        if (!isset($XML['jtlshop3plugin'][0]['Name'])) {
            return $oObj;
        }
        $node            = $XML['jtlshop3plugin'][0];
        $nLastVersionKey = count($node['Install'][0]['Version']) / 2 - 1;

        $oObj->cName           = $node['Name'];
        $oObj->cDescription    = $node['Description'] ?? '';
        $oObj->cAuthor         = $node['Author'] ?? '';
        $oObj->cPluginID       = $node['PluginID'];
        $oObj->cIcon           = $node['Icon'] ?? null;
        $oObj->cVerzeichnis    = $XML['cVerzeichnis'];
        $oObj->shop4compatible = !empty($XML['shop4compatible'])
            ? $XML['shop4compatible']
            : false;
        $oObj->nVersion        = $nLastVersionKey >= 0 && isset($node['Install'][0]['Version'][$nLastVersionKey . ' attr']['nr'])
            ? (int)$node['Install'][0]['Version'][$nLastVersionKey . ' attr']['nr']
            : 0;
        $oObj->cVersion        = number_format($oObj->nVersion / 100, 2);
    }

    if (empty($oObj->cName) && empty($oObj->cDescription) && !empty($XML['cVerzeichnis'])) {
        $oObj->cName        = $XML['cVerzeichnis'];
        $oObj->cDescription = '';
        $oObj->cVerzeichnis = $XML['cVerzeichnis'];
    }
    if (isset($XML['cFehlercode']) && strlen($XML['cFehlercode']) > 0) {
        $oObj->cFehlercode         = $XML['cFehlercode'];
        $oObj->cFehlerBeschreibung = mappePlausiFehler($XML['cFehlercode'], $oObj);
    }

    return $oObj;
}

/**
 * Führt das SQL einer bestimmten Version pro Plugin aus
 * Füllt tplugincustomtabelle falls Tabellen angelegt werden im SQL
 *
 * @param string        $cSQLDatei
 * @param int           $nVersion
 * @param Plugin|object $oPlugin
 * @return int
 */
function logikSQLDatei($cSQLDatei, $nVersion, $oPlugin)
{
    if (empty($cSQLDatei) || (int)$nVersion < 100 || (int)$oPlugin->kPlugin <= 0 || empty($oPlugin->cPluginID)) {
        return PLUGIN_CODE_SQL_MISSING_DATA;
    }
    $cSQL_arr = parseSQLDatei($cSQLDatei, $oPlugin->cVerzeichnis, $nVersion);

    if (!is_array($cSQL_arr) || count($cSQL_arr) === 0) {
        return PLUGIN_CODE_SQL_INVALID_FILE_CONTENT;
    }
    $sqlRegEx = "/xplugin[_]{1}" . $oPlugin->cPluginID . "[_]{1}[a-zA-Z0-9_]+/";
    foreach ($cSQL_arr as $cSQL) {
        $cSQL = removeNumerousWhitespaces($cSQL);
        // SQL legt eine neue Tabelle an => fülle tplugincustomtabelle
        if (stripos($cSQL, 'create table') !== false) {
            // when using "create table if not exists" statement, the table name is at index 5, otherwise at 2
            $tableNameAtIndex = (stripos($cSQL, 'create table if not exists') !== false) ? 5 : 2;
            $cSQLTMP_arr      = explode(' ', $cSQL);
            $cTabelle         = str_replace(["'", "`"], '', $cSQLTMP_arr[$tableNameAtIndex]);
            preg_match($sqlRegEx, $cTabelle, $cTreffer_arr);
            if (!isset($cTreffer_arr[0]) || strlen($cTreffer_arr[0]) !== strlen($cTabelle)) {
                return 5;// Versuch eine nicht Plugintabelle anzulegen
            }
            // Prüfen, ob nicht bereits vorhanden => Wenn nein, anlegen
            $oPluginCustomTabelleTMP = Shop::DB()->select('tplugincustomtabelle', 'cTabelle', $cTabelle);
            if (!isset($oPluginCustomTabelleTMP->kPluginCustomTabelle)
                || !$oPluginCustomTabelleTMP->kPluginCustomTabelle
            ) {
                $oPluginCustomTabelle           = new stdClass();
                $oPluginCustomTabelle->kPlugin  = $oPlugin->kPlugin;
                $oPluginCustomTabelle->cTabelle = $cTabelle;

                Shop::DB()->insert('tplugincustomtabelle', $oPluginCustomTabelle);
            }
        } elseif (stripos($cSQL, 'drop table') !== false) {
            // SQL versucht eine Tabelle zu löschen => prüfen ob es sich um eine Plugintabelle handelt
            // when using "drop table if exists" statement, the table name is at index 5, otherwise at 2
            $tableNameAtIndex = (stripos($cSQL, 'drop table if exists') !== false) ? 4 : 2;
            $cSQLTMP_arr      = explode(' ', removeNumerousWhitespaces($cSQL));
            $cTabelle         = str_replace(["'", "`"], '', $cSQLTMP_arr[$tableNameAtIndex]);
            preg_match($sqlRegEx, $cTabelle, $cTreffer_arr);
            if (strlen($cTreffer_arr[0]) !== strlen($cTabelle)) {
                return PLUGIN_CODE_SQL_WRONG_TABLE_NAME_DELETE;
            }
        }

        Shop::DB()->query($cSQL, 4);
        $nErrno = Shop::DB()->getErrorCode();
        // Es gab einen SQL Fehler => fülle tpluginsqlfehler
        if ($nErrno) {
            Jtllog::writeLog(
                'SQL Fehler beim Installieren des Plugins (' . $oPlugin->cName . '): ' .
                str_replace("'", '', Shop::DB()->getErrorMessage()),
                JTLLOG_LEVEL_ERROR,
                false,
                'kPlugin',
                $oPlugin->kPlugin
            );

            return PLUGIN_CODE_SQL_ERROR;
        }
    }

    return PLUGIN_CODE_OK;
}

/**
 * Mehrfach Leerzeichen entfernen
 *
 * @param string $cStr
 * @return mixed
 */
function removeNumerousWhitespaces($cStr)
{
    if (strlen($cStr) > 0) {
        while (strpos($cStr, '  ')) {
            $cStr = str_replace('  ', ' ', $cStr);
        }
    }

    return $cStr;
}

/**
 * Geht die angegebene SQL durch und formatiert diese. Immer 1 SQL pro Zeile.
 *
 * @param string $cSQLDatei
 * @param string $cVerzeichnis
 * @param int    $nVersion
 * @return array
 */
function parseSQLDatei($cSQLDatei, $cVerzeichnis, $nVersion)
{
    $cSQLDateiPfad = PFAD_ROOT . PFAD_PLUGIN . $cVerzeichnis . '/' .
        PFAD_PLUGIN_VERSION . $nVersion . '/' .
        PFAD_PLUGIN_SQL;

    if (!file_exists($cSQLDateiPfad . $cSQLDatei)) {
        return [];// SQL Datei existiert nicht
    }
    $file_handle = fopen($cSQLDateiPfad . $cSQLDatei, 'r');
    $cSQL_arr    = [];
    $cLine       = '';
    while (($cData = fgets($file_handle)) !== false) {
        $cData = trim($cData);
        if ($cData !== '' && strpos($cData, '--') !== 0) {
            if (strpos($cData, 'CREATE TABLE') !== false) {
                $cLine .= trim($cData);
            } elseif (strpos($cData, 'INSERT') !== false) {
                $cLine .= trim($cData);
            } else {
                $cLine .= trim($cData);
            }

            if (substr($cData, strlen($cData) - 1, 1) === ';') {
                $cSQL_arr[] = $cLine;
                $cLine      = '';
            }
        }
    }
    fclose($file_handle);

    return $cSQL_arr;
}

/**
 * Gibt die nächst höheren SQL Versionen als Array
 *
 * @param string $cPluginVerzeichnis
 * @param int    $nVersion
 * @return array|bool
 */
function gibHoehereSQLVersionen($cPluginVerzeichnis, $nVersion)
{
    $cSQLVerzeichnis = PFAD_ROOT . PFAD_PLUGIN . $cPluginVerzeichnis . '/' . PFAD_PLUGIN_VERSION;
    if (is_dir($cSQLVerzeichnis)) {
        $nVerzeichnis_arr = [];
        $Dir              = opendir($cSQLVerzeichnis);
        while (($cVerzeichnis = readdir($Dir)) !== false) {
            if ($cVerzeichnis !== '.' && $cVerzeichnis !== '..' && is_dir($cSQLVerzeichnis . $cVerzeichnis)) {
                $nVerzeichnis_arr[] = (int)$cVerzeichnis;
            }
        }
        closedir($Dir);
        if (count($nVerzeichnis_arr) > 0) {
            usort($nVerzeichnis_arr, 'pluginverwaltungcmp');
            foreach ($nVerzeichnis_arr as $i => $nVerzeichnis) {
                if ($nVersion > $nVerzeichnis) {
                    unset($nVerzeichnis_arr[$i]);
                }
            }

            return array_merge($nVerzeichnis_arr);
        }
    }

    return false;
}

/**
 * Hilfsfunktion für usort
 *
 * @param int $a
 * @param int $b
 * @return int
 */
function pluginverwaltungcmp($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return $a < $b ? -1 : 1;
}

/**
 * Holt alle PluginSprachvariablen (falls vorhanden)
 *
 * @param int $kPlugin
 * @return array
 */
function gibSprachVariablen($kPlugin)
{
    $return                 = [];
    $kPlugin                = (int)$kPlugin;
    $oPluginSprachvariablen = Shop::DB()->query(
        "SELECT
            tpluginsprachvariable.kPluginSprachvariable,
            tpluginsprachvariable.kPlugin,
            tpluginsprachvariable.cName,
            tpluginsprachvariable.cBeschreibung,
            COALESCE(tpluginsprachvariablecustomsprache.cISO, tpluginsprachvariablesprache.cISO)  AS cISO,
            COALESCE(tpluginsprachvariablecustomsprache.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache
                    ON tpluginsprachvariablecustomsprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = COALESCE(tpluginsprachvariablecustomsprache.cISO, tpluginsprachvariablesprache.cISO)
            WHERE tpluginsprachvariable.kPlugin = " . $kPlugin . "
            ORDER BY tpluginsprachvariable.kPluginSprachvariable", 9
    );
    if (is_array($oPluginSprachvariablen) && count($oPluginSprachvariablen) > 0) {
        $new = [];
        foreach ($oPluginSprachvariablen as $_sv) {
            if (!isset($new[$_sv['kPluginSprachvariable']])) {
                $var                                   = new stdClass();
                $var->kPluginSprachvariable            = $_sv['kPluginSprachvariable'];
                $var->kPlugin                          = $_sv['kPlugin'];
                $var->cName                            = $_sv['cName'];
                $var->cBeschreibung                    = $_sv['cBeschreibung'];
                $var->oPluginSprachvariableSprache_arr = [$_sv['cISO'] => $_sv['customValue']];
                $new[$_sv['kPluginSprachvariable']] = $var;
            } else {
                $new[$_sv['kPluginSprachvariable']]->oPluginSprachvariableSprache_arr[$_sv['cISO']] = $_sv['customValue'];
            }
        }
        $return = array_values($new);
    }

    return $return;
}

/**
 * @param int    $nFehlerCode
 * @param object $oPlugin
 * @return string
 */
function mappePlausiFehler($nFehlerCode, $oPlugin)
{
    $return = '';
    if ($nFehlerCode > 0) {
        $return = 'Fehler: ';
        switch ($nFehlerCode) {
            case PLUGIN_CODE_WRONG_PARAM:
                $return .= 'Die Plausibilität ist aufgrund fehlender Parameter abgebrochen.';
                break;
            case PLUGIN_CODE_DIR_DOES_NOT_EXIST:
                $return .= 'Das Pluginverzeichnis existiert nicht.';
                break;
            case PLUGIN_CODE_INFO_XML_MISSING:
                $return .= 'Die Informations XML Datei existiert nicht.';
                break;
            case PLUGIN_CODE_NO_PLUGIN_FOUND:
                $return .= 'Das ausgewählte Plugin wurde nicht in der Datenbank gefunden.';
                break;
            case PLUGIN_CODE_INVALID_NAME:
                $return .= 'Der Pluginname entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PLUGIN_ID:
                $return .= 'Die PluginID entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INSTALL_NODE_MISSING:
                $return .= 'Der Installationsknoten ist nicht vorhanden.';
                break;
            case PLUGIN_CODE_INVALID_XML_VERSION_NUMBER:
                $return .= 'Erste Versionsnummer entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_VERSION_NUMBER:
                $return .= 'Die Versionsnummer entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_DATE:
                $return .= 'Das Versionsdatum entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_SQL_FILE:
                $return .= 'SQL-Datei für die aktuelle Version existiert nicht.';
                break;
            case PLUGIN_CODE_MISSING_HOOKS:
                $return .= 'Keine Hooks vorhanden.';
                break;
            case PLUGIN_CODE_INVALID_HOOK:
                $return .= 'Die Hook-Werte entsprechen nicht den Konventionen.';
                break;
            case PLUGIN_CODE_INVALID_CUSTOM_LINK_NAME:
                $return .= 'CustomLink Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_CUSTOM_LINK_FILE_NAME:
                $return .= 'Dateiname entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_CUSTOM_LINK_FILE:
                $return .= 'CustomLink-Datei existiert nicht.';
                break;
            case PLUGIN_CODE_INVALID_CONFIG_LINK_NAME:
                $return .= 'EinstellungsLink Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_CONFIG:
                $return .= 'Einstellungen fehlen.';
                break;
            case PLUGIN_CODE_INVALID_CONFIG_TYPE:
                $return .= 'Einstellungen type entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_CONFIG_INITIAL_VALUE:
                $return .= 'Einstellungen initialValue entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_CONFIG_SORT_VALUE:
                $return .= 'Einstellungen sort entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_CONFIG_NAME:
                $return .= 'Einstellungen Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_CONFIG_SELECTBOX_OPTIONS:
                $return .= 'Keine SelectboxOptionen vorhanden.';
                break;
            case PLUGIN_CODE_INVALID_CONFIG_OPTION:
                $return .= 'Die Option entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_LANG_VARS:
                $return .= 'Keine Sprachvariablen vorhanden.';
                break;
            case PLUGIN_CODE_INVALID_LANG_VAR_NAME:
                $return .= 'Variable Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_LOCALIZED_LANG_VAR:
                $return .= 'Keine lokalisierte Sprachvariable vorhanden.';
                break;
            case PLUGIN_CODE_INVALID_LANG_VAR_ISO:
                $return .= 'Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_LOCALIZED_LANG_VAR_NAME:
                $return .= 'Der Name der lokalisierten Sprachvariable entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_HOOK_FILE:
                $return .= 'Die Hook-Datei ist nicht vorhanden.';
                break;
            case PLUGIN_CODE_MISSING_VERSION_DIR:
                $return .= 'Version existiert nicht im Versionsordner.';
                break;
            case PLUGIN_CODE_INVALID_CONF:
                $return .= 'Einstellungen conf entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_CONF_VALUE_NAME:
                $return .= 'Einstellungen ValueName entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_XML_VERSION:
                $return .= 'XML-Version entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_SHOP_VERSION:
                $return .= 'Shopversion entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_SHOP_VERSION_COMPATIBILITY:
                $return .= 'Shopversion ist zu niedrig.';
                break;
            case PLUGIN_CODE_MISSING_FRONTEND_LINKS:
                $return .= 'Keine Frontendlinks vorhanden, obwohl der Node angelegt wurde.';
                break;
            case PLUGIN_CODE_INVALID_FRONTEND_LINK_FILENAME:
                $return .= 'Link Filename entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONTEND_LINK_NAME:
                $return .= 'LinkName entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONEND_LINK_VISIBILITY:
                $return .= 'Angabe ob erst Sichtbar nach Login entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONEND_LINK_PRINT:
                $return .= 'Abgabe ob eine Druckbutton gezeigt werden soll entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONEND_LINK_ISO:
                $return .= 'Die ISO der Linksprache entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONEND_LINK_SEO:
                $return .= 'Der Seo Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONEND_LINK_NAME:
                $return .= 'Der Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONEND_LINK_TITLE:
                $return .= 'Der Title entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONEND_LINK_META_TITLE:
                $return .= 'Der MetaTitle entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONEND_LINK_META_KEYWORDS:
                $return .= 'Die MetaKeywords entsprechen nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONEND_LINK_META_DESCRIPTION:
                $return .= 'Die MetaDescription entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_NAME:
                $return .= 'Der Name in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_MAIL:
                $return .= 'Sende Mail in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_TSCODE:
                $return .= 'TSCode in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_PRE_ORDER:
                $return .= 'PreOrder in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_CLASS_FILE:
                $return .= 'ClassFile in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_PAYMENT_METHOD_FILE:
                $return .= 'Die Datei für die Klasse der Zahlungsmethode existiert nicht.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_TEMPLATE:
                $return .= 'TemplateFile in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_PAYMENT_METHOD_TEMPLATE:
                $return .= 'Die Datei für das Template der Zahlungsmethode existiert nicht.';
                break;
            case PLUGIN_CODE_MISSING_PAYMENT_METHOD_LANGUAGES:
                $return .= 'Keine Sprachen in den Zahlungsmethoden hinterlegt.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_LANGUAGE_ISO:
                $return .= 'Die ISO der Sprache in der Zahlungsmethode entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_NAME_LOCALIZED:
                $return .= 'Der Name in den Zahlungsmethoden Sprache entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_CHARGE_NAME:
                $return .= 'Der ChargeName in den Zahlungsmethoden Sprache entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_INFO_TEXT:
                $return .= 'Der InfoText in den Zahlungsmethoden Sprache entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_TYPE:
                $return .= 'Zahlungsmethode Einstellungen type entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_INITITAL_VALUE:
                $return .= 'Zahlungsmethode Einstellungen initialValue entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_SORT:
                $return .= 'Zahlungsmethode Einstellungen sort entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_CONF:
                $return .= 'Zahlungsmethode Einstellungen conf entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_CONFIG_NAME:
                $return .= 'Zahlungsmethode Einstellungen Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_VALUE_NAME:
                $return .= 'Zahlungsmethode Einstellungen ValueName entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS:
                $return .= 'Keine SelectboxOptionen vorhanden.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_OPTION:
                $return .= 'Die Option entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_SORT:
                $return .= 'Die Sortierung in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_SOAP:
                $return .= 'Soap in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_CURL:
                $return .= 'Curl in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_SOCKETS:
                $return .= 'Sockets in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_CLASS_NAME:
                $return .= 'ClassName in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FULLSCREEN_TEMPLATE:
                $return .= 'Der Fullscreen-Templatename entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_FRONTEND_LINK_TEMPLATE:
                $return .= 'Die Templatedatei für den Frontend Link existiert nicht.';
                break;
            case PLUGIN_CODE_TOO_MANY_FULLSCREEN_TEMPLATE_NAMES:
                $return .= 'Es darf nur ein Templatename oder ein Fullscreen Templatename existieren.';
                break;
            case PLUGIN_CODE_INVALID_FULLSCREEN_TEMPLATE_NAME:
                $return .= 'Der Fullscreen Templatename entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_FULLSCREEN_TEMPLATE_FILE:
                $return .= 'Die Fullscreen Templatedatei für den Frontend Link existiert nicht.';
                break;
            case PLUGIN_CODE_INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE:
                $return .= 'Für ein Frontend Link muss ein Templatename oder Fullscreen Templatename angegeben werden.';
                break;
            case PLUGIN_CODE_MISSING_BOX:
                $return .= 'Keine Box vorhanden.';
                break;
            case PLUGIN_CODE_INVALID_BOX_NAME:
                $return .= 'Box Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_BOX_TEMPLATE:
                $return .= 'Box Templatedatei entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_BOX_TEMPLATE_FILE:
                $return .= 'Box Templatedatei existiert nicht.';
                break;
            case PLUGIN_CODE_MISSING_LICENCE_FILE:
                $return .= 'Lizenzklasse existiert nicht.';
                break;
            case PLUGIN_CODE_INVALID_LICENCE_FILE_NAME:
                $return .= 'Name der Lizenzklasse entspricht nicht der konvention.';
                break;
            case PLUGIN_CODE_MISSING_LICENCE:
                $return .= 'Lizenklasse ist nicht definiert.';
                break;
            case PLUGIN_CODE_MISSING_LICENCE_CHECKLICENCE_METHOD:
                $return .= 'Methode checkLicence in der Lizenzklasse ist nicht definiert.';
                break;
            case PLUGIN_CODE_DUPLICATE_PLUGIN_ID:
                $return .= 'PluginID bereits in der Datenbank vorhanden.';
                break;
            case PLUGIN_CODE_MISSING_EMAIL_TEMPLATES:
                $return .= 'Keine Emailtemplates vorhanden, obwohl der Node angelegt wurde.';
                break;
            case PLUGIN_CODE_INVALID_TEMPLATE_NAME:
                $return .= 'Template Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_TEMPLATE_TYPE:
                $return .= 'Template Type entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_TEMPLATE_MODULE_ID:
                $return .= 'Template ModulId entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_TEMPLATE_ACTIVE:
                $return .= 'Template Active entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_TEMPLATE_AKZ:
                $return .= 'Template AKZ entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_TEMPLATE_AGB:
                $return .= 'Template AGB entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_TEMPLATE_WRB:
                $return .= 'Template WRB entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_EMAIL_TEMPLATE_ISO:
                $return .= 'Die ISO der Emailtemplate Sprache entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_EMAIL_TEMPLATE_SUBJECT:
                $return .= 'Der Subject Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_EMAIL_TEMPLATE_LANGUAGE:
                $return .= 'Keine Templatesprachen vorhanden.';
                break;
            case PLUGIN_CODE_INVALID_CHECKBOX_FUNCTION_NAME:
                $return .= 'CheckBoxFunction Name entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_CHECKBOX_FUNCTION_ID:
                $return .= 'CheckBoxFunction ID entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_FRONTEND_LINK_NO_FOLLOW:
                $return .= 'Frontend Link Attribut NoFollow entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_WIDGETS:
                $return .= 'Keine Widgets vorhanden.';
                break;
            case PLUGIN_CODE_INVALID_WIDGET_TITLE:
                $return .= 'Widget Title entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_WIDGET_CLASS:
                $return .= 'Widget Class entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_MISSING_WIDGET_CLASS_FILE:
                $return .= 'Die Datei für die Klasse des AdminWidgets existiert nicht.';
                break;
            case PLUGIN_CODE_INVALID_WIDGET_CONTAINER:
                $return .= 'Container im Widget entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_WIDGET_POS:
                $return .= 'Pos im Widget entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_WIDGET_EXPANDED:
                $return .= 'Expanded im Widget entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_WIDGET_ACTIVE:
                $return .= 'Active im Widget entspricht nicht der Konvention.';
                break;
            case PLUGIN_CODE_INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE:
                $return .= 'AdditionalTemplateFile in den Zahlungsmethoden entspricht nicht der Konvention';
                break;
            case PLUGIN_CODE_MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE:
                $return .= 'Die Datei für das Zusatzschritt-Template der Zahlungsmethode existiert nicht';
                break;
            case PLUGIN_CODE_MISSING_FORMATS:
                $return .= 'Keine Formate vorhanden';
                break;
            case PLUGIN_CODE_INVALID_FORMAT_NAME:
                $return .= 'Format Name entspricht nicht der Konvention';
                break;
            case PLUGIN_CODE_INVALID_FORMAT_FILE_NAME:
                $return .= 'Format Filename entspricht nicht der Konvention';
                break;
            case PLUGIN_CODE_MISSING_FORMAT_CONTENT:
                $return .= 'Format enthält weder Content, noch eine Contentdatei';
                break;
            case PLUGIN_CODE_INVALID_FORMAT_ENCODING:
                $return .= 'Format Encoding entspricht nicht der Konvention';
                break;
            case PLUGIN_CODE_INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY:
                $return .= 'Format ShippingCostsDeliveryCountry entspricht nicht der Konvention';
                break;
            case PLUGIN_CODE_INVALID_FORMAT_CONTENT_FILE:
                $return .= 'Format ContenFile entspricht nicht der Konvention';
                break;
            case PLUGIN_CODE_MISSING_EXTENDED_TEMPLATE:
                $return .= 'Kein Template vorhanden';
                break;
            case PLUGIN_CODE_INVALID_EXTENDED_TEMPLATE_FILE_NAME:
                $return .= 'Templatedatei entspricht nicht der Konvention';
                break;
            case PLUGIN_CODE_MISSING_EXTENDED_TEMPLATE_FILE:
                $return .= 'Templatedatei existiert nicht';
                break;
            case PLUGIN_CODE_MISSING_UNINSTALL_FILE:
                $return .= 'Uninstall File existiert nicht';
                break;
            case PLUGIN_CODE_IONCUBE_REQUIRED:
                $return .= 'Das Plugin ben&ouml;tigt ionCube';
                break;
            case PLUGIN_CODE_INVALID_OPTIONS_SOURE_FILE:
                $return .= 'OptionsSource-Datei wurde nicht angegeben';
                break;
            case PLUGIN_CODE_MISSING_OPTIONS_SOURE_FILE:
                $return .= 'OptionsSource-Datei existiert nicht';
                break;
            case PLUGIN_CODE_MISSING_BOOTSTRAP_CLASS:
                $return .= 'Bootstrap-Klasse "%cPluginID%\\Bootstrap" existiert nicht';
                break;
            case PLUGIN_CODE_INVALID_BOOTSTRAP_IMPLEMENTATION:
                $return .= 'Bootstrap-Klasse "%cPluginID%\\Bootstrap" muss das Interface "IPlugin" implementieren';
                break;
            case PLUGIN_CODE_INVALID_AUTHOR:
                $return .= 'Autor entspricht nicht der Konvention.';
                break;
            default:
                $return = 'Unbekannter Fehler.';
                break;
        }
    }

    $search = array_map(function ($val) {
        return sprintf('%%%s%%', $val);
    }, array_keys((array)$oPlugin));

    $replace = array_values((array)$oPlugin);

    return str_replace($search, $replace, $return);
}
