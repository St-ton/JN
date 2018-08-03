<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News\Admin;


use DB\DbInterface;
use DB\ReturnType;
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
    const UPLOAD_DIR = PFAD_ROOT . \PFAD_NEWSBILDER;

    const UPLOAD_DIR_CATEGORY = PFAD_ROOT . \PFAD_NEWSKATEGORIEBILDER;

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
    private $step = 'news_uebersicht';

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

        $newsItemID      = (int)($post['kNews'] ?? 0);
        $update          = $newsItemID > 0;
        $customerGroups  = $post['kKundengruppe'] ?? null;
        $newsCategoryIDs = $post['kNewsKategorie'] ?? null;
        $active          = (int)$post['nAktiv'];
        $dateValidFrom   = $post['dGueltigVon'];
        $previewImage    = $post['previewImage'];
        $authorID        = isset($post['kAuthor']) ? (int)$post['kAuthor'] : 0;

        $validation = [];//$this->pruefeNewsPost($cBetreff, $cText, $customerGroups, $newsCategoryIDs);

        if (\is_array($validation) && \count($validation) === 0) {
            $newsItem                = new \stdClass();
            $newsItem->cKundengruppe = ';' . \implode(';', $customerGroups) . ';';
            $newsItem->nAktiv        = $active;
            $newsItem->dErstellt     = (new \DateTime())->format('Y-m-d H:i:s');
            $newsItem->dGueltigVon   = \DateTime::createFromFormat('d.m.Y H:i', $dateValidFrom)->format('Y-m-d H:i:00');
            $newsItem->cPreviewImage = $previewImage;
            if ($update === true) {
                $this->db->update('tnews', 'kNews', $newsItemID, $newsItem);
                $this->db->delete('tseo', ['cKey', 'kKey'], ['kNews', $newsItemID]);
            } else {
                $newsItemID = $this->db->insert('tnews', $newsItem);
            }
            if ($authorID > 0) {
                $contentAuthor->setAuthor('NEWS', $newsItemID, $authorID);
            } else {
                $contentAuthor->clearAuthor('NEWS', $newsItemID);
            }

            $this->db->delete('tnewssprache', 'kNews', $newsItemID);
            $flags = \ENT_COMPAT | \ENT_HTML401;
            foreach ($languages as $language) {
                $iso                  = $language->cISO;
                $loc                  = new \stdClass();
                $loc->kNews           = $newsItemID;
                $loc->languageID      = $post['lang_' . $iso];
                $loc->languageCode    = $iso;
                $loc->title           = \htmlspecialchars($post['betreff_' . $iso], $flags,
                    \JTL_CHARSET);
                $loc->content         = parseText($post['text_' . $iso], $newsItemID);
                $loc->preview         = parseText($post['cVorschauText_' . $iso], $newsItemID);
                $loc->previewImage    = $previewImage; //@todo!
                $loc->metaTitle       = \htmlspecialchars($post['cMetaTitle_' . $iso], $flags,
                    \JTL_CHARSET);
                $loc->metaDescription = \htmlspecialchars($post['cMetaDescription_' . $iso], $flags,
                    \JTL_CHARSET);
                $loc->metaKeywords    = \htmlspecialchars($post['cMetaKeywords_' . $iso], $flags,
                    \JTL_CHARSET);

                $seoData           = new \stdClass();
                $seoData->cKey     = 'kNews';
                $seoData->kKey     = $newsItemID;
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


            $oldImages = [];
            // Bilder hochladen
            if (!\is_dir(self::UPLOAD_DIR . $newsItemID)) {
                \mkdir(self::UPLOAD_DIR . $newsItemID);
            } else {
                $oldImages = \holeNewsBilder($newsItem->kNews, self::UPLOAD_DIR);
            }
            $newsItem->cPreviewImage = $this->addPreviewImage($oldImages, $newsItemID);
            $this->addImages($oldImages, $newsItemID);
            $upd                = new \stdClass();
            $upd->cPreviewImage = $newsItem->cPreviewImage;
            $this->db->update('tnews', 'kNews', $newsItemID, $upd);

            // tnewskategorienews fuer aktuelle news loeschen
            $this->db->delete('tnewskategorienews', 'kNews', $newsItemID);
            // tnewskategorienews eintragen
            foreach ($newsCategoryIDs as $categoryID) {
                $ins                 = new \stdClass();
                $ins->kNews          = $newsItemID;
                $ins->kNewsKategorie = (int)$categoryID;
                $this->db->insert('tnewskategorienews', $ins);
            }
            if ($active === 1) {
                $oDatum = \DateTime::createFromFormat('Y-m-d H:i:s', $newsItem->dGueltigVon);
                $month  = (int)$oDatum->format('m');
                $year   = (int)$oDatum->format('Y');

                $monthOverview = $this->db->select(
                    'tnewsmonatsuebersicht',
                    'kSprache',
                    (int)$_SESSION['kSprache'], //@todo!
                    'nMonat',
                    $month,
                    'nJahr',
                    $year
                );
                // Falls dies die erste News des Monats ist, neuen Eintrag in tnewsmonatsuebersicht, ansonsten updaten
                if (isset($monthOverview->kNewsMonatsUebersicht) && $monthOverview->kNewsMonatsUebersicht > 0) {
                    unset($monthOverviewPrefix);
                    $monthOverviewPrefix = $this->db->select('tnewsmonatspraefix', 'kSprache',
                        (int)$_SESSION['kSprache']);
                    if (empty($monthOverviewPrefix->cPraefix)) {
                        $monthOverviewPrefix->cPraefix = 'Newsuebersicht';
                    }
                    $this->db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        [
                            'kNewsMonatsUebersicht',
                            (int)$monthOverview->kNewsMonatsUebersicht,
                            (int)$_SESSION['kSprache']//@todo!
                        ]
                    );
                    $oSeo           = new \stdClass();
                    $oSeo->cSeo     = \checkSeo(\getSeo($monthOverviewPrefix->cPraefix . '-' . $month . '-' . $year));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = $monthOverview->kNewsMonatsUebersicht;
                    $oSeo->kSprache = $_SESSION['kSprache'];//@todo!
                    $this->db->insert('tseo', $oSeo);
                } else {
                    $monthOverviewPrefix = $this->db->select('tnewsmonatspraefix', 'kSprache',
                        (int)$_SESSION['kSprache']);
                    if (empty($monthOverviewPrefix->cPraefix)) {
                        $monthOverviewPrefix->cPraefix = 'Newsuebersicht';
                    }
                    $monthOverview           = new \stdClass();
                    $monthOverview->kSprache = (int)$_SESSION['kSprache'];//@todo!
                    $monthOverview->cName    = \mappeDatumName((string)$month, $year, $oSpracheNews->cISO);
                    $monthOverview->nMonat   = $month;
                    $monthOverview->nJahr    = $year;

                    $kNewsMonatsUebersicht = $this->db->insert('tnewsmonatsuebersicht', $monthOverview);

                    $this->db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kNewsMonatsUebersicht', $kNewsMonatsUebersicht, (int)$_SESSION['kSprache']]
                    );
                    $oSeo           = new \stdClass();
                    $oSeo->cSeo     = \checkSeo(\getSeo($monthOverviewPrefix->cPraefix . '-' . $month . '-' . $year));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = $kNewsMonatsUebersicht;
                    $oSeo->kSprache = (int)$_SESSION['kSprache'];//@todo!
                    $this->db->insert('tseo', $oSeo);
                }
            }
            $this->msg .= 'Ihre News wurde erfolgreich gespeichert.<br />';
            if (isset($post['continue']) && $post['continue'] === '1') {
                $this->step   = 'news_editieren';
                $continueWith = $newsItemID;
            } else {
                $tab = \RequestHelper::verifyGPDataString('tab');
                \newsRedirect(empty($tab) ? 'aktiv' : $tab, $this->msg);
            }
        } else {
            $this->step = 'news_editieren';
            $this->smarty->assign('cPostVar_arr', $post)
                         ->assign('cPlausiValue_arr', $validation);
            $this->errorMsg .= 'Fehler: Bitte füllen Sie alle Pflichtfelder aus.<br />';

            if (isset($post['kNews']) && \is_numeric($post['kNews'])) {
                $continueWith = $newsItemID;
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
        foreach ($newsItems as $newsItemID) {
            $newsItemID = (int)$newsItemID;
            if ($newsItemID <= 0) {
                continue;
            }
            $author->clearAuthor('NEWS', $newsItemID);
            $newsData = $this->db->select('tnews', 'kNews', $newsItemID);
            $this->db->delete('tnews', 'kNews', $newsItemID);
            \loescheNewsBilderDir($newsItemID, self::UPLOAD_DIR);
            $this->db->delete('tnewskommentar', 'kNews', $newsItemID);
            $this->db->delete('tseo', ['cKey', 'kKey'], ['kNews', $newsItemID]);
            $this->db->delete('tnewskategorienews', 'kNews', $newsItemID);
            // War das die letzte News fuer einen bestimmten Monat?
            // => Falls ja, tnewsmonatsuebersicht Monat loeschen
            $date    = \DateTime::createFromFormat('Y-m-d H:i:s', $newsData->dGueltigVon);
            $month   = (int)$date->format('m');
            $year    = (int)$date->format('Y');
            $langID  = (int)$newsData->kSprache;
            $newsIDs = $this->db->queryPrepared(
                'SELECT kNews
                    FROM tnews
                    WHERE MONTH(dGueltigVon) = :mnth1
                        AND YEAR(dGueltigVon) = :yr
                        AND kSprache = :lid',
                [
                    'lid'  => $langID,
                    'mnth' => $month,
                    'yr'   => $year
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (\count($newsIDs) === 0) {
                $this->db->queryPrepared(
                    'DELETE tnewsmonatsuebersicht, tseo 
                        FROM tnewsmonatsuebersicht
                        LEFT JOIN tseo 
                            ON tseo.cKey = :cky
                            AND tseo.kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                            AND tseo.kSprache = tnewsmonatsuebersicht.kSprache
                        WHERE tnewsmonatsuebersicht.nMonat = :mnth
                            AND tnewsmonatsuebersicht.nJahr = :yr
                            AND tnewsmonatsuebersicht.kSprache = :lid',
                    [
                        'cky'  => 'kNewsMonatsUebersicht',
                        'lid'  => $langID,
                        'mnth' => $month,
                        'yr'   => $year
                    ],
                    ReturnType::DEFAULT
                );
            }
        }
    }

    /**
     * @param array $post
     * @param array $languages
     * @return \stdClass
     */
    public function createOrUpdateCategory(array $post, array $languages)
    {
        $flag         = \ENT_COMPAT | \ENT_HTML401;
        $this->step   = 'news_uebersicht';
        $categoryID   = (int)($post['kNewsKategorie'] ?? 0);
        $sort         = (int)$post['nSort'];
        $active       = (int)$post['nAktiv'];
        $previewImage = $post['previewImage'];
        $parentID     = (int)$post['kParent'];
//        $validation = pruefeNewsKategorie($post['cName'], isset($post['newskategorie_edit_speichern'])
//            ? (int)$post['newskategorie_edit_speichern']
//            : 0);
        $validation = [];
        if (\count($validation) === 0) {
            $this->errorMsg .= 'Fehler: Bitte überprüfen Sie Ihre Eingaben.<br />';
            $this->step     = 'news_kategorie_erstellen';

            $newsCategory = \editiereNewskategorie(\RequestHelper::verifyGPCDataInt('kNewsKategorie'),
                $_SESSION['kSprache']);

            if (isset($newsCategory->kNewsKategorie) && (int)$newsCategory->kNewsKategorie > 0) {
                $this->smarty->assign('oNewsKategorie', $newsCategory);
            } else {
                $this->step     = 'news_uebersicht';
                $this->errorMsg .= 'Fehler: Die Newskategorie mit der ID ' . $categoryID .
                    ' konnte nicht gefunden werden.<br />';
            }

            $this->smarty->assign('cPlausiValue_arr', $validation)
                         ->assign('cPostVar_arr', $post);

            return $newsCategory;
        }

        $this->db->delete('tseo', ['cKey', 'kKey'], ['kNewsKategorie', $categoryID]);
        $newsCategory                        = new \stdClass();
        $newsCategory->kParent               = $parentID;
        $newsCategory->nSort                 = $sort > -1 ? $sort : 0;
        $newsCategory->nAktiv                = $active;
        $newsCategory->dLetzteAktualisierung = (new \DateTime())->format('Y-m-d H:i:s');
        $newsCategory->cPreviewImage         = $previewImage;

        if ($categoryID > 0) {
            $newsCategory->kNewsKategorie = $categoryID;
            $this->db->insert('tnewskategorie', $newsCategory);
        } else {
            $categoryID = $this->db->insert('tnewskategorie', $newsCategory);
        }

        foreach ($languages as $language) {
            $iso   = $language->cISO;
            $cSeo  = $post['cSeo_' . $iso] ?? '';
            $cName = \htmlspecialchars($post['cName_' . $iso] ?? '', $flag, \JTL_CHARSET);

            $loc                  = new \stdClass();
            $loc->kNewsKategorie  = $categoryID;
            $loc->languageID      = $language->kSprache;
            $loc->languageCode    = $iso;
            $loc->name            = $cName;
            $loc->description     = $post['cBeschreibung_' . $iso];
            $loc->metaTitle       = \htmlspecialchars($post['cMetaTitle_' . $iso] ?? '', $flag,
                \JTL_CHARSET);
            $loc->metaDescription = \htmlspecialchars($post['cMetaDescription_' . $iso] ?? '',
                $flag, \JTL_CHARSET);

            $seoData           = new \stdClass();
            $seoData->cKey     = 'kNewsKategorie';
            $seoData->kKey     = $categoryID;
            $seoData->kSprache = $loc->languageID;
            $seoData->cSeo     = \checkSeo(\getSeo(\strlen($cSeo) > 0 ? $cSeo : $cName));

            $this->db->insert('tnewskategoriesprache', $loc);
            $this->db->insert('tseo', $seoData);
        }

        //set same activity status for all subcategories
        // @todo
        $newsCatAndSubCats = \News::getNewsCatAndSubCats($categoryID, $_SESSION['kSprache']);
        $upd               = new \stdClass();
        $upd->nAktiv       = $newsCategory->nAktiv;
        foreach ($newsCatAndSubCats as $newsSubCat) {
            $this->db->update('tnewskategorie', 'kNewsKategorie', $newsSubCat, $upd);
        }

        // Vorschaubild hochladen
        if (!\is_dir(self::UPLOAD_DIR_CATEGORY . $categoryID)) {
            \mkdir(self::UPLOAD_DIR_CATEGORY . $categoryID, 0777, true);
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
            $uploadFile = self::UPLOAD_DIR_CATEGORY . $categoryID . '/preview.' . $extension;
            \move_uploaded_file($_FILES['previewImage']['tmp_name'], $uploadFile);
            $newsCategory->cPreviewImage = \PFAD_NEWSKATEGORIEBILDER . $categoryID . '/preview.' . $extension;
            $upd                         = new \stdClass();
            $upd->cPreviewImage          = $newsCategory->cPreviewImage;
            $this->db->update('tnewskategorie', 'kNewsKategorie', $categoryID, $upd);
        }

        $this->msg .= 'Ihre Newskategorie wurde erfolgreich eingetragen.<br />';
        \newsRedirect('kategorien', $this->msg);

        return $newsCategory;
    }

    /**
     * @param array $oldImages
     * @param int   $newsItemID
     * @return string
     */
    private function addPreviewImage(array $oldImages, int $newsItemID): string
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
        foreach ($oldImages as $image) {
            if (\strpos($image->cDatei, 'preview') !== false) {
                \loescheNewsBild($image->cName, $newsItemID, self::UPLOAD_DIR);
            }
        }
        $uploadFile = self::UPLOAD_DIR . $newsItemID . '/preview.' . $extension;
        \move_uploaded_file($_FILES['previewImage']['tmp_name'], $uploadFile);

        return \PFAD_NEWSBILDER . $newsItemID . '/preview.' . $extension;
    }

    /**
     * @param array $oldImages
     * @param int   $kNews
     * @return int
     */
    private function addImages(array $oldImages, int $kNews): int
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
                && $_FILES['Bilder']['error'][$i - $nZaehler] === \UPLOAD_ERR_OK
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
                foreach ($oldImages as $image) {
                    if (\strpos($image->cDatei, 'Bild' . ($i + 1) . '.') !== false
                        && $_FILES['Bilder']['name'][$i - $nZaehler] !== ''
                    ) {
                        \loescheNewsBild($image->cName, $kNews, self::UPLOAD_DIR);
                    }
                }
                $uploadFile = self::UPLOAD_DIR . $kNews . '/Bild' . ($i + 1) . '.' . $extension;
                \move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $nZaehler], $uploadFile);
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
        $validation = [];
        if (\strlen($cBetreff) === 0) {
            $validation['cBetreff'] = 1;
        }
        if (\strlen($cText) === 0) {
            $validation['cText'] = 1;
        }
        if (!\is_array($kKundengruppe_arr) || \count($kKundengruppe_arr) === 0) {
            $validation['kKundengruppe_arr'] = 1;
        }
        if (!\is_array($kNewsKategorie_arr) || \count($kNewsKategorie_arr) === 0) {
            $validation['kNewsKategorie_arr'] = 1;
        }

        return $validation;
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
            ReturnType::ARRAY_OF_OBJECTS
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
            ReturnType::ARRAY_OF_OBJECTS
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
            ReturnType::ARRAY_OF_OBJECTS
        ), function ($e) {
            return (int)$e->id;
        });
        $itemList->createItems($ids);

        return $itemList->getItems();
    }

    /**
     * @return string
     */
    public function getStep(): string
    {
        return $this->step;
    }

    /**
     * @param string $step
     */
    public function setStep(string $step)
    {
        $this->step = $step;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @param string $msg
     */
    public function setMsg(string $msg)
    {
        $this->msg = $msg;
    }

    /**
     * @return string
     */
    public function getErrorMsg(): string
    {
        return $this->errorMsg;
    }

    /**
     * @param string $errorMsg
     */
    public function setErrorMsg(string $errorMsg)
    {
        $this->errorMsg = $errorMsg;
    }
}
