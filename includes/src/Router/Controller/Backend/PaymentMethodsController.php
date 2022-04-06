<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use InvalidArgumentException;
use JTL\Backend\Settings\Manager as SettingsManager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\PluginPaymentMethod;
use JTL\Checkout\Zahlungsart;
use JTL\Checkout\ZahlungsLog;
use JTL\DB\SqlObject;
use JTL\Helpers\Form;
use JTL\Helpers\PaymentMethod;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Recommendation\Manager;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class PaymentMethodsController
 * @package JTL\Router\Controller\Backend
 */
class PaymentMethodsController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/zahlungsarten');
        $this->getText->loadConfigLocales(true, true);
        $this->checkPermissions('ORDER_PAYMENT_VIEW');


        $defaultCurrency = $this->db->select('twaehrung', 'cStandard', 'Y');
        $step            = 'uebersicht';
        $recommendations = new Manager($this->alertService, Manager::SCOPE_BACKEND_PAYMENT_PROVIDER);
        $filteredPost    = Text::filterXSS($_POST);
        $sectionFactory  = new SectionFactory();
        $settingManager  = new SettingsManager($this->db, $smarty, $this->account, $this->getText, $this->alertService);
        if (Request::verifyGPCDataInt('checkNutzbar') === 1) {
            PaymentMethod::checkPaymentMethodAvailability();
            $this->alertService->addSuccess(\__('successPaymentMethodCheck'), 'successPaymentMethodCheck');
        }
        // reset log
        if (($action = Request::verifyGPDataString('a')) !== ''
            && $action === 'logreset'
            && ($paymentMethodID = Request::verifyGPCDataInt('kZahlungsart')) > 0
            && Form::validateToken()
        ) {
            $method = $this->db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);

            if (isset($method->cModulId) && mb_strlen($method->cModulId) > 0) {
                (new ZahlungsLog($method->cModulId))->loeschen();
                $this->alertService->addSuccess(\sprintf(\__('successLogReset'), $method->cName), 'successLogReset');
            }
        }
        if ($action !== 'logreset' && Request::verifyGPCDataInt('kZahlungsart') > 0 && Form::validateToken()) {
            $step = 'einstellen';
            if ($action === 'payments') {
                $step = 'payments';
            } elseif ($action === 'log') {
                $step = 'log';
            } elseif ($action === 'del') {
                $step = 'delete';
            }
        }
        if (Request::postInt('einstellungen_bearbeiten') === 1
            && Request::postInt('kZahlungsart') > 0
            && Form::validateToken()
        ) {
            $step          = 'uebersicht';
            $paymentMethod = $this->db->select(
                'tzahlungsart',
                'kZahlungsart',
                Request::postInt('kZahlungsart')
            );
            if ($paymentMethod !== null) {
                $paymentMethod->kZahlungsart        = (int)$paymentMethod->kZahlungsart;
                $paymentMethod->nSort               = (int)$paymentMethod->nSort;
                $paymentMethod->nWaehrendBestellung = (int)$paymentMethod->nWaehrendBestellung;
            }
            $nMailSenden       = Request::postInt('nMailSenden');
            $nMailSendenStorno = Request::postInt('nMailSendenStorno');
            $nMailBits         = 0;
            if (\is_array($filteredPost['kKundengruppe'])) {
                $filteredPost['kKundengruppe'] = \array_map('\intval', $filteredPost['kKundengruppe']);
                $cKundengruppen                = Text::createSSK($filteredPost['kKundengruppe']);
                if (\in_array(0, $filteredPost['kKundengruppe'], true)) {
                    unset($cKundengruppen);
                }
            }
            if ($nMailSenden) {
                $nMailBits |= \ZAHLUNGSART_MAIL_EINGANG;
            }
            if ($nMailSendenStorno) {
                $nMailBits |= \ZAHLUNGSART_MAIL_STORNO;
            }
            if (!isset($cKundengruppen)) {
                $cKundengruppen = '';
            }

            $duringCheckout = Request::postInt('nWaehrendBestellung', $paymentMethod->nWaehrendBestellung);

            $upd                      = new stdClass();
            $upd->cKundengruppen      = $cKundengruppen;
            $upd->nSort               = Request::postInt('nSort');
            $upd->nMailSenden         = $nMailBits;
            $upd->cBild               = $filteredPost['cBild'];
            $upd->nWaehrendBestellung = $duringCheckout;
            $this->db->update('tzahlungsart', 'kZahlungsart', $paymentMethod->kZahlungsart, $upd);
            // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
            if (mb_strpos($paymentMethod->cModulId, 'kPlugin_') !== false) {
                $kPlugin = PluginHelper::getIDByModuleID($paymentMethod->cModulId);
                $sql     = new SqlObject();
                $sql->setWhere(" cWertName LIKE :mid 
                AND cConf = 'Y'");
                $sql->addParam('mid', $paymentMethod->cModulId . '\_%');
                $section         = new PluginPaymentMethod($settingManager, \CONF_ZAHLUNGSARTEN);
                $post            = $_POST;
                $post['kPlugin'] = $kPlugin;
                $section->update($post);
            } else {
                $section = $sectionFactory->getSection(\CONF_ZAHLUNGSARTEN, $settingManager);
                $sql     = new SqlObject();
                $sql->setWhere(' ec.cModulId = :mid');
                $sql->addParam('mid', $paymentMethod->cModulId);
                $section->load($sql);
                $post             = $_POST;
                $post['cModulId'] = $paymentMethod->cModulId;
                $section->update($post);
            }
            $localized               = new stdClass();
            $localized->kZahlungsart = Request::postInt('kZahlungsart');
            foreach (LanguageHelper::getAllLanguages(0, true) as $lang) {
                $langCode               = $lang->getCode();
                $localized->cISOSprache = $langCode;
                $localized->cName       = $paymentMethod->cName;
                if ($filteredPost['cName_' . $langCode]) {
                    $localized->cName = $filteredPost['cName_' . $langCode];
                }
                $localized->cGebuehrname     = $filteredPost['cGebuehrname_' . $langCode];
                $localized->cHinweisText     = $filteredPost['cHinweisText_' . $langCode];
                $localized->cHinweisTextShop = $filteredPost['cHinweisTextShop_' . $langCode];

                $this->db->delete(
                    'tzahlungsartsprache',
                    ['kZahlungsart', 'cISOSprache'],
                    [Request::postInt('kZahlungsart'), $langCode]
                );
                $this->db->insert('tzahlungsartsprache', $localized);
            }

            $this->cache->flushAll();
            $this->alertService->addSuccess(\__('successPaymentMethodSave'), 'successSave');
            $step = 'uebersicht';
        }

        if ($step === 'einstellen') {
            $paymentMethod = new Zahlungsart(Request::verifyGPCDataInt('kZahlungsart'));
            if ($paymentMethod->getZahlungsart() === null) {
                $step = 'uebersicht';
                $this->alertService->addError(\__('errorPaymentMethodNotFound'), 'errorNotFound');
            } else {
                $paymentMethod->cName = Text::filterXSS($paymentMethod->cName);
                PaymentMethod::activatePaymentMethod($paymentMethod);
                // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
                if (mb_strpos($paymentMethod->cModulId, 'kPlugin_') !== false) {
                    $sql = new SqlObject();
                    $sql->setWhere(" cWertName LIKE :mid AND cConf = 'Y'");
                    $sql->addParam('mid', $paymentMethod->cModulId . '\_%');
                    $section = new PluginPaymentMethod($settingManager, \CONF_ZAHLUNGSARTEN);
                    $section->load($sql);
                    $conf = $section->getItems();
                } else {
                    $section = $sectionFactory->getSection(\CONF_ZAHLUNGSARTEN, $settingManager);
                    $sql     = new SqlObject();
                    $sql->setWhere(' ec.cModulId = :mid');
                    $sql->addParam('mid', $paymentMethod->cModulId);
                    $section->load($sql);
                    $conf = $section->getItems();
                }

                $customerGroups = $this->db->getObjects(
                    'SELECT *
                        FROM tkundengruppe
                        ORDER BY cName'
                );
                $smarty->assign('configItems', $conf)
                    ->assign('zahlungsart', $paymentMethod)
                    ->assign('kundengruppen', $customerGroups)
                    ->assign('gesetzteKundengruppen', $this->getGesetzteKundengruppen($paymentMethod))
                    ->assign('Zahlungsartname', $this->getNames($paymentMethod->kZahlungsart))
                    ->assign('Gebuehrname', $this->getshippingTimeNames($paymentMethod->kZahlungsart))
                    ->assign('cHinweisTexte_arr', $this->getHinweisTexte($paymentMethod->kZahlungsart))
                    ->assign('cHinweisTexteShop_arr', $this->getHinweisTexteShop($paymentMethod->kZahlungsart))
                    ->assign('ZAHLUNGSART_MAIL_EINGANG', \ZAHLUNGSART_MAIL_EINGANG)
                    ->assign('ZAHLUNGSART_MAIL_STORNO', \ZAHLUNGSART_MAIL_STORNO);
            }
        } elseif ($step === 'log') {
            $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');
            $method          = $this->db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);

            $filterStandard = new Filter('standard');
            $filterStandard->addDaterangefield('Zeitraum', 'dDatum');
            $filterStandard->assemble();

            if (isset($method->cModulId) && mb_strlen($method->cModulId) > 0) {
                $paginationPaymentLog = (new Pagination('standard'))
                    ->setItemCount(ZahlungsLog::count($method->cModulId, -1, $filterStandard->getWhereSQL()))
                    ->assemble();
                $paymentLogs          = (new ZahlungsLog($method->cModulId))->holeLog(
                    $paginationPaymentLog->getLimitSQL(),
                    -1,
                    $filterStandard->getWhereSQL()
                );

                $smarty->assign('paymentLogs', $paymentLogs)
                    ->assign('paymentData', $method)
                    ->assign('filterStandard', $filterStandard)
                    ->assign('paginationPaymentLog', $paginationPaymentLog);
            }
        } elseif ($step === 'payments') {
            if (isset($filteredPost['action'], $filteredPost['kEingang_arr'])
                && $filteredPost['action'] === 'paymentwawireset'
                && Form::validateToken()
            ) {
                $this->db->query(
                    "UPDATE tzahlungseingang
                        SET cAbgeholt = 'N'
                        WHERE kZahlungseingang IN (" . \implode(',', \array_map('\intval', $filteredPost['kEingang_arr'])) . ')'
                );
            }

            $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');

            $filter = new Filter('payments-' . $paymentMethodID);
            $filter->addTextfield(
                ['Suchbegriff', 'Sucht in Bestell-Nr., Betrag, Kunden-Vornamen, E-Mail-Adresse, Hinweis'],
                ['cBestellNr', 'fBetrag', 'cVorname', 'cMail', 'cHinweis']
            );
            $filter->addDaterangefield('Zeitraum', 'dZeit');
            $filter->assemble();

            $method        = $this->db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
            $incoming      = $this->db->getObjects(
                'SELECT ze.*, b.kZahlungsart, b.cBestellNr, k.kKunde, k.cVorname, k.cNachname, k.cMail
                    FROM tzahlungseingang AS ze
                        JOIN tbestellung AS b
                            ON ze.kBestellung = b.kBestellung
                        JOIN tkunde AS k
                            ON b.kKunde = k.kKunde
                    WHERE b.kZahlungsart = :pmid ' .
                        ($filter->getWhereSQL() !== '' ? 'AND ' . $filter->getWhereSQL() : '') . '
                    ORDER BY dZeit DESC',
                ['pmid' => $paymentMethodID]
            );
            $pagination    = (new Pagination('payments' . $paymentMethodID))
                ->setItemArray($incoming)
                ->assemble();
            $cryptoService = Shop::Container()->getCryptoService();
            foreach ($incoming as $item) {
                $item->cNachname = $cryptoService->decryptXTEA($item->cNachname);
                $item->dZeit     = \date_create($item->dZeit)->format('d.m.Y\<\b\r\>H:i');
            }
            $smarty->assign('oZahlungsart', $method)
                ->assign('oZahlunseingang_arr', $pagination->getPageItems())
                ->assign('pagination', $pagination)
                ->assign('oFilter', $filter);
        } elseif ($step === 'delete') {
            $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');
            $method          = $this->db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
            $pluginID        = PluginHelper::getIDByModuleID($method->cModulId);
            if ($pluginID > 0) {
                try {
                    $this->getText->loadPluginLocale(
                        'base',
                        PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                    );
                    $this->alertService->addWarning(
                        \sprintf(\__('Payment method can not been deleted'), \__($method->cName)),
                        'paymentcantdel',
                        ['saveInSession' => true]
                    );
                } catch (InvalidArgumentException $e) {
                    // Only delete if plugin is not installed
                    $this->db->delete('tversandartzahlungsart', 'kZahlungsart', $paymentMethodID);
                    $this->db->delete('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID);
                    $this->db->delete('tzahlungsart', 'kZahlungsart', $paymentMethodID);
                    $this->alertService->addSuccess(
                        \sprintf(\__('Payment method has been deleted'), $method->cName),
                        'paymentdeleted',
                        ['saveInSession' => true]
                    );
                }
            }
            \header('Location: ' . Shop::getURL() . $route->getPath());
            exit;
        }

        if ($step === 'uebersicht') {
            $methods = $this->db->selectAll(
                'tzahlungsart',
                ['nActive', 'nNutzbar'],
                [1, 1],
                '*',
                'cAnbieter, cName, nSort, kZahlungsart, cModulId'
            );
            foreach ($methods as $method) {
                $method->markedForDelete = false;

                $pluginID = PluginHelper::getIDByModuleID($method->cModulId);
                if ($pluginID > 0) {
                    try {
                        $this->getText->loadPluginLocale(
                            'base',
                            PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                        );
                    } catch (InvalidArgumentException $e) {
                        $method->markedForDelete = true;
                        $this->alertService->addWarning(
                            \sprintf(\__('Plugin for payment method not found'), $method->cName, $method->cAnbieter),
                            'notfound_' . $pluginID
                        );
                    }
                }
                $method->nEingangAnzahl = (int)$this->db->getSingleObject(
                    'SELECT COUNT(*) AS `cnt`
                        FROM `tzahlungseingang` AS ze
                            JOIN `tbestellung` AS b ON ze.`kBestellung` = b.`kBestellung`
                        WHERE b.`kZahlungsart` = :kzahlungsart',
                    ['kzahlungsart' => $method->kZahlungsart]
                )->cnt;
                $method->nLogCount      = ZahlungsLog::count($method->cModulId);
                $method->nErrorLogCount = ZahlungsLog::count($method->cModulId, \JTLLOG_LEVEL_ERROR);
                $method->cName          = \__($method->cName);
                $method->cAnbieter      = \__($method->cAnbieter);
            }
            $smarty->assign('zahlungsarten', $methods);
        }

        return $smarty->assign('step', $step)
            ->assign('waehrung', $defaultCurrency->cName)
            ->assign('recommendations', $recommendations)
            ->assign('route', $this->route)
            ->getResponse('zahlungsarten.tpl');
    }
    /**
     * @param int $paymentMethodID
     * @return array
     * @former getNames()
     */
    private function getNames(int $paymentMethodID): array
    {
        $res = [];
        if (!$paymentMethodID) {
            return $res;
        }
        $items = $this->db->selectAll('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID);
        foreach ($items as $item) {
            $res[$item->cISOSprache] = $item->cName;
        }

        return $res;
    }

    /**
     * @param int $paymentMethodID
     * @return array
     * @former getshippingTimeNames()
     */
    private function getshippingTimeNames(int $paymentMethodID): array
    {
        $res = [];
        if (!$paymentMethodID) {
            return $res;
        }
        $items = $this->db->selectAll('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID);
        foreach ($items as $item) {
            $res[$item->cISOSprache] = $item->cGebuehrname;
        }

        return $res;
    }

    /**
     * @param int $paymentMethodID
     * @return array
     * @former getHinweisTexte()
     */
    private function getHinweisTexte(int $paymentMethodID): array
    {
        $messages = [];
        if (!$paymentMethodID) {
            return $messages;
        }
        $localizations = $this->db->selectAll(
            'tzahlungsartsprache',
            'kZahlungsart',
            $paymentMethodID
        );
        foreach ($localizations as $localization) {
            $messages[$localization->cISOSprache] = $localization->cHinweisText;
        }

        return $messages;
    }

    /**
     * @param int $paymentMethodID
     * @return array
     * @former getHinweisTexteShop()
     */
    private function getHinweisTexteShop(int $paymentMethodID): array
    {
        $messages = [];
        if (!$paymentMethodID) {
            return $messages;
        }
        $localizations = $this->db->selectAll(
            'tzahlungsartsprache',
            'kZahlungsart',
            $paymentMethodID
        );
        foreach ($localizations as $localization) {
            $messages[$localization->cISOSprache] = $localization->cHinweisTextShop;
        }

        return $messages;
    }

    /**
     * @param stdClass|Zahlungsart $paymentMethod
     * @return array
     * @former getGesetzteKundengruppen()
     */
    private function getGesetzteKundengruppen($paymentMethod): array
    {
        $ret = [];
        if (!isset($paymentMethod->cKundengruppen) || !$paymentMethod->cKundengruppen) {
            $ret[0] = true;

            return $ret;
        }
        foreach (\explode(';', $paymentMethod->cKundengruppen) as $customerGroupID) {
            $ret[$customerGroupID] = true;
        }

        return $ret;
    }
}
