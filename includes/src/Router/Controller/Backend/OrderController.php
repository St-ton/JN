<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Checkout\Bestellung;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OrderController
 * @package JTL\Router\Controller\Backend
 */
class OrderController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getText->loadAdminLocale('pages/bestellungen');
        $this->checkPermissions(Permissions::ORDER_VIEW);

        $searchFilter = '';
        if ($this->tokenIsValid && $this->request->requestInt('zuruecksetzen') === 1) {
            if ($this->request->post('kBestellung') !== null
                && $this->resetSyncStatus($this->request->post('kBestellung'))
            ) {
                $this->alertService->addSuccess(\__('successOrderReset'), 'successOrderReset');
            } else {
                $this->alertService->addError(\__('errorAtLeastOneOrder'), 'errorAtLeastOneOrder');
            }
        } elseif ($this->tokenIsValid && $this->request->requestInt('Suche') === 1) {
            $query = Text::filterXSS($this->request->request('cSuche'));
            if (\mb_strlen($query) > 0) {
                $searchFilter = $query;
            } else {
                $this->alertService->addError(\__('errorMissingOrderNumber'), 'errorMissingOrderNumber');
            }
        }

        $pagination = (new Pagination('bestellungen'))
            ->setItemCount($this->getOrderCount($searchFilter))
            ->assemble();

        return $this->smarty->assign('step', 'bestellungen_uebersicht')
            ->assign('orders', $this->getOrders(' LIMIT ' . $pagination->getLimitSQL(), $searchFilter))
            ->assign('pagination', $pagination)
            ->assign('cSuche', $searchFilter)
            ->getResponse('bestellungen.tpl');
    }

    /**
     * @param string $limitSQL
     * @param string $query
     * @return array
     * @former gibBestellungsUebersicht()
     */
    public function getOrders(string $limitSQL, string $query): array
    {
        $orders       = [];
        $prep         = [];
        $searchFilter = '';
        if (\mb_strlen($query) > 0) {
            $searchFilter = ' WHERE cBestellNr LIKE :fltr';
            $prep['fltr'] = '%' . $query . '%';
        }
        $items = $this->db->getInts(
            'SELECT kBestellung
                FROM tbestellung
                ' . $searchFilter . '
                ORDER BY dErstellt DESC' . $limitSQL,
            'kBestellung',
            $prep
        );
        foreach ($items as $orderID) {
            if ($orderID > 0) {
                $order = new Bestellung($orderID);
                $order->fuelleBestellung(true, 0, false);
                $orders[] = $order;
            }
        }

        return $orders;
    }

    /**
     * @param string $query
     * @return int
     * @former gibAnzahlBestellungen()
     */
    private function getOrderCount(string $query): int
    {
        $prep         = [];
        $searchFilter = '';
        if (\mb_strlen($query) > 0) {
            $searchFilter = ' WHERE cBestellNr LIKE :fltr';
            $prep['fltr'] = '%' . $query . '%';
        }

        return (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM tbestellung' . $searchFilter,
            $prep
        )->cnt;
    }

    /**
     * @param array $orderIDs
     * @return bool
     * @former setzeAbgeholtZurueck()
     */
    private function resetSyncStatus(array $orderIDs): bool
    {
        if (\count($orderIDs) === 0) {
            return false;
        }
        $orderList = \implode(',', \array_map('\intval', $orderIDs));
        $customers = $this->db->getCollection(
            'SELECT kKunde
                FROM tbestellung
                WHERE kBestellung IN (' . $orderList . ")
                    AND cAbgeholt = 'Y'"
        )->pluck('kKunde')->map(static function ($item): int {
            return (int)$item;
        })->unique()->toArray();
        if (\count($customers) > 0) {
            $this->db->query(
                "UPDATE tkunde
                    SET cAbgeholt = 'N'
                    WHERE kKunde IN (" . \implode(',', $customers) . ')'
            );
        }
        $this->db->query(
            "UPDATE tbestellung
                SET cAbgeholt = 'N'
                WHERE kBestellung IN (" . $orderList . ")
                    AND cAbgeholt = 'Y'"
        );
        $this->db->query(
            "UPDATE tzahlungsinfo
                SET cAbgeholt = 'N'
                WHERE kBestellung IN (" . $orderList . ")
                    AND cAbgeholt = 'Y'"
        );

        return true;
    }
}
