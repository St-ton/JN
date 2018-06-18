<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'news_inc.php';

$oAccount->permission('RESET_SHOP_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
if (isset($_POST['zuruecksetzen']) && (int)$_POST['zuruecksetzen'] === 1 && FormHelper::validateToken()) {
    $cOption_arr = $_POST['cOption_arr'];
    if (is_array($cOption_arr) && count($cOption_arr) > 0) {
        foreach ($cOption_arr as $cOption) {
            switch ($cOption) {
                // JTL-Wawi Inhalte
                case 'artikel':
                    Shop::Container()->getDB()->query('TRUNCATE tartikel', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelabnahme', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelattribut', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelkategorierabatt', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelkonfiggruppe', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelmerkmal', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelpict', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelsichtbarkeit', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelsonderpreis', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelsprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tartikelwarenlager', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tattribut', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tattributsprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tbild', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaft', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaftkombiwert', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaftsichtbarkeit', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaftsprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaftwert', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaftwertabhaengigkeit', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaftwertaufpreis', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaftwertpict', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaftwertsichtbarkeit', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teigenschaftwertsprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE teinheit', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkategorie', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkategorieartikel', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkategorieattribut', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkategorieattributsprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkategoriekundengruppe', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkategoriemapping', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkategoriepict', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkategoriesichtbarkeit', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkategoriesprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tmediendatei', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tmediendateiattribut', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tmediendateisprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tmerkmal', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tmerkmalsprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tmerkmalwert', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tmerkmalwertbild', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tmerkmalwertsprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tpreise', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tpreis', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tpreisdetail', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tsonderpreise', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE txsell', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE txsellgruppe', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE thersteller', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE therstellersprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tlieferstatus', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkonfiggruppe', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkonfigitem', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkonfiggruppesprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkonfigitempreis', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkonfigitemsprache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenlager', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenlagersprache', \DB\ReturnType::DEFAULT);

                    Shop::Container()->getDB()->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kArtikel'
                            OR cKey = 'kKategorie'
                            OR cKey = 'kMerkmalWert'
                            OR cKey = 'kHersteller'",
                        \DB\ReturnType::DEFAULT
                    );
                    break;

                case 'revisions':
                    Shop::Container()->getDB()->query('TRUNCATE trevisions', \DB\ReturnType::DEFAULT);
                    break;

                // Shopinhalte
                case 'news':
                    $_index = Shop::Container()->getDB()->query(
                        'SELECT kNews FROM tnews', 
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                    foreach ($_index as $i) {
                        loescheNewsBilderDir($i->kNews, PFAD_ROOT . PFAD_NEWSBILDER);
                    }
                    Shop::Container()->getDB()->query('TRUNCATE tnews', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->delete('trevisions', 'type', 'news');
                    Shop::Container()->getDB()->query('TRUNCATE tnewskategorie', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tnewskategorienews', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tnewskommentar', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tnewsmonatsuebersicht', \DB\ReturnType::DEFAULT);

                    Shop::Container()->getDB()->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kNews'
                              OR cKey = 'kNewsKategorie'
                              OR cKey = 'kNewsMonatsUebersicht'",
                        \DB\ReturnType::DEFAULT
                    );
                    break;

                case 'bestseller':
                    Shop::Container()->getDB()->query('TRUNCATE tbestseller', \DB\ReturnType::DEFAULT);
                    break;

                case 'besucherstatistiken':
                    Shop::Container()->getDB()->query('TRUNCATE tbesucher', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tbesucherarchiv', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tbesuchteseiten', \DB\ReturnType::DEFAULT);
                    break;

                case 'preisverlaeufe':
                    Shop::Container()->getDB()->query('TRUNCATE tpreisverlauf', \DB\ReturnType::DEFAULT);
                    break;

                case 'umfragen':
                    Shop::Container()->getDB()->query('TRUNCATE tumfrage', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tumfragedurchfuehrung', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tumfragedurchfuehrungantwort', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tumfragefrage', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tumfragefrageantwort', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tumfragematrixoption', \DB\ReturnType::DEFAULT);

                    Shop::Container()->getDB()->delete('tseo', 'cKey', 'kUmfrage');
                    break;

                case 'verfuegbarkeitsbenachrichtigungen':
                    Shop::Container()->getDB()->query("TRUNCATE tverfuegbarkeitsbenachrichtigung", \DB\ReturnType::DEFAULT);
                    break;

                // Benutzergenerierte Inhalte
                case 'suchanfragen':
                    Shop::Container()->getDB()->query('TRUNCATE tsuchanfrage', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tsuchanfrageerfolglos', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tsuchanfragemapping', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tsuchanfragencache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tsuchcache', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tsuchcachetreffer', \DB\ReturnType::DEFAULT);

                    Shop::Container()->getDB()->delete('tseo', 'cKey', 'kSuchanfrage');
                    break;

                case 'tags':
                    Shop::Container()->getDB()->query('TRUNCATE ttagmapping', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE ttag', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE ttagartikel', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE ttagkunde', \DB\ReturnType::DEFAULT);

                    Shop::Container()->getDB()->delete('tseo', 'cKey', 'kTag');
                    break;

                case 'bewertungen':
                    Shop::Container()->getDB()->query('TRUNCATE tartikelext', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tbewertung', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tbewertungguthabenbonus', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tbewertunghilfreich', \DB\ReturnType::DEFAULT);
                    break;

                // Shopkunden & Kunden werben Kunden & Bestellungen & Kupons
                case 'shopkunden':
                    Shop::Container()->getDB()->query('TRUNCATE tkunde', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkundenattribut', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkundendatenhistory', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkundenfeld', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkundenfeldwert', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkundenherkunft', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkundenkontodaten', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkundenwerbenkunden', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tlieferadresse', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenkorbpers', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenkorbperspos', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenkorbpersposeigenschaft', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twunschliste', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twunschlistepos', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twunschlisteposeigenschaft', \DB\ReturnType::DEFAULT);
                    break;
                case 'kwerbenk':
                    Shop::Container()->getDB()->query('TRUNCATE tkundenwerbenkunden', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkundenwerbenkundenbonus', \DB\ReturnType::DEFAULT);
                    break;
                case 'bestellungen':
                    Shop::Container()->getDB()->query('TRUNCATE tbestellid', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tbestellstatus', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tbestellung', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tlieferschein', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tlieferscheinpos', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tlieferscheinposinfo', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenkorb', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenkorbpers', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenkorbperspos', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenkorbpersposeigenschaft', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenkorbpos', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE twarenkorbposeigenschaft', \DB\ReturnType::DEFAULT);
                    break;
                case 'kupons':
                    Shop::Container()->getDB()->query('TRUNCATE tkupon', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkuponbestellung', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkuponkunde', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkuponneukunde', \DB\ReturnType::DEFAULT);
                    Shop::Container()->getDB()->query('TRUNCATE tkuponsprache', \DB\ReturnType::DEFAULT);
                    break;
            }
        }
        Shop::Cache()->flushAll();
        Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = now()', \DB\ReturnType::DEFAULT);
        $cHinweis = 'Der Shop wurde mit Ihren gew&auml;hlten Optionen zur&uuml;ckgesetzt.';
    } else {
        $cFehler = 'Bitte w&auml;hlen Sie mindestens eine Option aus.';
    }

    executeHook(HOOK_BACKEND_SHOP_RESET_AFTER);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('shopzuruecksetzen.tpl');
