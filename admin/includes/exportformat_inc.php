<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $cDateinameSplit_arr
 * @param int   $nDateiZaehler
 * @return string
 */
function gibDateiname($cDateinameSplit_arr, $nDateiZaehler)
{
    if (is_array($cDateinameSplit_arr) && count($cDateinameSplit_arr) > 1) {
        return $cDateinameSplit_arr[0] . $nDateiZaehler . $cDateinameSplit_arr[1];
    }

    return $cDateinameSplit_arr[0] . $nDateiZaehler;
}

/**
 * @param array $cDateinameSplit_arr
 * @param int   $nDateiZaehler
 * @return string
 */
function gibDateiPfad($cDateinameSplit_arr, $nDateiZaehler)
{
    return PFAD_ROOT . PFAD_EXPORT . gibDateiname($cDateinameSplit_arr, $nDateiZaehler);
}

/**
 * @return array
 */
function pruefeExportformat()
{
    $cPlausiValue_arr = array();

    // Name
    if (!isset($_POST['cName']) || strlen($_POST['cName']) === 0) {
        $cPlausiValue_arr['cName'] = 1;
    }
    // Dateiname
    if (!isset($_POST['cDateiname']) || strlen($_POST['cDateiname']) === 0) {
        $cPlausiValue_arr['cDateiname'] = 1;
    }
    // Dateiname Endung fehlt
    if (strpos($_POST['cDateiname'], '.') === false) {
        $cPlausiValue_arr['cDateiname'] = 2;
    }
    // Content
    if (!isset($_POST['cContent']) || strlen($_POST['cContent']) === 0) {
        $cPlausiValue_arr['cContent'] = 1;
    }
    // Sprache
    if (!isset($_POST['kSprache']) || intval($_POST['kSprache']) === 0) {
        $cPlausiValue_arr['kSprache'] = 1;
    }
    // Sprache
    if (!isset($_POST['kWaehrung']) || intval($_POST['kWaehrung']) === 0) {
        $cPlausiValue_arr['kWaehrung'] = 1;
    }
    // Kundengruppe
    if (!isset($_POST['kKundengruppe']) || intval($_POST['kKundengruppe']) === 0) {
        $cPlausiValue_arr['kKundengruppe'] = 1;
    }

    return $cPlausiValue_arr;
}

/**
 * Falls eingestellt, wird die Exportdatei in mehrere Dateien gesplittet
 *
 * @param object $oExportformat
 */
function splitteExportDatei($oExportformat)
{
    if (isset($oExportformat->nSplitgroesse) && intval($oExportformat->nSplitgroesse) > 0 && file_exists(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname)) {
        $nDateiZaehler       = 1;
        $cDateinameSplit_arr = array();
        $nFileTypePos        = strrpos($oExportformat->cDateiname, '.');
        // Dateiname splitten nach Name + Typ
        if ($nFileTypePos === false) {
            $cDateinameSplit_arr[0] = $oExportformat->cDateiname;
        } else {
            $cDateinameSplit_arr[0] = substr($oExportformat->cDateiname, 0, $nFileTypePos);
            $cDateinameSplit_arr[1] = substr($oExportformat->cDateiname, $nFileTypePos);
        }
        // Ist die angelegte Datei größer als die Einstellung im Exportformat?
        clearstatcache();
        if (filesize(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname) >= ($oExportformat->nSplitgroesse * 1024 * 1024 - 102400)) {
            sleep(2);
            loescheExportDateien($oExportformat->cDateiname, $cDateinameSplit_arr[0]);
            $handle     = fopen(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname, 'r');
            $nZeile     = 1;
            $new_handle = fopen(gibDateiPfad($cDateinameSplit_arr, $nDateiZaehler), 'w');
            $nSizeDatei = 0;
            while ($cContent = fgets($handle)) {
                if ($nZeile > 1) {
                    $nSizeZeile = strlen($cContent) + 2;
                    //Schwelle erreicht?
                    if ($nSizeDatei <= ($oExportformat->nSplitgroesse * 1024 * 1024 - 102400)) {
                        // Schreibe Content
                        fwrite($new_handle, $cContent);
                        $nSizeDatei += $nSizeZeile;
                    } else {
                        //neue Datei
                        schreibeFusszeile($new_handle, $oExportformat->cFusszeile, $oExportformat->cKodierung);
                        fclose($new_handle);
                        $nDateiZaehler++;
                        $new_handle = fopen(gibDateiPfad($cDateinameSplit_arr, $nDateiZaehler), 'w');
                        schreibeKopfzeile($new_handle, $oExportformat->cKopfzeile, $oExportformat->cKodierung);
                        // Schreibe Content
                        fwrite($new_handle, $cContent);
                        $nSizeDatei = $nSizeZeile;
                    }
                } elseif ($nZeile === 1) {
                    schreibeKopfzeile($new_handle, $oExportformat->cKopfzeile, $oExportformat->cKodierung);
                }
                $nZeile++;
            }
            fclose($new_handle);
            fclose($handle);
            unlink(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname);
        }
    }
}

/**
 * @param resource $dateiHandle
 * @param string   $cKopfzeile
 * @param string   $cKodierung
 */
function schreibeKopfzeile($dateiHandle, $cKopfzeile, $cKodierung)
{
    //export begin
    if ($cKopfzeile) {
        if ($cKodierung === 'UTF-8' || $cKodierung === 'UTF-8noBOM') {
            if ($cKodierung === 'UTF-8') {
                fwrite($dateiHandle, "\xEF\xBB\xBF");
            }
            fwrite($dateiHandle, utf8_encode($cKopfzeile . "\n"));
        } else {
            fwrite($dateiHandle, $cKopfzeile . "\n");
        }
    }
}

/**
 * @param resource $dateiHandle
 * @param string   $cFusszeile
 * @param string   $cKodierung
 */
function schreibeFusszeile($dateiHandle, $cFusszeile, $cKodierung)
{
    if (strlen($cFusszeile) > 0) {
        if ($cKodierung === 'UTF-8' || $cKodierung === 'UTF-8noBOM') {
            fwrite($dateiHandle, utf8_encode($cFusszeile));
        } else {
            fwrite($dateiHandle, $cFusszeile);
        }
    }
}

/**
 * @param string $cDateiname
 * @param string $cDateinameSplit
 */
function loescheExportDateien($cDateiname, $cDateinameSplit)
{
    if (is_dir(PFAD_ROOT . PFAD_EXPORT)) {
        $dir = opendir(PFAD_ROOT . PFAD_EXPORT);
        if ($dir !== false) {
            while ($cDatei = readdir($dir)) {
                if ($cDatei != $cDateiname && strpos($cDatei, $cDateinameSplit) !== false) {
                    @unlink(PFAD_ROOT . PFAD_EXPORT . $cDatei);
                }
            }
            closedir($dir);
        }
    }
}

/**
 * @param string    $tpl_name
 * @param string    $tpl_source
 * @param JTLSmarty $smarty
 * @return bool
 */
function xdb_get_template($tpl_name, &$tpl_source, $smarty)
{
    $exportformat = Shop::DB()->select('texportformat', 'kExportformat', (int)$tpl_name);
    if (empty($exportformat->kExportformat) || $exportformat->kExportformat <= 0) {
        return false;
    }
    $tpl_source = $exportformat->cContent;

    return true;
}

/**
 * @param string    $tpl_name
 * @param string    $tpl_timestamp
 * @param JTLSmarty $smarty
 * @return bool
 */
function xdb_get_timestamp($tpl_name, &$tpl_timestamp, $smarty)
{
    $tpl_timestamp = time();

    return true;
}

/**
 * @param string    $tpl_name
 * @param JTLSmarty $smarty
 * @return bool
 */
function xdb_get_secure($tpl_name, $smarty)
{
    return true;
}

/**
 * @param string    $tpl_name
 * @param JTLSmarty $smarty
 */
function xdb_get_trusted($tpl_name, $smarty)
{
}

/**
 * @param Artikel $Artikel
 * @param object  $exportformat
 * @param array   $ExportEinstellungen
 * @param array   $KategorieListe
 * @param array   $oGlobal_arr
 */
function verarbeiteYategoExport(&$Artikel, $exportformat, $ExportEinstellungen, &$KategorieListe, &$oGlobal_arr)
{
    if ($Artikel->kArtikel > 0) {
        // Vater
        if ($Artikel->nIstVater == 1) {
            if (count($Artikel->Variationen) > 1) {
                return;
            }
        }

        if (($ExportEinstellungen['exportformate_lager_ueber_null'] === 'Y' && $Artikel->fLagerbestand <= 0) ||
            ($ExportEinstellungen['exportformate_lager_ueber_null'] === 'O' && $Artikel->fLagerbestand <= 0 && $Artikel->cLagerKleinerNull === 'N') ||
            ($ExportEinstellungen['exportformate_preis_ueber_null'] === 'Y' && $Artikel->Preise->fVKNetto <= 0) ||
            ($ExportEinstellungen['exportformate_beschreibung'] === 'Y' && !$Artikel->cBeschreibung)
        ) {
            return;
        }

        $Artikel->cBeschreibungHTML     = str_replace('"', '&quot;', $Artikel->cBeschreibung);
        $Artikel->cKurzBeschreibungHTML = str_replace('"', '&quot;', $Artikel->cKurzBeschreibung);
        $Artikel->cName                 = strip_tags($Artikel->cName);
        $Artikel->cBeschreibung         = strip_tags($Artikel->cBeschreibung, DESCRIPTION_TAGS);
        $Artikel->cBeschreibung         = str_replace('\"', '""', addslashes($Artikel->cBeschreibung));
        $Artikel->cKurzBeschreibung     = strip_tags($Artikel->cKurzBeschreibung);
        $Artikel->cKurzBeschreibung     = str_replace('\"', '""', addslashes($Artikel->cKurzBeschreibung));

        $find    = array("\r\n", "\r", "\n", "\x0B", "\x0");
        $replace = array(' ', ' ', ' ', ' ', '');

        if ($ExportEinstellungen['exportformate_quot'] !== 'N' && $ExportEinstellungen['exportformate_quot']) {
            $find[] = '"';
            if ($ExportEinstellungen['exportformate_quot'] === 'bq') {
                $replace[] = '\"';
            } elseif ($ExportEinstellungen['exportformate_quot'] === 'qq') {
                $replace[] = '""';
            } else {
                $replace[] = $ExportEinstellungen['exportformate_quot'];
            }
        }
        if ($ExportEinstellungen['exportformate_equot'] !== 'N' && $ExportEinstellungen['exportformate_equot']) {
            $find[] = "'";
            if ($ExportEinstellungen['exportformate_equot'] === 'q') {
                $replace[] = '"';
            } else {
                $replace[] = $ExportEinstellungen['exportformate_equot'];
            }
        }
        if ($ExportEinstellungen['exportformate_semikolon'] !== 'N' && $ExportEinstellungen['exportformate_semikolon']) {
            $find[]    = ';';
            $replace[] = $ExportEinstellungen['exportformate_semikolon'];
        }
        $Artikel->cName                 = StringHandler::unhtmlentities($Artikel->cName);
        $Artikel->cBeschreibung         = StringHandler::unhtmlentities($Artikel->cBeschreibung);
        $Artikel->cKurzBeschreibung     = StringHandler::unhtmlentities($Artikel->cKurzBeschreibung);
        $Artikel->cName                 = StringHandler::removeDoubleSpaces(str_replace($find, $replace, $Artikel->cName));
        $Artikel->cBeschreibung         = StringHandler::removeDoubleSpaces(str_replace($find, $replace, $Artikel->cBeschreibung));
        $Artikel->cKurzBeschreibung     = StringHandler::removeDoubleSpaces(str_replace($find, $replace, $Artikel->cKurzBeschreibung));
        $Artikel->cBeschreibungHTML     = StringHandler::removeDoubleSpaces(str_replace($find, $replace, $Artikel->cBeschreibungHTML));
        $Artikel->cKurzBeschreibungHTML = StringHandler::removeDoubleSpaces(str_replace($find, $replace, $Artikel->cKurzBeschreibungHTML));
        $Artikel->Preise->fVKBrutto     = berechneBrutto($Artikel->Preise->fVKNetto, gibUst($Artikel->kSteuerklasse));
        //Kategoriepfad
        $Artikel->Kategorie     = new Kategorie($Artikel->gibKategorie());
        $Artikel->Kategoriepfad = gibKategoriepfad($Artikel->Kategorie, $exportformat->kKundengruppe, $exportformat->kSprache);
        $Artikel->Versandkosten = gibGuenstigsteVersandkosten($ExportEinstellungen['exportformate_lieferland'], $Artikel, 0, $exportformat->kKundengruppe);
        // Kampagne URL
        if (isset($exportformat->tkampagne_cParameter)) {
            $cSep = '?';
            if (strpos($Artikel->cURL, '.php') !== false) {
                $cSep = '&';
            }

            $Artikel->cURL .= $cSep . $exportformat->tkampagne_cParameter . '=' . $exportformat->tkampagne_cWert;
        }

        $Artikel->cDeeplink   = Shop::getURL() . '/' . $Artikel->cURL;
        $Artikel->Artikelbild = '';
        if (isset($Artikel->Bilder[0]->cPfadGross) && strlen($Artikel->Bilder[0]->cPfadGross) > 0) {
            $Artikel->Artikelbild = Shop::getURL() . '/' . $Artikel->Bilder[0]->cPfadGross;
        }
        $Artikel->Lieferbar = 'Y';
        if ($Artikel->fLagerbestand <= 0) {
            $Artikel->Lieferbar = 'N';
        }
        $Artikel->Lieferbar_01 = 1;
        if ($Artikel->fLagerbestand <= 0) {
            $Artikel->Lieferbar_01 = 0;
        }
        $Artikel->Verfuegbarkeit_kelkoo = '003';
        if ($Artikel->fLagerbestand > 0) {
            $Artikel->Verfuegbarkeit_kelkoo = '001';
        }
        // X-Selling
        $oXSellingTMP_arr = Shop::DB()->query(
            "SELECT kXSellArtikel
                FROM txsell
                WHERE kArtikel = " . (int)$Artikel->kArtikel, 9
        );
        $oXSelling_arr = array();
        if (is_array($oXSellingTMP_arr) && count($oXSellingTMP_arr) > 0) {
            foreach ($oXSellingTMP_arr as $oXSellingTMP) {
                $oXSelling_arr[] = $oXSellingTMP['kXSellArtikel'];
            }
        }
        $cVarianten       = '';
        $kKategorie_arr   = array(); // Alle Kategorien vom Artikel
        $kYatKategoie_arr = array(); // Alle Yatego Kategorien vom Artikel
        $oKategorie_arr   = Shop::DB()->query(
            "SELECT kKategorie
                FROM tkategorieartikel
                WHERE kArtikel = " . (int)$Artikel->kArtikel, 2
        );
        if (is_array($oKategorie_arr) && count($oKategorie_arr) > 0) {
            foreach ($oKategorie_arr as $oKategorie) {
                $kKategorie_arr[] = $oKategorie->kKategorie;
            }
        }
        if ($Artikel->FunktionsAttribute['yategokat']) {
            $kYatKategoie_arr = explode(',', $Artikel->FunktionsAttribute['yategokat']);
        }
        // Liste von kKategorien vom Artikel inkl. Yatego Kats
        $kKategorieListe_arr = array_merge($kYatKategoie_arr, $kKategorie_arr);
        // Varianten
        if (is_array($Artikel->Variationen)) {
            $oVariationsListe_arr = array();
            $bEigenschaftCheck    = true;
            // Kinder Prüfen
            $oEigenschaftKombi_arr = array();
            if ($Artikel->nIstVater > 0) {
                $oVariationsKind_arr = ArtikelHelper::getChildren($Artikel->kArtikel);

                if (is_array($oVariationsKind_arr) && count($oVariationsKind_arr) > 0) {
                    $cSQL = '';
                    foreach ($oVariationsKind_arr as $i => $oVariationsKind) {
                        if ($i > 0) {
                            $cSQL .= ", " . (int)$oVariationsKind->kEigenschaftKombi;
                        } else {
                            $cSQL .= (int)$oVariationsKind->kEigenschaftKombi;
                        }
                    }
                    if (strlen($cSQL) > 0) {
                        $oEigenschaftKombi_arr = Shop::DB()->query(
                            "SELECT teigenschaftkombiwert.*, tartikel.kArtikel
                                FROM teigenschaftkombiwert
                                JOIN tartikel ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                                WHERE teigenschaftkombiwert.kEigenschaftKombi IN (" . $cSQL . ")
                                GROUP BY teigenschaftkombiwert.kEigenschaftWert
                                ORDER BY teigenschaftkombiwert.kEigenschaft, teigenschaftkombiwert.kEigenschaftWert", 2
                        );
                    }
                }
            }
            $shopURL              = Shop::getURL();
            $oVariationsLager_arr = array();
            foreach ($Artikel->Variationen as $oVariation) {
                $oGlobal_arr['varianten'][] = array(
                    'foreign_id'       => $oVariation->kEigenschaft,
                    'vs_title'         => $oVariation->cName,
                    'variant_set_name' => $oVariation->cName,
                    'set_sorting'      => 'man',
                    'sorting_number'   => $oVariation->nSort);

                $oVariationsListe_arr[] = $oVariation->kEigenschaft;

                if (is_array($oVariation->Werte) && count($oVariation->Werte) > 0) {
                    foreach ($oVariation->Werte as $oWert) {
                        $oVariationsKind = new stdClass();
                        if (is_array($oEigenschaftKombi_arr) && count($oEigenschaftKombi_arr) > 0) {
                            $bEigenschaftCheck = false;
                            $oArtikelOptionen  = new stdClass();
                            foreach ($oEigenschaftKombi_arr as $oEigenschaftKombi) {
                                if ($oEigenschaftKombi->kEigenschaft == $oVariation->kEigenschaft && $oEigenschaftKombi->kEigenschaftWert == $oWert->kEigenschaftWert) {
                                    $oVariationsKind = new Artikel();
                                    $oVariationsKind->fuelleArtikel($oEigenschaftKombi->kArtikel, $oArtikelOptionen, $exportformat->kKundengruppe, $exportformat->kSprache, true);
                                    $bEigenschaftCheck = true;
                                    break;
                                }
                            }
                        }

                        if ($bEigenschaftCheck) {
                            //@todo: utf_decode ok?
                            $cEinheit = utf8_decode('Stück');
                            $fVPEWert = 1;
                            // Gibts einen Vater und ein Kind dazu?
                            if ($Artikel->nIstVater > 0 && $oVariationsKind->kArtikel > 0) {
                                if (isset($oVariationsKind->cVPEEinheit) && strlen($oVariationsKind->cVPEEinheit) > 0) {
                                    $cEinheit = $oVariationsKind->cVPEEinheit;
                                }
                                if (isset($oVariationsKind->fVPEWert) && $oVariationsKind->fVPEWert > 0) {
                                    $fVPEWert = $oVariationsKind->fVPEWert;
                                }
                                $oWert->fAufpreisNetto = $oVariationsKind->Preise->fVKNetto - $Artikel->Preise->fVKNetto;

                                $cBild = '';
                                if (isset($oVariationsKind->Bilder[0]->cPfadNormal) && strlen($oVariationsKind->Bilder[0]->cPfadNormal) > 0) {
                                    $cBild = $shopURL . '/' . $oVariationsKind->Bilder[0]->cPfadNormal;
                                }
                                $oGlobal_arr['variantenwerte'][] = array(
                                    'variant_set_id' => $oWert->kEigenschaft,
                                    'foreign_id'     => $oWert->kEigenschaftWert,
                                    'title'          => $oWert->cName,
                                    'picture'        => $cBild,
                                    'price'          => getNum($oWert->fAufpreisNetto)
                                );
                                $fLagerbestand = -1;
                                $nAktiv        = 1;
                                if ($oVariationsKind->cLagerBeachten === 'Y') {
                                    $fLagerbestand = $oVariationsKind->fLagerbestand;
                                    if ($oVariationsKind->fLagerbestand <= 0) {
                                        $nAktiv = 0;
                                    }
                                }

                                $oVariationsLager_arr[] = array(
                                    'foreign_id'    => $Artikel->kArtikel . sprintf("%05s", $oWert->kEigenschaftWert),
                                    'article_id'    => $Artikel->kArtikel,
                                    'variant_ids'   => $oWert->kEigenschaftWert,
                                    'stock_value'   => $fLagerbestand,
                                    'delivery_date' => $oVariationsKind->cLieferstatus,
                                    'active'        => $nAktiv,
                                    'article_nr'    => $oVariationsKind->cArtNr . '.' . $oWert->cName,
                                    'price'         => 0,
                                    'quantity_unit' => $cEinheit,
                                    'package_size'  => $fVPEWert,
                                    'info_v_title'  => '',
                                    'info_vs_id'    => '',
                                    'info_p_title'  => '',
                                    'delitem'       => '');
                            } else {
                                if (isset($Artikel->cVPEEinheit) && strlen($Artikel->cVPEEinheit) > 0) {
                                    $cEinheit = $Artikel->cVPEEinheit;
                                }

                                if (isset($Artikel->fVPEWert) && $Artikel->fVPEWert > 0) {
                                    $fVPEWert = $Artikel->fVPEWert;
                                }
                                $oVariationsBild = Shop::DB()->query(
                                    "SELECT cPfad
                                        FROM teigenschaftwertpict
                                        WHERE kEigenschaftWert = " . (int)$oWert->kEigenschaftWert, 1
                                );

                                $cBild = '';
                                if (isset($oVariationsBild->cPfad) && strlen($oVariationsBild->cPfad) > 0) {
                                    $cBild = $shopURL . '/' . PFAD_VARIATIONSBILDER_GROSS . $oVariationsBild->cPfad;
                                }

                                $oGlobal_arr['variantenwerte'][] = array(
                                    'variant_set_id' => $oWert->kEigenschaft,
                                    'foreign_id'     => $oWert->kEigenschaftWert,
                                    'title'          => $oWert->cName,
                                    'picture'        => $cBild,
                                    'price'          => getNum($oWert->fAufpreisNetto)
                                );

                                $fLagerbestand = -1;
                                $nAktiv        = 1;
                                if ($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerVariation === 'Y') {
                                    $fLagerbestand = $oWert->fLagerbestand;
                                    if ($oWert->fLagerbestand <= 0) {
                                        $nAktiv = 0;
                                    }
                                } elseif ($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerVariation === 'N') {
                                    $fLagerbestand = $Artikel->fLagerbestand;
                                    if ($Artikel->fLagerbestand <= 0) {
                                        $nAktiv = 0;
                                    }
                                }

                                $oVariationsLager_arr[] = array(
                                    'foreign_id'    => $Artikel->kArtikel . sprintf("%05s", $oWert->kEigenschaftWert),
                                    'article_id'    => $Artikel->kArtikel,
                                    'variant_ids'   => $oWert->kEigenschaftWert,
                                    'stock_value'   => $fLagerbestand,
                                    'delivery_date' => $Artikel->cLieferstatus,
                                    'active'        => $nAktiv,
                                    'article_nr'    => $Artikel->cArtNr . '.' . $oWert->cName,
                                    'price'         => 0,
                                    'quantity_unit' => $cEinheit,
                                    'package_size'  => $fVPEWert,
                                    'info_v_title'  => '',
                                    'info_vs_id'    => '',
                                    'info_p_title'  => '',
                                    'delitem'       => ''
                                );
                            }
                        }
                    }
                }
            }

            $cVarianten = implode(',', $oVariationsListe_arr);
        }

        $cBacklink = '<h1 style="font-size: 100%;"><a href="' . getURL($Artikel->cURL) . '" target="_blank">' . $Artikel->cName . '</a></h1>';

        $oGlobal_arr['artikel'][] = array(
            'foreign_id'         => $Artikel->kArtikel,
            'article_nr'         => $Artikel->cArtNr,
            'title'              => $Artikel->cName,
            'tax'                => getNum($Artikel->fMwSt),
            'price'              => getNum($Artikel->Preise->fVKBrutto),
            'price_uvp'          => getNum($Artikel->fUVP),
            'delivery_surcharge' => 0,
            'delivery_calc_once' => 0,
            'short_desc'         => '<h2>' . $Artikel->cName . '</h2>' . (($Artikel->cKurzBeschreibung) ? $Artikel->cKurzBeschreibung : substr($Artikel->cBeschreibung, 0, 130)),
            'long_desc'          => '<h2>' . $Artikel->cName . '</h2>' . $Artikel->cBeschreibung . $cBacklink,
            'url'                => getURL($Artikel->cURL),
            'picture'            => getURL($Artikel->Bilder[0]->cPfadGross),
            'picture2'           => getURL($Artikel->Bilder[1]->cPfadGross),
            'picture3'           => getURL($Artikel->Bilder[2]->cPfadGross),
            'picture4'           => getURL($Artikel->Bilder[3]->cPfadGross),
            'picture5'           => getURL($Artikel->Bilder[4]->cPfadGross),
            'categories'         => implode(',', $kKategorieListe_arr),
            'variants'           => $cVarianten,
            'delivery_date'      => $Artikel->cLieferstatus,
            'stock'              => $Artikel->fLagerbestand,
            'cross_selling'      => implode(',', $oXSelling_arr)
        );
        $KategorieListe[$Artikel->Kategorie->kKategorie] = 1;
        // Lager
        if (count($Artikel->Variationen) === 0) {
            $cEinheit      = (isset($Artikel->cVPEEinheit) && strlen($Artikel->cVPEEinheit) > 0) ? $Artikel->cVPEEinheit : utf8_decode('Stück');
            $fVPEWert      = (isset($Artikel->fVPEWert) && $Artikel->fVPEWert > 0) ? $Artikel->fVPEWert : 1;
            $fLagerbestand = -1;
            $nAktiv        = 1;
            if ($Artikel->cLagerBeachten === 'Y') {
                $fLagerbestand = $oWert->fLagerbestand;
                if ($Artikel->fLagerbestand <= 0) {
                    $nAktiv = 0;
                }
            }
            $oGlobal_arr['lager'][] = array(
                'foreign_id'    => $Artikel->kArtikel,
                'article_id'    => $Artikel->kArtikel,
                'variant_ids'   => $cVarianten,
                'stock_value'   => $fLagerbestand,
                'delivery_date' => $Artikel->cLieferstatus,
                'active'        => $nAktiv,
                'article_nr'    => $Artikel->cArtNr,
                'price'         => 0,
                'quantity_unit' => $cEinheit,
                'package_size'  => $fVPEWert,
                'info_v_title'  => '',
                'info_vs_id'    => '',
                'info_p_title'  => '',
                'delitem'       => ''
            );
        } else {
            $oGlobal_arr['lager'] = array_merge($oGlobal_arr['lager'], $oVariationsLager_arr);
        }
    }
}

/**
 * @return bool
 */
function pruefeYategoExportPfad()
{
    return (is_dir(PFAD_ROOT . PFAD_EXPORT_YATEGO) && is_writeable(PFAD_ROOT . PFAD_EXPORT_YATEGO));
}

/**
 * @param int $kExportformat
 * @return array
 */
function getEinstellungenExport($kExportformat)
{
    $kExportformat = (int)$kExportformat;
    $ret           = array();
    if ($kExportformat > 0) {
        $einst = Shop::DB()->query("SELECT cName, cWert FROM texportformateinstellungen WHERE kExportformat = " . $kExportformat, 2);
        foreach ($einst as $eins) {
            if ($eins->cName) {
                $ret[$eins->cName] = $eins->cWert;
            }
        }
    }

    return $ret;
}

/**
 * @param object $oExportformat
 * @return array
 */
function baueArtikelExportSQL(&$oExportformat)
{
    $cSQL_arr          = array();
    $cSQL_arr['Where'] = '';
    $cSQL_arr['Join']  = '';

    if (!$oExportformat->kExportformat) {
        return $cSQL_arr;
    }
    $cExportEinstellungAssoc_arr = getEinstellungenExport($oExportformat->kExportformat);

    switch ($oExportformat->nVarKombiOption) {
        case 2:
            $cSQL_arr['Where'] = " AND kVaterArtikel = 0";
            break;
        case 3:
            $cSQL_arr['Where'] = " AND (tartikel.nIstVater != 1
                            OR tartikel.kEigenschaftKombi > 0)";
            break;
    }
    if (isset($cExportEinstellungAssoc_arr['exportformate_lager_ueber_null']) && $cExportEinstellungAssoc_arr['exportformate_lager_ueber_null'] === 'Y') {
        $cSQL_arr['Where'] .= " AND (NOT (tartikel.fLagerbestand<=0 AND tartikel.cLagerBeachten='Y'))";
    } elseif (isset($cExportEinstellungAssoc_arr['exportformate_lager_ueber_null']) && $cExportEinstellungAssoc_arr['exportformate_lager_ueber_null'] === 'O') {
        $cSQL_arr['Where'] .= " AND (NOT (tartikel.fLagerbestand<=0 AND tartikel.cLagerBeachten='Y') OR tartikel.cLagerKleinerNull='Y')";
    }

    if (isset($cExportEinstellungAssoc_arr['exportformate_preis_ueber_null']) && $cExportEinstellungAssoc_arr['exportformate_preis_ueber_null'] === 'Y') {
        $cSQL_arr['Join'] .= " JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                                AND tpreise.kKundengruppe = " . (int)$oExportformat->kKundengruppe . "
                                AND tpreise.fVKNetto > 0";
    }

    if (isset($cExportEinstellungAssoc_arr['exportformate_beschreibung']) && $cExportEinstellungAssoc_arr['exportformate_beschreibung'] === 'Y') {
        $cSQL_arr['Where'] .= " AND tartikel.cBeschreibung != ''";
    }

    return $cSQL_arr;
}

/**
 * @param object $oExportformat
 * @return mixed
 */
function holeMaxExportArtikelAnzahl(&$oExportformat)
{
    $cSQL_arr = baueArtikelExportSQL($oExportformat);
    $conf     = Shop::getSettings(array(CONF_GLOBAL));
    $sql      = 'AND NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))';
    if (isset($conf['global']['global_erscheinende_kaeuflich']) && $conf['global']['global_erscheinende_kaeuflich'] === 'Y') {
        $sql = 'AND (
                    NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))
                    OR  (
                            DATE(tartikel.dErscheinungsdatum) > DATE(NOW())
                            AND (tartikel.cLagerBeachten = "N" OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = "Y")
                        )
                )';
    }

    return Shop::DB()->query(
        "SELECT count(*) as nAnzahl
            FROM tartikel
            LEFT JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
            LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . (int)$oExportformat->kKundengruppe . "
            " . $cSQL_arr['Join'] . "
            WHERE tartikelattribut.kArtikelAttribut IS NULL" . $cSQL_arr['Where'] . "
                AND tartikelsichtbarkeit.kArtikel IS NULL
                {$sql}", 1
    );
}
