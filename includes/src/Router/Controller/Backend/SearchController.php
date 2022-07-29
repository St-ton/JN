<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Illuminate\Support\Collection;
use JTL\Backend\Menu;
use JTL\Backend\Permissions;
use JTL\Backend\Settings\Manager as SettingsManager;
use JTL\Backend\Settings\Search;
use JTL\Backend\Settings\Sections\SectionInterface;
use JTL\Helpers\Text;
use JTL\Plugin\Admin\Listing;
use JTL\Plugin\Admin\ListingItem;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Smarty\JTLSmarty;
use JTL\XMLParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SearchController
 * @package JTL\Router\Controller\Backend
 */
class SearchController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SETTINGS_SEARCH_VIEW);
        $query = $_GET['cSuche'] ?? '';

        $this->adminSearch(\trim($query), true);

        return $this->smarty->assign('route', $this->route)
            ->getResponse('suche.tpl');
    }
    /**
     * Search for backend settings
     *
     * @param string $query - search string
     * @param bool   $standalonePage - render as standalone page
     * @return string|null
     * @usedby IO!
     */
    public function adminSearch(string $query, bool $standalonePage = false): ?string
    {
        $adminMenuItems = $this->adminMenuSearch($query);
        $settings       = $this->configSearch($query);
        $shippings      = $this->getShippingByName($query);
        $paymentMethods = $this->getPaymentMethodsByName($query);
        foreach ($shippings as $shipping) {
            $shipping->cName = $this->highlightSearchTerm($shipping->cName, $query);
        }
        foreach ($paymentMethods as $paymentMethod) {
            $paymentMethod->cName = $this->highlightSearchTerm($paymentMethod->cName, $query);
        }
        $this->smarty->assign('standalonePage', $standalonePage)
            ->assign('query', Text::filterXSS($query))
            ->assign('adminMenuItems', $adminMenuItems)
            ->assign('settings', $settings)
            ->assign('shippings', \count($shippings) > 0 ? $shippings : null)
            ->assign('paymentMethods', \count($paymentMethods) > 0 ? $paymentMethods : null)
            ->assign('plugins', $this->getPlugins($query));

        if ($standalonePage) {
            return null;
        }

        return $this->smarty->fetch('suche.tpl');
    }

    /**
     * @param string $query
     * @return SectionInterface[]
     */
    private function configSearch(string $query): array
    {
        $manager = new SettingsManager(
            $this->db,
            $this->smarty,
            $this->account,
            $this->getText,
            $this->alertService
        );

        return (new Search($this->db, $this->getText, $manager))->getResultSections($query);
    }

    /**
     * @param string $query
     * @return array
     */
    private function adminMenuSearch(string $query): array
    {
        $results = [];
        $menu    = new Menu($this->db, $this->account, $this->getText);
        foreach ($menu->getStructure() as $menuName => $menu) {
            foreach ($menu->items as $subMenuName => $subMenu) {
                if (\is_array($subMenu)) {
                    foreach ($subMenu as $itemName => $item) {
                        if (\is_object($item) && (
                                \stripos($itemName, $query) !== false
                                || \stripos($subMenuName, $query) !== false
                                || \stripos($menuName, $query) !== false
                            )
                        ) {
                            $name      = $itemName;
                            $path      = $menuName . ' > ' . $subMenuName . ' > ' . $name;
                            $path      = $this->highlightSearchTerm($path, $query);
                            $results[] = (object)[
                                'title' => $itemName,
                                'path'  => $path,
                                'link'  => $item->link,
                                'icon'  => $menu->icon
                            ];
                        }
                    }
                } elseif (\is_object($subMenu)
                    && (\stripos($subMenuName, $query) !== false || \stripos($menuName, $query) !== false)
                ) {
                    $results[] = (object)[
                        'title' => $subMenuName,
                        'path'  => $this->highlightSearchTerm($menuName . ' > ' . $subMenuName, $query),
                        'link'  => $subMenu->link,
                        'icon'  => $menu->icon
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return string
     */
    private function highlightSearchTerm(string $haystack, string $needle): string
    {
        return \preg_replace(
            '/\p{L}*?' . \preg_quote($needle, '/') . '\p{L}*/ui',
            '<mark>$0</mark>',
            $haystack
        );
    }

    /**
     * @param string $query
     * @return Collection
     */
    private function getPlugins(string $query): Collection
    {
        if (\mb_strlen($query) <= 2) {
            return new Collection();
        }
        $parser          = new XMLParser();
        $legacyValidator = new LegacyPluginValidator($this->db, $parser);
        $pluginValidator = new PluginValidator($this->db, $parser);
        $listing         = new Listing($this->db, $this->cache, $legacyValidator, $pluginValidator);

        return $listing->getInstalled()->filter(function (ListingItem $e) use ($query): bool {
            if (\stripos($e->getName(), $query) !== false) {
                $e->setName($this->highlightSearchTerm($e->getName(), $query));

                return true;
            }

            return false;
        });
    }

    /**
     * @param string $query
     * @return array
     * @former getShippingByName()
     */
    private function getShippingByName(string $query): array
    {
        $results = [];
        foreach (\explode(',', $query) as $search) {
            $search = \trim($search);
            if (\mb_strlen($search) < 3) {
                continue;
            }
            $hits = $this->db->getObjects(
                'SELECT va.kVersandart, va.cName
                    FROM tversandart AS va
                    LEFT JOIN tversandartsprache AS vs 
                        ON vs.kVersandart = va.kVersandart
                        AND vs.cName LIKE :search
                    WHERE va.cName LIKE :search
                    OR vs.cName LIKE :search',
                ['search' => '%' . $search . '%']
            );
            foreach ($hits as $item) {
                $item->kVersandart           = (int)$item->kVersandart;
                $results[$item->kVersandart] = $item;
            }
        }

        return $results;
    }

    /**
     * @param string $query
     * @return array
     */
    private function getPaymentMethodsByName(string $query): array
    {
        $paymentMethodsByName = [];
        foreach (\explode(',', $query) as $string) {
            $string = \trim($string);
            if (\mb_strlen($string) < 3) {
                continue;
            }
            $data = $this->db->getObjects(
                'SELECT za.kZahlungsart, za.cName
                    FROM tzahlungsart AS za
                    LEFT JOIN tzahlungsartsprache AS zs 
                        ON zs.kZahlungsart = za.kZahlungsart
                        AND zs.cName LIKE :search
                    WHERE za.cName LIKE :search 
                    OR zs.cName LIKE :search
                    GROUP BY za.kZahlungsart',
                ['search' => '%' . $string . '%']
            );
            foreach ($data as $paymentMethodByName) {
                $paymentMethodByName->kZahlungsart = (int)$paymentMethodByName->kZahlungsart;

                $paymentMethodsByName[$paymentMethodByName->kZahlungsart] = $paymentMethodByName;
            }
        }

        return $paymentMethodsByName;
    }
}
