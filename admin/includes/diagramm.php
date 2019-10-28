<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$bExtern = true;
require_once __DIR__ . '/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admininclude.php';
require_once PFAD_ROOT . PFAD_GRAPHCLASS . 'graph.php';

if (isset($_SESSION['nDiagrammTyp'])) {
    switch ((int)$_SESSION['nDiagrammTyp']) {
        case 1: // Umsatz - Jahr
            erstelleUmsatzGraph($_SESSION['oY1_arr'], $_SESSION['oY2_arr'], $_SESSION['nYmax'], 1);
            break;
        case 2: // Umsatz - Monat
            erstelleUmsatzGraph($_SESSION['oY1_arr'], $_SESSION['oY2_arr'], $_SESSION['nYmax'], 2);
            break;
        case 3:    // Besuchte Seiten Top10
            erstelleTop10Graph($_SESSION['oGraphData_arr'], $_SESSION['nYmax'], $_SESSION['cGraphFilter']);
            break;
        case 4: // Top Vergleichsliste
            erstelleTopVergleichslisteGraph(
                $_SESSION['oGraphData_arr'],
                $_SESSION['nYmax'],
                $_SESSION['Vergleichsliste']->nAnzahl
            );
            break;
        case 5: // Kampagne DetailStats
            erstelleKampagneDetailGraph($_SESSION['Kampagne']->oKampagneDetailGraph, $_GET['kKampagneDef']);
            break;
    }
}

/**
 * @param array $y1
 * @param array $y2
 * @param int   $yMax
 * @param int   $type
 */
function erstelleUmsatzGraph($y1, $y2, $yMax, $type)
{
    if (count($y1) > 0 && count($y2) > 0) {
        $graph = new graph(785, 400);

        $graph->parameter['path_to_fonts']     = PFAD_GRAPHCLASS . 'fonts/';
        $graph->parameter['y_label_right']     = 'Umsatz';
        $graph->parameter['x_grid']            = 'none';
        $graph->parameter['y_decimal_right']   = 2;
        $graph->parameter['y_min_right']       = 0;
        $graph->parameter['y_max_right']       = $yMax;
        $graph->parameter['y_axis_gridlines']  = 11;
        $graph->parameter['y_axis_text_right'] = 2;  //print a tick every 2nd grid line
        $graph->parameter['shadow']            = 'none';
        $graph->parameter['title']             = 'Umsatzstatistik';
        $graph->parameter['x_label']           = 'Monate';
        $graph->x_data                         = [
            'Januar',
            'Februar',
            'März',
            'April',
            'Mai',
            'Juni',
            'Juli',
            'August',
            'September',
            'Oktober',
            'November',
            'Dezember'
        ];
        $graph->y_data['alpha']                = [];
        $graph->y_data['beta']                 = [];
        // Monatsumsatz?
        if ($type == 2) {
            $graph->parameter['x_label'] = 'Tage';
            $graph->x_data               = [];

            for ($i = 1; $i <= 31; $i++) {
                $graph->x_data[] = $i;
            }
        }

        // Balken 1 (Umsatz pro Monat) Werte Array aufbauen
        foreach ($graph->x_data as $i => $x_data) {
            $tmpUmsatz = 0;

            foreach ($y1 as $oY1) {
                if ((int)$oY1->ZeitWert === ($i + 1)) {
                    $tmpUmsatz = $oY1->Umsatz;
                }
            }

            $graph->y_data['alpha'][] = $tmpUmsatz;
        }
        // Balken 2 (Durchschnittsumsatz pro Monat) Werte Array aufbauen
        foreach ($graph->x_data as $i => $x_data) {
            $tmpUmsatz = 0;

            foreach ($y2 as $oY2) {
                if ((int)$oY2->ZeitWert === $i + 1) {
                    $tmpUmsatz = $oY2->Umsatz;
                }
            }

            $graph->y_data['beta'][] = $tmpUmsatz;
        }

        if (count($graph->y_data['alpha']) > 1 && count($graph->y_data['beta']) > 1 && count($graph->x_data) > 1) {
            $graph->y_format['alpha'] = ['colour' => 'blue', 'bar' => 'fill', 'bar_size' => 0.8, 'y_axis' => 'right'];
            $graph->y_format['beta']  = ['colour' => 'red', 'bar' => 'fill', 'bar_size' => 0.3, 'y_axis' => 'right'];

            $graph->y_order = ['alpha', 'beta'];

            $graph->draw_stack();
        }
    }
}

/**
 * @param array  $graphData
 * @param int    $yMax
 * @param string $graphFilter
 */
function erstelleTop10Graph($graphData, $yMax, $graphFilter)
{
    if (count($graphData) === 0) {
        return;
    }
    $graph = new graph(785, 400);

    $graph->parameter['path_to_fonts']     = PFAD_ROOT . PFAD_GRAPHCLASS . 'fonts/';
    $graph->parameter['y_label_right']     = 'Anzahl Besuche';
    $graph->parameter['x_grid']            = 'none';
    $graph->parameter['y_decimal_right']   = 2;
    $graph->parameter['y_min_right']       = 0;
    $graph->parameter['y_max_right']       = $yMax;
    $graph->parameter['y_axis_gridlines']  = 11;
    $graph->parameter['y_axis_text_right'] = 2;  //print a tick every 2nd grid line
    $graph->parameter['shadow']            = 'none';
    $graph->parameter['title']             = 'Top10 ' . $graphFilter;
    $graph->parameter['x_label']           = $graphFilter;
    $graph->x_data                         = [];
    $graph->y_data['alpha']                = [];

    if (count($graphData) > 0) {
        // Array sortieren
        usort($graphData, 'Sortierung');

        foreach ($graphData as $i => $oGraphData) {
            if ($i > 10) {
                // Nach 10 Elemente stoppen (Top10)
                break;
            }
            $graph->x_data[]          = $oGraphData->cName;
            $graph->y_data['alpha'][] = $oGraphData->nWert;
        }
    }

    if (count($graph->x_data) > 1 && count($graph->y_data['alpha']) > 1) {
        $graph->y_format['alpha'] = ['colour' => 'blue', 'bar' => 'fill', 'bar_size' => 0.8, 'y_axis' => 'right'];
        $graph->y_order           = ['alpha'];

        $graph->draw_stack();
    }
}

/**
 * @param array $graphData
 * @param int   $yMax
 * @param int   $count
 */
function erstelleTopVergleichslisteGraph($graphData, $yMax, $count)
{
    if (!is_array($graphData) || count($graphData) === 0) {
        return;
    }
    $graph = new graph(785, 400);

    $graph->parameter['path_to_fonts']     = PFAD_ROOT . PFAD_GRAPHCLASS . 'fonts/';
    $graph->parameter['y_label_right']     = 'Anzahl Vergleiche';
    $graph->parameter['x_grid']            = 'none';
    $graph->parameter['y_decimal_right']   = 2;
    $graph->parameter['y_min_right']       = 0;
    $graph->parameter['y_max_right']       = $yMax;
    $graph->parameter['y_axis_gridlines']  = 11;
    $graph->parameter['y_axis_text_right'] = 2;  //print a tick every 2nd grid line
    $graph->parameter['shadow']            = 'none';
    $graph->parameter['title']             = 'Top' . $count . ' Artikel die verglichen wurden';
    $graph->parameter['x_label']           = 'Artikel';
    $graph->x_data                         = [];
    $graph->y_data['alpha']                = [];

    foreach ($graphData as $oGraphData) {
        $graph->x_data[]          = $oGraphData->cArtikelName;
        $graph->y_data['alpha'][] = $oGraphData->nAnzahl;
    }

    if (count($graph->x_data) > 1 && count($graph->y_data['alpha']) > 1) {
        $graph->y_format['alpha'] = ['colour' => 'blue', 'bar' => 'fill', 'bar_size' => 0.8, 'y_axis' => 'right'];
        $graph->y_order           = ['alpha'];

        $graph->draw_stack();
    }
}

/**
 * @param object $campaignDetailGraph
 * @param int    $definitionID
 */
function erstelleKampagneDetailGraph($campaignDetailGraph, $definitionID)
{
    $graph = new graph(950, 400);

    $graph->parameter['path_to_fonts']     = PFAD_ROOT . PFAD_GRAPHCLASS . 'fonts/';
    $graph->parameter['y_label_right']     = 'Anzahl';
    $graph->parameter['x_grid']            = 'none';
    $graph->parameter['y_decimal_right']   = 2;
    $graph->parameter['y_min_right']       = 0;
    $graph->parameter['y_max_right']       = ceil((int)$campaignDetailGraph->nGraphMaxAssoc_arr[$definitionID] * 1.1);
    $graph->parameter['y_axis_gridlines']  = 11;
    $graph->parameter['y_axis_text_right'] = 2;  //print a tick every 2nd grid line
    $graph->parameter['shadow']            = 'none';
    $graph->parameter['title']             = $campaignDetailGraph->oKampagneDef_arr[$definitionID]->cName;
    $graph->x_data                         = [];
    $graph->y_data['alpha']                = [];

    if (is_array($campaignDetailGraph->oKampagneDetailGraph_arr)
        && count($campaignDetailGraph->oKampagneDetailGraph_arr) > 0
    ) {
        foreach ($campaignDetailGraph->oKampagneDetailGraph_arr as $def) {
            $graph->x_data[]          = '(' . $def[$definitionID] . ') ' . $def['cDatum'];
            $graph->y_data['alpha'][] = $def[$definitionID];
        }
    }
    // Balken 1 (Umsatz pro Monat) Werte Array aufbauen
    if (count($graph->y_data['alpha']) > 1 && count($graph->x_data) > 1) {
        $graph->y_format['alpha'] = ['colour' => 'blue', 'bar' => 'fill', 'bar_size' => 0.8, 'y_axis' => 'right'];
        $graph->y_order           = ['alpha'];
        $graph->draw_stack();
    }
}

/**
 * @param object $oA
 * @param object $oB
 * @return int
 */
function Sortierung($oA, $oB)
{
    if ($oA->nWert == $oB->nWert) {
        return 0;
    }

    return ($oA->nWert < $oB->nWert) ? +1 : -1;
}
