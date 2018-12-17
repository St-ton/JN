<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'news_inc.php';

$oAccount->permission('RESET_SHOP_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$db       = Shop::Container()->getDB();
if (isset($_POST['zuruecksetzen']) && (int)$_POST['zuruecksetzen'] === 1 && Form::validateToken()) {
    $cOption_arr = $_POST['cOption_arr'];
    if (is_array($cOption_arr) && count($cOption_arr) > 0) {
        foreach ($cOption_arr as $cOption) {
            switch ($cOption) {
                // JTL-Wawi Inhalte
                case 'artikel':
                    $db->query('TRUNCATE tartikel', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelabnahme', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelattribut', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelkategorierabatt', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelkonfiggruppe', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelmerkmal', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelpict', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelsichtbarkeit', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelsonderpreis', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelsprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tartikelwarenlager', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tattribut', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tattributsprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbild', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaft', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftkombiwert', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftsichtbarkeit', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftsprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwert', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertabhaengigkeit', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertaufpreis', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertpict', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertsichtbarkeit', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teigenschaftwertsprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE teinheit', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategorie', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategorieartikel', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategorieattribut', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategorieattributsprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriekundengruppe', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriemapping', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriepict', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriesichtbarkeit', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkategoriesprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmediendatei', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmediendateiattribut', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmediendateisprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmal', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmalsprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmalwert', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmalwertbild', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmerkmalwertsprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tpreise', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tpreis', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tpreisdetail', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsonderpreise', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE txsell', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE txsellgruppe', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE thersteller', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE therstellersprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferstatus', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfiggruppe', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfigitem', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfiggruppesprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfigitempreis', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkonfigitemsprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenlager', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenlagersprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tuploadschema', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tuploadschemasprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmasseinheit', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tmasseinheitsprache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsteuerklasse', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsteuersatz', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsteuerzone', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsteuerzoneland', \DB\ReturnType::DEFAULT);

                    $db->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kArtikel'
                            OR cKey = 'kKategorie'
                            OR cKey = 'kMerkmalWert'
                            OR cKey = 'kHersteller'",
                        \DB\ReturnType::DEFAULT
                    );
                    break;

                case 'revisions':
                    $db->query('TRUNCATE trevisions', \DB\ReturnType::DEFAULT);
                    break;

                // Shopinhalte
                case 'news':
                    $_index = $db->query(
                        'SELECT kNews FROM tnews',
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                    foreach ($_index as $i) {
                        loescheNewsBilderDir($i->kNews, PFAD_ROOT . PFAD_NEWSBILDER);
                    }
                    $db->query('TRUNCATE tnews', \DB\ReturnType::DEFAULT);
                    $db->delete('trevisions', 'type', 'news');
                    $db->query('TRUNCATE tnewskategorie', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tnewskategorienews', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tnewskommentar', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tnewsmonatsuebersicht', \DB\ReturnType::DEFAULT);

                    $db->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kNews'
                              OR cKey = 'kNewsKategorie'
                              OR cKey = 'kNewsMonatsUebersicht'",
                        \DB\ReturnType::DEFAULT
                    );
                    break;

                case 'bestseller':
                    $db->query('TRUNCATE tbestseller', \DB\ReturnType::DEFAULT);
                    break;

                case 'besucherstatistiken':
                    $db->query('TRUNCATE tbesucher', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbesucherarchiv', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbesuchteseiten', \DB\ReturnType::DEFAULT);
                    break;

                case 'preisverlaeufe':
                    $db->query('TRUNCATE tpreisverlauf', \DB\ReturnType::DEFAULT);
                    break;

                case 'umfragen':
                    $db->query('TRUNCATE tumfrage', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tumfragedurchfuehrung', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tumfragedurchfuehrungantwort', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tumfragefrage', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tumfragefrageantwort', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tumfragematrixoption', \DB\ReturnType::DEFAULT);

                    $db->delete('tseo', 'cKey', 'kUmfrage');
                    break;

                case 'verfuegbarkeitsbenachrichtigungen':
                    $db->query(
                        'TRUNCATE tverfuegbarkeitsbenachrichtigung',
                        \DB\ReturnType::DEFAULT
                    );
                    break;

                // Benutzergenerierte Inhalte
                case 'suchanfragen':
                    $db->query('TRUNCATE tsuchanfrage', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchanfrageerfolglos', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchanfragemapping', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchanfragencache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchcache', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tsuchcachetreffer', \DB\ReturnType::DEFAULT);

                    $db->delete('tseo', 'cKey', 'kSuchanfrage');
                    break;

                case 'tags':
                    $db->query('TRUNCATE ttagmapping', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE ttag', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE ttagartikel', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE ttagkunde', \DB\ReturnType::DEFAULT);

                    $db->delete('tseo', 'cKey', 'kTag');
                    break;

                case 'bewertungen':
                    $db->query('TRUNCATE tartikelext', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbewertung', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbewertungguthabenbonus', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbewertunghilfreich', \DB\ReturnType::DEFAULT);
                    break;

                // Shopkunden & Kunden werben Kunden & Bestellungen & Kupons
                case 'shopkunden':
                    $db->query('TRUNCATE tkunde', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenattribut', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundendatenhistory', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenfeld', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenfeldwert', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenherkunft', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenkontodaten', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenwerbenkunden', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferadresse', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpers', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbperspos', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpersposeigenschaft', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twunschliste', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twunschlistepos', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twunschlisteposeigenschaft', \DB\ReturnType::DEFAULT);
                    break;
                case 'kwerbenk':
                    $db->query('TRUNCATE tkundenwerbenkunden', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkundenwerbenkundenbonus', \DB\ReturnType::DEFAULT);
                    break;
                case 'bestellungen':
                    $db->query('TRUNCATE tbestellid', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbestellstatus', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tbestellung', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferschein', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferscheinpos', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tlieferscheinposinfo', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorb', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpers', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbperspos', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpersposeigenschaft', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbpos', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE twarenkorbposeigenschaft', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tuploaddatei', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tuploadqueue', \DB\ReturnType::DEFAULT);

                    $uploadfiles = glob(PFAD_UPLOADS . '*');

                    foreach ($uploadfiles as $file) {
                        if (is_file($file) && strpos($file, '.') !== 0) {
                            unlink($file);
                        }
                    }

                    break;
                case 'kupons':
                    $db->query('TRUNCATE tkupon', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkuponbestellung', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkuponkunde', \DB\ReturnType::DEFAULT);
                    $db->query('TRUNCATE tkuponsprache', \DB\ReturnType::DEFAULT);
                    break;
            }
        }
        Shop::Container()->getCache()->flushAll();
        $db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);
        $cHinweis = 'Der Shop wurde mit Ihren gewählten Optionen zurückgesetzt.';
    } else {
        $cFehler = 'Bitte wählen Sie mindestens eine Option aus.';
    }

    executeHook(HOOK_BACKEND_SHOP_RESET_AFTER);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('shopzuruecksetzen.tpl');
