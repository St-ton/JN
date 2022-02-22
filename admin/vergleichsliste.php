<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Backend\Settings\Manager;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

Shop::Container()->getGetText()->loadConfigLocales(true, true);

$oAccount->permission('MODULE_COMPARELIST_VIEW', true, true);
$db             = Shop::Container()->getDB();
$alertService   = Shop::Container()->getAlertService();
$settingManager = new Manager(
    $db,
    Shop::Smarty(),
    $oAccount,
    Shop::Container()->getGetText(),
    $alertService
);
if (!isset($_SESSION['Vergleichsliste'])) {
    $_SESSION['Vergleichsliste'] = new stdClass();
}
$_SESSION['Vergleichsliste']->nZeitFilter = 1;
$_SESSION['Vergleichsliste']->nAnzahl     = 10;
if (Request::postInt('zeitfilter') === 1) {
    $_SESSION['Vergleichsliste']->nZeitFilter = Request::postInt('nZeitFilter');
    $_SESSION['Vergleichsliste']->nAnzahl     = Request::postInt('nAnzahl');
}

if ((Request::postInt('einstellungen') === 1 || Request::postVar('resetSetting') !== null) && Form::validateToken()) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSectionSettings(CONF_VERGLEICHSLISTE, $_POST),
        'saveSettings'
    );
}

$listCount  = (int)$db->getSingleObject(
    'SELECT COUNT(*) AS cnt
        FROM tvergleichsliste'
)->cnt;
$pagination = (new Pagination())
    ->setItemCount($listCount)
    ->assemble();
$last20     = $db->getObjects(
    "SELECT kVergleichsliste, DATE_FORMAT(dDate, '%d.%m.%Y  %H:%i') AS Datum
        FROM tvergleichsliste
        ORDER BY dDate DESC
        LIMIT " . $pagination->getLimitSQL()
);

if (count($last20) > 0) {
    $positions = [];
    foreach ($last20 as $list) {
        $positions                              = $db->selectAll(
            'tvergleichslistepos',
            'kVergleichsliste',
            (int)$list->kVergleichsliste,
            'kArtikel, cArtikelName'
        );
        $list->oLetzten20VergleichslistePos_arr = $positions;
    }
}
$topComparisons = $db->getObjects(
    'SELECT tvergleichsliste.dDate, tvergleichslistepos.kArtikel, 
        tvergleichslistepos.cArtikelName, COUNT(tvergleichslistepos.kArtikel) AS nAnzahl
        FROM tvergleichsliste
        JOIN tvergleichslistepos 
            ON tvergleichsliste.kVergleichsliste = tvergleichslistepos.kVergleichsliste
        WHERE DATE_SUB(NOW(), INTERVAL :ds DAY)  < tvergleichsliste.dDate
        GROUP BY tvergleichslistepos.kArtikel
        ORDER BY nAnzahl DESC
        LIMIT :lmt',
    ['ds' => (int)$_SESSION['Vergleichsliste']->nZeitFilter, 'lmt' => (int)$_SESSION['Vergleichsliste']->nAnzahl]
);
if (count($topComparisons) > 0) {
    erstelleDiagrammTopVergleiche($topComparisons);
}
getAdminSectionSettings(CONF_VERGLEICHSLISTE);
$smarty->assign('Letzten20Vergleiche', $last20)
    ->assign('TopVergleiche', $topComparisons)
    ->assign('pagination', $pagination)
    ->display('vergleichsliste.tpl');

/**
 * @param array $topCompareLists
 */
function erstelleDiagrammTopVergleiche(array $topCompareLists): void
{
    unset($_SESSION['oGraphData_arr'], $_SESSION['nYmax'], $_SESSION['nDiagrammTyp']);
    $graphData = [];
    if (count($topCompareLists) === 0) {
        return;
    }
    $yMax                     = []; // Y-Achsen Werte um spaeter den Max Wert zu erlangen
    $_SESSION['nDiagrammTyp'] = 4;

    foreach ($topCompareLists as $i => $list) {
        $top               = new stdClass();
        $top->nAnzahl      = $list->nAnzahl;
        $top->cArtikelName = checkName($list->cArtikelName);
        $graphData[]       = $top;
        $yMax[]            = $list->nAnzahl;
        unset($top);

        if ($i >= (int)$_SESSION['Vergleichsliste']->nAnzahl) {
            break;
        }
    }
    // Naechst hoehere Zahl berechnen fuer die Y-Balkenbeschriftung
    if (count($yMax) > 0) {
        $fMax = (float)max($yMax);
        if ($fMax > 10) {
            $temp  = 10 ** floor(log10($fMax));
            $nYmax = ceil($fMax / $temp) * $temp;
        } else {
            $nYmax = 10;
        }

        $_SESSION['nYmax'] = $nYmax;
    }

    $_SESSION['oGraphData_arr'] = $graphData;
}

/**
 * Hilfsfunktion zur Regulierung der X-Achsen Werte
 *
 * @param string $name
 * @return string
 */
function checkName(string $name): string
{
    $name = stripslashes(trim(str_replace([';', '_', '#', '%', '$', ':', '"'], '', $name)));

    if (mb_strlen($name) > 20) {
        // Wenn der String laenger als 20 Zeichen ist
        $name = mb_substr($name, 0, 20) . '...';
    }

    return $name;
}
