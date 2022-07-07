<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use DateTime;
use DirectoryIterator;
use Exception;
use Illuminate\Support\Collection;
use JTL\Backend\Permissions;
use JTL\Backend\Revision;
use JTL\ContentAuthor;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\News\Category;
use JTL\News\CategoryInterface;
use JTL\News\CategoryList;
use JTL\News\Comment;
use JTL\News\CommentList;
use JTL\News\Item;
use JTL\News\ItemList;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class NewsController
 * @package JTL\Router\Controller\Backend
 */
class NewsController extends AbstractBackendController
{
    use MultiSizeImage;

    public const UPLOAD_DIR = \PFAD_ROOT . \PFAD_NEWSBILDER;

    public const UPLOAD_DIR_CATEGORY = \PFAD_ROOT . \PFAD_NEWSKATEGORIEBILDER;

    /**
     * @var string
     */
    protected string $step = 'news_uebersicht';

    /**
     * @var int
     */
    private int $continueWith = 0;

    /**
     * @var string
     */
    private string $msg = '';

    /**
     * @var string
     */
    private string $errorMsg = '';

    /**
     * @var bool
     */
    private bool $allEmpty = false;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::CONTENT_NEWS_SYSTEM_VIEW);
        $this->getText->loadAdminLocale('pages/news');

        $uploadDir    = \PFAD_ROOT . \PFAD_NEWSBILDER;
        $uploadDirCat = \PFAD_ROOT . \PFAD_NEWSKATEGORIEBILDER;
        $author       = ContentAuthor::getInstance();
        $newsCategory = new Category($this->db);
        $languages    = LanguageHelper::getAllLanguages(0, true, true);
        $adminID      = (int)$_SESSION['AdminAccount']->kAdminlogin;
        $valid        = Form::validateToken();
        $adminName    = $this->db->select('tadminlogin', 'kAdminlogin', $adminID)->cName;

        if (\mb_strlen(Request::verifyGPDataString('tab')) > 0) {
            $smarty->assign('files', []);

            switch (Request::verifyGPDataString('tab')) {
                case 'inaktiv':
                    if (Request::verifyGPCDataInt('s1') > 1) {
                        $smarty->assign('cBackPage', 'tab=inaktiv&s1=' . Request::verifyGPCDataInt('s1'))
                            ->assign('cSeite', Request::verifyGPCDataInt('s1'));
                    }
                    break;
                case 'aktiv':
                    if (Request::verifyGPCDataInt('s2') > 1) {
                        $smarty->assign('cBackPage', 'tab=aktiv&s2=' . Request::verifyGPCDataInt('s2'))
                            ->assign('cSeite', Request::verifyGPCDataInt('s2'));
                    }
                    break;
                case 'kategorien':
                    if (Request::verifyGPCDataInt('s3') > 1) {
                        $smarty->assign('cBackPage', 'tab=kategorien&s3=' . Request::verifyGPCDataInt('s3'))
                            ->assign('cSeite', Request::verifyGPCDataInt('s3'));
                    }
                    break;
            }
        }
        if ($valid && Request::postInt('einstellungen') === 1) {
            $this->actionConfig($languages);
        } elseif ($valid && Request::verifyGPCDataInt('news') === 1) {
            if ((isset($_POST['erstellen'], $_POST['news_erstellen']) && (int)$_POST['erstellen'] === 1)
                || Request::postInt('news_erstellen') === 1
            ) {
                $newsCategories = $this->getAllNewsCategories();
                if (\count($newsCategories) > 0) {
                    $newsItem = new Item($this->db, $this->cache);
                    $this->setStep('news_erstellen');
                    $smarty->assign('oNewsKategorie_arr', $newsCategories)
                        ->assign('oNews', $newsItem)
                        ->assign('oPossibleAuthors_arr', $author->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
                } else {
                    $this->setErrorMsg(\__('errorNewsCatFirst'));
                    $this->setStep('news_uebersicht');
                }
            } elseif ((isset($_POST['erstellen'], $_POST['news_kategorie_erstellen']) && (int)$_POST['erstellen'] === 1)
                || Request::postInt('news_kategorie_erstellen') === 1
            ) {
                $this->setStep('news_kategorie_erstellen');
            } elseif (Request::verifyGPCDataInt('nkedit') === 1 && Request::verifyGPCDataInt('kNews') > 0) {
                if (isset($_POST['newskommentarsavesubmit'])) {
                    if ($this->saveComment(Request::verifyGPCDataInt('kNewsKommentar'), $_POST)) {
                        $this->setStep('news_vorschau');
                        $this->setMsg(\__('successNewsCommmentEdit'));

                        if (Request::verifyGPCDataInt('nFZ') === 1) {
                            return new RedirectResponse(Shop::getURL() . $this->route);
                        }
                        $tab = Request::verifyGPDataString('tab');
                        if ($tab === 'aktiv') {
                            return $this->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $this->getMsg(), [
                                'news'  => '1',
                                'nd'    => '1',
                                'kNews' => Request::verifyGPCDataInt('kNews'),
                                'token' => $_SESSION['jtl_token'],
                            ]);
                        }
                        return $this->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $this->getMsg());
                    }

                    $this->setStep('news_kommentar_editieren');
                    $this->setErrorMsg(\__('errorCheckInput'));
                    $comment = new Comment($this->db);
                    $comment->load((int)$_POST['kNewsKommentar']);
                    $comment->setName(Text::filterXSS($_POST['cName']));
                    $comment->setText(Text::filterXSS($_POST['cKommentar']));
                    $smarty->assign('oNewsKommentar', $comment);
                } else {
                    $this->setStep('news_kommentar_editieren');
                    $comment = new Comment($this->db);
                    $comment->load(Request::verifyGPCDataInt('kNewsKommentar'));
                    $smarty->assign('oNewsKommentar', $comment);
                    if (Request::verifyGPCDataInt('nFZ') === 1) {
                        $smarty->assign('nFZ', 1);
                    }
                }
            } elseif (Request::verifyGPCDataInt('nkanswer') === 1 && Request::verifyGPCDataInt('kNews') > 0) {
                $this->setStep('news_kommentar_antwort_editieren');
                $comment         = new Comment($this->db);
                $parentCommentID = Request::verifyGPCDataInt('parentCommentID');
                if ($comment->loadByParentCommentID($parentCommentID) === null) {
                    $comment->setID(0);
                    $comment->setNewsID(Request::verifyGPCDataInt('kNews'));
                    $comment->setCustomerID(0);
                    $comment->setIsActive(true);
                    $comment->setName($adminName);
                    $comment->setMail('');
                    $comment->setText('');
                    $comment->setIsAdmin($adminID);
                    $comment->setParentCommentID($parentCommentID);
                }
                $smarty->assign('oNewsKommentar', $comment);
            } elseif (Request::postInt('news_speichern') === 1) {
                if (($response = $this->createOrUpdateNewsItem($_POST, $languages, $author)) !== null) {
                    return $response;
                }
            } elseif (Request::postInt('news_loeschen') === 1) {
                if (GeneralObject::hasCount('kNews', $_POST)) {
                    $this->deleteNewsItems($_POST['kNews'], $author);
                    $this->setMsg(\__('successNewsDelete'));

                    return $this->newsRedirect('aktiv', $this->getMsg());
                }
                $this->setErrorMsg(\__('errorAtLeastOneNews'));
            } elseif (Request::postInt('news_kategorie_speichern') === 1) {
                $newsCategory = $this->createOrUpdateCategory($_POST, $languages);
            } elseif (Request::postInt('news_kategorie_loeschen') === 1) {
                $this->setStep('news_uebersicht');
                if (isset($_POST['kNewsKategorie'])) {
                    $this->deleteCategories($_POST['kNewsKategorie']);
                    $this->setMsg(\__('successNewsCatDelete'));

                    return $this->newsRedirect('kategorien', $this->getMsg());
                }
                $this->setErrorMsg(\__('errorAtLeastOneNewsCat'));
            } elseif (Request::getInt('newskategorie_editieren') === 1) {
                if (\mb_strlen(Request::verifyGPDataString('delpic')) > 0) {
                    if ($this->deleteNewsImage(
                        Request::verifyGPDataString('delpic'),
                        Request::getInt('kNewsKategorie'),
                        $uploadDirCat
                    )) {
                        $this->setMsg(\__('successNewsImageDelete'));
                    } else {
                        $this->setErrorMsg(\__('errorNewsImageDelete'));
                    }
                }
                if (Request::getInt('kNewsKategorie') > 0) {
                    $this->setStep('news_kategorie_erstellen');
                    $newsCategory->load(Request::getInt('kNewsKategorie'), false);
                    if ($newsCategory->getID() > 0) {
                        $smarty->assign('oNewsKategorie', $newsCategory)
                            ->assign('files', $this->getCategoryImages($newsCategory->getID(), $uploadDirCat));
                    } else {
                        $this->setStep('news_uebersicht');
                        $this->setErrorMsg(
                            \sprintf(\__('errorNewsCatNotFound'), Request::getInt('kNewsKategorie'))
                        );
                    }
                }
            } elseif (Request::postInt('newskommentar_freischalten') > 0
                && !isset($_POST['kommentareloeschenSubmit'])
            ) {
                $commentIDs = Request::verifyGPDataIntegerArray('kNewsKommentar');
                if (\count($commentIDs) > 0) {
                    $this->activateComments($commentIDs);
                    $tab = Request::verifyGPDataString('tab');

                    return $this->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $this->getMsg());
                }
                $this->setErrorMsg(\__('errorAtLeastOneNewsComment'));

                return $this->newsRedirect('', $this->getErrorMsg());
            } elseif (isset(
                $_POST['newskommentar_freischalten'],
                $_POST['kNewsKommentar'],
                $_POST['kommentareloeschenSubmit']
            )) {
                if (($response = $this->deleteComments($_POST['kNewsKommentar'])) !== null) {
                    return $response;
                }
            }
            if (Request::getInt('news_editieren') === 1 || $this->getContinueWith() > 0) {
                $newsCategories = $this->getAllNewsCategories();
                $newsItemID     = $this->getContinueWith() > 0
                    ? $this->getContinueWith()
                    : Request::getInt('kNews');
                if (\mb_strlen(Request::verifyGPDataString('delpic')) > 0) {
                    if ($this->deleteNewsImage(Request::verifyGPDataString('delpic'), $newsItemID, $uploadDir)) {
                        $this->setMsg(\__('successNewsImageDelete'));
                    } else {
                        $this->setErrorMsg(\__('errorNewsImageDelete'));
                    }
                }

                if ($newsItemID > 0 && \count($newsCategories) > 0) {
                    $smarty->assign('oNewsKategorie_arr', $this->getAllNewsCategories())
                        ->assign('oAuthor', $author->getAuthor('NEWS', $newsItemID))
                        ->assign('oPossibleAuthors_arr', $author->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
                    $this->setStep('news_editieren');
                    $newsItem = new Item($this->db, $this->cache);
                    $newsItem->load($newsItemID);

                    if ($newsItem->getID() > 0) {
                        if ($this->hasOPCContent($languages, $newsItem->getID())) {
                            $this->setMsg(\__('OPC content available'));
                        }
                        $smarty->assign('oNewsKategorie_arr', $this->getAllNewsCategories())
                            ->assign('files', $this->getNewsImages($newsItem->getID(), $uploadDir))
                            ->assign('oNews', $newsItem);
                    }
                } else {
                    $this->setErrorMsg(\__('errorNewsCatFirst'));
                    $this->setStep('news_uebersicht');
                }
            } elseif ($this->getStep() === 'news_vorschau' || Request::verifyGPCDataInt('nd') === 1) {
                $this->setStep('news_vorschau');
                $newsItemID = Request::verifyGPCDataInt('kNews');
                $newsItem   = new Item($this->db, $this->cache);
                $newsItem->load($newsItemID);

                if ($newsItem->getID() > 0) {
                    if (Request::postInt('kommentare_loeschen') === 1 || isset($_POST['kommentareloeschenSubmit'])) {
                        $response = $this->deleteComments($_POST['kNewsKommentar'] ?? [], $newsItem);
                        if ($response !== null) {
                            return $response;
                        }
                    }
                    $smarty->assign('oNews', $newsItem)
                        ->assign('files', $this->getNewsImages($newsItem->getID(), $uploadDir))
                        ->assign('comments', $newsItem->getComments()->getThreadedItems());
                }
            }
        }
        if ($this->getStep() === 'news_uebersicht') {
            $newsItems = $this->getAllNews();
            $comments  = $this->getNonActivatedComments();
            $prefixes  = [];
            foreach ($languages as $i => $lang) {
                $item                = new stdClass();
                $item->kSprache      = $lang->getId();
                $item->cNameEnglisch = $lang->getNameEN();
                $item->cNameDeutsch  = $lang->getNameDE();
                $item->name          = $lang->getLocalizedName();
                $item->cISOSprache   = $lang->getIso();
                $monthPrefix         = $this->db->select(
                    'tnewsmonatspraefix',
                    'kSprache',
                    $lang->getId()
                );
                $item->cPraefix      = $monthPrefix->cPraefix ?? null;
                $prefixes[$i]        = $item;
            }
            $newsCategories     = $this->getAllNewsCategories();
            $commentPagination  = (new Pagination('kommentar'))
                ->setItemArray($comments)
                ->assemble();
            $itemPagination     = (new Pagination('news'))
                ->setItemArray($newsItems)
                ->assemble();
            $categoryPagination = (new Pagination('kats'))
                ->setItemArray($newsCategories)
                ->assemble();
            $this->getAdminSectionSettings(\CONF_NEWS);
            $smarty->assign('comments', $commentPagination->getPageItems())
                ->assign('oNews_arr', $itemPagination->getPageItems())
                ->assign('oNewsKategorie_arr', $categoryPagination->getPageItems())
                ->assign('oNewsMonatsPraefix_arr', $prefixes)
                ->assign('oPagiKommentar', $commentPagination)
                ->assign('oPagiNews', $itemPagination)
                ->assign('oPagiKats', $categoryPagination);
        } elseif ($this->getStep() === 'news_kategorie_erstellen') {
            $smarty->assign('oNewsKategorie_arr', $this->getAllNewsCategories())
                ->assign('oNewsKategorie', $newsCategory);
        }

        $this->alertService->addNotice($this->getMsg(), 'newsMessage');
        $this->alertService->addError($this->getErrorMsg(), 'newsError');

        return $smarty->assign('customerGroups', CustomerGroup::getGroups())
            ->assign('route', $this->route)
            ->assign('step', $this->getStep())
            ->assign('nMaxFileSize', self::getMaxFileSize(\ini_get('upload_max_filesize')))
            ->getResponse('news.tpl');
    }

    /**
     * @param array $languages
     * @return void
     */
    private function actionConfig(array $languages): void
    {
        $this->saveAdminSectionSettings(\CONF_NEWS, $_POST, [\CACHING_GROUP_OPTION, \CACHING_GROUP_NEWS]);
        if (\count($languages) > 0) {
            $this->db->query('TRUNCATE tnewsmonatspraefix');
            foreach ($languages as $lang) {
                $monthPrefix           = new stdClass();
                $monthPrefix->kSprache = $lang->getId();
                if (!empty($_POST['praefix_' . $lang->getIso()])) {
                    $monthPrefix->cPraefix = \htmlspecialchars(
                        $_POST['praefix_' . $lang->getIso()],
                        \ENT_COMPAT | \ENT_HTML401,
                        \JTL_CHARSET
                    );
                } else {
                    $monthPrefix->cPraefix = $lang->getIso() === 'ger'
                        ? 'Newsuebersicht'
                        : 'Newsoverview';
                }
                $this->db->insert('tnewsmonatspraefix', $monthPrefix);
            }
        }
    }

    /**
     * @return int
     */
    private function flushCache(): int
    {
        return $this->cache->flushTags([\CACHING_GROUP_NEWS]);
    }

    /**
     * @param array         $post
     * @param array         $languages
     * @param ContentAuthor $contentAuthor
     * @return ResponseInterface|null
     * @throws Exception
     */
    public function createOrUpdateNewsItem(
        array $post,
        array $languages,
        ContentAuthor $contentAuthor
    ): ?ResponseInterface {
        $newsItemID      = (int)($post['kNews'] ?? 0);
        $update          = $newsItemID > 0;
        $customerGroups  = $post['kKundengruppe'] ?? null;
        $newsCategoryIDs = $post['kNewsKategorie'] ?? null;
        $active          = (int)$post['nAktiv'];
        $dateValidFrom   = $post['dGueltigVon'];
        $previewImage    = $_FILES['previewImage']['name'] ?? '';
        $authorID        = (int)($post['kAuthor'] ?? 0);
        $validation      = $this->validateNewsItem($customerGroups, $newsCategoryIDs, $post, $languages);
        if (\count($validation) === 0) {
            $newsItem                = new stdClass();
            $newsItem->cKundengruppe = ';' . \implode(';', $customerGroups) . ';';
            $newsItem->nAktiv        = $active;
            $newsItem->dErstellt     = (new DateTime())->format('Y-m-d H:i:s');
            $newsItem->dGueltigVon   = DateTime::createFromFormat('d.m.Y H:i', $dateValidFrom)->format('Y-m-d H:i:00');
            if ($previewImage !== '' && Image::isImageUpload($_FILES['previewImage'])) {
                $newsItem->cPreviewImage = $previewImage;
            }
            if ($update === true) {
                $revision = new Revision($this->db);
                $revision->addRevision('news', $newsItemID, true);
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
            $flags          = \ENT_COMPAT | \ENT_HTML401;
            $this->allEmpty = true;
            foreach ($languages as $language) {
                $iso                  = $language->getCode();
                $langID               = (int)$post['lang_' . $iso];
                $loc                  = new stdClass();
                $loc->kNews           = $newsItemID;
                $loc->languageID      = $langID;
                $loc->languageCode    = $iso;
                $loc->title           = \htmlspecialchars($post['cName_' . $iso], $flags, \JTL_CHARSET);
                $loc->content         = $this->parseContent($post['text_' . $iso], $newsItemID);
                $loc->preview         = $this->parseContent($post['cVorschauText_' . $iso], $newsItemID);
                $loc->metaTitle       = \htmlspecialchars($post['cMetaTitle_' . $iso], $flags, \JTL_CHARSET);
                $loc->metaDescription = \htmlspecialchars($post['cMetaDescription_' . $iso], $flags, \JTL_CHARSET);
                $loc->metaKeywords    = \htmlspecialchars($post['cMetaKeywords_' . $iso], $flags, \JTL_CHARSET);
                if (empty($loc->title)) {
                    // skip language without content
                    continue;
                }

                if (!empty($loc->content) || !empty($loc->preview)) {
                    $this->allEmpty = false;
                }
                $seoData           = new stdClass();
                $seoData->cKey     = 'kNews';
                $seoData->kKey     = $newsItemID;
                $seoData->kSprache = $langID;
                $seoData->cSeo     = Seo::checkSeo($this->getSeo($post, $languages, $iso));
                $this->db->insert('tnewssprache', $loc);
                $this->db->insert('tseo', $seoData);

                if ($active === 0) {
                    continue;
                }
                $date  = DateTime::createFromFormat('Y-m-d H:i:s', $newsItem->dGueltigVon);
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
                $prefix = $this->db->select(
                    'tnewsmonatspraefix',
                    'kSprache',
                    $langID
                )->cPraefix ?? 'Newsuebersicht';
                if (isset($monthOverview->kNewsMonatsUebersicht) && $monthOverview->kNewsMonatsUebersicht > 0) {
                    $this->db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kNewsMonatsUebersicht', (int)$monthOverview->kNewsMonatsUebersicht, $langID]
                    );
                    $seo       = new stdClass();
                    $seo->cSeo = Seo::checkSeo(Seo::getSeo($prefix . '-' . $month . '-' . $year));
                    $seo->cKey = 'kNewsMonatsUebersicht';
                    $seo->kKey = $monthOverview->kNewsMonatsUebersicht;
                } else {
                    $monthOverview           = new stdClass();
                    $monthOverview->kSprache = $langID;
                    $monthOverview->cName    = \JTL\Router\Controller\NewsController::mapDateName((string)$month, $year, $iso);
                    $monthOverview->nMonat   = $month;
                    $monthOverview->nJahr    = $year;

                    $overviewID = $this->db->insert('tnewsmonatsuebersicht', $monthOverview);

                    $this->db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kNewsMonatsUebersicht', $overviewID, $langID]
                    );
                    $seo       = new stdClass();
                    $seo->cSeo = Seo::checkSeo(Seo::getSeo($prefix . '-' . $month . '-' . $year));
                    $seo->cKey = 'kNewsMonatsUebersicht';
                    $seo->kKey = $overviewID;
                }
                $seo->kSprache = $langID;
                $this->db->insert('tseo', $seo);
            }
            $dir = self::UPLOAD_DIR . $newsItemID;
            if (!\is_dir($dir) && !\mkdir(self::UPLOAD_DIR . $newsItemID) && !\is_dir($dir)) {
                throw new Exception(\__('errorDirCreate') . $dir);
            }

            $oldImages = $this->getNewsImages($newsItemID, self::UPLOAD_DIR, false);
            $this->addImages($newsItemID);
            if ($previewImage !== '') {
                $upd = (object)['cPreviewImage' => $this->addPreviewImage($oldImages, $newsItemID)];
                $this->db->update('tnews', 'kNews', $newsItemID, $upd);
            }

            $this->db->delete('tnewskategorienews', 'kNews', $newsItemID);
            foreach ($newsCategoryIDs as $categoryID) {
                $ins                 = new stdClass();
                $ins->kNews          = $newsItemID;
                $ins->kNewsKategorie = (int)$categoryID;
                $this->db->insert('tnewskategorienews', $ins);
            }
            $this->flushCache();
            $this->msg .= \__('successNewsSave');
            if (isset($post['continue']) && $post['continue'] === '1') {
                $this->step         = 'news_editieren';
                $this->continueWith = $newsItemID;
            } else {
                $tab = Request::verifyGPDataString('tab');

                return $this->newsRedirect(empty($tab) ? 'aktiv' : $tab, $this->getMsg());
            }
        } else {
            $newsItem   = new Item($this->db, $this->cache);
            $this->step = 'news_editieren';
            $this->smarty->assign('cPostVar_arr', $post)
                ->assign('cPlausiValue_arr', $validation)
                ->assign('oNewsKategorie_arr', $this->getAllNewsCategories())
                ->assign('oNews', $newsItem);
            $this->errorMsg .= \__('errorFillRequired');

            if (isset($post['kNews']) && \is_numeric($post['kNews'])) {
                $this->continueWith = $newsItemID;
            } else {
                $this->smarty->assign(
                    'oPossibleAuthors_arr',
                    $contentAuthor->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW'])
                );
            }
        }

        return null;
    }

    /**
     * @param int   $id
     * @param array $post
     * @return bool
     */
    public function saveComment(int $id, array $post): bool
    {
        if ($id < 1) {
            return $this->insertComment($post);
        }
        $upd = (object)['cName' => $post['cName'], 'cKommentar' => $post['cKommentar']];
        $this->flushCache();

        return $this->db->update('tnewskommentar', 'kNewsKommentar', $id, $upd) >= 0;
    }

    /**
     * @param array $post
     * @return bool
     */
    public function insertComment(array $post): bool
    {
        $insert                  = new stdClass();
        $insert->kNews           = $post['kNews'];
        $insert->cKommentar      = $post['cKommentar'];
        $insert->kKunde          = $post['kKunde'] ?? 0;
        $insert->nAktiv          = $post['nAktiv'] ?? 1;
        $insert->cName           = $post['cName'] ?? 'Admin';
        $insert->cEmail          = $post['cEmail'] ?? '';
        $insert->isAdmin         = $post['isAdmin'] ?? (int)($_SESSION['AdminAccount']->kAdminlogin ?? 0);
        $insert->parentCommentID = $post['parentCommentID'] ?? 0;
        $insert->dErstellt       = 'NOW()';
        $this->flushCache();

        return $this->db->insert('tnewskommentar', $insert) >= 0;
    }

    /**
     * @param int[] $commentIDs
     */
    public function activateComments(array $commentIDs): void
    {
        foreach ($commentIDs as $id) {
            $this->db->update('tnewskommentar', 'kNewsKommentar', $id, (object)['nAktiv' => 1]);
        }
        $this->setMsg(\__('successNewsCommentUnlock'));
        $this->flushCache();
    }


    /**
     * @param array         $newsItems
     * @param ContentAuthor $author
     */
    public function deleteNewsItems(array $newsItems, ContentAuthor $author): void
    {
        foreach ($newsItems as $newsItemID) {
            $newsItemID = (int)$newsItemID;
            if ($newsItemID <= 0) {
                continue;
            }
            $author->clearAuthor('NEWS', $newsItemID);
            $newsData = $this->db->select('tnews', 'kNews', $newsItemID);
            $this->db->delete('tnews', 'kNews', $newsItemID);
            self::deleteImageDir($newsItemID);
            $this->db->delete('tnewskommentar', 'kNews', $newsItemID);
            $this->db->delete('tseo', ['cKey', 'kKey'], ['kNews', $newsItemID]);
            $this->db->delete('tnewskategorienews', 'kNews', $newsItemID);
            // War das die letzte News fuer einen bestimmten Monat?
            // => Falls ja, tnewsmonatsuebersicht Monat loeschen
            $date    = DateTime::createFromFormat('Y-m-d H:i:s', $newsData->dGueltigVon);
            $month   = (int)$date->format('m');
            $year    = (int)$date->format('Y');
            $newsIDs = $this->db->getObjects(
                'SELECT kNews
                    FROM tnews
                    WHERE MONTH(dGueltigVon) = :mnth
                        AND YEAR(dGueltigVon) = :yr',
                [
                    'mnth' => $month,
                    'yr'   => $year
                ]
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
                            AND tnewsmonatsuebersicht.nJahr = :yr',
                    [
                        'cky'  => 'kNewsMonatsUebersicht',
                        'mnth' => $month,
                        'yr'   => $year
                    ]
                );
            }
        }
        $this->flushCache();
    }

    /**
     * @param int[]|string[] $ids
     * @return bool
     */
    public function deleteCategories(array $ids): bool
    {
        foreach ($ids as $id) {
            foreach ($this->getCategoryAndChildrenByID((int)$id) as $newsSubCat) {
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
        return $this->db->getInts(
            'SELECT node.kNewsKategorie AS id
                FROM tnewskategorie AS node, tnewskategorie AS parent
                WHERE node.lft BETWEEN parent.lft AND parent.rght
                    AND parent.kNewsKategorie = :cid',
            'id',
            ['cid' => $categoryID]
        );
    }

    /**
     * deactivate all news items without a category
     * @return int
     */
    private function deactivateUnassociatedNewsItems(): int
    {
        return $this->db->getAffectedRows(
            'UPDATE tnews 
                SET nAktiv = 0
                WHERE kNews > 0 
                    AND kNews NOT IN (SELECT kNews FROM tnewskategorienews)'
        );
    }

    /**
     * @param array           $post
     * @param LanguageModel[] $languages
     * @param string|null     $iso
     * @return null|string
     */
    private function getSeo(array $post, array $languages, string $iso = null): ?string
    {
        if ($iso !== null) {
            $idx = 'cSeo_' . $iso;
            if (!empty($post[$idx])) {
                return Seo::getSeo($post[$idx], true);
            }
            $idx = 'cName_' . $iso;
            if (!empty($post[$idx])) {
                return Seo::getSeo($post[$idx]);
            }
        }
        foreach ($languages as $language) {
            $idx = 'cSeo_' . $language->getCode();
            if (!empty($post[$idx])) {
                return Seo::getSeo($post[$idx]);
            }
        }
        foreach ($languages as $language) {
            $idx = 'cName_' . $language->getCode();
            if (!empty($post[$idx])) {
                return Seo::getSeo($post[$idx]);
            }
        }

        return null;
    }

    /**
     * @param array           $post
     * @param LanguageModel[] $languages
     * @return CategoryInterface
     * @throws Exception
     */
    public function createOrUpdateCategory(array $post, array $languages): CategoryInterface
    {
        $this->step   = 'news_uebersicht';
        $categoryID   = (int)($post['kNewsKategorie'] ?? 0);
        $update       = $categoryID > 0;
        $sort         = (int)$post['nSort'];
        $active       = (int)$post['nAktiv'];
        $parentID     = (int)$post['kParent'];
        $previewImage = $post['previewImage'] ?? '';
        $oldPreview   = null;
        $flag         = \ENT_COMPAT | \ENT_HTML401;
        $this->db->delete('tseo', ['cKey', 'kKey'], ['kNewsKategorie', $categoryID]);
        $newsCategory                        = new stdClass();
        $newsCategory->kParent               = $parentID;
        $newsCategory->nSort                 = $sort > -1 ? $sort : 0;
        $newsCategory->nAktiv                = $active;
        $newsCategory->dLetzteAktualisierung = (new DateTime())->format('Y-m-d H:i:s');
        $newsCategory->cPreviewImage         = $previewImage;

        if ($update === true) {
            $oldPreview = $this->db->select('tnewskategorie', 'kNewsKategorie', $categoryID)->cPreviewImage ?? null;
            $this->db->update('tnewskategorie', 'kNewsKategorie', $categoryID, $newsCategory);
        } else {
            $categoryID = $this->db->insert('tnewskategorie', $newsCategory);
        }
        $newsCategory->kNewsKategorie = $categoryID;
        foreach ($languages as $language) {
            $iso  = $language->getIso();
            $seo  = $this->getSeo($post, $languages, $iso);
            $name = \htmlspecialchars($post['cName_' . $iso] ?? '', $flag, \JTL_CHARSET);

            $loc                  = new stdClass();
            $loc->kNewsKategorie  = $categoryID;
            $loc->languageID      = $language->getId();
            $loc->languageCode    = $iso;
            $loc->name            = $name;
            $loc->description     = $post['cBeschreibung_' . $iso];
            $loc->metaTitle       = \htmlspecialchars($post['cMetaTitle_' . $iso] ?? '', $flag, \JTL_CHARSET);
            $loc->metaDescription = \htmlspecialchars($post['cMetaDescription_' . $iso] ?? '', $flag, \JTL_CHARSET);

            $seoData           = new stdClass();
            $seoData->cKey     = 'kNewsKategorie';
            $seoData->kKey     = $categoryID;
            $seoData->kSprache = $loc->languageID;
            $seoData->cSeo     = Seo::checkSeo($seo);
            if (empty($seoData->cSeo)) {
                continue;
            }
            $exists = $this->db->getSingleObject(
                'SELECT *
                    FROM tnewskategoriesprache
                    WHERE kNewsKategorie = :nid
                        AND languageID = :lid',
                ['nid' => $categoryID, 'lid' => $language->getId()]
            );
            if ($exists !== null) {
                unset($loc->kNewsKategorie);
                $this->db->update(
                    'tnewskategoriesprache',
                    ['kNewsKategorie', 'languageID'],
                    [$categoryID, $language->getId()],
                    $loc
                );
            } else {
                $this->db->insert('tnewskategoriesprache', $loc);
            }
            $this->db->insert('tseo', $seoData);
        }
        $affected = $this->getCategoryAndChildrenByID($categoryID);
        $upd      = (object)['nAktiv' => $newsCategory->nAktiv];
        foreach ($affected as $id) {
            $this->db->update('tnewskategorie', 'kNewsKategorie', $id, $upd);
        }
        $error = false;
        $dir   = self::UPLOAD_DIR_CATEGORY . $categoryID;
        if (!\is_dir($dir) && !\mkdir($dir) && !\is_dir($dir)) {
            $error = true;
            $this->setErrorMsg(\__('errorDirCreate') . $dir);
        }
        if (isset($_FILES['previewImage']['name']) && Image::isImageUpload($_FILES['previewImage'])) {
            $this->updateNewsCategoryPreview($_FILES['previewImage'], $oldPreview, $categoryID);
        }
        $this->rebuildCategoryTree(0, 1);
        if ($error === false) {
            $this->msg .= \__('successNewsCatSave') . '<br />';
        }
        $newsCategory = new Category($this->db);
        $this->flushCache();

        return $newsCategory->load($categoryID);
    }

    /**
     * @param array       $upload
     * @param string|null $oldPreview
     * @param int         $categoryID
     * @return int
     */
    private function updateNewsCategoryPreview(array $upload, ?string $oldPreview, int $categoryID): int
    {
        if ($oldPreview !== null
            && \str_starts_with($oldPreview, \PFAD_NEWSKATEGORIEBILDER)
            && \file_exists(\PFAD_ROOT . $oldPreview)
        ) {
            $real = \realpath(\PFAD_ROOT . $oldPreview);
            if (\str_starts_with($real, \realpath(self::UPLOAD_DIR_CATEGORY))) {
                \unlink($real);
            }
        }
        $fileName = \basename($upload['name']);
        \move_uploaded_file($upload['tmp_name'], self::UPLOAD_DIR_CATEGORY . $categoryID . '/' . $fileName);
        $upd = (object)['cPreviewImage' => \PFAD_NEWSKATEGORIEBILDER . $categoryID . '/' . $fileName];

        return $this->db->update('tnewskategorie', 'kNewsKategorie', $categoryID, $upd);
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
        $extension = \pathinfo($_FILES['previewImage']['name'])['extension'];
        if ($extension === 'jpe') {
            $extension = 'jpg';
        }
        foreach ($oldImages as $image) {
            if (\str_contains($image->cDatei, '_preview.')) {
                $this->deleteNewsImage($image->cName, $newsItemID, self::UPLOAD_DIR);
            }
        }
        $newName    = Image::getCleanFilename(\explode('.', \basename($_FILES['previewImage']['name']))[0])
            . '_preview.' . $extension;
        $fileIDName = $newsItemID . '/' . $newName;
        $uploadFile = self::UPLOAD_DIR . $fileIDName;
        \move_uploaded_file($_FILES['previewImage']['tmp_name'], $uploadFile);

        return \PFAD_NEWSBILDER . $fileIDName;
    }

    /**
     * @param int $newsItemID
     * @return int
     */
    private function addImages(int $newsItemID): int
    {
        if (empty($_FILES['Bilder']['name']) || \count($_FILES['Bilder']['name']) === 0) {
            return 0;
        }
        $counter    = $this->getLastImageNumber($newsItemID);
        $imageCount = \count($_FILES['Bilder']['name']) + $counter;
        for ($i = $counter; $i < $imageCount; ++$i) {
            if (!empty($_FILES['Bilder']['size'][$i - $counter])) {
                $upload     = [
                    'size'     => $_FILES['Bilder']['size'][$i - $counter],
                    'error'    => $_FILES['Bilder']['error'][$i - $counter],
                    'type'     => $_FILES['Bilder']['type'][$i - $counter],
                    'name'     => $_FILES['Bilder']['name'][$i - $counter],
                    'tmp_name' => $_FILES['Bilder']['tmp_name'][$i - $counter],
                ];
                $info       = \pathinfo($_FILES['Bilder']['name'][$i - $counter]);
                $oldName    = $info['filename'];
                $newName    = Image::getCleanFilename($oldName);
                $uploadFile = self::UPLOAD_DIR . $newsItemID . '/' . $newName . '.' . $info['extension'];
                if (Image::isImageUpload($upload)) {
                    \move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $counter], $uploadFile);
                }
            }
        }

        return $imageCount;
    }

    /**
     * @param array|null      $customerGroups
     * @param array|null      $categories
     * @param array           $post
     * @param LanguageModel[] $languages
     * @return array
     */
    private function validateNewsItem(?array $customerGroups, ?array $categories, array $post, array $languages): array
    {
        $validation = [];
        if (!\is_array($customerGroups) || \count($customerGroups) === 0) {
            $validation['kKundengruppe_arr'] = 1;
        }
        if (!\is_array($categories) || \count($categories) === 0) {
            $validation['kNewsKategorie_arr'] = 1;
        }
        $validation['cBetreff'] = 1;
        foreach ($languages as $lang) {
            if (!empty($post['cName_' . $lang->getIso()])) {
                unset($validation['cBetreff']);
                break;
            }
        }

        return $validation;
    }

    /**
     * @return Collection
     */
    public function getAllNews(): Collection
    {
        $itemList = new ItemList($this->db);
        $itemList->createItems($this->db->getInts(
            'SELECT kNews FROM tnews',
            'kNews'
        ));

        return $itemList->getItems()->sortByDesc(static function (Item $e) {
            return $e->getDateCreated();
        });
    }

    /**
     * @return Collection
     */
    public function getNonActivatedComments(): Collection
    {
        $itemList = new CommentList($this->db);
        $ids      = $this->db->getInts(
            'SELECT tnewskommentar.kNewsKommentar AS id
                FROM tnewskommentar
                JOIN tnews 
                    ON tnews.kNews = tnewskommentar.kNews
                JOIN tnewssprache t 
                    ON tnews.kNews = t.kNews
                WHERE tnewskommentar.nAktiv = 0',
            'id'
        );
        $itemList->createItems($ids, false);

        return $itemList->getItems();
    }

    /**
     * @param bool $showOnlyActive
     * @return Collection
     */
    public function getAllNewsCategories(bool $showOnlyActive = false): Collection
    {
        $itemList = new CategoryList($this->db);
        $ids      = $this->db->getInts(
            'SELECT node.kNewsKategorie AS id
                FROM tnewskategorie AS node 
                INNER JOIN tnewskategorie AS parent
                WHERE node.lvl > 0 
                    AND parent.lvl > 0 ' . ($showOnlyActive ? ' AND node.nAktiv = 1 ' : '') .
            ' GROUP BY node.kNewsKategorie
                ORDER BY node.lft, node.nSort ASC',
            'id'
        );
        $itemList->createItems($ids);

        return $itemList->generateTree();
    }

    /**
     * @param int    $itemID
     * @param string $uploadDirName
     * @param bool   $excludePreview
     * @return array
     */
    public function getNewsImages(int $itemID, string $uploadDirName, bool $excludePreview = true): array
    {
        return $this->getImages(\PFAD_NEWSBILDER, $itemID, $uploadDirName, $excludePreview);
    }

    /**
     * @param int    $itemID
     * @param string $uploadDirName
     * @return array
     */
    public function getCategoryImages(int $itemID, string $uploadDirName): array
    {
        return $this->getImages(\PFAD_NEWSKATEGORIEBILDER, $itemID, $uploadDirName);
    }

    /**
     * @param string $base
     * @param int    $itemID
     * @param string $uploadDirName
     * @param bool   $excludePreview
     * @return array
     */
    private function getImages(string $base, int $itemID, string $uploadDirName, bool $excludePreview = true): array
    {
        $images = [];
        if ($this->sanitizeDir('fake', $itemID, $uploadDirName) === false) {
            return $images;
        }
        $imageBaseURL = Shop::getURL() . '/';
        $iterator     = new DirectoryIterator($uploadDirName . $itemID);
        foreach ($iterator as $fileinfo) {
            $fileName = $fileinfo->getFilename();
            if (($excludePreview && \str_contains($fileName, '_preview.'))
                || !$fileinfo->isFile()
                || $fileinfo->isDot()
            ) {
                continue;
            }
            $image           = new stdClass();
            $image->cName    = $fileinfo->getBasename('.' . $fileinfo->getExtension());
            $image->cURL     = $base . $itemID . '/' . $fileName;
            $image->cURLFull = $imageBaseURL . $base . $itemID . '/' . $fileName;
            $image->cDatei   = $fileName;

            $images[] = $image;
        }
        \usort($images, static function ($a, $b) {
            return \strcmp($a->cName, $b->cName);
        });

        return $images;
    }

    /**
     * @param array     $items
     * @param Item|null $newsItem
     * @return ResponseInterface|null
     */
    public function deleteComments(array $items, Item $newsItem = null): ?ResponseInterface
    {
        if (\count($items) === 0) {
            $this->setErrorMsg(\__('errorAtLeastOneNewsComment'));

            return null;
        }
        foreach ($items as $id) {
            $this->db->delete('tnewskommentar', 'kNewsKommentar', (int)$id);
        }
        $this->flushCache();
        $this->setMsg(\__('successNewsCommentDelete'));
        $tab    = Request::verifyGPDataString('tab');
        $params = [
            'news'  => '1',
            'token' => $_SESSION['jtl_token'],
        ];
        if ($newsItem !== null) {
            $params['kNews'] = $newsItem->getID();
            $params['nd']    = '1';
        }

        return $this->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $this->getMsg(), $params);
    }

    /**
     * @param string $imageName
     * @param int    $id
     * @param string $uploadDir
     * @return bool
     */
    public function deleteNewsImage(string $imageName, int $id, string $uploadDir): bool
    {
        if ($this->sanitizeDir($imageName, $id, $uploadDir) === false) {
            return false;
        }
        $iterator = new DirectoryIterator($uploadDir . $id);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot()
                || !$fileinfo->isFile()
                || $fileinfo->getFilename() !== $imageName . '.' . $fileinfo->getExtension()
            ) {
                continue;
            }
            \unlink($fileinfo->getPathname());
            if ($imageName === 'preview' || \str_contains($imageName, '_preview')) {
                $upd                = new stdClass();
                $upd->cPreviewImage = '';
                if (\str_contains($uploadDir, \PFAD_NEWSKATEGORIEBILDER)) {
                    $this->db->update('tnewskategorie', 'kNewsKategorie', $id, $upd);
                } else {
                    $this->db->update('tnews', 'kNews', $id, $upd);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $imageName
     * @param int    $id
     * @param string $uploadDir
     * @return bool
     */
    private function sanitizeDir(string $imageName, int $id, string $uploadDir): bool
    {
        if ($imageName === '' || $id < 1 || !\is_dir($uploadDir . $id)) {
            return false;
        }
        $real     = \realpath($uploadDir);
        $imgPath1 = \realpath(\PFAD_ROOT . \PFAD_NEWSKATEGORIEBILDER);
        $imgPath2 = \realpath(\PFAD_ROOT . \PFAD_NEWSBILDER);

        return \str_starts_with($real, $imgPath1) || \str_starts_with($real, $imgPath2);
    }


    /**
     * @param string      $tab
     * @param string|null $msg
     * @param array|null  $urlParams
     * @return ResponseInterface
     */
    public function newsRedirect(string $tab = '', ?string $msg = '', ?array $urlParams = null): ResponseInterface
    {
        $tabPageMapping = [
            'inaktiv'    => 's1',
            'aktiv'      => 's2',
            'kategorien' => 's3',
        ];
        if (empty($msg)) {
            $this->alertService->removeAlertByKey('newsMessage');
        } else {
            $this->alertService->addNotice($msg, 'newsMessage', ['saveInSession' => true]);
        }
        if ($this->isAllEmpty()) {
            $this->alertService->addWarning(\__('All content is empty'), 'newsAllEmpty', ['saveInSession' => true]);
        }

        if (!empty($tab)) {
            if (!\is_array($urlParams)) {
                $urlParams = [];
            }
            $urlParams['tab'] = $tab;
            if (isset($tabPageMapping[$tab])
                && !\array_key_exists($tabPageMapping[$tab], $urlParams)
                && Request::verifyGPCDataInt($tabPageMapping[$tab]) > 1
            ) {
                $urlParams[$tabPageMapping[$tab]] = Request::verifyGPCDataInt($tabPageMapping[$tab]);
            }
        }

        return new RedirectResponse($this->baseURL . $this->route
            . (\is_array($urlParams) ? '?' . \http_build_query($urlParams) : ''));
    }

    /**
     * @return string
     */
    public function getImageType(): string
    {
        return Image::TYPE_NEWS;
    }

    /**
     * @param int $newsID
     * @return bool
     */
    public static function deleteImageDir(int $newsID): bool
    {
        if (!\is_dir(self::UPLOAD_DIR . $newsID)) {
            return false;
        }
        $handle = \opendir(self::UPLOAD_DIR . $newsID);
        while (($file = \readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                \unlink(self::UPLOAD_DIR . $newsID . '/' . $file);
            }
        }
        \rmdir(self::UPLOAD_DIR . $newsID);

        return true;
    }

    /**
     * @param string $text
     * @param int    $id
     * @return string
     */
    private function parseContent(string $text, int $id): string
    {
        $uploadDir = \PFAD_ROOT . \PFAD_NEWSBILDER . $id;
        $images    = [];
        if (\is_dir($uploadDir)) {
            $handle = \opendir($uploadDir);
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $images[] = $file;
                }
            }

            \closedir($handle);
        }
        \usort($images, static function ($a, $b) {
            return \strcmp($a, $b);
        });
        $baseURL = Shop::getImageBaseURL();
        foreach ($images as $image) {
            if (\str_contains($image, '_preview.')) {
                $placeholder = '$#preview#$';
            } elseif (\str_starts_with($image, 'Bild')) {
                $placeholder = '$#Bild' . \substr(\explode('.', $image)[0], 4) . '#$';
            } else {
                $info        = \pathinfo($image);
                $placeholder = '$#' . $info['filename'] . '#$';
            }
            $text = \str_replace(
                $placeholder,
                '<img alt="" src="'
                . $baseURL
                . $this->generateImagePath(Image::SIZE_LG, 1, $id . '/' . $image)
                . '" />',
                $text
            );
        }

        return $text;
    }

    /**
     * @param int $kNews
     * @return int
     */
    private function getLastImageNumber(int $kNews): int
    {
        $uploadDir = \PFAD_ROOT . \PFAD_NEWSBILDER;
        $images    = [];
        if (\is_dir($uploadDir . $kNews)) {
            $handle = \opendir($uploadDir . $kNews);
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $images[] = $file;
                }
            }
        }
        $max = 0;
        foreach ($images as $image) {
            $num = \mb_substr($image, 4, (\mb_strlen($image) - \mb_strpos($image, '.')) - 3);
            if ($num > $max) {
                $max = (int)$num;
            }
        }

        return $max;
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
        $right  = $left + 1;
        $result = $this->db->selectAll(
            'tnewskategorie',
            'kParent',
            $parent_id,
            'kNewsKategorie',
            'nSort, kNewsKategorie'
        );
        foreach ($result as $_res) {
            $right = $this->rebuildCategoryTree((int)$_res->kNewsKategorie, $right, $level + 1);
        }
        $this->db->update('tnewskategorie', 'kNewsKategorie', $parent_id, (object)[
            'lft'  => $left,
            'rght' => $right,
            'lvl'  => $level,
        ]);

        return $right + 1;
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
    public function setMsg(string $msg): void
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
    public function setErrorMsg(string $errorMsg): void
    {
        $this->errorMsg = $errorMsg;
    }

    /**
     * @return int
     */
    public function getContinueWith(): int
    {
        return $this->continueWith;
    }

    /**
     * @param int $continueWith
     */
    public function setContinueWith(int $continueWith): void
    {
        $this->continueWith = $continueWith;
    }

    /**
     * @return bool
     */
    public function isAllEmpty(): bool
    {
        return $this->allEmpty;
    }

    /**
     * @param LanguageModel[] $languages
     * @param int             $newsId
     * @return bool
     */
    public function hasOPCContent(array $languages, int $newsId): bool
    {
        $pageService = Shop::Container()->getOPCPageService();
        foreach ($languages as $language) {
            $pageID = $pageService->createGenericPageId('news', $newsId, $language->getId());
            if ($pageService->getDraftCount($pageID) > 0) {
                return true;
            }
        }

        return false;
    }
}
