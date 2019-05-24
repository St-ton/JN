<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Rating;

use JTL\Alert\Alert;
use JTL\Catalog\Product\Artikel;
use JTL\Customer\Kunde;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Session\Frontend;
use JTL\Shop;
use JTLSmarty;
use stdClass;

/**
 * Class RatingController
 * @package JTL\Rating
 */
class RatingController
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var array
     */
    private $config;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * RatingController constructor.
     * @param DbInterface    $db
     * @param JTLSmarty|null $smarty
     */
    public function __construct(DbInterface $db, ?JTLSmarty $smarty = null)
    {
        $this->db     = $db;
        $this->smarty = $smarty;
        $this->config = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_BEWERTUNG]);
    }

    /**
     * @return bool|void
     */
    public function handleRequest()
    {
        $this->checkRedirect();
        $params   = Shop::getParameters();
        $customer = Frontend::getCustomer();
        if (isset($_POST['bfh']) && (int)$_POST['bfh'] === 1) {
            $messageSaveRating = $this->speicherBewertung(
                $params['kArtikel'],
                $customer->getID(),
                Shop::getLanguageID(),
                Request::verifyGPDataString('cTitel'),
                Request::verifyGPDataString('cText'),
                $params['nSterne']
            );
            \header('Location: ' . $messageSaveRating . '#alert-list', true, 303);
            exit;
        }
        if (isset($_POST['bhjn']) && (int)$_POST['bhjn'] === 1) {
            $this->speicherHilfreich(
                $params['kArtikel'],
                $customer->getID(),
                Shop::getLanguageID(),
                Request::verifyGPCDataInt('btgseite'),
                Request::verifyGPCDataInt('btgsterne')
            );
        }
        if (Request::verifyGPCDataInt('bfa') === 1) {
            return $this->ratingPreCheck($customer, $params);
        }
    }

    /**
     * @param Kunde $customer
     * @param array $params
     * @return bool|void
     */
    private function ratingPreCheck(Kunde $customer, array $params): bool
    {
        $ratingAllowed = true;
        if ($customer->getID() <= 0) {
            $helper = Shop::Container()->getLinkService();
            \header(
                'Location: ' . $helper->getStaticRoute('jtl.php') .
                '?a=' . Request::verifyGPCDataInt('a') .
                '&bfa=1&r=' . \R_LOGIN_BEWERTUNG,
                true,
                303
            );
            exit();
        }
        $product = new Artikel();
        $product->fuelleArtikel($params['kArtikel'], Artikel::getDefaultOptions());
        if (!$product->kArtikel) {
            \header('Location: ' . Shop::getURL() . '/', true, 303);
            exit;
        }
        if ($product->Bewertungen === null) {
            $product->holeBewertung(
                Shop::getLanguageID(),
                $this->config['bewertung']['bewertung_anzahlseite'],
                0,
                -1,
                $this->config['bewertung']['bewertung_freischalten'],
                $params['nSortierung']
            );
            $product->holehilfreichsteBewertung(Shop::getLanguageID());
        }
        if ($customer->isLoggedIn()) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_DANGER,
                Shop::Lang()->get('loginFirst', 'product rating'),
                'loginFirst',
                ['showInAlertListTemplate' => false]
            );
            $ratingAllowed = false;
        } elseif ($this->pruefeKundeArtikelGekauft($product->kArtikel, Frontend::getCustomer()) === false) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_DANGER,
                Shop::Lang()->get('productNotBuyed', 'product rating'),
                'productNotBuyed',
                ['showInAlertListTemplate' => false]
            );
            $ratingAllowed = false;
        }

        $this->smarty->assign('Artikel', $product)
            ->assign('ratingAllowed', $ratingAllowed)
            ->assign(
                'oBewertung',
                $this->db->select(
                    'tbewertung',
                    ['kArtikel', 'kKunde'],
                    [$product->kArtikel, $customer->getID()]
                )
            );

        return true;
    }

    /**
     *
     */
    private function checkRedirect(): void
    {
        if (!isset($_POST['bfh']) && !isset($_POST['bhjn']) && Request::verifyGPCDataInt('bfa') !== 1) {
            \header('Location: ' . Shop::getURL() . '/', true, 303);
            exit;
        }
    }

    /**
     * Fügt für einen bestimmten Artikel, in einer bestimmten Sprache eine Bewertung hinzu.
     *
     * @param int    $productID
     * @param int    $customerID
     * @param int    $langID
     * @param string $title
     * @param string $text
     * @param int    $stars
     * @return string
     */
    public function speicherBewertung(int $productID, int $customerID, int $langID, $title, $text, int $stars): string
    {
        $article = new Artikel();
        $article->fuelleArtikel($productID, Artikel::getDefaultOptions());
        $url = !empty($article->cURLFull)
            ? (mb_strpos($article->cURLFull, '?') === false ? $article->cURLFull . '?' : $article->cURLFull . '&')
            : (Shop::getURL() . '/?a=' . $productID . '&');
        if ($stars < 1 || $stars > 5) {
            return $url . 'bewertung_anzeigen=1&cFehler=f05';
        }
        if ($customerID <= 0 || $this->config['bewertung']['bewertung_anzeigen'] !== 'Y') {
            return $url . 'bewertung_anzeigen=1&cFehler=f04';
        }
        $title = Text::htmlentities(Text::filterXSS($title));
        $text  = Text::htmlentities(Text::filterXSS($text));

        if ($productID <= 0 || $langID <= 0 || $title === '' || $text === '') {
            return $url . 'bewertung_anzeigen=1&cFehler=f01';
        }
        if ($this->pruefeKundeArtikelGekauft($productID, Frontend::getCustomer()) === false) {
            return $url . 'bewertung_anzeigen=1&cFehler=f03';
        }
        $reward                  = 0.0;
        $rating                  = new stdClass();
        $rating->kArtikel        = $productID;
        $rating->kKunde          = $customerID;
        $rating->kSprache        = $langID;
        $rating->cName           = $_SESSION['Kunde']->cVorname . ' ' . mb_substr($_SESSION['Kunde']->cNachname, 0, 1);
        $rating->cTitel          = $title;
        $rating->cText           = \strip_tags($text);
        $rating->nHilfreich      = 0;
        $rating->nNichtHilfreich = 0;
        $rating->nSterne         = $stars;
        $rating->nAktiv          = (int)($this->config['bewertung']['bewertung_freischalten'] === 'N');
        $rating->dDatum          = \date('Y-m-d H:i:s');

        \executeHook(\HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNG, ['rating' => &$rating]);

        $ratingID = $this->db->select(
            'tbewertung',
            ['kArtikel', 'kKunde'],
            [$productID, $customerID]
        ) !== null
            ? $this->db->update('tbewertung', ['kArtikel', 'kKunde'], [$productID, $customerID], $rating)
            : $this->db->insert('tbewertung', $rating);
        $unlock   = 1;
        if ($this->config['bewertung']['bewertung_freischalten'] === 'N') {
            $unlock = 0;
            $this->aktualisiereDurchschnitt($productID, $this->config['bewertung']['bewertung_freischalten']);
            $reward = $this->checkeBewertungGuthabenBonus($ratingID);
            Shop::Container()->getCache()->flushTags([\CACHING_GROUP_ARTICLE . '_' . $productID]);
        }
        if ($unlock === 0) {
            return $url . (($reward > 0)
                    ? 'bewertung_anzeigen=1&fB=' . $reward . '&cHinweis=h04'
                    : 'bewertung_anzeigen=1&cHinweis=h01');
        }

        return $url . 'bewertung_anzeigen=1&cHinweis=h05';
    }

    /**
     * @param int   $productID
     * @param Kunde $customer
     * @return bool
     */
    public function pruefeKundeArtikelGekauft(int $productID, Kunde $customer): bool
    {
        if ($this->config['bewertung']['bewertung_artikel_gekauft'] !== 'Y' || !$customer->isLoggedIn()) {
            return true;
        }
        $order = $this->db->queryPrepared(
            'SELECT tbestellung.kBestellung
            FROM tbestellung
            LEFT JOIN tartikel 
                ON tartikel.kVaterArtikel = :aid
            JOIN twarenkorb 
                ON twarenkorb.kWarenkorb = tbestellung.kWarenkorb
            JOIN twarenkorbpos 
                ON twarenkorbpos.kWarenkorb = twarenkorb.kWarenkorb
            WHERE tbestellung.kKunde = :cid
                AND (twarenkorbpos.kArtikel = :aid 
                OR twarenkorbpos.kArtikel = tartikel.kArtikel)',
            ['aid' => $productID, 'cid' => $customer->getID()],
            ReturnType::SINGLE_OBJECT
        );

        return isset($order->kBestellung) && $order->kBestellung > 0;
    }

    /**
     * @param int    $productID
     * @param string $activate
     * @return bool
     */
    public function aktualisiereDurchschnitt(int $productID, string $activate): bool
    {
        $sql       = $activate === 'Y' ? ' AND nAktiv = 1' : '';
        $countData = $this->db->query(
            'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE kArtikel = ' . $productID . $sql,
            ReturnType::SINGLE_OBJECT
        );

        if ((int)$countData->nAnzahl === 1) {
            $sql = '';
        } elseif ((int)$countData->nAnzahl === 0) {
            $this->db->delete('tartikelext', 'kArtikel', $productID);

            return false;
        }

        $avg = $this->db->query(
            'SELECT (SUM(nSterne) / COUNT(*)) AS fDurchschnitt
            FROM tbewertung
            WHERE kArtikel = ' . $productID . $sql,
            ReturnType::SINGLE_OBJECT
        );

        if (isset($avg->fDurchschnitt) && $avg->fDurchschnitt > 0) {
            $this->db->delete('tartikelext', 'kArtikel', $productID);
            $oArtikelExt                          = new stdClass();
            $oArtikelExt->kArtikel                = $productID;
            $oArtikelExt->fDurchschnittsBewertung = (float)$avg->fDurchschnitt;

            $this->db->insert('tartikelext', $oArtikelExt);
        }

        return true;
    }

    /**
     * @param int $ratingID
     * @return float
     */
    public function checkeBewertungGuthabenBonus(int $ratingID): float
    {
        $reward = 0.0;
        if ($this->config['bewertung']['bewertung_guthaben_nutzen'] !== 'Y') {
            return $reward;
        }
        $rating        = $this->db->queryPrepared(
            'SELECT kBewertung, kKunde, cText
            FROM tbewertung
            WHERE kBewertung = :rid',
            ['rid' => $ratingID],
            ReturnType::SINGLE_OBJECT
        );
        $maxBalance    = (float)$this->config['bewertung']['bewertung_max_guthaben'];
        $level2balance = (float)$this->config['bewertung']['bewertung_stufe2_guthaben'];
        $level1balance = (float)$this->config['bewertung']['bewertung_stufe1_guthaben'];
        $customerID    = (int)$rating->kKunde;
        $ratingBonus   = $this->db->queryPrepared(
            'SELECT SUM(fGuthabenBonus) AS fGuthabenProMonat
            FROM tbewertungguthabenbonus
            WHERE kKunde = :cid
                AND kBewertung != :rid
                AND YEAR(dDatum) = :dyear
                AND MONTH(dDatum) = :dmonth',
            [
                'cid'    => $customerID,
                'rid'    => $ratingID,
                'dyear'  => \date('Y'),
                'dmonth' => \date('m')
            ],
            ReturnType::SINGLE_OBJECT
        );
        if ((float)$ratingBonus->fGuthabenProMonat > $maxBalance) {
            return $reward;
        }
        if ((int)$this->config['bewertung']['bewertung_stufe2_anzahlzeichen'] <= mb_strlen($rating->cText)) {
            $reward = ((float)$ratingBonus->fGuthabenProMonat + $level2balance) > $maxBalance
                ? $maxBalance - (float)$ratingBonus->fGuthabenProMonat
                : $level2balance;
        } else {
            $reward = ((float)$ratingBonus->fGuthabenProMonat + $level1balance) > $maxBalance
                ? $maxBalance - (float)$ratingBonus->fGuthabenProMonat
                : $level1balance;
        }
        $this->increaseCustomerBalance($customerID, $reward);
        $ratingBonus                 = new stdClass();
        $ratingBonus->kBewertung     = $ratingID;
        $ratingBonus->kKunde         = $customerID;
        $ratingBonus->fGuthabenBonus = $reward;
        $ratingBonus->dDatum         = 'NOW()';
        if ($this->bonusItemExists($ratingID, $customerID)) {
            $this->updateBonusItem($ratingID, $reward);
        } else {
            $this->db->insert('tbewertungguthabenbonus', $ratingBonus);
        }
        $this->sendRewardMail($ratingBonus);

        return $reward;
    }

    /**
     * @param int   $customerID
     * @param float $reward
     * @return int
     */
    private function increaseCustomerBalance(int $customerID, float $reward): int
    {
        return $this->db->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben + :rew
                WHERE kKunde = :cid',
            ['cid' => $customerID, 'rew' => $reward],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param int $ratingID
     * @param int $customerID
     * @return bool
     */
    private function bonusItemExists(int $ratingID, int $customerID): bool
    {
        return $this->db->select(
                'tbewertungguthabenbonus',
                ['kBewertung', 'kKunde'],
                [$ratingID, $customerID]
            ) !== null;
    }

    /**
     * @param int   $ratingID
     * @param float $reward
     * @return int
     */
    private function updateBonusItem(int $ratingID, float $reward): int
    {
        $this->db->queryPrepared(
            'UPDATE tbewertungguthabenbonus 
                    SET fGuthabenBonus = :reward 
                    WHERE kBewertung = :feedback',
            ['reward' => $reward, 'feedback' => $ratingID],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param stdClass $ratingBonus
     * @return bool
     */
    private function sendRewardMail(stdClass $ratingBonus): bool
    {
        $obj                          = new stdClass();
        $obj->tkunde                  = new Kunde($ratingBonus->kKunde);
        $obj->oBewertungGuthabenBonus = $ratingBonus;
        $mailer                       = Shop::Container()->get(Mailer::class);
        $mail                         = new Mail();

        return $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BEWERTUNG_GUTHABEN, $obj));
    }

    /**
     * Speichert für eine bestimmte Bewertung und bestimmten Kunden ab, ob sie hilfreich oder nicht hilfreich war.
     *
     * @param int $productID
     * @param int $customerID
     * @param int $langID
     * @param int $page
     * @param int $stars
     */
    public function speicherHilfreich(int $productID, int $customerID, int $langID, int $page = 1, int $stars = 0): void
    {
        $helpful = 0;
        // Prüfe ob Kunde eingeloggt
        if ($customerID <= 0
            || $productID <= 0
            || $langID <= 0
            || $this->config['bewertung']['bewertung_anzeigen'] !== 'Y'
            || $this->config['bewertung']['bewertung_hilfreich_anzeigen'] !== 'Y'
        ) {
            return;
        }
        $ratings = $this->db->selectAll(
            'tbewertung',
            ['kArtikel', 'kSprache'],
            [$productID, $langID],
            'kBewertung'
        );
        if (count($ratings) === 0) {
            return;
        }
        $ratingID = 0;
        foreach ($ratings as $rating) {
            $idx = 'hilfreich_' . $rating->kBewertung;
            if (isset($_POST[$idx])) {
                $ratingID = (int)$rating->kBewertung;
                $helpful  = 1;
            }
            $idx = 'nichthilfreich_' . $rating->kBewertung;
            if (isset($_POST[$idx])) {
                $ratingID = (int)$rating->kBewertung;
                $helpful  = 0;
            }
        }
        $redir         = '&btgseite=' . $page . '&btgsterne=' . $stars;
        $helpfulRating = $this->db->select(
            'tbewertunghilfreich',
            ['kBewertung', 'kKunde'],
            [$ratingID, $customerID]
        );
        // Hat der Kunde für diese Bewertung noch keine hilfreich flag gesetzt?
        if ((int)$helpfulRating->kKunde === 0) {
            unset($helpfulRating);
            $rating = $this->db->select('tbewertung', 'kBewertung', $ratingID);
            if ($rating !== null && (int)$rating->kKunde !== (int)$_SESSION['Kunde']->kKunde) {
                $helpfulRating             = new stdClass();
                $helpfulRating->kBewertung = $ratingID;
                $helpfulRating->kKunde     = $customerID;
                $helpfulRating->nBewertung = 0;
                // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese positiv ist
                if ($helpful === 1) {
                    $helpfulRating->nBewertung = 1;
                    $this->db->queryPrepared(
                        'UPDATE tbewertung
                        SET nHilfreich = nHilfreich + 1
                        WHERE kBewertung = :rid',
                        ['rid' => $ratingID],
                        ReturnType::AFFECTED_ROWS
                    );
                } else {
                    // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese negativ ist
                    $helpfulRating->nBewertung = 0;
                    $this->db->queryPrepared(
                        'UPDATE tbewertung
                        SET nNichtHilfreich = nNichtHilfreich + 1
                        WHERE kBewertung = :rid',
                        ['rid' => $ratingID],
                        ReturnType::AFFECTED_ROWS
                    );
                }

                \executeHook(\HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNGHILFREICH, ['rating' => &$helpfulRating]);

                $this->db->insert('tbewertunghilfreich', $helpfulRating);
                \header(
                    'Location: ' . Shop::getURL() . '/?a=' . $productID .
                    '&bewertung_anzeigen=1&cHinweis=h02' . $redir,
                    true,
                    303
                );
                exit;
            }
        } elseif ((int)$helpfulRating->kKunde > 0) {
            // Wenn Hilfreich nicht neu (wechsel) für eine Bewertung eingetragen wird und diese positiv ist
            if ($helpful === 1 && $helpfulRating->nBewertung !== $helpful) {
                $this->db->queryPrepared(
                    'UPDATE tbewertung
                    SET nHilfreich = nHilfreich + 1, nNichtHilfreich = nNichtHilfreich - 1
                    WHERE kBewertung = :rid',
                    ['rid' => $ratingID],
                    ReturnType::AFFECTED_ROWS
                );
            } elseif ($helpful === 0 && $helpfulRating->nBewertung !== $helpful) {
                // Wenn Hilfreich neu für (wechsel) eine Bewertung eingetragen wird und diese negativ ist
                $this->db->queryPrepared(
                    'UPDATE tbewertung
                    SET nHilfreich = nHilfreich-1, nNichtHilfreich = nNichtHilfreich+1
                    WHERE kBewertung = :rid',
                    ['rid' => $ratingID],
                    ReturnType::AFFECTED_ROWS
                );
            }

            $this->db->queryPrepared(
                'UPDATE tbewertunghilfreich
                SET nBewertung = :rnb
                WHERE kBewertung = :rid
                    AND kKunde = :cid',
                [
                    'rid' => $ratingID,
                    'rnb' => $helpful,
                    'cid' => $customerID
                ],
                ReturnType::AFFECTED_ROWS
            );
            \header(
                'Location: ' . Shop::getURL() . '/?a=' . $productID .
                '&bewertung_anzeigen=1&cHinweis=h03' . $redir,
                true,
                303
            );
            exit;
        }
    }

    /**
     * @param int $kBewertung
     * @return stdClass|null
     */
    public function holeBewertung(int $kBewertung): ?stdClass
    {
        return $this->db->select('tbewertung', 'kBewertung', $kBewertung);
    }

    /**
     * @param int $kBewertung
     */
    public function removeReply(int $kBewertung): void
    {
        $update = (object)[
            'cAntwort'      => null,
            'dAntwortDatum' => null
        ];

        $this->db->update('tbewertung', 'kBewertung', $kBewertung, $update);
    }

    /**
     * @param int $ratingID
     * @return bool
     */
    public function BewertungsGuthabenBonusLoeschen(int $ratingID): bool
    {
        $rating = $this->db->select('tbewertung', 'kBewertung', $ratingID);
        if ($rating === null || $rating->kBewertung <= 0) {
            return false;
        }
        $bonus = $this->db->select(
            'tbewertungguthabenbonus',
            'kBewertung',
            (int)$rating->kBewertung,
            'kKunde',
            (int)$rating->kKunde
        );
        if ($bonus !== null && $bonus->kBewertungGuthabenBonus > 0) {
            $customer = $this->db->select('tkunde', 'kKunde', (int)$rating->kKunde);
            if ($customer !== null && $customer->kKunde > 0) {
                $this->db->delete(
                    'tbewertungguthabenbonus',
                    'kBewertungGuthabenBonus',
                    $bonus->kBewertungGuthabenBonus
                );
                $balance        = $customer->fGuthaben - (float)$bonus->fGuthabenBonus;
                $upd            = new stdClass();
                $upd->fGuthaben = (($balance > 0) ? $balance : 0);
                $this->db->update('tkunde', 'kKunde', (int)$rating->kKunde, $upd);

                return true;
            }
        }

        return false;
    }

    /**
     * @param array $post
     * @return bool
     */
    public function editiereBewertung($post): bool
    {
        $id     = Request::verifyGPCDataInt('kBewertung');
        $rating = $this->holeBewertung($id);
        if ($rating === null
            || empty($post['cName'])
            || empty($post['cTitel'])
            || !isset($post['nSterne'])
            || (int)$post['nSterne'] <= 0
        ) {
            return false;
        }
        $upd           = new stdClass();
        $upd->cName    = $post['cName'];
        $upd->cTitel   = $post['cTitel'];
        $upd->cText    = $post['cText'];
        $upd->nSterne  = (int)$post['nSterne'];
        $upd->cAntwort = !empty($post['cAntwort']) ? $post['cAntwort'] : null;

        if ($post['cAntwort'] !== $rating->cAntwort) {
            $upd->dAntwortDatum = !empty($post['cAntwort']) ? \date('Y-m-d') : null;
        }

        $this->db->update('tbewertung', 'kBewertung', $id, $upd);
        $this->aktualisiereDurchschnitt($rating->kArtikel, $this->config['bewertung']['bewertung_freischalten']);

        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_ARTICLE . '_' . $rating->kArtikel]);

        return true;
    }
}
