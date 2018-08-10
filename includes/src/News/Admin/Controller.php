<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News\Admin;


use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\ReturnType;
use News\Category;
use News\CategoryInterface;
use News\CategoryList;
use News\CommentList;
use News\Item;
use News\ItemList;
use Tightenco\Collect\Support\Collection;
use function Functional\map;

/**
 * Class Controller
 * @package News\Admin
 */
class Controller
{
    const UPLOAD_DIR = \PFAD_ROOT . \PFAD_NEWSBILDER;

    const UPLOAD_DIR_CATEGORY = \PFAD_ROOT . \PFAD_NEWSKATEGORIEBILDER;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var \JTLSmarty
     */
    private $smarty;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

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
     * @param DbInterface       $db
     * @param \JTLSmarty        $smarty
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, \JTLSmarty $smarty, JTLCacheInterface $cache)
    {
        $this->db     = $db;
        $this->smarty = $smarty;
        $this->cache  = $cache;
    }

    /**
     * @return int
     */
    private function flushCache(): int
    {
        return $this->cache->flushTags([\CACHING_GROUP_NEWS]);
    }

    /**
     * @param array          $post
     * @param array          $languages
     * @param \ContentAuthor $contentAuthor
     * @throws \Exception
     */
    public function createOrUpdateNewsItem(array $post, array $languages, \ContentAuthor $contentAuthor)
    {
        $newsItemID      = (int)($post['kNews'] ?? 0);
        $update          = $newsItemID > 0;
        $customerGroups  = $post['kKundengruppe'] ?? null;
        $newsCategoryIDs = $post['kNewsKategorie'] ?? null;
        $active          = (int)$post['nAktiv'];
        $dateValidFrom   = $post['dGueltigVon'];
        $previewImage    = $post['previewImage'];
        $authorID        = (int)($post['kAuthor'] ?? 0);

        $validation = $this->pruefeNewsPost($customerGroups, $newsCategoryIDs);

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
                $langID               = (int)$post['lang_' . $iso];
                $loc                  = new \stdClass();
                $loc->kNews           = $newsItemID;
                $loc->languageID      = $langID;
                $loc->languageCode    = $iso;
                $loc->title           = \htmlspecialchars($post['betreff_' . $iso], $flags, \JTL_CHARSET);
                $loc->content         = $this->parseContent($post['text_' . $iso], $newsItemID);
                $loc->preview         = $this->parseContent($post['cVorschauText_' . $iso], $newsItemID);
                $loc->previewImage    = $previewImage; //@todo!
                $loc->metaTitle       = \htmlspecialchars($post['cMetaTitle_' . $iso], $flags, \JTL_CHARSET);
                $loc->metaDescription = \htmlspecialchars($post['cMetaDescription_' . $iso], $flags, \JTL_CHARSET);
                $loc->metaKeywords    = \htmlspecialchars($post['cMetaKeywords_' . $iso], $flags, \JTL_CHARSET);

                if (empty($loc->content)) {
                    // skip language without content
                    continue;
                }

                $seoData           = new \stdClass();
                $seoData->cKey     = 'kNews';
                $seoData->kKey     = $newsItemID;
                $seoData->kSprache = $langID;
                $seoData->cSeo     = \checkSeo(\getSeo(\strlen($post['seo_' . $iso]) > 0
                    ? $post['seo_' . $iso]
                    : $post['betreff_' . $iso]));
                $this->db->insert('tnewssprache', $loc);
                $this->db->insert('tseo', $seoData);

                if ($active === 0) {
                    continue;
                }
                $date  = \DateTime::createFromFormat('Y-m-d H:i:s', $newsItem->dGueltigVon);
                $month = (int)$date->format('m');
                $year  = (int)$date->format('Y');

                $monthOverview = $this->db->select(
                    'tnewsmonatsuebersicht',
                    'kSprache',
                    $langID,
                    'nMonat',
                    $month,
                    'nJahr',
                    $year
                );
                // Falls dies die erste News des Monats ist, neuen Eintrag in tnewsmonatsuebersicht, ansonsten updaten
                if (isset($monthOverview->kNewsMonatsUebersicht) && $monthOverview->kNewsMonatsUebersicht > 0) {
                    $prefix = $this->db->select('tnewsmonatspraefix', 'kSprache',
                            $langID)->cPraefix ?? 'Newsuebersicht';
                    $this->db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        [
                            'kNewsMonatsUebersicht',
                            (int)$monthOverview->kNewsMonatsUebersicht,
                            $langID
                        ]
                    );
                    $oSeo           = new \stdClass();
                    $oSeo->cSeo     = \checkSeo(\getSeo($prefix . '-' . $month . '-' . $year));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = $monthOverview->kNewsMonatsUebersicht;
                    $oSeo->kSprache = $langID;
                    $this->db->insert('tseo', $oSeo);
                } else {
                    $prefix                  = $this->db->select('tnewsmonatspraefix', 'kSprache',
                            $langID)->cPraefix ?? 'Newsuebersicht';
                    $monthOverview           = new \stdClass();
                    $monthOverview->kSprache = $langID;
                    $monthOverview->cName    = \News\Controller::mapDateName((string)$month, $year, $iso);
                    $monthOverview->nMonat   = $month;
                    $monthOverview->nJahr    = $year;

                    $kNewsMonatsUebersicht = $this->db->insert('tnewsmonatsuebersicht', $monthOverview);

                    $this->db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kNewsMonatsUebersicht', $kNewsMonatsUebersicht, $langID]
                    );
                    $oSeo           = new \stdClass();
                    $oSeo->cSeo     = \checkSeo(\getSeo($prefix . '-' . $month . '-' . $year));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = $kNewsMonatsUebersicht;
                    $oSeo->kSprache = $langID;
                    $this->db->insert('tseo', $oSeo);
                }
            }

//            if ($update === true) {
//                $revision = new \Revision();
//                $revision->addRevision('news', $kNews);
//                $this->db->delete('tnews', 'kNews', $kNews);
//                $this->db->delete('tseo', ['cKey', 'kKey'], ['kNews', $kNews]);
//            }

            $dir = self::UPLOAD_DIR . $newsItemID;
            if (!\is_dir($dir) && !\mkdir(self::UPLOAD_DIR . $newsItemID) && !\is_dir($dir)) {
                throw new \Exception('Cannot create upload dir: ' . $dir);
            }
            $oldImages = $this->getNewsImages($newsItemID, self::UPLOAD_DIR);

            $newsItem->cPreviewImage = $this->addPreviewImage($oldImages, $newsItemID);
            $this->addImages($oldImages, $newsItemID);
            $upd                = new \stdClass();
            $upd->cPreviewImage = $newsItem->cPreviewImage;
            $this->db->update('tnews', 'kNews', $newsItemID, $upd);
            $this->db->delete('tnewskategorienews', 'kNews', $newsItemID);
            foreach ($newsCategoryIDs as $categoryID) {
                $ins                 = new \stdClass();
                $ins->kNews          = $newsItemID;
                $ins->kNewsKategorie = (int)$categoryID;
                $this->db->insert('tnewskategorienews', $ins);
            }
            $this->msg .= 'Ihre News wurde erfolgreich gespeichert.<br />';
            if (isset($post['continue']) && $post['continue'] === '1') {
                $this->step   = 'news_editieren';
                $continueWith = $newsItemID;
            } else {
                $tab = \RequestHelper::verifyGPDataString('tab');
                $this->newsRedirect(empty($tab) ? 'aktiv' : $tab, $this->msg);
            }
            $this->flushCache();
        } else {
            $this->step = 'news_editieren';
            $this->smarty->assign('cPostVar_arr', $post)
                         ->assign('cPlausiValue_arr', $validation);
            $this->errorMsg .= 'Fehler: Bitte füllen Sie alle Pflichtfelder aus.<br />';

            if (isset($post['kNews']) && \is_numeric($post['kNews'])) {
                $continueWith = $newsItemID;
            } else {
                // @todo
                $oNewsKategorie_arr = \News::getAllNewsCategories($_SESSION['kSprache'], true);
                $this->smarty->assign('oNewsKategorie_arr', $oNewsKategorie_arr)
                             ->assign('oPossibleAuthors_arr',
                                 $contentAuthor->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
            }
        }
    }

    /**
     * @param int   $id
     * @param array $post
     * @return bool
     */
    public function saveComment(int $id, array $post): bool
    {
        $upd             = new \stdClass();
        $upd->cName      = $post['cName'];
        $upd->cKommentar = $post['cKommentar'];
        $this->flushCache();

        return $this->db->update('tnewskommentar', 'kNewsKommentar', $id, $upd) >= 0;
    }

    /**
     * @param array          $newsItems
     * @param \ContentAuthor $author
     */
    public function deleteNewsItems(array $newsItems, \ContentAuthor $author)
    {
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
                    WHERE MONTH(dGueltigVon) = :mnth
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
        $this->flushCache();
    }

    /**
     * @param int[] $ids
     * @return bool
     */
    public function deleteCategories(array $ids): bool
    {
        foreach ($ids as $id) {
            foreach ($this->getCategoryAndChildrenByID($id) as $newsSubCat) {
                $this->db->delete('tnewskategorie', 'kNewsKategorie', $newsSubCat);
                $this->db->delete('tseo', ['cKey', 'kKey'], ['kNewsKategorie', $newsSubCat]);
                $this->db->delete('tnewskategorienews', 'kNewsKategorie', $newsSubCat);
                $this->db->delete('tnewskategoriesprache ', 'kNewsKategorie', $newsSubCat);
            }
        }
        $this->deactivateUnassociatedNewsItems();
        $this->flushCache();

        return true;
    }

    /**
     * @param int $categoryID
     * @return int[]
     */
    private function getCategoryAndChildrenByID(int $categoryID): array
    {
        return map($this->db->queryPrepared(
            'SELECT node.kNewsKategorie AS id
                FROM tnewskategorie AS node, tnewskategorie AS parent
                WHERE node.lft BETWEEN parent.lft AND parent.rght
                    AND parent.kNewsKategorie = :cid',
            ['cid' => $categoryID],
            ReturnType::ARRAY_OF_OBJECTS
        ), function ($e) {
            return (int)$e->id;
        });
    }

//    /**
//     * @param Collection $categories
//     * @param int        $find
//     * @return Category|null
//     */
//    private function findCategoryByID(Collection $categories, int $find)
//    {
//        return $categories->first(function (Category $i) use ($find) {
//            return ($id = $i->getID()) > 0 && $id === $find;
//        });
//    }

//    /**
//     * @param Collection $categories
//     * @param array      $parentIDs
//     * @return Collection
//     */
//    private function findChildCategories(Collection $categories, array $parentIDs): Collection
//    {
//        return $categories->filter(function (Category $i) use ($parentIDs) {
//            return \in_array($i->getParentID(), $parentIDs, true);
//        });
//    }

    /**
     * deactivate all news items without a category
     * @return int
     */
    private function deactivateUnassociatedNewsItems(): int
    {
        return $this->db->query(
            'UPDATE tnews 
                SET nAktiv = 0
                WHERE kNews > 0 AND kNews NOT IN (
                    SELECT kNews FROM tnewskategorienews
                )',
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param array $post
     * @param array $languages
     * @return CategoryInterface
     * @throws \Exception
     */
    public function createOrUpdateCategory(array $post, array $languages): CategoryInterface
    {
        $this->step   = 'news_uebersicht';
        $categoryID   = (int)($post['kNewsKategorie'] ?? 0);
        $sort         = (int)$post['nSort'];
        $active       = (int)$post['nAktiv'];
        $parentID     = (int)$post['kParent'];
        $previewImage = $post['previewImage'];
        $flag         = \ENT_COMPAT | \ENT_HTML401;
//        $validation = $this->pruefeNewsKategorie($post);
//        if (\count($validation) > 0) {
//            $this->errorMsg .= 'Fehler: Bitte überprüfen Sie Ihre Eingaben.<br />';
//            $this->step     = 'news_kategorie_erstellen';
//            $newsCategory   = new Category($this->db);
//            $newsCategory->load($categoryID);
//
//            if ($newsCategory->getID() > 0) {
//                $this->smarty->assign('oNewsKategorie', $newsCategory);
//            }
//
//            $this->smarty->assign('cPlausiValue_arr', $validation)
//                         ->assign('cPostVar_arr', $post);
//
//            return $newsCategory;
//        }
        $this->db->delete('tseo', ['cKey', 'kKey'], ['kNewsKategorie', $categoryID]);
        $newsCategory                        = new \stdClass();
        $newsCategory->kParent               = $parentID;
        $newsCategory->nSort                 = $sort > -1 ? $sort : 0;
        $newsCategory->nAktiv                = $active;
        $newsCategory->dLetzteAktualisierung = (new \DateTime())->format('Y-m-d H:i:s');
        $newsCategory->cPreviewImage         = $previewImage;

        if ($categoryID > 0) {
            $this->db->update('tnewskategorie', 'kNewsKategorie', $categoryID, $newsCategory);
        } else {
            $categoryID = $this->db->insert('tnewskategorie', $newsCategory);
        }
        $newsCategory->kNewsKategorie = $categoryID;

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
            $loc->metaTitle       = \htmlspecialchars($post['cMetaTitle_' . $iso] ?? '', $flag, \JTL_CHARSET);
            $loc->metaDescription = \htmlspecialchars($post['cMetaDescription_' . $iso] ?? '', $flag, \JTL_CHARSET);

            $seoData           = new \stdClass();
            $seoData->cKey     = 'kNewsKategorie';
            $seoData->kKey     = $categoryID;
            $seoData->kSprache = $loc->languageID;
            $seoData->cSeo     = \checkSeo(\getSeo(\strlen($cSeo) > 0 ? $cSeo : $cName));

            $this->db->insert('tnewskategoriesprache', $loc);
            $this->db->insert('tseo', $seoData);
        }
        // set same activation status for all subcategories
        $affected    = $this->getCategoryAndChildrenByID($categoryID);
        $upd         = new \stdClass();
        $upd->nAktiv = $newsCategory->nAktiv;
        foreach ($affected as $id) {
            $this->db->update('tnewskategorie', 'kNewsKategorie', $id, $upd);
        }
        // Vorschaubild hochladen
        $dir = self::UPLOAD_DIR_CATEGORY . $categoryID;
        if (!\is_dir($dir) && !\mkdir($dir) && !\is_dir($dir)) {
            throw new \Exception('Cannot create upload dir: ' . $dir);
        }
        if (isset($_FILES['previewImage']['name']) && \strlen($_FILES['previewImage']['name']) > 0) {
            $extension = \substr(
                $_FILES['previewImage']['type'],
                \strpos($_FILES['previewImage']['type'], '/') + 1,
                \strlen($_FILES['previewImage']['type'] - \strpos($_FILES['previewImage']['type'], '/')) + 1
            );
            // not elegant, but since it's 99% jpg..
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
        $this->rebuildCategoryTree(0, 1);
        $this->msg .= 'Ihre Newskategorie wurde erfolgreich eingetragen.<br />';
        $this->newsRedirect('kategorien', $this->msg);
        $newsCategory = new Category($this->db);
        $this->flushCache();

        return $newsCategory->load($categoryID);
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
        if ($extension === 'jpe') {
            $extension = 'jpg';
        }
        foreach ($oldImages as $image) {
            if (\strpos($image->cDatei, 'preview') !== false) {
                $this->deleteNewsImage($image->cName, $newsItemID, self::UPLOAD_DIR);
            }
        }
        $uploadFile = self::UPLOAD_DIR . $newsItemID . '/preview.' . $extension;
        \move_uploaded_file($_FILES['previewImage']['tmp_name'], $uploadFile);

        return \PFAD_NEWSBILDER . $newsItemID . '/preview.' . $extension;
    }

    /**
     * @param array $oldImages
     * @param int   $newsItemID
     * @return int
     */
    private function addImages(array $oldImages, int $newsItemID): int
    {
        if (empty($_FILES['Bilder']['name']) || \count($_FILES['Bilder']['name']) === 0) {
            return 0;
        }
        $lastImage = \gibLetzteBildNummer($newsItemID);
        $counter   = 0;
        if ($lastImage > 0) {
            $counter = $lastImage;
        }
        $imageCount = \count($_FILES['Bilder']['name']) + $counter;
        for ($i = $counter; $i < $imageCount; ++$i) {
            if (!empty($_FILES['Bilder']['size'][$i - $counter])
                && $_FILES['Bilder']['error'][$i - $counter] === \UPLOAD_ERR_OK
            ) {
                $type      = $_FILES['Bilder']['type'][$i - $counter];
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
                        && $_FILES['Bilder']['name'][$i - $counter] !== ''
                    ) {
                        $this->deleteNewsImage($image->cName, $newsItemID, self::UPLOAD_DIR);
                    }
                }
                $uploadFile = self::UPLOAD_DIR . $newsItemID . '/Bild' . ($i + 1) . '.' . $extension;
                \move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $counter], $uploadFile);
            }
        }

        return $imageCount;
    }

    /**
     * @param string $cName
     * @param int    $nNewskategorieEditSpeichern
     * @return array
     */
    private function pruefeNewsKategorie($cName, $nNewskategorieEditSpeichern = 0)
    {
        return [];
//        $cPlausiValue_arr = [];
//        // Name prüfen
//        if (strlen($cName) === 0) {
//            $cPlausiValue_arr['cName'] = 1;
//        }
//        // Prüfen ob Name schon vergeben
//        if ($nNewskategorieEditSpeichern === 0) {
//            $oNewsKategorieTMP = Shop::Container()->getDB()->select('tnewskategorie', 'cName', $cName);
//            if (isset($oNewsKategorieTMP->kNewsKategorie) && $oNewsKategorieTMP->kNewsKategorie > 0) {
//                $cPlausiValue_arr['cName'] = 2;
//            }
//        }
//
//        return $cPlausiValue_arr;
    }

    /**
     * @param array $customerGroups
     * @param array $categories
     * @return array
     */
    private function pruefeNewsPost($customerGroups, $categories): array
    {
        $validation = [];
//        if (\strlen($cBetreff) === 0) {
//            $validation['cBetreff'] = 1;
//        }
//        if (\strlen($cText) === 0) {
//            $validation['cText'] = 1;
//        }
        if (!\is_array($customerGroups) || \count($customerGroups) === 0) {
            $validation['kKundengruppe_arr'] = 1;
        }
        if (!\is_array($categories) || \count($categories) === 0) {
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
            'SELECT node.kNewsKategorie AS id
                FROM tnewskategorie AS node INNER JOIN tnewskategorie AS parent
                WHERE node.lvl > 0 
                    AND parent.lvl > 0 ' . ($showOnlyActive ? ' AND node.nAktiv = 1 ' : '') .
            ' GROUP BY node.kNewsKategorie
                ORDER BY node.lft, node.nSort ASC',
            ReturnType::ARRAY_OF_OBJECTS
        ), function ($e) {
            return (int)$e->id;
        });
        $itemList->createItems($ids);

        return $itemList->generateTree();
    }

    /**
     * @param int    $itemID
     * @param string $uploadDirName
     * @return array
     */
    public function getNewsImages(int $itemID, string $uploadDirName): array
    {
        $images = [];
        if ($itemID > 0 && \is_dir($uploadDirName . $itemID)) {
            $handle       = \opendir($uploadDirName . $itemID);
            $imageBaseURL = \Shop::getURL() . '/';
            while (false !== ($file = \readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    $image           = new \stdClass();
                    $image->cName    = \substr($file, 0, \strpos($file, '.'));
                    $image->cURL     = \PFAD_NEWSBILDER . $itemID . '/' . $file;
                    $image->cURLFull = $imageBaseURL . \PFAD_NEWSBILDER . $itemID . '/' . $file;
                    $image->cDatei   = $file;

                    $images[] = $image;
                }
            }

            \usort($images, function ($a, $b) {
                return \strcmp($a->cName, $b->cName);
            });
        }

        return $images;
    }

    /**
     * @param int    $itemID
     * @param string $uploadDirName
     * @return array
     */
    public function getCategoryImages(int $itemID, string $uploadDirName): array
    {
        $images = [];
        if ($itemID > 0 && \is_dir($uploadDirName . $itemID)) {
            $handle       = \opendir($uploadDirName . $itemID);
            $imageBaseURL = \Shop::getURL() . '/';
            while (false !== ($file = \readdir($handle))) {
                if ($file !== '.' && $file !== '..') {
                    $image           = new \stdClass();
                    $image->cName    = \substr($file, 0, \strpos($file, '.'));
                    $image->cURL     = '<img src="' . $imageBaseURL .
                        \PFAD_NEWSKATEGORIEBILDER . $itemID . '/' . $file . '" />';
                    $image->cURLFull = $imageBaseURL . \PFAD_NEWSBILDER . $itemID . '/' . $file;
                    $image->cDatei   = $file;

                    $images[] = $image;
                }
            }

            \usort($images, function ($a, $b) {
                return \strcmp($a->cName, $b->cName);
            });
        }

        return $images;
    }

    /**
     * @param array     $items
     * @param Item|null $newsItem
     */
    public function deleteComments(array $items, Item $newsItem = null)
    {
        if (\count($items) > 0) {
            foreach ($items as $id) {
                $this->db->delete('tnewskommentar', 'kNewsKommentar', (int)$id);
            }
            $this->flushCache();
            $this->setMsg('Ihre markierten Kommentare wurden erfolgreich gelöscht.');
            $tab    = \RequestHelper::verifyGPDataString('tab');
            $params = [
                'news'  => '1',
                'nd'    => '1',
                'token' => $_SESSION['jtl_token'],
            ];
            if ($newsItem !== null) {
                $params['kNews'] = $newsItem->getID();
            }
            $this->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $this->getMsg(), $params);
        } else {
            $this->setErrorMsg('Fehler: Sie müssen mindestens einen Kommentar markieren.');
        }
    }

    /**
     * @param string $imageName
     * @param int    $id
     * @param string $uploadDir
     * @return bool
     */
    public function deleteNewsImage(string $imageName, int $id, string $uploadDir): bool
    {
        if ($id > 0
            && \strlen($imageName) > 0
            && \is_dir($uploadDir)
            && \is_dir($uploadDir . $id)
        ) {
            $handle = \opendir($uploadDir . $id);
            while (false !== ($file = \readdir($handle))) {
                if ($file !== '.' && $file !== '..' && \substr($file, 0, \strpos($file, '.')) === $imageName) {
                    \unlink($uploadDir . $id . '/' . $file);
                    \closedir($handle);
                    if ($imageName === 'preview') {
                        $upd                = new \stdClass();
                        $upd->cPreviewImage = '';
                        if (\strpos($uploadDir, \PFAD_NEWSKATEGORIEBILDER) === false) {
                            $this->db->update('tnews', 'kNews', $id, $upd);
                        } else {
                            $this->db->update('tnewskategorie', 'kNewsKategorie', $id, $upd);
                        }
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $tab
     * @param string $msg
     * @param array  $urlParams
     */
    public function newsRedirect($tab = '', $msg = '', $urlParams = null)
    {
        $tabPageMapping = [
            'inaktiv'    => 's1',
            'aktiv'      => 's2',
            'kategorien' => 's3',
        ];
        if (empty($msg)) {
            unset($_SESSION['news.cHinweis']);
        } else {
            $_SESSION['news.cHinweis'] = $msg;
        }

        if (!empty($tab)) {
            if (!\is_array($urlParams)) {
                $urlParams = [];
            }
            $urlParams['tab'] = $tab;
            if (isset($tabPageMapping[$tab])
                && \RequestHelper::verifyGPCDataInt($tabPageMapping[$tab]) > 1
                && !\array_key_exists($tabPageMapping[$tab], $urlParams)
            ) {
                $urlParams[$tabPageMapping[$tab]] = \RequestHelper::verifyGPCDataInt($tabPageMapping[$tab]);
            }
        }

        \header('Location: news.php' . (\is_array($urlParams)
                ? '?' . \http_build_query($urlParams, '', '&')
                : ''));
        exit;
    }

    /**
     * @param string $text
     * @param int    $id
     * @return string
     */
    private function parseContent(string $text, int $id): string
    {
        $uploadDir = \PFAD_ROOT . \PFAD_NEWSBILDER;
        $images    = [];
        if (\is_dir($uploadDir . $id)) {
            $handle = \opendir($uploadDir . $id);
            while (false !== ($Datei = \readdir($handle))) {
                if ($Datei !== '.' && $Datei !== '..') {
                    $images[] = $Datei;
                }
            }

            \closedir($handle);
        }
        \usort($images, 'cmp');

        $shopURL = \Shop::getURL() . '/';
        $count   = \count($images);
        for ($i = 1; $i <= $count; $i++) {
            $text = \str_replace(
                "$#Bild" . $i . "#$",
                '<img alt="" src="' . $shopURL . \PFAD_NEWSBILDER . $id . '/' . $images[$i - 1] . '" />',
                $text
            );
        }
        if (\strpos(\end($images), 'preview') !== false) {
            $text = \str_replace(
                "$#preview#$",
                '<img alt="" src="' . $shopURL . \PFAD_NEWSBILDER . $id . '/' . $images[\count($images) - 1] . '" />',
                $text
            );
        }

        return $text;
    }

    /**
     * update lft/rght values for categories in the nested set model
     *
     * @param int $parent_id
     * @param int $left
     * @param int $level
     * @return int
     */
    private function rebuildCategoryTree(int $parent_id, int $left, int $level = 0): int
    {
        // the right value of this node is the left value + 1
        $right = $left + 1;
        // get all children of this node
        $result = $this->db->selectAll('tnewskategorie', 'kParent', $parent_id, 'kNewsKategorie',
            'nSort, kNewsKategorie');
        foreach ($result as $_res) {
            $right = $this->rebuildCategoryTree($_res->kNewsKategorie, $right, $level + 1);
        }
        // we've got the left value, and now that we've processed the children of this node we also know the right value
        $this->db->update('tnewskategorie', 'kNewsKategorie', $parent_id, (object)[
            'lft'  => $left,
            'rght' => $right,
            'lvl'  => $level,
        ]);

        // return the right value of this node + 1
        return $right + 1;
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
