<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Checkout\Bestellung;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OrderController
 * @package JTL\Router\Controller\Backend
 */
class OrderController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->getText->loadAdminLocale('pages/bestellungen');
        $this->smarty = $smarty;
        $this->checkPermissions('ORDER_VIEW');

        $step         = 'bestellungen_uebersicht';
        $searchFilter = '';
        // Bestellung Wawi Abholung zuruecksetzen
        if (Request::verifyGPCDataInt('zuruecksetzen') === 1 && Form::validateToken()) {
            if (isset($_POST['kBestellung'])) {
                switch ($this->setzeAbgeholtZurueck($_POST['kBestellung'])) {
                    case -1: // Alles O.K.
                        $this->alertService->addSuccess(\__('successOrderReset'), 'successOrderReset');
                        break;
                    case 1:  // Array mit Keys nicht vorhanden oder leer
                        $this->alertService->addError(\__('errorAtLeastOneOrder'), 'errorAtLeastOneOrder');
                        break;
                }
            } else {
                $this->alertService->addError(\__('errorAtLeastOneOrder'), 'errorAtLeastOneOrder');
            }
        } elseif (Request::verifyGPCDataInt('Suche') === 1 && Form::validateToken()) {
            $query = Text::filterXSS(Request::verifyGPDataString('cSuche'));
            if (\mb_strlen($query) > 0) {
                $searchFilter = $query;
            } else {
                $this->alertService->addError(\__('errorMissingOrderNumber'), 'errorMissingOrderNumber');
            }
        }

        if ($step === 'bestellungen_uebersicht') {
            $pagination = (new Pagination('bestellungen'))
                ->setItemCount($this->gibAnzahlBestellungen($searchFilter))
                ->assemble();
            $orders     = $this->gibBestellungsUebersicht(' LIMIT ' . $pagination->getLimitSQL(), $searchFilter);
            $smarty->assign('orders', $orders)
                ->assign('pagination', $pagination);
        }

        return $smarty->assign('cSuche', $searchFilter)
            ->assign('step', $step)
            ->assign('route', $route->getPath())
            ->getResponse('bestellungen.tpl');
    }

    /**
     * @param string $limitSQL
     * @param string $query
     * @return array
     * @former gibBestellungsUebersicht()
     */
    private function gibBestellungsUebersicht(string $limitSQL, string $query): array
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
    private function gibAnzahlBestellungen(string $query): int
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
     * @return int
     * @former setzeAbgeholtZurueck()
     */
    private function setzeAbgeholtZurueck(array $orderIDs): int
    {
        if (\count($orderIDs) === 0) {
            return 1;
        }
        $orderList = \implode(',', \array_map('\intval', $orderIDs));
        $customers = $this->db->getCollection(
            'SELECT kKunde
            FROM tbestellung
            WHERE kBestellung IN (' . $orderList . ")
                AND cAbgeholt = 'Y'"
        )->pluck('kKunde')->map(static function ($item) {
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

        return -1;
    }
}
