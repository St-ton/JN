<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Rating;

use Exception;
use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
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
use function Functional\map;

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
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * RatingController constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     * @param JTLSmarty|null    $smarty
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, ?JTLSmarty $smarty = null)
    {
        $this->db     = $db;
        $this->smarty = $smarty;
        $this->config = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_BEWERTUNG]);
        $this->cache  = $cache;
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
            $messageSaveRating = $this->save(
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
            $this->updateWasHelpful(
                $params['kArtikel'],
                $customer->getID(),
                Request::verifyGPCDataInt('btgseite'),
                Request::verifyGPCDataInt('btgsterne')
            );
        }
        if (Request::verifyGPCDataInt('bfa') === 1) {
            return $this->ratingPreCheck($customer, $params);
        }
    }

    /**
     * @param int $id
     * @return RatingModel|null
     */
    public function holeBewertung(int $id): ?RatingModel
    {
        try {
            return new RatingModel(['id' => $id], $this->db);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function edit($data): bool
    {
        $id = Request::verifyGPCDataInt('kBewertung');
        try {
            $rating = new RatingModel(['id' => $id], $this->db);
        } catch (Exception $e) {
            return false;
        }
        if ($rating->id === null
            || empty($data['cName'])
            || empty($data['cTitel'])
            || !isset($data['nSterne'])
            || (int)$data['nSterne'] <= 0
        ) {
            return false;
        }
        $rating->name    = $data['cName'];
        $rating->title   = $data['cTitel'];
        $rating->content = $data['cText'];
        $rating->stars   = (int)$data['nSterne'];
        $rating->answer  = !empty($data['cAntwort']) ? $data['cAntwort'] : null;

        if ($data['cAntwort'] !== $rating->answer) {
            $rating->answerDate = !empty($data['cAntwort']) ? \date('Y-m-d') : null;
        }
        $rating->save();
        $this->updateAverage($rating->productID, $this->config['bewertung']['bewertung_freischalten']);

        $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $rating->productID]);

        return true;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function delete(array $ids): int
    {
        $cacheTags = [];
        $affected  = 0;
        foreach (\array_map('\intval', $ids) as $id) {
            try {
                $model = new RatingModel(['id' => $id], $this->db);
            } catch (Exception $e) {
                continue;
            }
            $this->updateAverage($model->productID, $this->config['bewertung']['bewertung_freischalten']);
            $this->deleteRatingReward($model);
            $model->delete();
            $cacheTags[] = $model->productID;
            ++$affected;
        }
        $this->cache->flushTags(map($cacheTags, function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));

        return $affected;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function activate(array $ids): int
    {
        $cacheTags = [];
        $affected  = 0;
        foreach (\array_map('\intval', $ids) as $i => $id) {
            try {
                $model = new RatingModel(['id' => $id], $this->db);
            } catch (Exception $e) {
                continue;
            }
            $model->active = 1;
            $model->save(['active']);
            $this->updateAverage($model->productID, $this->config['bewertung']['bewertung_freischalten']);
            $this->addReward($model);
            $cacheTags[] = $model->productID;
            ++$affected;
        }
        $this->cache->flushTags(map($cacheTags, function ($e) {
            return \CACHING_GROUP_ARTICLE . '_' . $e;
        }));

        return $affected;
    }

    /**
     * @param int $id
     */
    public function removeReply(int $id): void
    {
        try {
            $model = new RatingModel(['id' => $id], $this->db);
        } catch (Exception $e) {
            return;
        }
        $model->answer     = null;
        $model->answerDate = null;
        $model->save(['answer', 'answerDate']);
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
     * @param int $productID
     * @return string
     */
    private function getProductURL(int $productID): string
    {
        $product = new Artikel();
        $product->fuelleArtikel($productID, Artikel::getDefaultOptions());
        if (!empty($product->cURLFull)) {
            return \mb_strpos($product->cURLFull, '?') === false
                ? $product->cURLFull . '?'
                : $product->cURLFull . '&';
        }

        return Shop::getURL() . '/?a=' . $productID . '&';
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
    private function save(int $productID, int $customerID, int $langID, $title, $text, int $stars): string
    {
        $url = $this->getProductURL($productID);
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
        if ($this->checkProductWasPurchased($productID, Frontend::getCustomer()) === false) {
            return $url . 'bewertung_anzeigen=1&cFehler=f03';
        }
        $rating = RatingModel::loadByAttributes(['productID' => $productID, 'customerID' => $customerID], $this->db);
        /** @var RatingModel $rating */
        $rating->productID  = $productID;
        $rating->customerID = $customerID;
        $rating->languageID = $langID;
        $rating->name       = $_SESSION['Kunde']->cVorname . ' ' . mb_substr($_SESSION['Kunde']->cNachname, 0, 1);
        $rating->title      = $title;
        $rating->content    = \strip_tags($text);
        $rating->helpful    = 0;
        $rating->notHelpful = 0;
        $rating->stars      = $stars;
        $rating->active     = (int)($this->config['bewertung']['bewertung_freischalten'] === 'N');
        $rating->date       = \date('Y-m-d H:i:s');

        \executeHook(\HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNG, ['rating' => &$rating]);

        $rating->save();
        if ($this->config['bewertung']['bewertung_freischalten'] === 'N') {
            $this->updateAverage($productID, $this->config['bewertung']['bewertung_freischalten']);
            $reward = $this->addReward($rating);
            $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $productID]);

            return $url . (($reward > 0)
                    ? 'bewertung_anzeigen=1&fB=' . $reward . '&cHinweis=h04'
                    : 'bewertung_anzeigen=1&cHinweis=h01');
        }

        return $url . 'bewertung_anzeigen=1&cHinweis=h05';
    }

    /**
     * @param Kunde $customer
     * @param array $params
     * @return bool|void
     */
    private function ratingPreCheck(Kunde $customer, array $params): bool
    {
        $ratingAllowed = true;
        if (!$customer->isLoggedIn()) {
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
        if ($this->checkProductWasPurchased($product->kArtikel, Frontend::getCustomer()) === false) {
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
                RatingModel::loadByAttributes(
                    ['productID' => $product->kArtikel, 'customerID' => $customer->getID()],
                    $this->db
                )
            );

        return true;
    }

    /**
     * @param int   $productID
     * @param Kunde $customer
     * @return bool
     */
    private function checkProductWasPurchased(int $productID, Kunde $customer): bool
    {
        if ($this->config['bewertung']['bewertung_artikel_gekauft'] !== 'Y') {
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
    private function updateAverage(int $productID, string $activate): bool
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
     * @param RatingModel $rating
     * @return float
     */
    private function addReward(RatingModel $rating): float
    {
        $reward = 0.0;
        if ($this->config['bewertung']['bewertung_guthaben_nutzen'] !== 'Y') {
            return $reward;
        }
        $maxBalance    = (float)$this->config['bewertung']['bewertung_max_guthaben'];
        $level2balance = (float)$this->config['bewertung']['bewertung_stufe2_guthaben'];
        $level1balance = (float)$this->config['bewertung']['bewertung_stufe1_guthaben'];
        $ratingBonus   = $this->db->queryPrepared(
            'SELECT SUM(fGuthabenBonus) AS fGuthabenProMonat
            FROM tbewertungguthabenbonus
            WHERE kKunde = :cid
                AND kBewertung != :rid
                AND YEAR(dDatum) = :dyear
                AND MONTH(dDatum) = :dmonth',
            [
                'cid'    => $rating->customerID,
                'rid'    => $rating->id,
                'dyear'  => \date('Y'),
                'dmonth' => \date('m')
            ],
            ReturnType::SINGLE_OBJECT
        );
        if ((float)$ratingBonus->fGuthabenProMonat > $maxBalance) {
            return $reward;
        }
        if ((int)$this->config['bewertung']['bewertung_stufe2_anzahlzeichen'] <= mb_strlen($rating->content)) {
            $reward = ((float)$ratingBonus->fGuthabenProMonat + $level2balance) > $maxBalance
                ? $maxBalance - (float)$ratingBonus->fGuthabenProMonat
                : $level2balance;
        } else {
            $reward = ((float)$ratingBonus->fGuthabenProMonat + $level1balance) > $maxBalance
                ? $maxBalance - (float)$ratingBonus->fGuthabenProMonat
                : $level1balance;
        }
        $this->increaseCustomerBalance($rating->customerID, $reward);

        $ratingBonus = RatingBonusModel::loadByAttributes(
            ['customerID' => $rating->customerID, 'ratingID' => $rating->id],
            $this->db
        );
        /** @var $ratingBonus RatingBonusModel */
        $ratingBonus->bonus      = $reward;
        $ratingBonus->ratingID   = $rating->id;
        $ratingBonus->customerID = $rating->customerID;
        $ratingBonus->date       = 'NOW()';
        $ratingBonus->save();
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
     * @param RatingBonusModel $ratingBonus
     * @return bool
     */
    private function sendRewardMail(RatingBonusModel $ratingBonus): bool
    {
        $obj                          = new stdClass();
        $obj->tkunde                  = new Kunde($ratingBonus->customerID);
        $obj->oBewertungGuthabenBonus = $ratingBonus->rawObject();
        $mailer                       = Shop::Container()->get(Mailer::class);
        $mail                         = new Mail();

        return $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BEWERTUNG_GUTHABEN, $obj));
    }

    /**
     * Speichert für eine bestimmte Bewertung und bestimmten Kunden ab, ob sie hilfreich oder nicht hilfreich war.
     *
     * @param int $productID
     * @param int $customerID
     * @param int $page
     * @param int $stars
     */
    private function updateWasHelpful(int $productID, int $customerID, int $page = 1, int $stars = 0): void
    {
        $helpful  = 0;
        $ratingID = 0;
        foreach (\array_keys($_POST) as $key) {
            \preg_match('/^(nichthilfreich_)(\d*)/', $key, $hits);
            if (\count($hits) === 3) {
                $ratingID = (int)$hits[2];
                break;
            }
            \preg_match('/^(hilfreich_)(\d*)/', $key, $hits);
            if (\count($hits) === 3) {
                $ratingID = (int)$hits[2];
                $helpful  = 1;
                break;
            }
        }
        if ($customerID <= 0
            || $ratingID === 0
            || $this->config['bewertung']['bewertung_anzeigen'] !== 'Y'
            || $this->config['bewertung']['bewertung_hilfreich_anzeigen'] !== 'Y'
        ) {
            return;
        }
        try {
            $rating = new RatingModel(['id' => $ratingID], $this->db);
        } catch (Exception $e) {
            return;
        }
        if ($rating->customerID === $customerID) {
            return;
        }
        $helpfulRating = RatingHelpfulModel::loadByAttributes(
            ['ratingID' => $ratingID, 'customerID' => $customerID],
            $this->db
        );
        /** @var $helpfulRating RatingHelpfulModel */
        $baseURL = $this->getProductURL($productID) . 'bewertung_anzeigen=1&btgseite=' . $page . '&btgsterne=' . $stars;
        // Hat der Kunde für diese Bewertung noch keine hilfreich flag gesetzt?
        if ($helpfulRating->id === null) {
            $helpfulRating->ratingID   = $ratingID;
            $helpfulRating->customerID = $customerID;
            $helpfulRating->rating     = 0;
            // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese positiv ist
            if ($helpful === 1) {
                $helpfulRating->rating = 1;
                ++$rating->helpful;
                $rating->save(['helpful']);
            } else {
                // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese negativ ist
                ++$rating->notHelpful;
                $rating->save(['notHelpful']);
            }

            \executeHook(\HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNGHILFREICH, ['rating' => &$helpfulRating]);

            $helpfulRating->save();
            $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $rating->productID]);
            \header('Location: ' . $baseURL . '&cHinweis=h02', true, 303);
            exit;
        }
        // Wenn Hilfreich nicht neu (wechsel) für eine Bewertung eingetragen wird und diese positiv ist
        if ($helpful === 1 && $helpfulRating->rating !== $helpful) {
            ++$rating->helpful;
            --$rating->notHelpful;
            $rating->save(['helpful', 'notHelpful']);
        } elseif ($helpful === 0 && $helpfulRating->rating !== $helpful) {
            // Wenn Hilfreich neu für (wechsel) eine Bewertung eingetragen wird und diese negativ ist
            --$rating->helpful;
            ++$rating->notHelpful;
            $rating->save(['helpful', 'notHelpful']);
        }
        $helpfulRating->rating     = $helpful;
        $helpfulRating->ratingID   = $ratingID;
        $helpfulRating->customerID = $customerID;
        $helpfulRating->save();
        $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $rating->productID]);
        \header('Location: ' . $baseURL . '&cHinweis=h03', true, 303);
        exit;
    }

    /**
     * @param RatingModel $rating
     * @return int
     */
    private function deleteRatingReward(RatingModel $rating): int
    {
        $affected = 0;
        foreach ($rating->bonus as $bonusItem) {
            /** @var RatingBonusModel $bonusItem */
            $customer = $this->db->select('tkunde', 'kKunde', $bonusItem->customerID);
            if ($customer !== null && $customer->kKunde > 0) {
                $balance = $customer->fGuthaben - $bonusItem->bonus;
                $upd     = (object)['fGuthaben' => $balance > 0 ? $balance : 0];
                $this->db->update('tkunde', 'kKunde', $bonusItem->customerID, $upd);
                ++$affected;
            }
        }

        return $affected;
    }
}
