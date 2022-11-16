<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use InvalidArgumentException;
use JTL\Backend\Permissions;
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
use Laminas\Diactoros\Response\RedirectResponse;
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
     * @var SectionFactory
     */
    private SectionFactory $sectionFactory;

    /**
     * @var SettingsManager
     */
    private SettingsManager $settingManager;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/zahlungsarten');
        $this->getText->loadConfigLocales(true, true);
        $this->checkPermissions(Permissions::ORDER_PAYMENT_VIEW);
        $this->assignScrollPosition();

        $defaultCurrency      = $this->db->select('twaehrung', 'cStandard', 'Y');
        $this->step           = 'uebersicht';
        $recommendations      = new Manager($this->alertService, Manager::SCOPE_BACKEND_PAYMENT_PROVIDER);
        $filteredPost         = Text::filterXSS($_POST);
        $this->sectionFactory = new SectionFactory();
        $this->settingManager = new SettingsManager(
            $this->db,
            $smarty,
            $this->account,
            $this->getText,
            $this->alertService
        );
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
            if ($method !== null && \mb_strlen($method->cModulId) > 0) {
                (new ZahlungsLog($method->cModulId))->loeschen();
                $this->alertService->addSuccess(\sprintf(\__('successLogReset'), $method->cName), 'successLogReset');
            }
        }
        if ($action !== 'logreset' && Request::verifyGPCDataInt('kZahlungsart') > 0 && Form::validateToken()) {
            $this->step = 'einstellen';
            if ($action === 'payments') {
                $this->step = 'payments';
            } elseif ($action === 'log') {
                $this->step = 'log';
            } elseif ($action === 'del') {
                $this->step = 'delete';
            }
        }
        if (Request::postInt('einstellungen_bearbeiten') === 1
            && Request::postInt('kZahlungsart') > 0
            && Form::validateToken()
        ) {
            $this->actionSaveConfig($filteredPost);

            if (Request::postVar('saveAndContinue')) {
                $this->setStep('einstellen');
            }
        }

        if ($this->step === 'einstellen') {
            $this->stepConfig();
        } elseif ($this->step === 'log') {
            $this->stepLog();
        } elseif ($this->step === 'payments') {
            $this->stepPayments();
        } elseif ($this->step === 'delete') {
            $this->stepDelete();
            return new RedirectResponse(Shop::getURL() . $this->route);
        }

        if ($this->step === 'uebersicht') {
            $this->stepOverview();
        }

        return $this->smarty->assign('step', $this->step)
            ->assign('waehrung', $defaultCurrency->cName ?? '')
            ->assign('recommendations', $recommendations)
            ->assign('route', $this->route)
            ->getResponse('zahlungsarten.tpl');
    }

    private function stepOverview(): void
    {
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
                } catch (InvalidArgumentException) {
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
        $this->smarty->assign('zahlungsarten', $methods);
    }

    private function stepDelete(): void
    {
        $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');
        $method          = $this->db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
        if ($method === null) {
            return;
        }
        $pluginID = PluginHelper::getIDByModuleID($method->cModulId);
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
            } catch (InvalidArgumentException) {
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
    }

    private function stepPayments(): void
    {
        if (isset($filteredPost['action'], $filteredPost['kEingang_arr'])
            && $filteredPost['action'] === 'paymentwawireset'
            && Form::validateToken()
        ) {
            $this->db->query(
                "UPDATE tzahlungseingang
                        SET cAbgeholt = 'N'
                        WHERE kZahlungseingang IN ("
                . \implode(',', \array_map('\intval', $filteredPost['kEingang_arr']))
                . ')'
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
        $this->smarty->assign('oZahlungsart', $method)
            ->assign('oZahlunseingang_arr', $pagination->getPageItems())
            ->assign('pagination', $pagination)
            ->assign('oFilter', $filter);
    }

    private function stepLog(): void
    {
        $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');
        $method          = $this->db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);

        $filterStandard = new Filter('standard');
        $filterStandard->addDaterangefield('Zeitraum', 'dDatum');
        $filterStandard->assemble();

        if ($method !== null && \mb_strlen($method->cModulId) > 0) {
            $paginationPaymentLog = (new Pagination('standard'))
                ->setItemCount(ZahlungsLog::count($method->cModulId, -1, $filterStandard->getWhereSQL()))
                ->assemble();
            $paymentLogs          = (new ZahlungsLog($method->cModulId))->holeLog(
                $paginationPaymentLog->getLimitSQL(),
                -1,
                $filterStandard->getWhereSQL()
            );

            $this->smarty->assign('paymentLogs', $paymentLogs)
                ->assign('paymentData', $method)
                ->assign('filterStandard', $filterStandard)
                ->assign('paginationPaymentLog', $paginationPaymentLog);
        }
    }

    private function stepConfig(): void
    {
        $paymentMethod = new Zahlungsart(Request::verifyGPCDataInt('kZahlungsart'));
        if ($paymentMethod->getZahlungsart() === null) {
            $this->step = 'uebersicht';
            $this->alertService->addError(\__('errorPaymentMethodNotFound'), 'errorNotFound');
        } else {
            $paymentMethod->cName = Text::filterXSS($paymentMethod->cName);
            PaymentMethod::activatePaymentMethod($paymentMethod);
            // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
            $sql = new SqlObject();
            if (\str_contains($paymentMethod->cModulId, 'kPlugin_')) {
                $sql->setWhere(" cWertName LIKE :mid AND cConf = 'Y'");
                $sql->addParam('mid', $paymentMethod->cModulId . '\_%');
                $section = new PluginPaymentMethod($this->settingManager, \CONF_ZAHLUNGSARTEN);
            } else {
                $section = $this->sectionFactory->getSection(\CONF_ZAHLUNGSARTEN, $this->settingManager);
                $sql->setWhere(' ec.cModulId = :mid');
                $sql->addParam('mid', $paymentMethod->cModulId);
            }
            $section->load($sql);
            $conf = $section->getItems();

            $customerGroups = $this->db->getObjects(
                'SELECT *
                    FROM tkundengruppe
                    ORDER BY cName'
            );
            $this->smarty->assign('configItems', $conf)
                ->assign('zahlungsart', $paymentMethod)
                ->assign('kundengruppen', $customerGroups)
                ->assign('gesetzteKundengruppen', $this->getActiveCustomerGroups($paymentMethod))
                ->assign('Zahlungsartname', $this->getNames($paymentMethod->kZahlungsart))
                ->assign('Gebuehrname', $this->getshippingTimeNames($paymentMethod->kZahlungsart))
                ->assign('cHinweisTexte_arr', $this->getNotices($paymentMethod->kZahlungsart))
                ->assign('cHinweisTexteShop_arr', $this->getShopNotices($paymentMethod->kZahlungsart))
                ->assignDeprecated('ZAHLUNGSART_MAIL_EINGANG', \ZAHLUNGSART_MAIL_EINGANG, '5.0.0')
                ->assignDeprecated('ZAHLUNGSART_MAIL_STORNO', \ZAHLUNGSART_MAIL_STORNO, '5.0.0');
        }
    }

    /**
     * @param array $filteredPost
     * @return void
     */
    private function actionSaveConfig(array $filteredPost): void
    {
        $this->step    = 'uebersicht';
        $paymentMethod = $this->db->select('tzahlungsart', 'kZahlungsart', Request::postInt('kZahlungsart'));
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
        $sql = new SqlObject();
        if (\str_contains($paymentMethod->cModulId, 'kPlugin_')) {
            $kPlugin = PluginHelper::getIDByModuleID($paymentMethod->cModulId);
            $sql->setWhere(" cWertName LIKE :mid 
                AND cConf = 'Y'");
            $sql->addParam('mid', $paymentMethod->cModulId . '\_%');
            $section         = new PluginPaymentMethod($this->settingManager, \CONF_ZAHLUNGSARTEN);
            $post            = $_POST;
            $post['kPlugin'] = $kPlugin;
        } else {
            $section = $this->sectionFactory->getSection(\CONF_ZAHLUNGSARTEN, $this->settingManager);
            $sql->setWhere(' ec.cModulId = :mid');
            $sql->addParam('mid', $paymentMethod->cModulId);
            $section->load($sql);
            $post             = $_POST;
            $post['cModulId'] = $paymentMethod->cModulId;
        }
        $section->update($post);
        $localized               = new stdClass();
        $localized->kZahlungsart = Request::postInt('kZahlungsart');
        foreach (LanguageHelper::getAllLanguages(0, true, true) as $lang) {
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
        $this->step = 'uebersicht';
    }

    /**
     * @param int $paymentMethodID
     * @return array
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
     */
    private function getshippingTimeNames(int $paymentMethodID): array
    {
        $res = [];
        if (!$paymentMethodID) {
            return $res;
        }
        foreach ($this->db->selectAll('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID) as $item) {
            $res[$item->cISOSprache] = $item->cGebuehrname;
        }

        return $res;
    }

    /**
     * @param int $paymentMethodID
     * @return array
     * @former getHinweisTexte()
     */
    private function getNotices(int $paymentMethodID): array
    {
        $messages = [];
        if (!$paymentMethodID) {
            return $messages;
        }
        foreach ($this->db->selectAll('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID) as $localization) {
            $messages[$localization->cISOSprache] = $localization->cHinweisText;
        }

        return $messages;
    }

    /**
     * @param int $paymentMethodID
     * @return array
     * @former getHinweisTexteShop()
     */
    private function getShopNotices(int $paymentMethodID): array
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
     * @param Zahlungsart $paymentMethod
     * @return array
     * @former getGesetzteKundengruppen()
     */
    private function getActiveCustomerGroups(Zahlungsart $paymentMethod): array
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
