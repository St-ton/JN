<?php declare(strict_types=1);

namespace JTL\Crawler;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Router\Route;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use stdClass;

/**
 * Class Controller
 * @package JTL\Crawler
 */
class Controller
{
    /**
     * Crawler constructor.
     * @param DbInterface                $db
     * @param JTLCacheInterface          $cache
     * @param AlertServiceInterface|null $alertService
     */
    public function __construct(
        private readonly DbInterface       $db,
        private readonly JTLCacheInterface $cache,
        protected ?AlertServiceInterface   $alertService = null
    ) {
    }

    /**
     * @param int $id
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getCrawler(int $id): array
    {
        $crawler = $this->db->getObjects(
            'SELECT * FROM tbesucherbot WHERE kBesucherBot = :id ',
            ['id' => $id]
        );
        if (\count($crawler) === 0) {
            throw new \InvalidArgumentException('Provided crawler id ' . $id . ' not found.');
        }

        return $crawler;
    }

    /**
     * @return array
     */
    public function getAllCrawlers(): array
    {
        $cacheID = 'crawler';
        if (($crawlers = $this->cache->get($cacheID)) === false) {
            $crawlers = $this->db->getObjects('SELECT * FROM tbesucherbot ORDER BY kBesucherBot DESC');
            $this->cache->set($cacheID, $crawlers, [\CACHING_GROUP_CORE]);
        }

        return $crawlers;
    }

    /**
     * @param string $userAgent
     * @return object|bool
     */
    public function getByUserAgent(string $userAgent)
    {
        if ($userAgent === '') {
            return false;
        }
        $crawlers = $this->getAllCrawlers();
        $result   = \array_filter($crawlers, static function ($item) use ($userAgent): bool {
            return $item->cUserAgent !== '' && \mb_stripos($userAgent, $item->cUserAgent) !== false;
        });
        $result   = \array_values($result);

        return \count($result) > 0 ? (object)$result[0] : false;
    }

    /**
     * @param array $ids
     * @return bool
     */
    public function deleteCrawler(array $ids): bool
    {
        $where_in = '(' . \implode(',', \array_map('\intval', $ids)) . ')';
        $this->db->query(
            'DELETE FROM tbesucherbot 
                WHERE kBesucherBot IN ' . $where_in . ' '
        );
        $this->cache->flush('crawler');

        return true;
    }

    /**
     * @param object $item
     * @return int
     */
    public function saveCrawler(object $item): int
    {
        $this->cache->flush('crawler');
        if (isset($item->kBesucherBot, $item->cBeschreibung) && !empty($item->kBesucherBot)) {
            return $this->db->update(
                'tbesucherbot',
                'kBesucherBot',
                $item->kBesucherBot,
                $item
            );
        }

        return $this->db->insert(
            'tbesucherbot',
            $item
        );
    }

    /**
     * @return Crawler|bool
     */
    public function checkRequest()
    {
        if (Form::validateToken() === false
            && (Request::postInt('save_crawler') || Request::postInt('delete_crawler'))
        ) {
            $this->alertService->addError(\__('errorCSRF'), 'errorCSRF');

            return false;
        }
        if (Request::postInt('delete_crawler') === 1) {
            $selectedCrawler = Request::postVar('selectedCrawler');
            $this->deleteCrawler($selectedCrawler);
        }
        if (Request::postInt('save_crawler') === 1) {
            if (!empty(Request::postVar('useragent')) && !empty(Request::postVar('description'))) {
                $item                = new stdClass();
                $item->kBesucherBot  = Request::postInt('id');
                $item->cUserAgent    = Text::filterXSS(Request::postVar('useragent'));
                $item->cBeschreibung = Text::filterXSS(Request::postVar('description'));
                $result              = $this->saveCrawler($item);
                if ($result === -1) {
                    $this->alertService->addError(\__('missingCrawlerFields'), 'missingCrawlerFields');
                } else {
                    \header('Location: ' . Shop::getAdminURL() . '/' . Route::STATS . '/3?tab=settings');
                    exit;
                }
            } else {
                $this->alertService->addError(\__('missingCrawlerFields'), 'missingCrawlerFields');
            }
        }
        $crawler = false;
        if (Request::verifyGPCDataInt('edit') === 1 || Request::verifyGPCDataInt('new') === 1) {
            $crawlerId = Request::verifyGPCDataInt('id');
            $crawler   = new Crawler();
            if ($crawlerId > 0) {
                $item = $this->getCrawler($crawlerId);
                $crawler->map($item);
            }
        }

        return $crawler;
    }
}
