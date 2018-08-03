<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News\Admin;


use DB\DbInterface;
use News\CategoryList;
use News\CommentList;
use News\ItemList;
use Tightenco\Collect\Support\Collection;
use function Functional\map;

/**
 * Class Controller
 * @package News\Admin
 */
class Controller
{
    const UPLOAD_DIR = PFAD_ROOT . PFAD_NEWSBILDER;

    const UPLOAD_DIR_CATEGORY = PFAD_ROOT . PFAD_NEWSKATEGORIEBILDER;
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var \JTLSmarty
     */
    private $smarty;

    /**
     * @var string
     */
    private $msg = '';

    /**
     * @var string
     */
    private $errorMsg = '';

    /**
     * Controller constructor.
     * @param DbInterface $db
     * @param \JTLSmarty  $smarty
     */
    public function __construct(DbInterface $db, \JTLSmarty $smarty)
    {
        $this->db     = $db;
        $this->smarty = $smarty;
    }

    /**
     * @param array          $post
     * @param array          $languages
     * @param \ContentAuthor $contentAuthor
     */
    public function createOrUpdateNewsItem(array $post, array $languages, \ContentAuthor $contentAuthor)
    {
//        \Shop::dbg($post, true, 'POST@controller:');

        $kNews              = (int)($post['kNews'] ?? 0);
        $update             = $kNews > 0;
        $kKundengruppe_arr  = $post['kKundengruppe'] ?? null;
        $kNewsKategorie_arr = $post['kNewsKategorie'] ?? null;
//        $cBetreff           = $post['betreff'];
//        $cSeo               = $post['seo'];
//        $cText              = $post['text'];
//        $cVorschauText      = $post['cVorschauText'];
        $nAktiv = (int)$post['nAktiv'];
//        $cMetaTitle         = $post['cMetaTitle'];
//        $cMetaDescription   = $post['cMetaDescription'];
//        $cMetaKeywords      = $post['cMetaKeywords'];
        $dGueltigVon   = $post['dGueltigVon'];
        $cPreviewImage = $post['previewImage'];
        $kAuthor       = isset($post['kAuthor']) ? (int)$post['kAuthor'] : 0;
        //$dGueltigBis      = $post['dGueltigBis'];

        $cPlausiValue_arr = [];//$this->pruefeNewsPost($cBetreff, $cText, $kKundengruppe_arr, $kNewsKategorie_arr);

        if (\is_array($cPlausiValue_arr) && \count($cPlausiValue_arr) === 0) {
            $oNews                = new \stdClass();
            $oNews->cKundengruppe = ';' . \implode(';', $kKundengruppe_arr) . ';';
            $oNews->nAktiv        = $nAktiv;
            $oNews->dErstellt     = (new \DateTime())->format('Y-m-d H:i:s');
            $oNews->dGueltigVon   = \DateTime::createFromFormat('d.m.Y H:i', $dGueltigVon)->format('Y-m-d H:i:00');
            $oNews->cPreviewImage = $cPreviewImage;
            if ($update === true) {
                $this->db->update('tnews', 'kNews', $kNews, $oNews);
                $this->db->delete('tseo', ['cKey', 'kKey'], ['kNews', $kNews]);
            } else {
                $kNews = $this->db->insert('tnews', $oNews);
            }
            if ($kAuthor > 0) {
                $contentAuthor->setAuthor('NEWS', $kNews, $kAuthor);
            } else {
                $contentAuthor->clearAuthor('NEWS', $kNews);
            }

            $this->db->delete('tnewssprache', 'kNews', $kNews);
            $flags = \ENT_COMPAT | \ENT_HTML401;
            foreach ($languages as $language) {
                $iso                  = $language->cISO;
                $loc                  = new \stdClass();
                $loc->kNews           = $kNews;
                $loc->languageID      = $post['lang_' . $iso];
                $loc->languageCode    = $iso;
                $loc->title           = \htmlspecialchars($post['betreff_' . $iso], $flags,
                    \JTL_CHARSET);
                $loc->content         = parseText($post['text_' . $iso], $kNews);
                $loc->preview         = parseText($post['cVorschauText_' . $iso], $kNews);
                $loc->previewImage    = $cPreviewImage; //@todo!
                $loc->metaTitle       = \htmlspecialchars($post['cMetaTitle_' . $iso], $flags,
                    \JTL_CHARSET);
                $loc->metaDescription = \htmlspecialchars($post['cMetaDescription_' . $iso], $flags,
                    \JTL_CHARSET);
                $loc->metaKeywords    = \htmlspecialchars($post['cMetaKeywords_' . $iso], $flags,
                    \JTL_CHARSET);

                $seoData           = new \stdClass();
                $seoData->cKey     = 'kNews';
                $seoData->kKey     = $kNews;
                $seoData->kSprache = $loc->languageID;
                $seoData->cSeo     = \checkSeo(\getSeo(\strlen($post['seo_' . $iso]) > 0 ? $post['seo_' . $iso] : $post['betreff_' . $iso]));
                $this->db->insert('tnewssprache', $loc);
                $this->db->insert('tseo', $seoData);
            }

//            if ($update === true) {
//                $revision = new \Revision();
//                $revision->addRevision('news', $kNews);
//                $this->db->delete('tnews', 'kNews', $kNews);
//                $this->db->delete('tseo', ['cKey', 'kKey'], ['kNews', $kNews]);
//            }


            $oAlteBilder_arr = [];
            // Bilder hochladen
            if (!\is_dir(self::UPLOAD_DIR . $kNews)) {
                \mkdir(self::UPLOAD_DIR . $kNews);
            } else {
                $oAlteBilder_arr = \holeNewsBilder($oNews->kNews, self::UPLOAD_DIR);
            }
            $oNews->cPreviewImage = $this->addPreviewImage($oAlteBilder_arr, $kNews);
            $this->addImages($oAlteBilder_arr, $kNews);
            $upd                = new \stdClass();
            $upd->cPreviewImage = $oNews->cPreviewImage;
            $this->db->update('tnews', 'kNews', $kNews, $upd);

            // tnewskategorienews fuer aktuelle news loeschen
            $this->db->delete('tnewskategorienews', 'kNews', $kNews);
            // tnewskategorienews eintragen
            foreach ($kNewsKategorie_arr as $kNewsKategorie) {
                $oNewsKategorieNews                 = new \stdClass();
                $oNewsKategorieNews->kNews          = $kNews;
                $oNewsKategorieNews->kNewsKategorie = (int)$kNewsKategorie;
                $this->db->insert('tnewskategorienews', $oNewsKategorieNews);
            }
            // tnewsmonatsuebersicht updaten
            if ($nAktiv === 1) {
                $oDatum = \DateTime::createFromFormat('Y-m-d H:i:s', $oNews->dGueltigVon);
                $dMonat = (int)$oDatum->format('m');
                $dJahr  = (int)$oDatum->format('Y');

                $oNewsMonatsUebersicht = $this->db->select(
                    'tnewsmonatsuebersicht',
                    'kSprache',
                    (int)$_SESSION['kSprache'], //@todo!
                    'nMonat',
                    $dMonat,
                    'nJahr',
                    $dJahr
                );
                // Falls dies die erste News des Monats ist, neuen Eintrag in tnewsmonatsuebersicht, ansonsten updaten
                if (isset($oNewsMonatsUebersicht->kNewsMonatsUebersicht) && $oNewsMonatsUebersicht->kNewsMonatsUebersicht > 0) {
                    unset($oNewsMonatsPraefix);
                    $oNewsMonatsPraefix = $this->db->select('tnewsmonatspraefix', 'kSprache',
                        (int)$_SESSION['kSprache']);
                    if (empty($oNewsMonatsPraefix->cPraefix)) {
                        $oNewsMonatsPraefix->cPraefix = 'Newsuebersicht';
                    }
                    $this->db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        [
                            'kNewsMonatsUebersicht',
                            (int)$oNewsMonatsUebersicht->kNewsMonatsUebersicht,
                            (int)$_SESSION['kSprache']//@todo!
                        ]
                    );
                    // SEO tseo eintragen
                    $oSeo           = new \stdClass();
                    $oSeo->cSeo     = checkSeo(getSeo($oNewsMonatsPraefix->cPraefix . '-' . (string)$dMonat . '-' . $dJahr));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = $oNewsMonatsUebersicht->kNewsMonatsUebersicht;
                    $oSeo->kSprache = $_SESSION['kSprache'];//@todo!
                    $this->db->insert('tseo', $oSeo);
                } else {
                    $oNewsMonatsPraefix = $this->db->select('tnewsmonatspraefix', 'kSprache',
                        (int)$_SESSION['kSprache']);
                    if (empty($oNewsMonatsPraefix->cPraefix)) {
                        $oNewsMonatsPraefix->cPraefix = 'Newsuebersicht';
                    }
                    $oNewsMonatsUebersichtTMP           = new \stdClass();
                    $oNewsMonatsUebersichtTMP->kSprache = (int)$_SESSION['kSprache'];//@todo!
                    $oNewsMonatsUebersichtTMP->cName    = \mappeDatumName((string)$dMonat, $dJahr, $oSpracheNews->cISO);
                    $oNewsMonatsUebersichtTMP->nMonat   = $dMonat;
                    $oNewsMonatsUebersichtTMP->nJahr    = $dJahr;

                    $kNewsMonatsUebersicht = $this->db->insert('tnewsmonatsuebersicht', $oNewsMonatsUebersichtTMP);

                    $this->db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kNewsMonatsUebersicht', $kNewsMonatsUebersicht, (int)$_SESSION['kSprache']]
                    );
                    // SEO tseo eintragen
                    $oSeo           = new \stdClass();
                    $oSeo->cSeo     = \checkSeo(\getSeo($oNewsMonatsPraefix->cPraefix . '-' . (string)$dMonat . '-' . $dJahr));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = $kNewsMonatsUebersicht;
                    $oSeo->kSprache = (int)$_SESSION['kSprache'];//@todo!
                    $this->db->insert('tseo', $oSeo);
                }
            }
            $this->msg .= 'Ihre News wurde erfolgreich gespeichert.<br />';
            if (isset($post['continue']) && $post['continue'] === '1') {
                $step         = 'news_editieren';
                $continueWith = (int)$kNews;
            } else {
                $tab = \RequestHelper::verifyGPDataString('tab');
                \newsRedirect(empty($tab) ? 'aktiv' : $tab, $this->msg);
            }
        } else {
            $step = 'news_editieren';
            $this->smarty->assign('cPostVar_arr', $post)
                         ->assign('cPlausiValue_arr', $cPlausiValue_arr);
            $this->errorMsg .= 'Fehler: Bitte füllen Sie alle Pflichtfelder aus.<br />';

            if (isset($post['kNews']) && \is_numeric($post['kNews'])) {
                $continueWith = (int)$post['kNews'];
            } else {
                $oNewsKategorie_arr = \News::getAllNewsCategories($_SESSION['kSprache'], true);
                $this->smarty->assign('oNewsKategorie_arr', $oNewsKategorie_arr)
                             ->assign('oPossibleAuthors_arr',
                                 $contentAuthor->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
            }
        }
    }

    /**
     * @param array          $newsItems
     * @param \ContentAuthor $author
     */
    public function deleteNewsItems(array $newsItems, \ContentAuthor $author)
    {
        \Shop::dbg($_POST['kNews'], true, 'DELETE:');
        foreach ($newsItems as $kNews) {
            $kNews = (int)$kNews;
            if ($kNews <= 0) {
                continue;
            }
            $author->clearAuthor('NEWS', $kNews);
            $oNewsTMP = $this->db->select('tnews', 'kNews', $kNews);
            $this->db->delete('tnews', 'kNews', $kNews);
            // Bilderverzeichnis loeschen
            \loescheNewsBilderDir($kNews, $cUploadVerzeichnis);
            // Kommentare loeschen
            $this->db->delete('tnewskommentar', 'kNews', $kNews);
            // tseo loeschen
            $this->db->delete('tseo', ['cKey', 'kKey'], ['kNews', $kNews]);
            // tnewskategorienews loeschen
            $this->db->delete('tnewskategorienews', 'kNews', $kNews);
            // War das die letzte News fuer einen bestimmten Monat?
            // => Falls ja, tnewsmonatsuebersicht Monat loeschen
            $oDatum       = \DateTime::createFromFormat('Y-m-d H:i:s', $oNewsTMP->dGueltigVon);
            $dMonat       = (int)$oDatum->format('m');
            $dJahr        = (int)$oDatum->format('Y');
            $kSpracheTMP  = (int)$oNewsTMP->kSprache;
            $oNewsTMP_arr = $this->db->query(
                'SELECT kNews
                    FROM tnews
                    WHERE month(dGueltigVon) = ' . $dMonat . '
                        AND year(dGueltigVon) = ' . $dJahr . '
                        AND kSprache = ' . $kSpracheTMP,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            if (count($oNewsTMP_arr) === 0) {
                $this->db->query(
                    "DELETE tnewsmonatsuebersicht, tseo FROM tnewsmonatsuebersicht
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kNewsMonatsUebersicht'
                            AND tseo.kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                            AND tseo.kSprache = tnewsmonatsuebersicht.kSprache
                        WHERE tnewsmonatsuebersicht.nMonat = " . $dMonat . "
                            AND tnewsmonatsuebersicht.nJahr = " . $dJahr . "
                            AND tnewsmonatsuebersicht.kSprache = " . $kSpracheTMP,
                    \DB\ReturnType::DEFAULT
                );
            }
        }
    }

    /**
     * @param array $post
     * @param array $languages
     */
    public function createOrUpdateCategory(array $post, array $languages)
    {
        $flag = \ENT_COMPAT | \ENT_HTML401;
//        $step             = 'news_uebersicht';
        $kNewsKategorie = (int)($post['kNewsKategorie'] ?? 0);
        $nSort          = (int)$post['nSort'];
        $nAktiv         = (int)$post['nAktiv'];
        $cPreviewImage  = $post['previewImage'];
        $kParent        = (int)$post['kParent'];
//        $cPlausiValue_arr = pruefeNewsKategorie($post['cName'], isset($post['newskategorie_edit_speichern'])
//            ? (int)$post['newskategorie_edit_speichern']
//            : 0);
        $cPlausiValue_arr = [];
        if (\is_array($cPlausiValue_arr) && \count($cPlausiValue_arr) === 0) {

//            if (isset($post['newskategorie_edit_speichern'], $post['kNewsKategorie'])
//                &&
//                (int)$post['newskategorie_edit_speichern'] === 1 && (int)$post['kNewsKategorie'] > 0
//            ) {
//                $kNewsKategorie = (int)$post['kNewsKategorie'];
//                $this->db->delete('tnewskategorie', 'kNewsKategorie', $kNewsKategorie);
//            }
            $this->db->delete('tseo', ['cKey', 'kKey'], ['kNewsKategorie', $kNewsKategorie]);
            $newsCategory                        = new \stdClass();
            $newsCategory->kParent               = $kParent;
            $newsCategory->nSort                 = $nSort > -1 ? $nSort : 0;
            $newsCategory->nAktiv                = $nAktiv;
            $newsCategory->dLetzteAktualisierung = (new \DateTime())->format('Y-m-d H:i:s');
            $newsCategory->cPreviewImage         = $cPreviewImage;

            if ($kNewsKategorie > 0) {
                $newsCategory->kNewsKategorie = $kNewsKategorie;
                $this->db->insert('tnewskategorie', $newsCategory);
            } else {
                $kNewsKategorie = $this->db->insert('tnewskategorie', $newsCategory);
            }

            foreach ($languages as $language) {
                $iso   = $language->cISO;
                $cSeo  = $post['cSeo_' . $iso] ?? '';
                $cName = \htmlspecialchars($post['cName_' . $iso] ?? '', $flag, JTL_CHARSET);

                $loc                  = new \stdClass();
                $loc->kNewsKategorie  = $kNewsKategorie;
                $loc->languageID      = $language->kSprache;
                $loc->languageCode    = $iso;
                $loc->name            = $cName;
                $loc->description     = $post['cBeschreibung_' . $iso];
                $loc->metaTitle       = \htmlspecialchars($post['cMetaTitle_' . $iso] ?? '', $flag,
                    JTL_CHARSET);
                $loc->metaDescription = \htmlspecialchars($post['cMetaDescription_' . $iso] ?? '',
                    $flag, JTL_CHARSET);

                $seoData           = new \stdClass();
                $seoData->cKey     = 'kNewsKategorie';
                $seoData->kKey     = $kNewsKategorie;
                $seoData->kSprache = $loc->languageID;
                $seoData->cSeo     = \checkSeo(\getSeo(\strlen($cSeo) > 0 ? $cSeo : $cName));

                $this->db->insert('tnewskategoriesprache', $loc);
                $this->db->insert('tseo', $seoData);
            }

            //set same activity status for all subcategories
            // @todo
            $oNewsCatAndSubCats_arr = \News::getNewsCatAndSubCats($kNewsKategorie, $_SESSION['kSprache']);
            $upd                    = new \stdClass();
            $upd->nAktiv            = $newsCategory->nAktiv;
            foreach ($oNewsCatAndSubCats_arr as $newsSubCat) {
                $this->db->update('tnewskategorie', 'kNewsKategorie', $newsSubCat, $upd);
            }

            // Vorschaubild hochladen
            if (!\is_dir(self::UPLOAD_DIR_CATEGORY . $kNewsKategorie)) {
                \mkdir(self::UPLOAD_DIR_CATEGORY . $kNewsKategorie, 0777, true);
            }
            if (isset($_FILES['previewImage']['name']) && \strlen($_FILES['previewImage']['name']) > 0) {
                $extension = \substr(
                    $_FILES['previewImage']['type'],
                    \strpos($_FILES['previewImage']['type'], '/') + 1,
                    \strlen($_FILES['previewImage']['type'] - \strpos($_FILES['previewImage']['type'], '/')) + 1
                );
                //not elegant, but since it's 99% jpg..
                if ($extension === 'jpe') {
                    $extension = 'jpg';
                }
                $cUploadDatei = self::UPLOAD_DIR_CATEGORY . $kNewsKategorie . '/preview.' . $extension;
                \move_uploaded_file($_FILES['previewImage']['tmp_name'], $cUploadDatei);
                $newsCategory->cPreviewImage = \PFAD_NEWSKATEGORIEBILDER . $kNewsKategorie . '/preview.' . $extension;
                $upd                         = new \stdClass();
                $upd->cPreviewImage          = $newsCategory->cPreviewImage;
                $this->db->update('tnewskategorie', 'kNewsKategorie', $kNewsKategorie, $upd);
            }

            $this->msg .= 'Ihre Newskategorie wurde erfolgreich eingetragen.<br />';
            \newsRedirect('kategorien', $this->msg);
        } else {
            $this->errorMsg .= 'Fehler: Bitte überprüfen Sie Ihre Eingaben.<br />';
            $step           = 'news_kategorie_erstellen';

            $newsCategory = editiereNewskategorie(\RequestHelper::verifyGPCDataInt('kNewsKategorie'),
                $_SESSION['kSprache']);

            if (isset($newsCategory->kNewsKategorie) && (int)$newsCategory->kNewsKategorie > 0) {
                $this->smarty->assign('oNewsKategorie', $newsCategory);
            } else {
                $step           = 'news_uebersicht';
                $this->errorMsg .= 'Fehler: Die Newskategorie mit der ID ' . $kNewsKategorie .
                    ' konnte nicht gefunden werden.<br />';
            }

            $this->smarty->assign('cPlausiValue_arr', $cPlausiValue_arr)
                         ->assign('cPostVar_arr', $post);
        }
    }

    /**
     * @param array $oAlteBilder_arr
     * @param int   $kNews
     * @return string
     */
    private function addPreviewImage(array $oAlteBilder_arr, int $kNews): string
    {
        if (empty($_FILES['previewImage']['name'])) {
            return '';
        }
        $extension = \substr(
            $_FILES['previewImage']['type'],
            \strpos($_FILES['previewImage']['type'], '/') + 1,
            \strlen($_FILES['previewImage']['type'] - \strpos($_FILES['previewImage']['type'], '/')) + 1
        );
        //not elegant, but since it's 99% jpg..
        if ($extension === 'jpe') {
            $extension = 'jpg';
        }
        //check if preview exists and delete
        foreach ($oAlteBilder_arr as $oBild) {
            if (\strpos($oBild->cDatei, 'preview') !== false) {
                loescheNewsBild($oBild->cName, $kNews, self::UPLOAD_DIR);
            }
        }
        $cUploadDatei = self::UPLOAD_DIR . $kNews . '/preview.' . $extension;
        \move_uploaded_file($_FILES['previewImage']['tmp_name'], $cUploadDatei);

        return PFAD_NEWSBILDER . $kNews . '/preview.' . $extension;
    }

    /**
     * @param array $oAlteBilder_arr
     * @param int   $kNews
     * @return int
     */
    private function addImages(array $oAlteBilder_arr, int $kNews): int
    {
        if (empty($_FILES['Bilder']['name']) || \count($_FILES['Bilder']['name']) === 0) {
            return 0;
        }
        $nLetztesBild = \gibLetzteBildNummer($kNews);
        $nZaehler     = 0;
        if ($nLetztesBild > 0) {
            $nZaehler = $nLetztesBild;
        }
        $imageCount = \count($_FILES['Bilder']['name']) + $nZaehler;
        for ($i = $nZaehler; $i < $imageCount; ++$i) {
            if (!empty($_FILES['Bilder']['size'][$i - $nZaehler])
                && $_FILES['Bilder']['error'][$i - $nZaehler] === UPLOAD_ERR_OK
            ) {
                $type      = $_FILES['Bilder']['type'][$i - $nZaehler];
                $extension = \substr(
                    $type,
                    \strpos($type, '/') + 1,
                    \strlen($type - \strpos($type, '/')) + 1
                );
                //not elegant, but since it's 99% jpg..
                if ($extension === 'jpe') {
                    $extension = 'jpg';
                }
                //check if image exists and delete
                foreach ($oAlteBilder_arr as $oBild) {
                    if (\strpos($oBild->cDatei, 'Bild' . ($i + 1) . '.') !== false
                        && $_FILES['Bilder']['name'][$i - $nZaehler] !== ''
                    ) {
                        \loescheNewsBild($oBild->cName, $kNews, self::UPLOAD_DIR);
                    }
                }
                $cUploadDatei = self::UPLOAD_DIR . $kNews . '/Bild' . ($i + 1) . '.' . $extension;
                \move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $nZaehler], $cUploadDatei);
            }
        }

        return $imageCount;
    }

    /**
     * @param string $cBetreff
     * @param string $cText
     * @param array  $kKundengruppe_arr
     * @param array  $kNewsKategorie_arr
     * @return array
     */
    private function pruefeNewsPost($cBetreff, $cText, $kKundengruppe_arr, $kNewsKategorie_arr)
    {
        $cPlausiValue_arr = [];
        // Betreff prüfen
        if (strlen($cBetreff) === 0) {
            $cPlausiValue_arr['cBetreff'] = 1;
        }
        // Text prüfen
        if (strlen($cText) === 0) {
            $cPlausiValue_arr['cText'] = 1;
        }
        // Kundengruppe prüfen
        if (!\is_array($kKundengruppe_arr) || \count($kKundengruppe_arr) === 0) {
            $cPlausiValue_arr['kKundengruppe_arr'] = 1;
        }
        // Newskategorie prüfen
        if (!\is_array($kNewsKategorie_arr) || \count($kNewsKategorie_arr) === 0) {
            $cPlausiValue_arr['kNewsKategorie_arr'] = 1;
        }

        return $cPlausiValue_arr;
    }

    /**
     * @return Collection
     */
    public function getAllNews(): Collection
    {
        $itemList = new ItemList($this->db);
        $ids      = map($this->db->query(
            'SELECT kNews FROM tnews
                ORDER BY tnews.dGueltigVon DESC',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        ), function ($e) {
            return (int)$e->kNews;
        });
        $itemList->createItems($ids);

        return $itemList->getItems();
    }

    /**
     * @param int $currentLanguageID
     * @return Collection
     */
    public function getNonActivatedComments(int $currentLanguageID): Collection
    {
        $itemList = new CommentList($this->db);
        $ids      = map($this->db->queryPrepared(
            'SELECT tnewskommentar.kNewsKommentar AS id
                FROM tnewskommentar
                JOIN tnews 
                    ON tnews.kNews = tnewskommentar.kNews
                JOIN tnewssprache t 
                    ON tnews.kNews = t.kNews
                WHERE tnewskommentar.nAktiv = 0 
                    AND t.languageID = :lid',
            ['lid' => $currentLanguageID],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        ), function ($e) {
            return (int)$e->id;
        });
        $itemList->createItems($ids);

        return $itemList->getItems();
    }

    /**
     * @param bool $showOnlyActive
     * @return Collection
     */
    public function getAllNewsCategories(bool $showOnlyActive = false): Collection
    {
        $itemList = new CategoryList($this->db);
        $ids      = map($this->db->query(
            'SELECT kNewsKategorie AS id
                FROM tnewskategorie' .
            ($showOnlyActive ? ' WHERE nAktiv = 1 ' : '') .
            ' ORDER BY nSort ASC',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        ), function ($e) {
            return (int)$e->id;
        });
        $itemList->createItems($ids);

        return $itemList->getItems();
    }
}
