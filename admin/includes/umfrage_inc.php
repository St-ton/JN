<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// Anzahl Antworten die komplett angezeigt werden, der Rest wird unter "Sonstige" zusammengefasst
define('UMFRAGE_MAXANZAHLANZEIGEN', 20);

/**
 * @deprecated since 4.06
 * @param string $string
 * @return string
 */
function convertDate($string)
{
    [$date, $time] = explode(' ', $string);
    $exploded      = explode(':', $time);
    if (count($exploded) === 2) {
        [$hour, $minute] = $exploded;
    } else {
        [$hour, $minute] = $exploded;
    }
    [$day, $month, $year] = explode('.', $date);

    return $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':00';
}

/**
 * @deprecated since 4.06
 * @param string $cDateTimeStr
 * @return stdClass
 */
function gibJahrMonatVonDateTime($cDateTimeStr)
{
    [$dDatum, $dUhrzeit]     = explode(' ', $cDateTimeStr);
    [$dJahr, $dMonat, $dTag] = explode('-', $dDatum);

    $date        = new stdClass();
    $date->Jahr  = $dJahr;
    $date->Monat = $dMonat;
    $date->Tag   = $dTag;

    return $date;
}

/**
 * @param int    $kUmfrageFrage
 * @param string $cTyp
 * @param array  $cNameOption_arr
 * @param array  $cNameAntwort_arr
 * @param array  $nSortAntwort_arr
 * @param array  $nSortOption_arr
 * @param array  $answers
 * @param array  $matrixOptions
 * @return stdClass
 */
function updateAntwortUndOption(
    $kUmfrageFrage,
    $cTyp,
    $cNameOption_arr = [],
    $cNameAntwort_arr = [],
    $nSortAntwort_arr = [],
    $nSortOption_arr = [],
    $answers = [],
    $matrixOptions = []
) {
    $res                   = new stdClass();
    $res->nAnzahlAntworten = count($answers);
    $res->nAnzahlOptionen  = count($matrixOptions);

    $db = Shop::Container()->getDB();
    if ($cTyp !== \Survey\QuestionType::TEXT_SMALL & $cTyp !== \Survey\QuestionType::TEXT_BIG) {
        if (is_array($answers) && count($answers) > 0) {
            foreach ($answers as $i => $kUmfrageFrageAntwort) {
                $_upd        = new stdClass();
                $_upd->cName = $cNameAntwort_arr[$i];
                $_upd->nSort = (int)$nSortAntwort_arr[$i];
                $db->update(
                    'tumfragefrageantwort',
                    'kUmfrageFrageAntwort',
                    (int)$kUmfrageFrageAntwort,
                    $_upd
                );
            }
        }
        if ($cTyp === \Survey\QuestionType::MATRIX_SINGLE || $cTyp === \Survey\QuestionType::MATRIX_MULTI) {
            if (is_array($matrixOptions) && count($matrixOptions) > 0) {
                foreach ($matrixOptions as $j => $kUmfrageMatrixOption) {
                    $_upd        = new stdClass();
                    $_upd->cName = $cNameOption_arr[$j];
                    $_upd->nSort = (int)$nSortOption_arr[$j];
                    $db->update(
                        'tumfragematrixoption',
                        'kUmfrageMatrixOption',
                        (int)$kUmfrageMatrixOption,
                        $_upd
                    );
                }
            }
        }
    }

    return $res;
}

/**
 * @param int          $questionID
 * @param string       $type
 * @param string|array $optionName
 * @param string|array $answerName
 * @param array        $answerSort
 * @param array        $sortOptions
 * @param object       $data
 */
function speicherAntwortZuFrage(
    int $questionID,
    $type,
    $optionName,
    $answerName,
    $answerSort,
    $sortOptions,
    $data
) {
    switch ($type) {
        case \Survey\QuestionType::MULTI_SINGLE:
            if (is_array($answerName) && count($answerName) > 0) {
                $count = count($answerName);
                for ($i = $data->nAnzahlAntworten; $i < $count; $i++) {
                    unset($answer);
                    $answer                = new stdClass();
                    $answer->kUmfrageFrage = $questionID;
                    $answer->cName         = $answerName[$i];
                    $answer->nSort         = $answerSort[$i];

                    Shop::Container()->getDB()->insert('tumfragefrageantwort', $answer);
                }
            }
            break;
        case \Survey\QuestionType::MULTI:
            if (is_array($answerName) && count($answerName) > 0) {
                $count = count($answerName);
                for ($i = $data->nAnzahlAntworten; $i < $count; $i++) {
                    unset($answer);
                    $answer                = new stdClass();
                    $answer->kUmfrageFrage = $questionID;
                    $answer->cName         = $answerName[$i];
                    $answer->nSort         = $answerSort[$i];

                    Shop::Container()->getDB()->insert('tumfragefrageantwort', $answer);
                }
            }
            break;
        case \Survey\QuestionType::SELECT_SINGLE:
            if (is_array($answerName) && count($answerName) > 0) {
                $count = count($answerName);
                for ($i = $data->nAnzahlAntworten; $i < $count; $i++) {
                    unset($answer);
                    $answer                = new stdClass();
                    $answer->kUmfrageFrage = $questionID;
                    $answer->cName         = $answerName[$i];
                    $answer->nSort         = $answerSort[$i];

                    Shop::Container()->getDB()->insert('tumfragefrageantwort', $answer);
                }
            }
            break;
        case \Survey\QuestionType::SELECT_MULTI:
            if (is_array($answerName) && count($answerName) > 0) {
                $count = count($answerName);
                for ($i = $data->nAnzahlAntworten; $i < $count; $i++) {
                    unset($answer);
                    $answer                = new stdClass();
                    $answer->kUmfrageFrage = $questionID;
                    $answer->cName         = $answerName[$i];
                    $answer->nSort         = $answerSort[$i];

                    Shop::Container()->getDB()->insert('tumfragefrageantwort', $answer);
                }
            }
            break;
        case \Survey\QuestionType::MATRIX_SINGLE:
            if (is_array($answerName) && is_array($optionName) && count($answerName) > 0 && count($optionName) > 0) {
                $count = count($answerName);
                for ($i = $data->nAnzahlAntworten; $i < $count; $i++) {
                    unset($answer);
                    $answer                = new stdClass();
                    $answer->kUmfrageFrage = $questionID;
                    $answer->cName         = $answerName[$i];
                    $answer->nSort         = $answerSort[$i];

                    Shop::Container()->getDB()->insert('tumfragefrageantwort', $answer);
                }
                $count = count($optionName);
                for ($i = $data->nAnzahlOptionen; $i < $count; $i++) {
                    unset($matrixOpt);
                    $matrixOpt                = new stdClass();
                    $matrixOpt->kUmfrageFrage = $questionID;
                    $matrixOpt->cName         = $optionName[$i];
                    $matrixOpt->nSort         = $sortOptions[$i];

                    Shop::Container()->getDB()->insert('tumfragematrixoption', $matrixOpt);
                }
            }
            break;
        case \Survey\QuestionType::MATRIX_MULTI:
            if (is_array($answerName) && is_array($optionName) && count($answerName) > 0 && count($optionName) > 0) {
                $count = count($answerName);
                for ($i = $data->nAnzahlAntworten; $i < $count; $i++) {
                    unset($answer);
                    $answer                = new stdClass();
                    $answer->kUmfrageFrage = $questionID;
                    $answer->cName         = $answerName[$i];
                    $answer->nSort         = $answerSort[$i];

                    Shop::Container()->getDB()->insert('tumfragefrageantwort', $answer);
                }
                $count = count($optionName);
                for ($i = $data->nAnzahlOptionen; $i < $count; $i++) {
                    unset($matrixOpt);
                    $matrixOpt                = new stdClass();
                    $matrixOpt->kUmfrageFrage = $questionID;
                    $matrixOpt->cName         = $optionName[$i];
                    $matrixOpt->nSort         = $sortOptions[$i];

                    Shop::Container()->getDB()->insert('tumfragematrixoption', $matrixOpt);
                }
            }
            break;
    }
}

/**
 * @param int $questionID
 */
function loescheFrage(int $questionID)
{
    if ($questionID > 0) {
        Shop::Container()->getDB()->query(
            'DELETE tumfragefrage, tumfragedurchfuehrungantwort 
                FROM tumfragefrage
                LEFT JOIN tumfragedurchfuehrungantwort 
                    ON tumfragedurchfuehrungantwort.kUmfrageFrage = tumfragefrage.kUmfrageFrage
                WHERE tumfragefrage.kUmfrageFrage = ' . $questionID,
            \DB\ReturnType::AFFECTED_ROWS
        );
        Shop::Container()->getDB()->delete('tumfragefrageantwort', 'kUmfrageFrage', $questionID);
        Shop::Container()->getDB()->delete('tumfragematrixoption', 'kUmfrageFrage', $questionID);
    }
}

/**
 * @param string $cTyp
 * @param int    $questionID
 * @return bool
 */
function pruefeTyp($cTyp, int $questionID)
{
    $oUmfrageFrage = Shop::Container()->getDB()->select('tumfragefrage', 'kUmfrageFrage', $questionID);
    // Wenn sich der Typ geändert hat, dann return false
    return $cTyp === $oUmfrageFrage->cTyp;
}

/**
 * @param int $surveyID
 * @return mixed
 */
function holeUmfrageStatistik(int $surveyID)
{
    $oTMP  = null;
    $db    = Shop::Container()->getDB();
    $stats = $db->query(
        "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
            DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de
            FROM tumfrage
            WHERE kUmfrage = " . $surveyID,
        \DB\ReturnType::SINGLE_OBJECT
    );
    // Wenn es eine Umfrage gibt
    if ($stats === null) {
        return null;
    }
    // Hole alle Fragen der Umfrage
    $stats->oUmfrageFrage_arr = [];
    $surveys                  = $db->query(
        'SELECT *
            FROM tumfragefrage
            WHERE kUmfrage = ' . (int)$stats->kUmfrage . '
            ORDER BY nSort',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    // Mappe Fragentyp
    foreach ($surveys as $i => $question) {
        $surveys[$i]->cTypMapped = mappeFragenTyp($question->cTyp);
    }
    $stats->oUmfrageFrage_arr = $surveys;
    // Anzahl Durchführungen
    $executions                  = $db->query(
        'SELECT kUmfrageDurchfuehrung
            FROM tumfragedurchfuehrung
            WHERE kUmfrage = ' . (int)$stats->kUmfrage,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $stats->nAnzahlDurchfuehrung = count($executions);
    // Laufe alle Fragen der Umfrage durch und berechne die Statistik
    foreach ($surveys as $i => $question) {
        if ($question->cTyp === \Survey\QuestionType::TEXT_PAGE_CHANGE
            || $question->cTyp === \Survey\QuestionType::TEXT_STATIC
        ) {
            continue;
        }
        $stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = [];

        // Matrix
        if ($question->cTyp === \Survey\QuestionType::MATRIX_SINGLE
            || $question->cTyp === \Survey\QuestionType::MATRIX_MULTI
        ) {
            $answers       = [];
            $matrixOptions = [];
            $resMatrix     = [];

            $answerData = $db->query(
                'SELECT cName, kUmfrageFrageAntwort
                    FROM tumfragefrageantwort
                    WHERE kUmfrageFrage = ' . (int)$question->kUmfrageFrage . '
                    ORDER BY nSort',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($answerData as $oUmfrageFrageAntwortTMP) {
                $answer                       = new stdClass();
                $answer->cName                = $oUmfrageFrageAntwortTMP->cName;
                $answer->kUmfrageFrageAntwort = $oUmfrageFrageAntwortTMP->kUmfrageFrageAntwort;
                $answers[]                    = $answer;
            }
            $matrixOptTMP_arr = $db->query(
                'SELECT tumfragematrixoption.kUmfrageMatrixOption, tumfragematrixoption.cName, 
                    COUNT(tumfragedurchfuehrungantwort.kUmfrageMatrixOption) AS nAnzahlOption
                    FROM tumfragematrixoption
                    LEFT JOIN tumfragedurchfuehrungantwort 
                        ON tumfragedurchfuehrungantwort.kUmfrageMatrixOption = tumfragematrixoption.kUmfrageMatrixOption
                    WHERE tumfragematrixoption.kUmfrageFrage = ' . (int)$question->kUmfrageFrage . '
                    GROUP BY tumfragematrixoption.kUmfrageMatrixOption
                    ORDER BY tumfragematrixoption.nSort',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($matrixOptTMP_arr as $matrixOptTMP) {
                unset($opt);
                $opt                       = new stdClass();
                $opt->nAnzahlOption        = $matrixOptTMP->nAnzahlOption;
                $opt->cName                = $matrixOptTMP->cName;
                $opt->kUmfrageMatrixOption = $matrixOptTMP->kUmfrageMatrixOption;
                $matrixOptions[]                 = $opt;
            }
            //Leereinträge in die Matrix einfügen
            foreach ($answers as $answer) {
                foreach ($matrixOptions as $opt) {
                    $res                = new stdClass();
                    $res->nAnzahl       = 0;
                    $res->nGesamtAnzahl = $opt->nAnzahlOption;
                    $res->fProzent      = 0;
                    $res->nBold         = 0;

                    $resMatrix[$answer->kUmfrageFrageAntwort][$opt->kUmfrageMatrixOption] = $res;
                }
            }
            //der gesamten umfrage hinzufügen
            $stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = $answers;
            $stats->oUmfrageFrage_arr[$i]->oUmfrageMatrixOption_arr = $matrixOptions;
            //hole pro Option die Anzahl raus
            foreach ($matrixOptions as $opt) {
                $matrixOptAnzahlSpalte_arr = $db->query(
                    'SELECT COUNT(*) AS nAnzahlOptionProAntwort, kUmfrageFrageAntwort
                        FROM  tumfragedurchfuehrungantwort
                        WHERE kUmfrageMatrixOption = ' . (int)$opt->kUmfrageMatrixOption . '
                            AND kUmfrageFrage = ' . (int)$question->kUmfrageFrage . '
                        GROUP BY kUmfrageFrageAntwort ',
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                //setze jeder Antwort den entsprechenden Matrixeintrag
                foreach ($matrixOptAnzahlSpalte_arr as $col) {
                    $resMatrix[$col->kUmfrageFrageAntwort][$opt->kUmfrageMatrixOption]->nAnzahl  =
                        $col->nAnzahlOptionProAntwort;
                    $resMatrix[$col->kUmfrageFrageAntwort][$opt->kUmfrageMatrixOption]->fProzent =
                        round(
                            (
                                $col->nAnzahlOptionProAntwort /
                                $resMatrix[$col->kUmfrageFrageAntwort][$opt->kUmfrageMatrixOption]->nGesamtAnzahl
                            ) * 100,
                            1
                        );
                }
            }
            //ermittele die maximalen Werte und setze nBold=1
            foreach ($matrixOptions as $opt) {
                $maxAnswers = 0;
                if (!is_array($answers)) {
                    continue;
                }
                //max ermitteln
                foreach ($answers as $answer) {
                    if ($resMatrix[$answer->kUmfrageFrageAntwort][$opt->kUmfrageMatrixOption]->nAnzahl > $maxAnswers) {
                        $maxAnswers = $resMatrix[$answer->kUmfrageFrageAntwort][$opt->kUmfrageMatrixOption]->nAnzahl;
                    }
                }
                //bold setzen
                foreach ($answers as $answer) {
                    if ($resMatrix[$answer->kUmfrageFrageAntwort][$opt->kUmfrageMatrixOption]->nAnzahl == $maxAnswers
                    ) {
                        $resMatrix[$answer->kUmfrageFrageAntwort][$opt->kUmfrageMatrixOption]->nBold = 1;
                    }
                }
            }
            //Ergebnismatrix für die Frage setzen
            $stats->oUmfrageFrage_arr[$i]->oErgebnisMatrix_arr = $resMatrix;
        } elseif ($question->cTyp === \Survey\QuestionType::TEXT_SMALL
            || $question->cTyp === \Survey\QuestionType::TEXT_BIG
        ) {
            $answers = $db->query(
                'SELECT cText AS cName, COUNT(cText) AS nAnzahlAntwort
                    FROM tumfragedurchfuehrungantwort
                    WHERE kUmfrageFrage = ' . (int)$question->kUmfrageFrage . "
                        AND TRIM(cText) !=''
                    GROUP BY cText
                    ORDER BY nAnzahlAntwort DESC
                    LIMIT " . UMFRAGE_MAXANZAHLANZEIGEN,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            // Anzahl Antworten
            foreach ($answers as $j => $answer) {
                if (!isset($stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten)) {
                    $stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten = 0;
                }
                $stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten += $answer->nAnzahlAntwort;
            }
            // Anzahl Sonstiger Antworten
            $oUmfrageFrageAntwortTMP = $db->query(
                'SELECT SUM(b.nAnzahlAntwort) AS nAnzahlAntwort
                     FROM
                     (
                        SELECT COUNT(cText) AS nAnzahlAntwort
                            FROM tumfragedurchfuehrungantwort
                            WHERE kUmfrageFrage = ' . (int)$question->kUmfrageFrage . '
                            GROUP BY cText
                            ORDER BY nAnzahlAntwort DESC
                            LIMIT ' . UMFRAGE_MAXANZAHLANZEIGEN . ', ' . count($answers) . '
                     ) AS b',
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oUmfrageFrageAntwortTMP->nAnzahlAntwort) && (int)$oUmfrageFrageAntwortTMP->nAnzahlAntwort > 0) {
                $stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten += (int)$oUmfrageFrageAntwortTMP->nAnzahlAntwort;
                $oTMP                                            = new stdClass();
                $oTMP->cName                                     = '<a href="umfrage.php?umfrage=1&uf=' .
                    $question->kUmfrageFrage . '&aa=' . $stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten .
                    '&ma=' . count($answers) . '&a=zeige_sonstige">Sonstige</a>';
                $oTMP->nAnzahlAntwort                            = $oUmfrageFrageAntwortTMP->nAnzahlAntwort;
                $oTMP->fProzent                                  = round(
                    ($oUmfrageFrageAntwortTMP->nAnzahlAntwort / $stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten) * 100,
                    1
                );
            }
            $stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = [];
            //$oUmfrageStats->oUmfrageFrage_arr[$i]->nAnzahlAntworten = count($oUmfrageFrageAntwort_arr);
            if (is_array($answers) && count($answers) > 0) {
                $stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = $answers;

                foreach ($answers as $j => $answer) {
                    $stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr[$j]->fProzent =
                        round(
                            ($answer->nAnzahlAntwort / $stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten) * 100,
                            1
                        );
                }
            }
            // Sontiges Element (falls vorhanden) dem Antworten Array hinzufügen
            if (isset($oUmfrageFrageAntwortTMP->nAnzahlAntwort) && (int)$oUmfrageFrageAntwortTMP->nAnzahlAntwort > 0) {
                $stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr[] = $oTMP;
            }
        } else {
            $answers         = $db->query(
                'SELECT tumfragefrageantwort.kUmfrageFrageAntwort, tumfragefrageantwort.cName, 
                    COUNT(tumfragedurchfuehrungantwort.kUmfrageFrageAntwort) AS nAnzahlAntwort
                    FROM tumfragefrageantwort
                    LEFT JOIN tumfragedurchfuehrungantwort 
                        ON tumfragedurchfuehrungantwort.kUmfrageFrageAntwort = tumfragefrageantwort.kUmfrageFrageAntwort
                    WHERE tumfragefrageantwort.kUmfrageFrage = ' . (int)$question->kUmfrageFrage . '
                    GROUP BY tumfragefrageantwort.kUmfrageFrageAntwort
                    ORDER BY nAnzahlAntwort DESC, tumfragefrageantwort.kUmfrageFrageAntwort',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $oAnzahl         = $db->query(
                'SELECT COUNT(*) AS nAnzahl
                    FROM tumfragedurchfuehrungantwort
                    WHERE kUmfrageFrage = ' . (int)$question->kUmfrageFrage . '
                        AND kUmfrageFrageAntwort != 0',
                \DB\ReturnType::SINGLE_OBJECT
            );
            $freeTextAnswers = [];
            if ($stats->oUmfrageFrage_arr[$i]->nFreifeld == 1) {
                $freeTextAnswers = $db->query(
                    'SELECT cText AS cName, COUNT(cText) AS nAnzahlAntwort
                        FROM tumfragedurchfuehrungantwort
                        WHERE kUmfrageFrage = ' . (int)$question->kUmfrageFrage . "
                            AND kUmfrageFrageAntwort = 0
                            AND kUmfrageMatrixOption = 0
                            AND TRIM(cText) !=''
                        GROUP BY cText
                        ORDER BY nAnzahlAntwort DESC",
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            }
            $stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = array_merge($answers, $freeTextAnswers);
            $stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten         = $oAnzahl->nAnzahl + count($freeTextAnswers);

            if (is_array($stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr)
                && count($stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr) > 0
            ) {
                foreach ($stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr as $j => $answer) {
                    $stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr[$j]->fProzent = 0.0;
                    if ($stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten > 0) {
                        $stats->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr[$j]->fProzent =
                            round(
                                ($answer->nAnzahlAntwort / $stats->oUmfrageFrage_arr[$i]->nAnzahlAntworten) * 100,
                                1
                            );
                    }
                }
            }
        }
    }
    $stats->cKundengruppe_arr = [];
    $customerGroups           = StringHandler::parseSSK($stats->cKundengruppe);
    foreach ($customerGroups as $kKundengruppe) {
        if ($kKundengruppe == -1) {
            $stats->cKundengruppe_arr[] = 'Alle';
        } else {
            $oKundengruppe = $db->select('tkundengruppe', 'kKundengruppe', (int)$kKundengruppe);
            if (!empty($oKundengruppe->cName)) {
                $stats->cKundengruppe_arr[] = $oKundengruppe->cName;
            }
        }
    }

    return $stats;
}

/**
 * @param int $surveyID
 * @param int $maxAnswers
 * @param int $limit
 * @return stdClass
 */
function holeSonstigeTextAntworten(int $surveyID, int $maxAnswers, int $limit)
{
    if (!$surveyID || !$maxAnswers || !$limit) {
        $question                           = new stdClass();
        $question->oUmfrageFrageAntwort_arr = [];

        return $question;
    }
    $question                = Shop::Container()->getDB()->query(
        'SELECT kUmfrage, cName, cTyp
            FROM tumfragefrage
            WHERE kUmfrageFrage = ' . $surveyID,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $answers                 = Shop::Container()->getDB()->query(
        'SELECT cText AS cName, COUNT(cText) AS nAnzahlAntwort
            FROM tumfragedurchfuehrungantwort
            WHERE kUmfrageFrage = ' . $surveyID . '
            GROUP BY cText
            ORDER BY nAnzahlAntwort DESC
            LIMIT ' . UMFRAGE_MAXANZAHLANZEIGEN . ', ' . $limit,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $question->nMaxAntworten = $maxAnswers;
    if (is_array($answers) && count($answers) > 0) {
        $question->oUmfrageFrageAntwort_arr = $answers;
        foreach ($question->oUmfrageFrageAntwort_arr as $i => $answer) {
            $question->oUmfrageFrageAntwort_arr[$i]->nProzent = round(($answer->nAnzahlAntwort / $maxAnswers) * 100, 1);
        }
    }

    return $question;
}

/**
 * @param string $cTyp
 * @return string
 */
function mappeFragenTyp(string $cTyp): string
{
    switch ($cTyp) {
        case \Survey\QuestionType::MULTI_SINGLE:
            return __('questionTypeMultipleChoice');

        case \Survey\QuestionType::MULTI:
            return __('questionTypeMultipleChoiceMany');

        case \Survey\QuestionType::SELECT_SINGLE:
            return __('questionTypeSelectboxOne');

        case \Survey\QuestionType::SELECT_MULTI:
            return __('questionTypeSelectboxMany');

        case \Survey\QuestionType::TEXT_SMALL:
            return __('questionTypeTextSmall');

        case \Survey\QuestionType::TEXT_BIG:
            return __('questionTypeTextBig');

        case \Survey\QuestionType::MATRIX_SINGLE:
            return __('questionTypeMatrixOne');

        case \Survey\QuestionType::MATRIX_MULTI:
            return __('questionTypeMatrixMany');

        case \Survey\QuestionType::TEXT_STATIC:
            return __('questionTypeDivider');

        case \Survey\QuestionType::TEXT_PAGE_CHANGE:
            return __('questionTypeDividerNewPage');

        default:
            return '';
    }
}
