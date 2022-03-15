<?php declare(strict_types=1);

namespace JTL\Router;

use DbInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use stdClass;
use function FastRoute\simpleDispatcher;

/**
 * Class DefaultHandler
 * @package JTL\Router
 */
class Router
{
    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;

    /**
     * @var string
     */
    private string $uri = '';

    /**
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db         = $db;
        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
            $defaultHandler      = new DefaultHandler($this->db);
            $productHandler      = new ProductHandler($this->db);
            $categoryHandler     = new CategoryHandler($this->db);
            $manufacturerHandler = new ManufacturerHandler($this->db);
            $rootHandler         = new RootHandler($this->db);
            $newsHandler         = new NewsHandler($this->db);
            $pageHandler         = new PageHandler($this->db);
            $r->addRoute('GET', '/products/{id:\d+}', [$productHandler, 'handle']);
            $r->addRoute('GET', '/categories/{id:\d+}', [$categoryHandler, 'handle']);
            $r->addRoute('GET', '/manufacturers/{id:\d+}', [$manufacturerHandler, 'handle']);
            $r->addRoute('GET', '/news/{id:\d+}', [$newsHandler, 'handle']);
            $r->addRoute('GET', '/page/{id:\d+}', [$pageHandler, 'handle']);
            $r->addRoute('GET', '/{slug}', [$defaultHandler, 'handle']);
            $r->addRoute('GET', '/', [$rootHandler, 'handle']);
        });
    }

    /**
     * @param bool $decoded - true to decode %-sequences in the URI, false to leave them unchanged
     * @return string
     */
    public function getRequestUri(bool $decoded = false): string
    {
        $shopURLdata = \parse_url(Shop::getURL());
        $baseURLdata = \parse_url($this->getRequestURL());

        $uri = isset($baseURLdata['path'])
            ? \mb_substr($baseURLdata['path'], \mb_strlen($shopURLdata['path'] ?? '') + 1)
            : '';
        $uri = '/' . $uri;

        if ($decoded) {
            $uri = \rawurldecode($uri);
        }

        return $uri;
    }

    /**
     * @return string
     */
    public function getRequestURL(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['HTTP_X_REWRITE_URL'] ?? $_SERVER['REQUEST_URI'] ?? '');
    }

    /**
     * @return bool|stdClass
     */
    public function dispatch()
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
//        $uri        = $_SERVER['REQUEST_URI'];
//
//        // Strip query string (?foo=bar) and decode URI
//        if (false !== $pos = strpos($uri, '?')) {
//            $uri = substr($uri, 0, $pos);
//        }
//        $uri = rawurldecode($uri);

        $uri = $this->getRequestUri();
        $uri = $this->extractExternalParams($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        $this->uri = $uri;

        return $this->handle($routeInfo);
    }

    /**
     * Affiliate trennen
     *
     * @param string|bool $seo
     * @return string|bool
     * @former extFremdeParameter()
     * @since 5.0.0
     */
    private function extractExternalParams($seo)
    {
        $seoData = \preg_split('/[' . \EXT_PARAMS_SEPERATORS_REGEX . ']+/', $seo);
        if (\is_array($seoData) && \count($seoData) > 1) {
            $seo = $seoData[0];
            $cnt = \count($seoData);
            for ($i = 1; $i < $cnt; $i++) {
                $keyValue = \explode('=', $seoData[$i]);
                if (\count($keyValue) > 1) {
                    [$name, $value]                    = $keyValue;
                    $_SESSION['FremdParameter'][$name] = $value;
                }
            }
        }

        return $seo;
    }

    /**
     * @param array $routeInfo
     * @return stdClass|bool
     */
    public function handle(array $routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                return false;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                return false;
            case Dispatcher::FOUND:
                [, $handler, $vars] = $routeInfo;

                return $handler($vars);
        }
    }

    /**
     * @return array
     */
    public function initCompat(): array
    {
        $params                           = [];
        $params['kKonfigPos']             = Request::verifyGPCDataInt('ek');
        $params['kKategorie']             = Request::verifyGPCDataInt('k');
        $params['kArtikel']               = Request::verifyGPCDataInt('a');
        $params['kVariKindArtikel']       = Request::verifyGPCDataInt('a2');
        $params['kSeite']                 = Request::verifyGPCDataInt('s');
        $params['kLink']                  = Request::verifyGPCDataInt('s');
        $params['kHersteller']            = Request::verifyGPCDataInt('h');
        $params['kSuchanfrage']           = Request::verifyGPCDataInt('l');
        $params['kMerkmalWert']           = Request::verifyGPCDataInt('m');
        $params['kSuchspecial']           = Request::verifyGPCDataInt('q');
        $params['kNews']                  = Request::verifyGPCDataInt('n');
        $params['kNewsMonatsUebersicht']  = Request::verifyGPCDataInt('nm');
        $params['kNewsKategorie']         = Request::verifyGPCDataInt('nk');
        $params['nBewertungSterneFilter'] = Request::verifyGPCDataInt('bf');
        $params['cPreisspannenFilter']    = Request::verifyGPDataString('pf');
        $params['manufacturerFilterIDs']  = Request::verifyGPDataIntegerArray('hf');
        $params['kHerstellerFilter']      = \count($params['manufacturerFilterIDs']) > 0
            ? $params['manufacturerFilterIDs'][0]
            : 0;
        $params['categoryFilterIDs']      = Request::verifyGPDataIntegerArray('kf');
        $params['kKategorieFilter']       = \count($params['categoryFilterIDs']) > 0
            ? $params['categoryFilterIDs'][0]
            : 0;
        $params['searchSpecialFilterIDs'] = Request::verifyGPDataIntegerArray('qf');
        $params['kSuchFilter']            = Request::verifyGPCDataInt('sf');
        $params['kSuchspecialFilter']     = \count($params['searchSpecialFilterIDs']) > 0
            ? $params['searchSpecialFilterIDs'][0]
            : 0;

        $params['nDarstellung'] = Request::verifyGPCDataInt('ed');
        $params['nSortierung']  = Request::verifyGPCDataInt('sortierreihenfolge');
        $params['nSort']        = Request::verifyGPCDataInt('Sortierung');

        $params['show']            = Request::verifyGPCDataInt('show');
        $params['vergleichsliste'] = Request::verifyGPCDataInt('vla');
        $params['bFileNotFound']   = false;
        $params['cCanonicalURL']   = '';
        $params['is404']           = false;
        $params['nLinkart']        = 0;

        $params['nSterne'] = Request::verifyGPCDataInt('nSterne');

        $params['kWunschliste'] = Wishlist::checkeParameters();

        $params['nNewsKat'] = Request::verifyGPCDataInt('nNewsKat');
        $params['cDatum']   = Request::verifyGPDataString('cDatum');
        $params['nAnzahl']  = Request::verifyGPCDataInt('nAnzahl');

        $params['optinCode'] = Request::verifyGPDataString('oc');

        if (Request::verifyGPDataString('qs') !== '') {
            $params['cSuche'] = Text::xssClean(Request::verifyGPDataString('qs'));
        } elseif (Request::verifyGPDataString('suchausdruck') !== '') {
            $params['cSuche'] = Text::xssClean(Request::verifyGPDataString('suchausdruck'));
        } else {
            $params['cSuche'] = Text::xssClean(Request::verifyGPDataString('suche'));
        }
        $params['nArtikelProSeite'] = Request::verifyGPCDataInt('af');

        return $params;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function setDispatcher(Dispatcher $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }
}
