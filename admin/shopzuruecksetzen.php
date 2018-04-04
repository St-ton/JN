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
if (isset($_POST['zuruecksetzen']) && (int)$_POST['zuruecksetzen'] === 1 && validateToken()) {
    $cOption_arr = $_POST['cOption_arr'];
    if (is_array($cOption_arr) && count($cOption_arr) > 0) {
        foreach ($cOption_arr as $cOption) {
            switch ($cOption) {
                // JTL-Wawi Inhalte
                case 'artikel':
                    Shop::Container()->getDB()->query("TRUNCATE tartikel", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelabnahme", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelattribut", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelkategorierabatt", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelkonfiggruppe", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelmerkmal", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelpict", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelsichtbarkeit", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelsonderpreis", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelsprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tartikelwarenlager", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tattribut", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tattributsprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tbild", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaft", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaftkombiwert", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaftsichtbarkeit", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaftsprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaftwert", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaftwertabhaengigkeit", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaftwertaufpreis", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaftwertpict", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaftwertsichtbarkeit", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teigenschaftwertsprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE teinheit", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkategorie", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkategorieartikel", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkategorieattribut", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkategorieattributsprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkategoriekundengruppe", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkategoriemapping", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkategoriepict", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkategoriesichtbarkeit", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkategoriesprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tmediendatei", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tmediendateiattribut", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tmediendateisprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tmerkmal", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tmerkmalsprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tmerkmalwert", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tmerkmalwertbild", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tmerkmalwertsprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tpreise", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tpreis", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tpreisdetail", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tsonderpreise", 4);
                    Shop::Container()->getDB()->query("TRUNCATE txsell", 4);
                    Shop::Container()->getDB()->query("TRUNCATE txsellgruppe", 4);
                    Shop::Container()->getDB()->query("TRUNCATE thersteller", 4);
                    Shop::Container()->getDB()->query("TRUNCATE therstellersprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tlieferstatus", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkonfiggruppe", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkonfigitem", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkonfiggruppesprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkonfigitempreis", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkonfigitemsprache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenlager", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenlagersprache", 4);

                    Shop::Container()->getDB()->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kArtikel'
                            OR cKey = 'kKategorie'
                            OR cKey = 'kMerkmalWert'
                            OR cKey = 'kHersteller'", 4
                    );
                    break;

                case 'revisions':
                    Shop::Container()->getDB()->query("TRUNCATE trevisions", 4);
                    break;

                // Shopinhalte
                case 'news':
                    $_index = Shop::Container()->getDB()->query("SELECT kNews FROM tnews;", 2);
                    foreach ($_index as $i) {
                        loescheNewsBilderDir($i->kNews, PFAD_ROOT . PFAD_NEWSBILDER);
                    }
                    Shop::Container()->getDB()->query("TRUNCATE tnews", 4);
                    Shop::Container()->getDB()->delete('trevisions', 'type', 'news', 4);
                    Shop::Container()->getDB()->query("TRUNCATE tnewskategorie", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tnewskategorienews", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tnewskommentar", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tnewsmonatsuebersicht", 4);

                    Shop::Container()->getDB()->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kNews'
                              OR cKey = 'kNewsKategorie'
                              OR cKey = 'kNewsMonatsUebersicht'", 4
                    );
                    break;

                case 'bestseller':
                    Shop::Container()->getDB()->query("TRUNCATE tbestseller", 4);
                    break;

                case 'besucherstatistiken':
                    Shop::Container()->getDB()->query("TRUNCATE tbesucher", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tbesucherarchiv", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tbesuchteseiten", 4);
                    break;

                case 'preisverlaeufe':
                    Shop::Container()->getDB()->query("TRUNCATE tpreisverlauf", 4);
                    break;

                case 'umfragen':
                    Shop::Container()->getDB()->query("TRUNCATE tumfrage", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tumfragedurchfuehrung", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tumfragedurchfuehrungantwort", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tumfragefrage", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tumfragefrageantwort", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tumfragematrixoption", 4);

                    Shop::Container()->getDB()->delete('tseo', 'cKey', 'kUmfrage');
                    break;

                case 'verfuegbarkeitsbenachrichtigungen':
                    Shop::Container()->getDB()->query("TRUNCATE tverfuegbarkeitsbenachrichtigung", 4);
                    break;

                // Benutzergenerierte Inhalte
                case 'suchanfragen':
                    Shop::Container()->getDB()->query("TRUNCATE tsuchanfrage", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tsuchanfrageerfolglos", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tsuchanfragemapping", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tsuchanfragencache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tsuchcache", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tsuchcachetreffer", 4);

                    Shop::Container()->getDB()->delete('tseo', 'cKey', 'kSuchanfrage');
                    break;

                case 'tags':
                    Shop::Container()->getDB()->query("TRUNCATE ttagmapping", 4);
                    Shop::Container()->getDB()->query("TRUNCATE ttag", 4);
                    Shop::Container()->getDB()->query("TRUNCATE ttagartikel", 4);
                    Shop::Container()->getDB()->query("TRUNCATE ttagkunde", 4);

                    Shop::Container()->getDB()->delete('tseo', 'cKey', 'kTag');
                    break;

                case 'bewertungen':
                    Shop::Container()->getDB()->query("TRUNCATE tartikelext", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tbewertung", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tbewertungguthabenbonus", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tbewertunghilfreich", 4);
                    break;

                // Shopkunden & Kunden werben Kunden & Bestellungen & Kupons
                case 'shopkunden':
                    Shop::Container()->getDB()->query("TRUNCATE tkunde", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkundenattribut", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkundendatenhistory", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkundenfeld", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkundenfeldwert", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkundenherkunft", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkundenkontodaten", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkundenwerbenkunden", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tlieferadresse", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenkorbpers", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenkorbperspos", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenkorbpersposeigenschaft", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twunschliste", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twunschlistepos", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twunschlisteposeigenschaft", 4);
                    break;
                case 'kwerbenk':
                    Shop::Container()->getDB()->query("TRUNCATE tkundenwerbenkunden", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkundenwerbenkundenbonus", 4);
                    break;
                case 'bestellungen':
                    Shop::Container()->getDB()->query("TRUNCATE tbestellid", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tbestellstatus", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tbestellung", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tlieferschein", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tlieferscheinpos", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tlieferscheinposinfo", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenkorb", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenkorbpers", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenkorbperspos", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenkorbpersposeigenschaft", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenkorbpos", 4);
                    Shop::Container()->getDB()->query("TRUNCATE twarenkorbposeigenschaft", 4);
                    break;
                case 'kupons':
                    Shop::Container()->getDB()->query("TRUNCATE tkupon", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkuponbestellung", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkuponkunde", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkuponneukunde", 4);
                    Shop::Container()->getDB()->query("TRUNCATE tkuponsprache", 4);
                    break;
            }
        }
        Shop::Cache()->flushAll();
        Shop::Container()->getDB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
        $cHinweis = 'Der Shop wurde mit Ihren gew&auml;hlten Optionen zur&uuml;ckgesetzt.';
    } else {
        $cFehler = 'Bitte w&auml;hlen Sie mindestens eine Option aus.';
    }

    executeHook(HOOK_BACKEND_SHOP_RESET_AFTER);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('shopzuruecksetzen.tpl');
