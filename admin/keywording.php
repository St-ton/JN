<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_META_KEYWORD_BLACKLIST_VIEW', true, true);

$sprachen = gibAlleSprachen();
if (isset($_POST['keywording']) && intval($_POST['keywording']) === 1 && validateToken()) {
    foreach ($sprachen as $sprache) {
        $text              = new stdClass();
        $text->cISOSprache = $sprache->cISO;
        $text->cKeywords   = $_POST['keywords_' . $sprache->cISO];
        Shop::DB()->delete('texcludekeywords', 'cISOSprache', $text->cISOSprache);
        Shop::DB()->insert('texcludekeywords', $text);
    }
    Shop::Cache()->flushTags(array(CACHING_GROUP_OPTION));
}
$keywords = array();
foreach ($sprachen as $sprache) {
    $text                     = Shop::DB()->select('texcludekeywords', 'cISOSprache', $sprache->cISO);
    $keywords[$sprache->cISO] = (!empty($text->cKeywords)) ? $text->cKeywords : '';
}
$smarty->assign('keywords', $keywords)
       ->assign('sprachen', $sprachen)
       ->display('keywording.tpl');
