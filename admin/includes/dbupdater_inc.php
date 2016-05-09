<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Stellt alle Werte die fuer das Update in der DB wichtig sind zurueck
 *
 * @return bool
 */
function resetteUpdateDB()
{
    $oColumns_arr = Shop::DB()->query("SHOW COLUMNS FROM tversion", 2);
    if (is_array($oColumns_arr) && count($oColumns_arr) > 0) {
        $cColumns_arr = array();
        foreach ($oColumns_arr as $oColumns) {
            $cColumns_arr[] = $oColumns->Field;
        }
        if (count($cColumns_arr) > 0) {
            if (!in_array('nZeileVon', $cColumns_arr)) {
                Shop::DB()->query("ALTER TABLE tversion ADD nZeileVon INT UNSIGNED NOT NULL AFTER nVersion", 4);
            }
            if (!in_array('nZeileBis', $cColumns_arr)) {
                Shop::DB()->query("ALTER TABLE tversion ADD nZeileBis INT UNSIGNED NOT NULL AFTER nZeileVon", 4);
            }
            if (!in_array('nInArbeit', $cColumns_arr)) {
                Shop::DB()->query("ALTER TABLE tversion ADD nInArbeit TINYINT NOT NULL AFTER nZeileBis", 4);
            }
            if (!in_array('nFehler', $cColumns_arr)) {
                Shop::DB()->query("ALTER TABLE tversion ADD nFehler TINYINT UNSIGNED NOT NULL AFTER nInArbeit", 4);
            }
            if (!in_array('nTyp', $cColumns_arr)) {
                Shop::DB()->query("ALTER TABLE tversion ADD nTyp TINYINT UNSIGNED NOT NULL AFTER nFehler", 4);
            }
            if (!in_array('cFehlerSQL', $cColumns_arr)) {
                Shop::DB()->query("ALTER TABLE tversion ADD cFehlerSQL VARCHAR(255) NOT NULL AFTER nTyp", 4);
            }
        }
        Shop::DB()->query(
            "UPDATE tversion
                SET nZeileVon = 1,
                nZeileBis = 0,
                nFehler = 0,
                nInArbeit = 0,
                nTyp = 1,
                cFehlerSQL = ''", 4
        );
    }
    // Template Cache leeren
    loescheTPLCacheUpdater();

    if (!Shop::DB()->getErrorCode()) {
        return true;
    }

    return false;
}

/**
 * @return bool
 */
function loescheTPLCacheUpdater()
{
    if (loescheVerzeichnisUpdater(PFAD_ROOT . PFAD_COMPILEDIR)) {
        if (loescheVerzeichnisUpdater(PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR)) {
            return true;
        }
    }

    return false;
}

/**
 * @param string $cPfad
 * @return bool
 */
function loescheVerzeichnisUpdater($cPfad)
{
    $bLinux = true;
    // Linux oder Windows?
    if (strpos($cPfad, '\\') !== false) {
        $bLinux = false;
    }

    if ($bLinux) {
        if (strpos(substr($cPfad, (strlen($cPfad) - 1), 1), '/') === false) {
            $cPfad .= '/';
        }
    } elseif (strpos(substr($cPfad, (strlen($cPfad) - 1), 1), '\\') === false) {
        $cPfad .= '\\';
    }

    if (is_dir($cPfad) && is_writable($cPfad)) {
        if ($dirhandle = opendir($cPfad)) {
            while (($file = readdir($dirhandle)) !== false) {
                if ($file !== '.' && $file !== '..' && $file !== '.svn'  && $file !== '.git'  && $file !== '.gitkeep') {
                    if (is_dir($cPfad . $file) && is_writable($cPfad . $file)) {
                        loescheVerzeichnisUpdater($cPfad . $file);
                    }
                    if (is_dir($cPfad . $file) && is_writable($cPfad . $file)) {
                        @rmdir($cPfad . $file);
                    } else {
                        @unlink($cPfad . $file);
                    }
                }
            }
            @closedir($dirhandle);

            return true;
        }

        return false;
    }
    echo $cPfad . ' ist kein Verzeichnis<br>';

    return false;
}

/**
 * @param string $cDatei
 * @return bool
 */
function updateZeilenBis($cDatei)
{
    if (file_exists($cDatei)) {
        $dir_handle = fopen($cDatei, 'r');
        $nRow       = 1;
        while ($cData = fgets($dir_handle)) {
            $nRow++;
        }
        Shop::DB()->query("UPDATE tversion SET nZeileBis = " . (int)$nRow, 4);

        if (!Shop::DB()->getErrorCode()) {
            return true;
        }
    }

    return false;
}

/**
 * @return mixed,
 */
function gibShopVersion()
{
    return Shop::DB()->query("SELECT * FROM tversion", 1);
}

/**
 * @param int $nVersion
 * @return mixed
 */
function gibZielVersion($nVersion)
{
    $nVersion = intval($nVersion);

    $nMajor_arr = [
        219 => 300,
        320 => 400
    ];

    if (array_key_exists($nVersion, $nMajor_arr)) {
        return $nMajor_arr[$nVersion];
    }

    return ++$nVersion;
}

/**
 * @param int $nFehlerCode
 * @return string
 */
function mappeFehlerCode($nFehlerCode)
{
    if (intval($nFehlerCode) > 0) {
        switch (intval($nFehlerCode)) {
            case 1:
                return 'Fehler: Ein SQL Befehl im Update konnte nicht ausgef&uuml;hrt werden. Bitte versuchen Sie es erneut.';
                break;
            case 100:
                return 'Ihr Update wurde erfolgreich abgeschlossen.<br>';
                break;
            case 999:
                return 'Fehler: Ein SQL-Befehl im Update hat 3 mal nicht funktioniert. Das Update wurde abgebrochen. Bitte kontaktieren Sie den Support!<br /><br />
                    <a href="mailto:' . JTLSUPPORT_EMAIL . '?subject=Shop-Update Fehler">Support kontaktieren</a>';
                break;
            default:
                return 'Unbekannter Fehler';
        }
    }

    return 'Unbekannter Fehler';
}

/**
 * @param int $nVersion
 */
function updateFertig($nVersion)
{
    Shop::DB()->query(
        "UPDATE tversion
            SET nVersion = " . intval($nVersion) . ",
            nZeileVon = 1,
            nZeileBis = 0,
            nFehler = 0,
            nInArbeit = 0,
            nTyp = 1,
            cFehlerSQL = '',
            dAktualisiert = now()", 4
    );
    Shop::Cache()->flushAll();
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'dbupdater.php?nErrorCode=100');
    exit();
}

/**
 * @param int $nTyp
 * @param int $nZeileBis
 */
function naechsterUpdateStep($nTyp, $nZeileBis = 1)
{
    Shop::DB()->query(
        "UPDATE tversion
            SET nZeileVon = 1,
            nZeileBis = " . intval($nZeileBis) . ",
            nFehler = 0,
            nInArbeit = 0,
            nTyp = " . intval($nTyp) . ",
            cFehlerSQL = ''", 4
    );

    Shop::DB()->query("UPDATE tversion SET nInArbeit = 0", 4);
    header('Location: ' . Shop::getURL() . '/' . PFAD_ADMIN . 'dbupdater.php?nErrorCode=-1');
    exit();
}
