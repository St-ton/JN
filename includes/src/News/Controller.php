<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


use DB\DbInterface;
use DB\ReturnType;
use Session\Session;
use function Functional\every;

/**
 * Class Controller
 * @package News
 */
class Controller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var \JTLSmarty
     */
    private $smarty;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $errorMsg = '';

    /**
     * @var string
     */
    private $noticeMsg = '';

    /**
     * @var int
     */
    private $currentNewsType;

    /**
     * Controller constructor.
     * @param DbInterface $db
     * @param array       $config
     * @param \JTLSmarty  $smarty
     */
    public function __construct(DbInterface $db, array $config, \JTLSmarty $smarty)
    {
        $this->config = $config;
        $this->db     = $db;
        $this->smarty = $smarty;
    }

    /**
     * @param array $params
     * @return int
     */
    public function getPageType(array $params): int
    {
        if (!isset($_SESSION['NewsNaviFilter'])) {
            $_SESSION['NewsNaviFilter'] = new \stdClass();
        }
        if (\RequestHelper::verifyGPCDataInt('nSort') > 0) {
            $_SESSION['NewsNaviFilter']->nSort = \RequestHelper::verifyGPCDataInt('nSort');
        } elseif (\RequestHelper::verifyGPCDataInt('nSort') === -1) {
            $_SESSION['NewsNaviFilter']->nSort = -1;
        } elseif (!isset($_SESSION['NewsNaviFilter']->nSort)) {
            $_SESSION['NewsNaviFilter']->nSort = 1;
        }
        if ((int)$params['cDatum'] === -1) {
            $_SESSION['NewsNaviFilter']->cDatum = -1;
        } elseif (\strlen($params['cDatum']) > 0) {
            $_date                              = \explode('-', $params['cDatum']);
            $_SESSION['NewsNaviFilter']->cDatum = (\count($_date) > 1)
                ? \StringHandler::filterXSS($params['cDatum'])
                : -1;
        }
        if ($params['nNewsKat'] > 0) {
            $_SESSION['NewsNaviFilter']->nNewsKat = $params['nNewsKat'];
        } elseif ($params['nNewsKat'] === -1) {
            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
        }
        if ($this->config['news']['news_benutzen'] !== 'Y') {
            return ViewType::NEWS_DISABLED;
        }
        $this->currentNewsType = ViewType::NEWS_OVERVIEW;
        if ($params['kNews'] > 0) {
            $this->currentNewsType = ViewType::NEWS_DETAIL;
        } elseif ($params['kNewsKategorie'] > 0) {
            $this->currentNewsType = ViewType::NEWS_CATEGORY;
        } elseif ($params['kNewsMonatsUebersicht'] > 0) {
            $this->currentNewsType = ViewType::NEWS_MONTH_OVERVIEW;
        }
        $this->smarty->assign('oDatum_arr', $this->getNewsDates($this->getFilterSQL(true)))
                     ->assign('nPlausiValue_arr', [
                         'cKommentar' => 0,
                         'nAnzahl'    => 0,
                         'cEmail'     => 0,
                         'cName'      => 0,
                         'captcha'    => 0
                     ]);

        return $this->currentNewsType;
    }

    /**
     * @param Item        $newsItem
     * @param \Pagination $pagination
     */
    public function displayItem(Item $newsItem, \Pagination $pagination)
    {
        $newsCategories = $this->getNewsCategories($newsItem->getID());
        foreach ($newsCategories as $category) {
            $category->cURL     = \UrlHelper::buildURL($category, \URLART_NEWSKATEGORIE);
            $category->cURLFull = \UrlHelper::buildURL($category, \URLART_NEWSKATEGORIE, true);
        }

        if ((int)$this->config['news']['news_kommentare_anzahlproseite'] > 0) {
            $nCountPerPagePref   = (int)$this->config['news']['news_kommentare_anzahlproseite'];
            $itemsPerPageOptions = [$nCountPerPagePref, $nCountPerPagePref * 2, $nCountPerPagePref * 5];
        } else {
            $itemsPerPageOptions = [10, 20, 50];
        }
        $oPagiComments = $pagination
            ->setItemsPerPageOptions($itemsPerPageOptions)
            ->setItemCount($newsItem->getComments()->getItems()->count())
            ->assemble();

        $comments = $newsItem->getComments()->getItems();
        if ($pagination->getItemsPerPage() > 0) {
            $comments = $comments->forPage(
                $oPagiComments->getPage() + 1,
                $oPagiComments->getItemsPerPage()
            );
        }

        $this->smarty->assign('oNewsKommentar_arr', $comments)
                     ->assign('oPagiComments', $oPagiComments)
                     ->assign('R_LOGIN_NEWSCOMMENT', \R_LOGIN_NEWSCOMMENT)
                     ->assign('oNewsKategorie_arr', $newsCategories)
                     ->assign('oNewsArchiv', $newsItem)
                     ->assign('meta_title', $newsItem->getMetaTitle())
                     ->assign('meta_description', $newsItem->getMetaDescription())
                     ->assign('meta_keywords', $newsItem->getMetaKeyword());
    }

    /**
     * @param \Pagination $pagination
     * @param int         $categoryID
     * @param int         $monthOverviewID
     * @return Category
     */
    public function displayOverview(\Pagination $pagination, int $categoryID = 0, int $monthOverviewID = 0): Category
    {
        $category = new Category($this->db);
        if ($categoryID > 0) {
            $category->load($categoryID);
        } elseif ($monthOverviewID > 0) {
            $category->getMonthOverview($monthOverviewID);
        } else {
            $category->getOverview($this->getFilterSQL());
        }

        $newsCountShow = ($conf = (int)$this->config['news']['news_anzahl_uebersicht']) > 0
            ? $conf
            : 10;
        $pagination
            ->setItemsPerPageOptions([$newsCountShow, $newsCountShow * 2, $newsCountShow * 5])
            ->setDefaultItemsPerPage(0)
            ->setItemCount($category->getItems()->count())
            ->assemble();

        $items = $category->filterAndSortItems();
        if ($pagination->getItemsPerPage() > 0) {
            $items = $items->forPage(
                $pagination->getPage() + 1,
                $pagination->getItemsPerPage()
            );
        }
        $cMetaTitle       = $category->getMetaTitle();
        $cMetaDescription = $category->getMetaDescription();
        $cMetaKeywords    = $category->getMetaKeyword();

        $cMetaTitle       = \strlen($cMetaDescription) < 1
            ? \Shop::Lang()->get('news', 'news') . ' ' . \Shop::Lang()->get('from',
                'global') . ' ' . $this->config['global']['global_shopname']
            : $cMetaTitle;
        $cMetaDescription = \strlen($cMetaDescription) < 1
            ? \Shop::Lang()->get('newsMetaDesc', 'news')
            : $cMetaDescription;
        $cMetaKeywords    = \strlen($cMetaKeywords) < 1
            ? $category->buildMetaKeywords()
            : $cMetaKeywords;
        $this->smarty->assign('oNewsUebersicht_arr', $items)
                     ->assign('oNewsKategorie_arr',
                         \News::getAllNewsCategories(\Shop::getLanguageID(), false, false, true))
                     ->assign('nSort', $_SESSION['NewsNaviFilter']->nSort)
                     ->assign('cDatum', $_SESSION['NewsNaviFilter']->cDatum)
                     ->assign('oNewsCat', \News::getNewsCategory($_SESSION['NewsNaviFilter']->nNewsKat))
                     ->assign('oPagination', $pagination)
                     ->assign('meta_title', $cMetaTitle)
                     ->assign('meta_description', $cMetaDescription)
                     ->assign('meta_keywords', $cMetaKeywords);

        if ($items->count() === 0) {
            $this->smarty->assign('noarchiv', 1);
            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
            $_SESSION['NewsNaviFilter']->cDatum   = -1;
        }

        \executeHook(\HOOK_NEWS_PAGE_NEWSUEBERSICHT);

        return $category;
    }

    /**
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function addComment(int $id, array $data): bool
    {
        if ($this->config['news']['news_kommentare_nutzen'] !== 'Y') {
            return false;
        }
        $checks    = self::checkComment($data, $id, $this->config);
        $checkedOK = every($checks, function ($e) {
            return $e === 0;
        });

        \executeHook(\HOOK_NEWS_PAGE_NEWSKOMMENTAR_PLAUSI);

        if ($this->config['news']['news_kommentare_eingeloggt'] === 'Y' && Session::Customer()->getID() > 0) {
            if ($checkedOK) {
                $comment             = new \stdClass();
                $comment->kNews      = (int)$_POST['kNews'];
                $comment->kKunde     = (int)$_SESSION['Kunde']->kKunde;
                $comment->nAktiv     = $this->config['news']['news_kommentare_freischalten'] === 'Y'
                    ? 0
                    : 1;
                $comment->cName      = $_SESSION['Kunde']->cVorname . ' ' . $_SESSION['Kunde']->cNachname[0] . '.';
                $comment->cEmail     = $_SESSION['Kunde']->cMail;
                $comment->cKommentar = \StringHandler::htmlentities(
                    \StringHandler::filterXSS($_POST['cKommentar'])
                );
                $comment->dErstellt  = 'now()';

                \executeHook(\HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, ['comment' => &$comment]);

                $this->db->insert('tnewskommentar', $comment);

                if ($this->config['news']['news_kommentare_freischalten'] === 'Y') {
                    $this->noticeMsg .= \Shop::Lang()->get('newscommentAddactivate', 'messages') . '<br>';
                } else {
                    $this->noticeMsg .= \Shop::Lang()->get('newscommentAdd', 'messages') . '<br>';
                }
            } else {
                $this->errorMsg .= self::getCommentErrors($checks);
                $this->smarty->assign('nPlausiValue_arr', $checks)
                             ->assign('cPostVar_arr', \StringHandler::filterXSS($_POST));
            }
        } elseif ($this->config['news']['news_kommentare_eingeloggt'] === 'N') {
            if ($checkedOK) {
                if (Session::Customer()->getID() > 0) {
                    $cName  = Session::Customer()->cVorname . ' ' . Session::Customer()->cNachname[0] . '.';
                    $cEmail = Session::Customer()->cMail;
                } else {
                    $cName  = \StringHandler::filterXSS($data['cName'] ?? '');
                    $cEmail = \StringHandler::filterXSS($data['cEmail'] ?? '');
                }
                $comment         = new \stdClass();
                $comment->kNews  = (int)$_POST['kNews'];
                $comment->kKunde = $_SESSION['Kunde']->kKunde ?? 0;
                $comment->nAktiv = $this->config['news']['news_kommentare_freischalten'] === 'Y'
                    ? 0
                    : 1;

                $comment->cName      = $cName;
                $comment->cEmail     = $cEmail;
                $comment->cKommentar = \StringHandler::htmlentities(
                    \StringHandler::filterXSS($_POST['cKommentar'])
                );
                $comment->dErstellt  = 'now()';

                \executeHook(\HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, ['comment' => &$comment]);

                $this->db->insert('tnewskommentar', $comment);

                if ($this->config['news']['news_kommentare_freischalten'] === 'Y') {
                    $this->noticeMsg .= \Shop::Lang()->get('newscommentAddactivate', 'messages') . '<br />';
                } else {
                    $this->noticeMsg .= \Shop::Lang()->get('newscommentAdd', 'messages') . '<br />';
                }
            } else {
                $this->errorMsg .= self::getCommentErrors($checks);
                $this->smarty->assign('nPlausiValue_arr', $checks)
                             ->assign('cPostVar_arr', \StringHandler::filterXSS($_POST));
            }
        }

        return true;
    }

    /**
     * @param array $post
     * @param int   $newsID
     * @param array $config
     * @return array
     */
    public static function checkComment(array $post, int $newsID, array $config): array
    {
        $checks = [
            'cKommentar' => 0,
            'nAnzahl'    => 0,
            'cEmail'     => 0,
            'cName'      => 0,
            'captcha'    => 0
        ];
        if (empty($post['cKommentar'])) {
            $checks['cKommentar'] = 1;
        } elseif (\strlen($post['cKommentar']) > 1000) {
            $checks['cKommentar'] = 2;
        }
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0 && $newsID > 0) {
            $oNewsKommentar = \Shop::Container()->getDB()->queryPrepared(
                'SELECT COUNT(*) AS nAnzahl
                    FROM tnewskommentar
                    WHERE kNews = :nid
                        AND kKunde = :cid',
                ['nid' => $newsID, 'cid' => Session::Customer()->getID()],
                ReturnType::SINGLE_OBJECT
            );

            if ((int)$oNewsKommentar->nAnzahl > (int)$config['news']['news_kommentare_anzahlprobesucher']
                && (int)$config['news']['news_kommentare_anzahlprobesucher'] !== 0
            ) {
                $checks['nAnzahl'] = 1;
            }
            $post['cEmail'] = $_SESSION['Kunde']->cMail;
        } else {
            // Kunde ist nicht eingeloggt - Name prüfen
            if (empty($post['cName'])) {
                $checks['cName'] = 1;
            }
            if (empty($post['cEmail']) || \StringHandler::filterEmailAddress($post['cEmail']) === false) {
                $checks['cEmail'] = 1;
            }
            if ($config['news']['news_sicherheitscode'] !== 'N' && !\FormHelper::validateCaptcha($post)) {
                $checks['captcha'] = 2;
            }
        }
        if ((!isset($checks['cName']) || !$checks['cName']) && \SimpleMail::checkBlacklist($post['cEmail'])) {
            $checks['cEmail'] = 2;
        }

        return $checks;
    }

    /**
     * @param array $checks
     * @return string
     */
    public static function getCommentErrors(array $checks): string
    {
        $msg = '';
        if ($checks['cKommentar'] > 0) {
            // Kommentarfeld ist leer
            if ($checks['cKommentar'] === 1) {
                $msg .= \Shop::Lang()->get('newscommentMissingtext', 'errorMessages') . '<br />';
            } elseif ($checks['cKommentar'] === 2) {
                // Kommentar ist länger als 1000 Zeichen
                $msg .= \Shop::Lang()->get('newscommentLongtext', 'errorMessages') . '<br />';
            }
        }
        // Kunde hat bereits einen Newskommentar zu der aktuellen News geschrieben
        if ($checks['nAnzahl'] === 1) {
            $msg .= \Shop::Lang()->get('newscommentAlreadywritten', 'errorMessages') . '<br />';
        }
        // Kunde ist nicht eingeloggt und das Feld Name oder Email ist leer
        if ($checks['cName'] === 1 || $checks['cEmail'] === 1) {
            $msg .= \Shop::Lang()->get('newscommentMissingnameemail', 'errorMessages') . '<br />';
        }
        // Emailadresse ist auf der Blacklist
        if ($checks['cEmail'] === 2) {
            $msg .= \Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />';
        }

        return $msg;
    }


    /**
     * @param bool $bActiveOnly
     * @return \stdClass
     */
    private function getFilterSQL(bool $bActiveOnly = false): \stdClass
    {
        $oSQL              = new \stdClass();
        $oSQL->cSortSQL    = '';
        $oSQL->cDatumSQL   = '';
        $oSQL->cNewsKatSQL = '';
        // Sortierung Filter
        if ($_SESSION['NewsNaviFilter']->nSort > 0) {
            switch ($_SESSION['NewsNaviFilter']->nSort) {
                case 1: // Datum absteigend
                    $oSQL->cSortSQL = ' ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC';
                    break;
                case 2: // Datum aufsteigend
                    $oSQL->cSortSQL = ' ORDER BY tnews.dGueltigVon';
                    break;
                case 3: // Name a ... z
                    $oSQL->cSortSQL = ' ORDER BY tnews.cBetreff';
                    break;
                case 4: // Name z ... a
                    $oSQL->cSortSQL = ' ORDER BY tnews.cBetreff DESC';
                    break;
                case 5: // Anzahl Kommentare absteigend
                    $oSQL->cSortSQL = ' ORDER BY nNewsKommentarAnzahl DESC';
                    break;
                case 6: // Anzahl Kommentare aufsteigend
                    $oSQL->cSortSQL = ' ORDER BY nNewsKommentarAnzahl';
                    break;
            }
        } elseif ($_SESSION['NewsNaviFilter']->nSort === -1) {
            // Standard
            $oSQL->cSortSQL = ' ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC';
        }
        // Datum Filter
        $oSQL->cDatumSQL = '';
        if ($_SESSION['NewsNaviFilter']->cDatum !== -1 && \strlen($_SESSION['NewsNaviFilter']->cDatum) > 0) {
            $_date = \explode('-', $_SESSION['NewsNaviFilter']->cDatum);
            if (\count($_date) > 1) {
                list($nMonat, $nJahr) = $_date;
                $oSQL->cDatumSQL = " AND MONTH(tnews.dGueltigVon) = '" . (int)$nMonat . "' 
                                      AND YEAR(tnews.dGueltigVon) = '" . (int)$nJahr . "'";
            } else { //invalid date given/xss -> reset to -1
                $_SESSION['NewsNaviFilter']->cDatum = -1;
            }
        }
        // NewsKat Filter
        $oSQL->cNewsKatSQL = ' JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews';
        if ($_SESSION['NewsNaviFilter']->nNewsKat > 0) {
            $oSQL->cNewsKatSQL = ' JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews
                               AND tnewskategorienews.kNewsKategorie = ' . (int)$_SESSION['NewsNaviFilter']->nNewsKat;
        }

        if ($bActiveOnly) {
            $oSQL->cNewsKatSQL .= ' JOIN tnewskategorie 
                                    ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                                    AND tnewskategorie.nAktiv = 1';
        }

        return $oSQL;
    }

    /**
     * @param object $oSQL
     * @return \stdClass[]
     */
    private function getNewsDates($oSQL): array
    {
        $dateData = $this->db->query(
            "SELECT MONTH(tnews.dGueltigVon) AS nMonat, YEAR(tnews.dGueltigVon) AS nJahr
                FROM tnews " . $oSQL->cNewsKatSQL . "
                WHERE tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= now()
                    AND (tnews.cKundengruppe LIKE '%;-1;%' 
                        OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
            . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                    AND tnews.kSprache = " . \Shop::getLanguageID() . "
                GROUP BY nJahr, nMonat
                ORDER BY dGueltigVon DESC",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $dates    = [];
        foreach ($dateData as $date) {
            $oTMP        = new \stdClass();
            $oTMP->cWert = $date->nMonat . '-' . $date->nJahr;
            $oTMP->cName = $this->mapDateName((string)$date->nMonat, (int)$date->nJahr, \Shop::getLanguageCode());
            $dates[]     = $oTMP;
        }

        return $dates;
    }

    /**
     * @param string $month
     * @param string $year
     * @param string $langCode
     * @return string
     */
    private function mapDateName($month, $year, $langCode): string
    {
        // @todo: i18n!
//        $monthNum  = 3;
//        $dateObj   = DateTime::createFromFormat('!m', $monthNum);
//        $monthName = $dateObj->format('F'); // March
        $name = '';
        if ($langCode === 'ger') {
            switch ($month) {
                case '01':
                    return \Shop::Lang()->get('january', 'news') . ',' . $year;
                case '02':
                    return \Shop::Lang()->get('february', 'news') . ' ' . $year;
                case '03':
                    return \Shop::Lang()->get('march', 'news') . ' ' . $year;
                case '04':
                    return \Shop::Lang()->get('april', 'news') . ' ' . $year;
                case '05':
                    return \Shop::Lang()->get('may', 'news') . ' ' . $year;
                case '06':
                    return \Shop::Lang()->get('june', 'news') . ' ' . $year;
                case '07':
                    return \Shop::Lang()->get('july', 'news') . ' ' . $year;
                case '08':
                    return \Shop::Lang()->get('august', 'news') . ' ' . $year;
                case '09':
                    return \Shop::Lang()->get('september', 'news') . ' ' . $year;
                case '10':
                    return \Shop::Lang()->get('october', 'news') . ' ' . $year;
                case '11':
                    return \Shop::Lang()->get('november', 'news') . ' ' . $year;
                case '12':
                    return \Shop::Lang()->get('december', 'news') . ' ' . $year;
            }
        } else {
            $name .= \date('F', \mktime(0, 0, 0, (int)$month, 1, $year)) . ', ' . $year;
        }

        return $name;
    }

    /**
     * @param int $newsItemID
     * @return array
     */
    public function getNewsCategories(int $newsItemID): array
    {
        $newsCategories = \Functional\map(
            \Functional\pluck($this->db->selectAll(
                'tnewskategorienews',
                'kNews',
                $newsItemID,
                'kNewsKategorie'
            ), 'kNewsKategorie'),
            function ($e) {
                return (int)$e;
            }
        );


        return \count($newsCategories) > 0
            ? $this->db->query(
                'SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
                tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
                tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung,
                tnewskategorie.cPreviewImage, tseo.cSeo,
                DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, \'%d.%m.%Y %H:%i\') AS dLetzteAktualisierung_de
                    FROM tnewskategorie
                    LEFT JOIN tnewskategorienews 
                        ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    LEFT JOIN tseo 
                        ON tseo.cKey = \'kNewsKategorie\'
                        AND tseo.kKey = tnewskategorie.kNewsKategorie
                        AND tseo.kSprache = ' . \Shop::getLanguageID() . '
                    WHERE tnewskategorie.kSprache = ' . \Shop::getLanguageID() . '
                        AND tnewskategorienews.kNewsKategorie IN (' . \implode(',', $newsCategories) . ')
                        AND tnewskategorie.nAktiv = 1
                    GROUP BY tnewskategorie.kNewsKategorie
                    ORDER BY tnewskategorie.nSort DESC',
                ReturnType::ARRAY_OF_OBJECTS
            )
            : [];
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

    /**
     * @return string
     */
    public function getNoticeMsg(): string
    {
        return $this->noticeMsg;
    }

    /**
     * @param string $noticeMsg
     */
    public function setNoticeMsg(string $noticeMsg)
    {
        $this->noticeMsg = $noticeMsg;
    }
}
