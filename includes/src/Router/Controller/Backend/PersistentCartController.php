<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Cart\PersistentCart;
use JTL\Customer\Customer;
use JTL\DB\SqlObject;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PersistentCartController
 * @package JTL\Router\Controller\Backend
 */
class PersistentCartController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::MODULE_SAVED_BASKETS_VIEW);
        $this->getText->loadAdminLocale('pages/warenkorbpers');

        $this->step = 'uebersicht';
        if (Request::getInt('l') > 0 && Form::validateToken()) {
            $customerID = Request::getInt('l');
            $persCart   = new PersistentCart($customerID);
            if ($persCart->entferneSelf()) {
                $this->alertService->addSuccess(\__('successCartPersPosDelete'), 'successCartPersPosDelete');
            }

            unset($persCart);
        }
        $this->getOverview();
        if (Request::getInt('a') > 0) {
            $this->actionShow(Request::getInt('a'));
        }

        return $smarty->assign('step', $this->step)
            ->assign('route', $this->route)
            ->getResponse('warenkorbpers.tpl');
    }

    /**
     * @return SqlObject
     */
    private function getSearchSQL(): SqlObject
    {
        $searchSQL = new SqlObject();
        if (\mb_strlen(Request::verifyGPDataString('cSuche')) === 0) {
            return $searchSQL;
        }
        $query = $this->db->escape(Text::filterXSS(Request::verifyGPDataString('cSuche')));
        if (\mb_strlen($query) > 0) {
            $searchSQL->setWhere(' WHERE (tkunde.cKundenNr LIKE :qry
                OR tkunde.cVorname LIKE :qry 
                OR tkunde.cMail LIKE :qry)');
            $searchSQL->addParam('qry', '%' . $query . '%');
        }
        $this->smarty->assign('cSuche', $query);

        return $searchSQL;
    }

    /**
     * @return void
     */
    private function getOverview(): void
    {
        $searchSQL     = $this->getSearchSQL();
        $customerCount = (int)$this->db->getSingleObject(
            'SELECT COUNT(DISTINCT tkunde.kKunde) AS cnt
                 FROM tkunde
                 JOIN twarenkorbpers
                     ON tkunde.kKunde = twarenkorbpers.kKunde
                 JOIN twarenkorbperspos
                     ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
                 ' . $searchSQL->getWhere(),
            $searchSQL->getParams()
        )->cnt;

        $customerPagination = (new Pagination('kunden'))
            ->setItemCount($customerCount)
            ->assemble();

        $customers = $this->db->getObjects(
            "SELECT tkunde.kKunde, tkunde.cFirma, tkunde.cVorname, tkunde.cNachname, 
                DATE_FORMAT(twarenkorbpers.dErstellt, '%d.%m.%Y  %H:%i') AS Datum, 
                COUNT(twarenkorbperspos.kWarenkorbPersPos) AS nAnzahl
                FROM tkunde
                JOIN twarenkorbpers 
                    ON tkunde.kKunde = twarenkorbpers.kKunde
                JOIN twarenkorbperspos 
                    ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
                " . $searchSQL->getWhere() . '
                GROUP BY tkunde.kKunde
                ORDER BY twarenkorbpers.dErstellt DESC
                LIMIT ' . $customerPagination->getLimitSQL(),
            $searchSQL->getParams()
        );
        $service   = Shop::Container()->getPasswordService();
        foreach ($customers as $item) {
            $customer = new Customer((int)$item->kKunde, $service, $this->db);

            $item->cNachname = $customer->cNachname;
            $item->cFirma    = $customer->cFirma;
        }
        $this->smarty->assign('oKunde_arr', $customers)
            ->assign('oPagiKunden', $customerPagination);
    }

    /**
     * @param int $customerID
     * @return void
     */
    private function actionShow(int $customerID): void
    {
        $this->step     = 'anzeigen';
        $persCartCount  = (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM twarenkorbperspos
                JOIN twarenkorbpers 
                    ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
                WHERE twarenkorbpers.kKunde = :cid',
            ['cid' => $customerID]
        )->cnt;
        $cartPagination = (new Pagination('warenkorb'))
            ->setItemCount($persCartCount)
            ->assemble();

        $carts   = $this->db->getObjects(
            "SELECT tkunde.kKunde AS kKundeTMP, tkunde.cVorname, tkunde.cNachname, twarenkorbperspos.kArtikel, 
                twarenkorbperspos.cArtikelName, twarenkorbpers.kKunde, twarenkorbperspos.fAnzahl, 
                DATE_FORMAT(twarenkorbperspos.dHinzugefuegt, '%d.%m.%Y  %H:%i') AS Datum
                FROM twarenkorbpers
                JOIN tkunde 
                    ON tkunde.kKunde = twarenkorbpers.kKunde
                JOIN twarenkorbperspos 
                    ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
                WHERE twarenkorbpers.kKunde = :cid
                LIMIT " . $cartPagination->getLimitSQL(),
            ['cid' => $customerID]
        );
        $service = Shop::Container()->getPasswordService();
        foreach ($carts as $cart) {
            $customer = new Customer((int)$cart->kKundeTMP, $service, $this->db);

            $cart->cNachname = $customer->cNachname;
            $cart->cFirma    = $customer->cFirma;
        }

        $this->smarty->assign('oWarenkorbPersPos_arr', $carts)
            ->assign('kKunde', $customerID)
            ->assign('oPagiWarenkorb', $cartPagination);
    }
}
