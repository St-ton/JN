<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$smarty->registerPlugin('function', 'gibPreisStringLocalizedSmarty', 'gibPreisStringLocalizedSmarty')
    ->registerPlugin('function', 'load_boxes', 'load_boxes')
    ->registerPlugin('function', 'load_boxes_raw', 'load_boxes_raw')
    ->registerPlugin('function', 'has_boxes', 'has_boxes')
    ->registerPlugin('function', 'image', 'get_img_tag')
    ->registerPlugin('function', 'getCheckBoxForLocation', 'getCheckBoxForLocation')
    ->registerPlugin('function', 'hasCheckBoxForLocation', 'hasCheckBoxForLocation')
    ->registerPlugin('function', 'aaURLEncode', 'aaURLEncode')
    ->registerPlugin('function', 'get_navigation', 'get_navigation')
    ->registerPlugin('function', 'ts_data', 'get_trustedshops_data')
    ->registerPlugin('function', 'get_category_array', 'get_category_array')
    ->registerPlugin('function', 'get_category_parents', 'get_category_parents')
    ->registerPlugin('function', 'prepare_image_details', 'prepare_image_details')
    ->registerPlugin('function', 'get_manufacturers', 'get_manufacturers')
    ->registerPlugin('function', 'get_cms_content', 'get_cms_content')
    ->registerPlugin('modifier', 'has_trans', 'has_translation')
    ->registerPlugin('modifier', 'trans', 'get_translation');

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return array
 */
function get_manufacturers($params, &$smarty)
{
    $helper        = HerstellerHelper::getInstance();
    $manufacturers = $helper->getManufacturers();
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $manufacturers);
    } else {
        return $manufacturers;
    }
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return string
 */
function load_boxes_raw($params, &$smarty)
{
    if (isset($params['array']) && $params['array'] === true && isset($params['assign'])) {
        $boxes   = Boxen::getInstance();
        $rawData = $boxes->getRawData();

        $smarty->assign($params['assign'], (isset($rawData[$params['type']]) ? $rawData[$params['type']] : null));
    }
}

/**
 * @param array     $params - categoryId mainCategoryId. 0 for first level categories
 * @param JTLSmarty $smarty
 * @return array|void
 */
function get_category_array($params = array(), &$smarty)
{
    $id = (isset($params['categoryId'])) ?
        (int)$params['categoryId'] :
        0;
    if ($id === 0) {
        $categories = KategorieHelper::getInstance();
        $list       = $categories->combinedGetAll();
    } else {
        $categories = new KategorieListe();
        $list       = $categories->getAllCategoriesOnLevel($id);
    }

    if (isset($params['categoryBoxNumber']) && (int)$params['categoryBoxNumber'] > 0) {
        $list2 = array();
        foreach ($list as $key => $oList) {
            if (isset($oList->KategorieAttribute[KAT_ATTRIBUT_KATEGORIEBOX]) && $oList->KategorieAttribute[KAT_ATTRIBUT_KATEGORIEBOX] == $params['categoryBoxNumber']) {
                $list2[$key] = $oList;
            }
        }
        $list = $list2;
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $list);
    } else {
        return $list;
    }
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return array|void
 */
function get_category_parents($params = array(), &$smarty)
{
    $id         = (isset($params['categoryId'])) ?
        (int)$params['categoryId'] :
        0;
    $category   = new Kategorie($id);
    $categories = new KategorieListe();
    $list       = $categories->getOpenCategories($category);

    array_shift($list);
    $list = array_reverse($list);

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $list);
    } else {
        return $list;
    }
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return string
 */
function get_img_tag($params, &$smarty)
{
    if (empty($params['src'])) {
        return '';
    }
    $oImgSize = get_image_size($params['src']);

    $imageURL   = $params['src'];
    $imageID    = isset($params['id']) ? ' id="' . $params['id'] . '"' : '';
    $imageALT   = isset($params['alt']) ? ' alt="' . truncate($params['alt'], 75) . '"' : '';
    $imageTITLE = isset($params['title']) ? ' title="' . truncate($params['title'], 75) . '"' : '';
    $imageCLASS = isset($params['class']) ? ' class="' . truncate($params['class'], 75) . '"' : '';
    if ($oImgSize != null && $oImgSize->size->width > 0 && $oImgSize->size->height > 0) {
        return '<img src="' . $imageURL . '" width="' . $oImgSize->size->width . '" height="' . $oImgSize->size->height . '"' . $imageID . $imageALT . $imageTITLE . $imageCLASS . ' />';
    }

    return '<img src="' . $imageURL . '"' . $imageID . $imageALT . $imageTITLE . $imageCLASS . ' />';
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return string
 */
function load_boxes($params, &$smarty)
{
    $cTplData     = '';
    $cOldTplDir   = '';
    $boxes        = Boxen::getInstance();
    $oBoxen_arr   = $boxes->compatGet();
    $cTemplateDir = $smarty->getTemplateDir($smarty->context);
    if (is_array($oBoxen_arr) && isset($params['type'])) {
        $cType   = $params['type'];
        $_sBoxes = $smarty->getTemplateVars('boxes');
        if (isset($_sBoxes[$cType]) && isset($oBoxen_arr[$cType]) && is_array($oBoxen_arr[$cType])) {
            foreach ($oBoxen_arr[$cType] as $oBox) {
                $oPluginVar = '';
                $cTemplate = 'tpl_inc/boxes/' . $oBox->cTemplate;
                if ($oBox->eTyp === 'plugin') {
                    $oPlugin = new Plugin($oBox->kCustomID);
                    if ($oPlugin->kPlugin > 0 && $oPlugin->nStatus == 2) {
                        $cTemplate    = $oBox->cTemplate;
                        $cOldTplDir   = $cTemplateDir;
                        $cTemplateDir = $oPlugin->cFrontendPfad . PFAD_PLUGIN_BOXEN;

                        $oPluginVar = 'oPlugin' . $oBox->kBox;
                        $smarty->assign($oPluginVar, $oPlugin);
                    }
                } elseif ($oBox->eTyp === 'link') {
                    $LinkHelper = LinkHelper::getInstance();
                    $linkGroups = $LinkHelper->getLinkGroups();
                    foreach ($linkGroups as $oLinkTpl) {
                        if ($oLinkTpl->kLinkgruppe == $oBox->kCustomID) {
                            $oBox->oLinkGruppeTemplate = $oLinkTpl;
                            $oBox->oLinkGruppe         = $oLinkTpl;
                        }
                    }
                }
                if (file_exists($cTemplateDir . '/' . $cTemplate)) {
                    $oBoxVar = 'oBox' . $oBox->kBox;
                    $smarty->assign($oBoxVar, $oBox);
                    // Custom Template
                    global $Einstellungen;
                    if ($Einstellungen['template']['general']['use_customtpl'] === 'Y') {
                        $cTemplatePath   = pathinfo($cTemplate);
                        $cCustomTemplate = $cTemplatePath['dirname'] . '/' . $cTemplatePath['filename'] . '_custom.tpl';
                        if (file_exists($cTemplateDir . '/' . $cCustomTemplate)) {
                            $cTemplate = $cCustomTemplate;
                        }
                    }
                    $cTemplatePath = $cTemplateDir . '/' . $cTemplate;
                    if ($oBox->eTyp === 'plugin') {
                        $cTplData .= "{include file='" . $cTemplatePath . "' oBox=\$$oBoxVar oPlugin=\$$oPluginVar}";
                    } else {
                        $cTplData .= "{include file='" . $cTemplatePath . "' oBox=\$$oBoxVar}";
                    }

                    if (strlen($cOldTplDir)) {
                        $cTemplateDir = $cOldTplDir;
                    }
                }
            }
        }
    }
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $cTplData);
    } else {
        return $cTplData;
    }
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 */
function has_boxes($params, &$smarty)
{
    $boxes = Boxen::getInstance();
    $smarty->assign($params['assign'], isset($boxes->boxes[$params['position']]));
}

/**
 * @param string $text
 * @param int    $numb
 * @return string
 */
function truncate($text, $numb)
{
    if (strlen($text) > $numb) {
        $text = substr($text, 0, $numb);
        $text = substr($text, 0, strrpos($text, ' '));
        $text .= '...';
    }

    return $text;
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return mixed|string
 */
function gibPreisStringLocalizedSmarty($params, &$smarty)
{
    $oAufpreis = new stdClass();
    if (doubleval($params['fAufpreisNetto']) != 0) {
        $fAufpreisNetto         = doubleval($params['fAufpreisNetto']);
        $fVKNetto               = doubleval($params['fVKNetto']);
        $kSteuerklasse          = intval($params['kSteuerklasse']);
        $fVPEWert               = doubleval($params['fVPEWert']);
        $cVPEEinheit            = $params['cVPEEinheit'];
        $FunktionsAttribute_arr = $params['FunktionsAttribute'];
        $nGenauigkeit = (isset($FunktionsAttribute_arr[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]) && intval($FunktionsAttribute_arr[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]) > 0) ?
            intval($FunktionsAttribute_arr[FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]) :
            2;

        if (intval($params['nNettoPreise']) === 1) {
            $oAufpreis->cAufpreisLocalized = gibPreisStringLocalized($fAufpreisNetto);
            $oAufpreis->cPreisInklAufpreis = gibPreisStringLocalized($fAufpreisNetto + $fVKNetto);
            $oAufpreis->cAufpreisLocalized = ($fAufpreisNetto > 0) ?
                ('+ ' . $oAufpreis->cAufpreisLocalized) :
                (str_replace('-', '- ', $oAufpreis->cAufpreisLocalized));

            if ($fVPEWert > 0) {
                $oAufpreis->cPreisVPEWertAufpreis = gibPreisStringLocalized(
                        $fAufpreisNetto / $fVPEWert,
                        $_SESSION['Waehrung'],
                        1,
                        $nGenauigkeit
                    ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $cVPEEinheit;
                $oAufpreis->cPreisVPEWertInklAufpreis = gibPreisStringLocalized(
                        ($fAufpreisNetto + $fVKNetto) / $fVPEWert,
                        $_SESSION['Waehrung'],
                        1,
                        $nGenauigkeit
                    ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $cVPEEinheit;

                $oAufpreis->cAufpreisLocalized = $oAufpreis->cAufpreisLocalized . ', ' . $oAufpreis->cPreisVPEWertAufpreis;
                $oAufpreis->cPreisInklAufpreis = $oAufpreis->cPreisInklAufpreis . ', ' . $oAufpreis->cPreisVPEWertInklAufpreis;
            }
        } else {
            $oAufpreis->cAufpreisLocalized = gibPreisStringLocalized(berechneBrutto($fAufpreisNetto, $_SESSION['Steuersatz'][$kSteuerklasse]));
            $oAufpreis->cPreisInklAufpreis = gibPreisStringLocalized(berechneBrutto($fAufpreisNetto + $fVKNetto, $_SESSION['Steuersatz'][$kSteuerklasse]));
            $oAufpreis->cAufpreisLocalized = ($fAufpreisNetto > 0) ?
                ('+ ' . $oAufpreis->cAufpreisLocalized) :
                (str_replace('-', '- ', $oAufpreis->cAufpreisLocalized));

            if ($fVPEWert > 0) {
                $oAufpreis->cPreisVPEWertAufpreis = gibPreisStringLocalized(
                        berechneBrutto($fAufpreisNetto / $fVPEWert, $_SESSION['Steuersatz'][$kSteuerklasse]),
                        $_SESSION['Waehrung'],
                        1, $nGenauigkeit
                    ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $cVPEEinheit;
                $oAufpreis->cPreisVPEWertInklAufpreis = gibPreisStringLocalized(
                        berechneBrutto(($fAufpreisNetto + $fVKNetto) / $fVPEWert,
                            $_SESSION['Steuersatz'][$kSteuerklasse]),
                        $_SESSION['Waehrung'],
                        1,
                        $nGenauigkeit
                    ) . ' ' . Shop::Lang()->get('vpePer', 'global') . ' ' . $cVPEEinheit;

                $oAufpreis->cAufpreisLocalized = $oAufpreis->cAufpreisLocalized . ', ' . $oAufpreis->cPreisVPEWertAufpreis;
                $oAufpreis->cPreisInklAufpreis = $oAufpreis->cPreisInklAufpreis . ', ' . $oAufpreis->cPreisVPEWertInklAufpreis;
            }
        }
    }

    return (isset($params['bAufpreise']) && (int)$params['bAufpreise'] > 0) ?
        $oAufpreis->cAufpreisLocalized :
        $oAufpreis->cPreisInklAufpreis;
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 */
function hasCheckBoxForLocation($params, &$smarty)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.CheckBox.php';

    $oCheckBox     = new CheckBox();
    $oCheckBox_arr = $oCheckBox->getCheckBoxFrontend(intval($params['nAnzeigeOrt']), 0, true, true);

    $smarty->assign($params['bReturn'], count($oCheckBox_arr) > 0);
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return string
 */
function getCheckBoxForLocation($params, &$smarty)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.CheckBox.php';

    $oCheckBox     = new CheckBox();
    $oCheckBox_arr = $oCheckBox->getCheckBoxFrontend(intval($params['nAnzeigeOrt']), 0, true, true);

    if (count($oCheckBox_arr) > 0) {
        foreach ($oCheckBox_arr as $oCheckBox) {
            // Link URL bauen
            $cLinkURL = '';
            if ($oCheckBox->kLink > 0) {
                $oLinkTMP = Shop::DB()->select('tseo', 'cKey', 'kLink', 'kKey', (int)$oCheckBox->kLink, 'kSprache', (int)$_SESSION['kSprache'], false, 'cSeo');
                if (isset($oLinkTMP->cSeo) && strlen($oLinkTMP->cSeo) > 0) {
                    $oCheckBox->oLink->cLocalizedSeo[$_SESSION['cISOSprache']] = $oLinkTMP->cSeo;
                }

                $cLinkURL = baueURL($oCheckBox->oLink, URLART_SEITE);
            }
            // Fehlende Angaben
            $bError              = isset($params['cPlausi_arr'][$oCheckBox->cID]);
            $cPost_arr           = $params['cPost_arr'];
            $oCheckBox->isActive = false;
            if (isset($cPost_arr[$oCheckBox->cID])) {
                $oCheckBox->isActive = true;
            }

            $oCheckBox->cName = $oCheckBox->oCheckBoxSprache_arr[$_SESSION['kSprache']]->cText;

            if (strlen($cLinkURL) > 0) {
                $oCheckBox->cLinkURL = $cLinkURL;
            }
            if (isset($oCheckBox->oCheckBoxSprache_arr[$_SESSION['kSprache']]->cBeschreibung) && strlen($oCheckBox->oCheckBoxSprache_arr[$_SESSION['kSprache']]->cBeschreibung) > 0) {
                $oCheckBox->cBeschreibung = $oCheckBox->oCheckBoxSprache_arr[$_SESSION['kSprache']]->cBeschreibung;
            }
            if ($bError) {
                $oCheckBox->cErrormsg = Shop::Lang()->get('pleasyAccept', 'account data');
            }
        }

        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $oCheckBox_arr);
        }
    }
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return string
 */
function aaURLEncode($params, &$smarty)
{
    $bReset         = (isset($params['nReset']) && (int)$params['nReset'] === 1);
    $cURL           = $_SERVER['REQUEST_URI'];
    $cParameter_arr = array('&aaParams', '?aaParams', '&aaReset', '?aaReset');
    $aaEnthalten    = false;
    foreach ($cParameter_arr as $cParameter) {
        $aaEnthalten = strpos($cURL, $cParameter);
        if ($aaEnthalten !== false) {
            $cURL = substr($cURL, 0, $aaEnthalten);
            break;
        }

        $aaEnthalten = false;
    }
    if ($aaEnthalten !== false) {
        $cURL = substr($cURL, 0, $aaEnthalten);
    }
    if (isset($params['bUrlOnly']) && intval($params['bUrlOnly']) === 1) {
        return $cURL;
    }
    $cParams = '';
    unset($params['nReset']);
    if (is_array($params) && count($params) > 0) {
        foreach ($params as $key => $param) {
            $cParams .= $key . '=' . $param . ';';
        }
    }

    if (strpos($cURL, '?') !== false) {
        $cURL .= $bReset ? '&aaReset=' : '&aaParams=';
    } else {
        $cURL .= $bReset ? '?aaReset=' : '?aaParams=';
    }

    return $cURL . base64_encode($cParams);
}

/**
 * @param array $params - ['type'] Templatename of link, ['assign'] array name to assign
 * @param JTLSmarty $smarty
 */
function get_navigation($params, &$smarty)
{
    $linkgroupIdentifier = $params['linkgroupIdentifier'];
    $oLinkGruppe         = null;
    if (strlen($linkgroupIdentifier) > 0) {
        $LinkHelper  = LinkHelper::getInstance();
        $linkGroups  = $LinkHelper->getLinkGroups();
        $oLinkGruppe = (isset($linkGroups->{$linkgroupIdentifier})) ?
            $linkGroups->{$linkgroupIdentifier} :
            null;
    }

    if (is_object($oLinkGruppe) && isset($params['assign'])) {
        $smarty->assign($params['assign'], build_navigation_subs($oLinkGruppe));
    }
}

/**
 * @param object $oLink_arr
 * @param int   $kVaterLink
 * @return array
 */
function build_navigation_subs($oLink_arr, $kVaterLink = 0)
{
    $oNew_arr = array();
    if ($oLink_arr->cName !== 'hidden') {
        $cISO = $_SESSION['cISOSprache'];
        foreach ($oLink_arr->Links as &$oLink) {
            if ($oLink->kVaterLink == $kVaterLink) {
                $oLink->oSub_arr = build_navigation_subs($oLink_arr, $oLink->kLink);
                //append bIsActive property
                $oLink->bIsActive = false;
                if (isset($GLOBALS['kLink']) && $GLOBALS['kLink'] == $oLink->kLink) {
                    $oLink->bIsActive = true;
                }
                //append cTitle property
                $cTitle = '';
                if (isset($oLink->cLocalizedTitle[$cISO]) && $oLink->cLocalizedTitle[$cISO] != $oLink->cLocalizedName[$cISO]) {
                    $cTitle = StringHandler::htmlentities($oLink->cLocalizedTitle[$cISO], ENT_QUOTES);
                }
                $oLink->cTitle = $cTitle;
                $oNew_arr[] = $oLink;
            }
        }
    }

    return $oNew_arr;
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 */
function get_trustedshops_data($params, &$smarty)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.TrustedShops.php';
    $oTrustedShops   = new TrustedShops(-1, StringHandler::convertISO2ISO639($_SESSION['cISOSprache']));
    $value['tsId']   = $oTrustedShops->tsId;
    $value['nAktiv'] = $oTrustedShops->nAktiv;

    $smarty->assign($params['assign'], $value);
}

/**
 * @param array     $params
 * @param JTLSmarty $smarty
 * @return array|mixed|object|string|void
 */
function prepare_image_details($params, &$smarty)
{
    if (!isset($params['item'])) {
        return;
    }

    $result = [
        'xs' => get_image_size($params['item']->cPfadMini),
        'sm' => get_image_size($params['item']->cPfadKlein),
        'md' => get_image_size($params['item']->cPfadNormal),
        'lg' => get_image_size($params['item']->cPfadGross)
    ];

    if (isset($params['type'])) {
        $type = $params['type'];
        if (isset($result[$type])) {
            $result = $result[$type];
        }
    }

    $result = (object) $result;

    if (isset($params['json']) && $params['json']) {
        return json_encode($result, JSON_FORCE_OBJECT);
    }

    return $result;
}

/**
 * @param string $image
 * @return object|void
 */
function get_image_size($image)
{
    if (!file_exists($image)) {
        $req = MediaImage::toRequest($image);

        if (!is_object($req)) {
            return null;
        }

        $settings = Image::getSettings();
        $refImage = $req->getRaw();

        list($width, $height, $type, $attr) = getimagesize($refImage);

        $size       = $settings['size'][$req->getSizeType()];
        $max_width  = $size['width'];
        $max_height = $size['height'];
        $old_width  = $width;
        $old_height = $height;

        $scale  = min($max_width / $old_width, $max_height / $old_height);
        $width  = ceil($scale * $old_width);
        $height = ceil($scale * $old_height);
    } else {
        list($width, $height, $type, $attr) = getimagesize($image);
    }

    return (object)[
        'src'  => $image,
        'size' => (object)[
            'width' => $width,
            'height' => $height
        ],
        'type' => $type
    ];
}

/**
 * @param array $params
 * @param JTLSmarty $smarty
 * @return mixed
 */
function get_cms_content($params, &$smarty)
{
    if (isset($params['kLink']) && intval($params['kLink']) > 0) {
        $kLink          = intval($params['kLink']);
        $linkHelper     = LinkHelper::getInstance();
        $oLink          = $linkHelper->getPageLink($kLink);
        $oLink->Sprache = $linkHelper->getPageLinkLanguage($oLink->kLink);
        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $oLink->Sprache->cContent);
        } else {
            return $oLink->Sprache->cContent;
        }
    }
}

/**
 * Input: ['ger' => 'Titel', 'eng' => 'Title']
 *
 * @param string|array $mixed
 * @param string|null $to - locale
 * @return null|string
 */
function get_translation($mixed, $to = null)
{
    $to = $to ?: Shop::getLanguage(true);

    if (has_translation($mixed, $to)) {
        return is_string($mixed)
            ? $mixed : $mixed[$to];
    }
    return null;
}

/**
 * Has any translation
 *
 * @param string|array $mixed
 * @param string|null $to - locale
 * @return bool
 */
function has_translation($mixed, $to = null)
{
    $to = $to ?: Shop::getLanguage(true);

    return (is_string($mixed)) ?: isset($mixed[$to]);
}
