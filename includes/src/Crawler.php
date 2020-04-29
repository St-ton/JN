<?php

namespace JTL;

use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Services\JTL\Validation\Rules\DateTime;
use JTL\Helpers\Request;
use JTL\Alert\Alert;

/**
 * Class Crawler
 * @package JTL
 * @since 5.0.0
 */
class Crawler
{
    /**
     * @var int
     */
    public $kBesucherBot;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cUserAgent;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cLink;

    /**
     * @var DateTime
     */
    public $dZeit;

    /**
     * @param $kBesucherBot int
     * @param string|null $cUserAgent
     * @param string|null $cBeschreibung
     */
    public function __construct(int $kBesucherBot = 0, string $cUserAgent = null, string $cBeschreibung = null)
    {
        $this->kBesucherBot  = $kBesucherBot;
        $this->cUserAgent    = $cUserAgent;
        $this->cBeschreibung = $cBeschreibung;
    }

    /**
     * @var $id int
     *  @return object|int
     */
    public static function getById(int $id)
    {
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT * FROM tbesucherbot WHERE kBesucherBot = :id LIMIT 1',
            ['id' => $id],
            ReturnType::SINGLE_OBJECT
        );
    }

    /**
     * @var $userAgent string
     * @return object|bool
     */
    public static function getByUserAgent(string $userAgent)
    {
        $crawler = self::getAll();
        $result  = array_filter($crawler, static function ($item) use ($userAgent) {
            return mb_stripos($item->cUserAgent, $userAgent) !== false;
        });
        $result  = array_values($result);
        return count($result) > 0 ? (object)$result[0] : false;
    }

    /**
     * @var $item object
     * @return bool
     */
    public static function setCrawler(object $item): bool
    {
        Shop::Container()->getCache()->flush('crawler');
        if (isset($item->cUserAgent, $item->cBeschreibung) && !empty($item->kBesucherBot)) {
            return Shop::Container()->getDB()->update(
                'tbesucherbot',
                'kBesucherBot',
                $item->kBesucherBot,
                $item
            );
        }
        return Shop::Container()->getDB()->insert(
            'tbesucherbot',
            $item
        );
    }

    /**
     * @return array
     */
    public static function getAll(): array
    {
        $cacheID = 'crawler';
        if (($crawler = Shop::Container()->getCache()->get($cacheID)) === false) {
            $crawler = Shop::Container()->getDB()->query(
                'SELECT * FROM tbesucherbot ORDER BY kBesucherBot DESC',
                ReturnType::ARRAY_OF_OBJECTS
            );
            Shop::Container()->getCache()->set($cacheID, $crawler, [\CACHING_GROUP_CORE]);
        }
        return $crawler;
    }

    /**
     * @var $ids array
     * @return bool
     */
    public static function deleteBatch(array $ids): bool
    {
        $db       = Shop::Container()->getDB();
        $ids      = array_map(static function ($id) {
            return (int)$id;
        }, $ids);
        $where_in = '('.implode(',', $ids).')';
        $db->executeQuery(
            'DELETE FROM tbesucherbot WHERE kBesucherBot IN '.$where_in.' ',
            ReturnType::DEFAULT
        );
        Shop::Container()->getCache()->flush('crawler');
        return true;
    }

    /**
     * @return object|mixed
     */
    public static function checkSubmit()
    {
        $crawler      = false;
        $alertService = Shop::Container()->getAlertService();
        if (Form::validateToken() === false &&
            (Request::postInt('save_crawler') || Request::postInt('delete_crawler'))
        ) {
            $alertService->addAlert(Alert::TYPE_ERROR, __('errorCSRF'), 'errorCSRF');
            return $crawler;
        }
        if (Request::postInt('delete_crawler') === 1) {
            $selectedCrawler = Request::postVar('selectedCrawler');
            self::deleteBatch($selectedCrawler);
        }
        if (Request::postInt('save_crawler') === 1) {
            if (!empty(Request::postVar('cUserAgent')) && !empty(Request::postVar('cBeschreibung'))) {
                $crawlerId = Request::postInt('id');
                $item      = new self(
                    (int)$crawlerId,
                    Request::postVar('cUserAgent'),
                    Request::postVar('cBeschreibung')
                );
                $result    = self::setCrawler($item);
                if ($result === -1) {
                    $alertService->addAlert(Alert::TYPE_ERROR, __('missingCrawlerFields'), 'missingCrawlerFields');
                } else {
                    header('Location: statistik.php?s=3&tab=settings');
                }
            } else {
                $alertService->addAlert(Alert::TYPE_ERROR, __('missingCrawlerFields'), 'missingCrawlerFields');
            }
        }
        if (Request::verifyGPCDataInt('edit') === 1 || Request::verifyGPCDataInt('new') === 1) {
            $crawlerId = Request::verifyGPCDataInt('id');
            if ($crawlerId === 0) {
                $crawler = new self();
            } else {
                $crawler = self::getById($crawlerId);
            }
        }
        return $crawler;
    }
}
