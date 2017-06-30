<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param $index
 * @param $create
 * @return array|IOError
 */
function createSearchIndex($index, $create)
{
    require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

    $index    = strtolower(StringHandler::xssClean($index));
    $cHinweis = '';
    $cFehler  = '';

    if (!in_array($index, ['tartikel', 'tartikelsprache'], true)) {
        return new IOError('Ung�ltiger Index angegeben', 403);
    }

    try {
        if (Shop::DB()->query("SHOW INDEX FROM $index WHERE KEY_NAME = 'idx_{$index}_fulltext'", 1)) {
            Shop::DB()->executeQuery("ALTER IGNORE TABLE $index DROP KEY idx_{$index}_fulltext", 10);
        }
    } catch (Exception $e) {
        // Fehler beim Index l�schen ignorieren
        null;
    }

    if ($create === 'Y') {
        $cSuchspalten_arr = array_map(function ($item) {
            $item_arr = explode('.', $item, 2);

            return $item_arr[1];
        }, gibSuchSpalten());

        switch ($index) {
            case 'tartikel':
                $cSpalten_arr = array_intersect(
                    $cSuchspalten_arr,
                    ['cName', 'cSeo', 'cSuchbegriffe', 'cArtNr', 'cKurzBeschreibung', 'cBeschreibung', 'cBarcode', 'cISBN', 'cHAN', 'cAnmerkung']
                );
                break;
            case 'tartikelsprache':
                $cSpalten_arr = array_intersect($cSuchspalten_arr, ['cName', 'cSeo', 'cKurzBeschreibung', 'cBeschreibung']);
                break;
            default:
                return new IOError('Ung�ltiger Index angegeben', 403);
        }

        try {
            $res = Shop::DB()->executeQuery(
                "ALTER TABLE $index
                    ADD FULLTEXT KEY idx_{$index}_fulltext (" . implode(', ', $cSpalten_arr) . ")",
                10
            );
        } catch (Exception $e) {
            $res = 0;
        }

        if ($res === 0) {
            $cFehler = 'Der Index f�r die Volltextsuche konnte nicht angelegt werden! Die Volltextsuche wird deaktiviert.';
            $param   = ['suche_fulltext' => 'N'];
            saveAdminSectionSettings(CONF_ARTIKELUEBERSICHT, $param);

            Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]);
            Shopsetting::getInstance()->reset();
        } else {
            $cHinweis = 'Der Volltextindex f�r ' . $index . ' wurde angelegt!';
        }
    } else {
        $cHinweis = 'Der Volltextindex f�r ' . $index . ' wurde gel�scht!';
    }

    if ($cFehler !== '') {
        return new IOError($cFehler);
    } else {
        return [
            'hinweis' => $cHinweis
        ];
    }
}
