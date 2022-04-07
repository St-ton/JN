<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use DateInterval;
use Exception;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Hersteller;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Kupon;
use JTL\CSV\Export;
use JTL\CSV\Import;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class StatsController
 * @package JTL\Router\Controller\Backend
 */
class CouponsController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/kupons');
        $this->checkPermissions('ORDER_COUPON_VIEW');

        $action    = Request::verifyGPDataString('action');
        $tab       = Kupon::TYPE_STANDARD;
        $languages = LanguageHelper::getAllLanguages(0, true);
        $coupon    = null;
        $importer  = Request::verifyGPDataString('importcsv');
        if (Form::validateToken()) {
            if ($importer !== '') {
                $import = new Import($this->db);
                $import->import('kupon', function ($obj, &$importDeleteDone, $importType = 2) {
                    $couponNames = [];
                    $cols        = $this->db->getCollection(
                        'SELECT `column_name` AS name
                            FROM information_schema.columns 
                            WHERE `table_schema` = :sma
                                AND `table_name` = :tn',
                        ['sma' => DB_NAME, 'tn' => 'tkupon']
                    )->map(static function ($e) {
                        return $e->name;
                    })->toArray();

                    foreach (\get_object_vars($obj) as $key => $val) {
                        if (\str_starts_with($key, 'cName_')) {
                            $couponNames[\mb_substr($key, 6)] = Text::filterXSS($val);
                            unset($obj->$key);
                        }
                        if (!\in_array($key, $cols, true)) {
                            unset($obj->$key);
                        }
                    }
                    if (!isset(
                        $obj->cCode,
                        $obj->nGanzenWKRabattieren,
                        $obj->cKunden,
                        $obj->cKategorien,
                        $obj->cHersteller,
                        $obj->cArtikel
                    )) {
                        return false;
                    }
                    if ($importType === 0 && $importDeleteDone === false) {
                        $this->db->query('TRUNCATE TABLE tkupon');
                        $this->db->query('TRUNCATE TABLE tkuponsprache');
                        $importDeleteDone = true;
                    }
                    if (isset($obj->cKuponTyp)
                        && $obj->cKuponTyp !== 'neukundenkupon'
                        && $this->db->select('tkupon', 'cCode', $obj->cCode) !== null
                    ) {
                        return false;
                    }

                    unset($obj->dLastUse);
                    if (isset($obj->dGueltigBis) && $obj->dGueltigBis === '') {
                        unset($obj->dGueltigBis);
                    }
                    if (isset($obj->dGueltigAb) && $obj->dGueltigAb === '') {
                        unset($obj->dGueltigAb);
                    }
                    $obj->cCode = Text::filterXSS($obj->cCode);
                    $obj->cName = Text::filterXSS($obj->cName);
                    $couponID   = $this->db->insert('tkupon', $obj);
                    if ($couponID === 0) {
                        return false;
                    }

                    foreach ($couponNames as $key => $val) {
                        $res = $this->db->insert(
                            'tkuponsprache',
                            (object)['kKupon' => $couponID, 'cISOSprache' => $key, 'cName' => $val]
                        );
                        if ($res === 0) {
                            return false;
                        }
                    }

                    return true;
                }, [], null, Request::verifyGPCDataInt('importType'));
                $errorCount = $import->getErrorCount();
                if ($errorCount > 0) {
                    foreach ($import->getErrors() as $key => $error) {
                        $this->alertService->addError($error, 'errorImportCSV_' . $key);
                    }
                } else {
                    $this->alertService->addSuccess(\__('successImportCSV'), 'successImportCSV');
                }
            }
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'speichern') {
                    $action = 'speichern';
                } elseif ($_POST['action'] === 'loeschen') {
                    $action = 'loeschen';
                }
            } elseif (Request::getInt('kKupon', -1) >= 0) {
                $action = 'bearbeiten';
            }
        }

        if ($action === 'bearbeiten') {
            $couponID = (int)($_GET['kKupon'] ?? $_POST['kKuponBearbeiten'] ?? 0);
            if ($couponID > 0) {
                $coupon = $this->getCoupon($couponID);
            } else {
                $coupon = $this->createNewCoupon($_REQUEST['cKuponTyp']);
            }
        } elseif ($action === 'speichern') {
            $coupon       = $this->createCouponFromInput();
            $couponErrors = $coupon->validate();
            if (\count($couponErrors) > 0) {
                // Es gab Fehler bei der Validierung => weiter bearbeiten
                $errorMessage = \__('errorCheckInput') . ':<ul>';

                foreach ($couponErrors as $couponError) {
                    $errorMessage .= '<li>' . $couponError . '</li>';
                }

                $errorMessage .= '</ul>';
                $action        = 'bearbeiten';
                $this->alertService->addError($errorMessage, 'errorCheckInput');
                $coupon->augment();
            } elseif ($this->saveCoupon($coupon, $languages) > 0) {// Validierung erfolgreich => Kupon speichern
                // erfolgreich gespeichert => evtl. Emails versenden
                if (isset($_POST['informieren'])
                    && $_POST['informieren'] === 'Y'
                    && ($coupon->cKuponTyp === Kupon::TYPE_STANDARD || $coupon->cKuponTyp === Kupon::TYPE_SHIPPING)
                    && $coupon->cAktiv === 'Y'
                ) {
                    $this->informCouponCustomers($coupon);
                }
                $this->alertService->addSuccess(\__('successCouponSave'), 'successCouponSave');
            } else {
                $this->alertService->addError(\__('errorCouponSave'), 'errorCouponSave');
            }
        } elseif ($action === 'loeschen') {
            // Kupons loeschen
            if (GeneralObject::hasCount('kKupon_arr', $_POST)) {
                $couponIDs = \array_map('\intval', $_POST['kKupon_arr']);
                if ($this->loescheKupons($couponIDs)) {
                    $this->alertService->addSuccess(\__('successCouponDelete'), 'successCouponDelete');
                } else {
                    $this->alertService->addError(\__('errorCouponDelete'), 'errorCouponDelete');
                }
            } else {
                $this->alertService->addError(\__('errorAtLeastOneCoupon'), 'errorAtLeastOneCoupon');
            }
        }
        if ($action === 'bearbeiten') {
            $taxClasses  = $this->db->getObjects('SELECT kSteuerklasse, cName FROM tsteuerklasse');
            $customerIDs = \array_filter(
                Text::parseSSKint($coupon->cKunden),
                static function ($customerID) {
                    return (int)$customerID > 0;
                }
            );
            if ($coupon->kKupon > 0) {
                $names = $coupon->translationList;
            } else {
                $names = [];
                foreach ($languages as $language) {
                    $postVarName                = 'cName_' . $language->getIso();
                    $names[$language->getIso()] = Request::postVar($postVarName, '') !== ''
                        ? Text::filterXSS($_POST[$postVarName])
                        : $coupon->cName;
                }
            }
            $smarty->assign('taxClasses', $taxClasses)
                ->assign('customerGroups', CustomerGroup::getGroups())
                ->assign('manufacturers', $this->getManufacturers($coupon->cHersteller))
                ->assign('categories', $this->getCategories($coupon->cKategorien))
                ->assign('customerIDs', $customerIDs)
                ->assign('couponNames', $names)
                ->assign('oKupon', $coupon);
        } else {
            // Seite: Uebersicht
            if (Request::hasGPCData('tab')) {
                $tab = Request::verifyGPDataString('tab');
            } elseif (Request::hasGPCData('cKuponTyp')) {
                $tab = Request::verifyGPDataString('cKuponTyp');
            }

            $this->deactivateOutdatedCoupons();
            $this->deactivateExhaustedCoupons();

            $filterStandard = new Filter(Kupon::TYPE_STANDARD);
            $filterStandard->addTextfield(\__('name'), 'cName');
            $filterStandard->addTextfield(\__('code'), 'cCode');
            $activeSelection = $filterStandard->addSelectfield(\__('status'), 'cAktiv');
            $activeSelection->addSelectOption(\__('all'), '');
            $activeSelection->addSelectOption(\__('active'), 'Y', Operation::EQUALS);
            $activeSelection->addSelectOption(\__('inactive'), 'N', Operation::EQUALS);
            $filterStandard->assemble();

            $filterVersand = new Filter(Kupon::TYPE_SHIPPING);
            $filterVersand->addTextfield(\__('name'), 'cName');
            $filterVersand->addTextfield(\__('code'), 'cCode');
            $activeSelection = $filterVersand->addSelectfield(\__('status'), 'cAktiv');
            $activeSelection->addSelectOption(\__('all'), '');
            $activeSelection->addSelectOption(\__('active'), 'Y', Operation::EQUALS);
            $activeSelection->addSelectOption(\__('inactive'), 'N', Operation::EQUALS);
            $filterVersand->assemble();

            $filterNeukunden = new Filter(Kupon::TYPE_NEWCUSTOMER);
            $filterNeukunden->addTextfield(\__('name'), 'cName');
            $activeSelection = $filterNeukunden->addSelectfield(\__('status'), 'cAktiv');
            $activeSelection->addSelectOption(\__('all'), '');
            $activeSelection->addSelectOption(\__('active'), 'Y', Operation::EQUALS);
            $activeSelection->addSelectOption(\__('inactive'), 'N', Operation::EQUALS);
            $filterNeukunden->assemble();

            $sortByOptions = [
                ['cName', \__('name')],
                ['cCode', \__('code')],
                ['nVerwendungenBisher', \__('curmaxusage')],
                ['dLastUse', \__('lastUsed')]
            ];


            $nKuponStandardCount  = $this->getCouponCount(Kupon::TYPE_STANDARD, $filterStandard->getWhereSQL());
            $nKuponVersandCount   = $this->getCouponCount(Kupon::TYPE_SHIPPING, $filterVersand->getWhereSQL());
            $nKuponNeukundenCount = $this->getCouponCount(Kupon::TYPE_NEWCUSTOMER, $filterNeukunden->getWhereSQL());
            $nKuponStandardTotal  = $this->getCouponCount(Kupon::TYPE_STANDARD);
            $nKuponVersandTotal   = $this->getCouponCount(Kupon::TYPE_SHIPPING);
            $nKuponNeukundenTotal = $this->getCouponCount(Kupon::TYPE_NEWCUSTOMER);

            $validExportTypes = [
                Kupon::TYPE_STANDARD,
                Kupon::TYPE_SHIPPING,
                Kupon::TYPE_NEWCUSTOMER
            ];
            $exportID         = Request::verifyGPDataString('exportcsv');
            if ($action === 'csvExport' && \in_array($exportID, $validExportTypes, true) && Form::validateToken()) {
                $export = new Export();
                if ($exportID === Kupon::TYPE_STANDARD) {
                    $export->export(
                        $exportID,
                        $exportID . '.csv',
                        function () use ($filterStandard) {
                            return $this->getExportableCoupons(Kupon::TYPE_STANDARD, $filterStandard->getWhereSQL());
                        },
                        [],
                        ['kKupon']
                    );
                } elseif ($exportID === Kupon::TYPE_STANDARD) {
                    $export->export(
                        $exportID,
                        $exportID . '.csv',
                        function () use ($filterVersand) {
                            return $this->getExportableCoupons(Kupon::TYPE_SHIPPING, $filterVersand->getWhereSQL());
                        },
                        [],
                        ['kKupon']
                    );
                } elseif ($exportID === Kupon::TYPE_NEWCUSTOMER) {
                    $export->export(
                        $exportID,
                        $exportID . '.csv',
                        function () use ($filterNeukunden) {
                            return $this->getExportableCoupons(Kupon::TYPE_NEWCUSTOMER, $filterNeukunden->getWhereSQL());
                        },
                        [],
                        ['kKupon']
                    );
                }
            }
            $paginationStandard  = (new Pagination(Kupon::TYPE_STANDARD))
                ->setSortByOptions($sortByOptions)
                ->setItemCount($nKuponStandardCount)
                ->assemble();
            $paginationVersand   = (new Pagination(Kupon::TYPE_SHIPPING))
                ->setSortByOptions($sortByOptions)
                ->setItemCount($nKuponVersandCount)
                ->assemble();
            $paginationNeukunden = (new Pagination(Kupon::TYPE_NEWCUSTOMER))
                ->setSortByOptions($sortByOptions)
                ->setItemCount($nKuponNeukundenCount)
                ->assemble();

            $standardCoupons    = $this->getCoupons(
                Kupon::TYPE_STANDARD,
                $filterStandard->getWhereSQL(),
                $paginationStandard->getOrderSQL(),
                $paginationStandard->getLimitSQL()
            );
            $shippingCoupons    = $this->getCoupons(
                Kupon::TYPE_SHIPPING,
                $filterVersand->getWhereSQL(),
                $paginationVersand->getOrderSQL(),
                $paginationVersand->getLimitSQL()
            );
            $newCustomerCoupons = $this->getCoupons(
                Kupon::TYPE_NEWCUSTOMER,
                $filterNeukunden->getWhereSQL(),
                $paginationNeukunden->getOrderSQL(),
                $paginationNeukunden->getLimitSQL()
            );

            $smarty->assign('tab', $tab)
                ->assign('oFilterStandard', $filterStandard)
                ->assign('oFilterVersand', $filterVersand)
                ->assign('oFilterNeukunden', $filterNeukunden)
                ->assign('oPaginationStandard', $paginationStandard)
                ->assign('oPaginationVersandkupon', $paginationVersand)
                ->assign('oPaginationNeukundenkupon', $paginationNeukunden)
                ->assign('oKuponStandard_arr', $standardCoupons)
                ->assign('oKuponVersandkupon_arr', $shippingCoupons)
                ->assign('oKuponNeukundenkupon_arr', $newCustomerCoupons)
                ->assign('nKuponStandardCount', $nKuponStandardTotal)
                ->assign('nKuponVersandCount', $nKuponVersandTotal)
                ->assign('nKuponNeukundenCount', $nKuponNeukundenTotal);
        }

        return $smarty->assign('action', $action)
            ->assign('couponTypes', Kupon::getCouponTypes())
            ->assign('route', $this->route)
            ->getResponse('kupons.tpl');
    }

    /**
     * @param int[] $ids
     * @return bool
     */
    private function loescheKupons(array $ids): bool
    {
        if (\count($ids) === 0) {
            return false;
        }
        $ids      = \array_map('\intval', $ids);
        $affected = $this->db->getAffectedRows(
            'DELETE tkupon, tkuponsprache, tkuponkunde, tkuponbestellung
            FROM tkupon
            LEFT JOIN tkuponsprache
              ON tkuponsprache.kKupon = tkupon.kKupon
            LEFT JOIN tkuponkunde
              ON tkuponkunde.kKupon = tkupon.kKupon
            LEFT JOIN tkuponbestellung
              ON tkuponbestellung.kKupon = tkupon.kKupon
            WHERE tkupon.kKupon IN(' . \implode(',', $ids) . ')'
        );

        return $affected >= \count($ids);
    }

    /**
     * @param int $id
     * @return array - key = lang-iso ; value = localized coupon name
     */
    private function getCouponNames(int $id): array
    {
        $names = [];
        if (!$id) {
            return $names;
        }
        foreach ($this->db->selectAll('tkuponsprache', 'kKupon', $id) as $coupon) {
            $names[$coupon->cISOSprache] = $coupon->cName;
        }

        return $names;
    }

    /**
     * @param string|null $selectedManufacturers
     * @return stdClass[]
     */
    private function getManufacturers(?string $selectedManufacturers = ''): array
    {
        $selected = Text::parseSSKint($selectedManufacturers);
        $items    = $this->db->getObjects('SELECT kHersteller, cName FROM thersteller');
        $langID   = Shop::getLanguageID();
        foreach ($items as $item) {
            $item->kHersteller = (int)$item->kHersteller;
            $manufacturer      = new Hersteller($item->kHersteller, $langID);
            $item->cName       = $manufacturer->cName;
            $item->selected    = \in_array($item->kHersteller, $selected, true);
            unset($manufacturer);
        }

        return $items;
    }

    /**
     * @param string|null $selectedCategories
     * @param int         $categoryID
     * @param int         $depth
     * @return stdClass[]
     */
    private function getCategories(?string $selectedCategories = '', int $categoryID = 0, int $depth = 0): array
    {
        $selected = Text::parseSSKint($selectedCategories);
        $arr      = [];
        $items    = $this->db->selectAll(
            'tkategorie',
            'kOberKategorie',
            $categoryID,
            'kKategorie, cName'
        );
        foreach ($items as $item) {
            $item->kKategorie = (int)$item->kKategorie;
            for ($i = 0; $i < $depth; $i++) {
                $item->cName = '--' . $item->cName;
            }
            $item->selected = \in_array($item->kKategorie, $selected, true);
            $arr[]          = $item;
            $arr            = \array_merge($arr, $this->getCategories($selectedCategories, $item->kKategorie, $depth + 1));
        }

        return $arr;
    }

    /**
     * Parse Datumsstring und formatiere ihn im DB-kompatiblen Standardformat
     *
     * @param string|null $string
     * @return string|null
     */
    private function normalizeDate(?string $string): ?string
    {
        if ($string === null || $string === '') {
            return null;
        }
        $date = \date_create($string);
        if ($date === false) {
            return $string;
        }

        return $date->format('Y-m-d H:i') . ':00';
    }

    /**
     * @param string $type
     * @param string $where
     * @param string $order
     * @param string $limit
     * @return stdClass[]
     */
    private function getRawCoupons(
        string $type = Kupon::TYPE_STANDARD,
        string $where = '',
        string $order = '',
        string $limit = ''
    ): array {
        return $this->db->getObjects(
            'SELECT k.*, MAX(kk.dErstellt) AS dLastUse
            FROM tkupon AS k
            LEFT JOIN tkuponkunde AS kk ON kk.kKupon = k.kKupon
            WHERE cKuponTyp = :type ' .
            ($where !== '' ? ' AND ' . $where : '') .
            'GROUP BY k.kKupon' .
            ($order !== '' ? ' ORDER BY ' . $order : '') .
            ($limit !== '' ? ' LIMIT ' . $limit : ''),
            ['type' => $type]
        );
    }

    /**
     * Get instances of existing coupons, each with some enhanced information that can be displayed
     *
     * @param string $type
     * @param string $whereSQL - an SQL WHERE clause (col1 = val1 AND vol2 LIKE ...)
     * @param string $orderSQL - an SQL ORDER BY clause (cName DESC)
     * @param string $limitSQL - an SQL LIMIT clause  (10,20)
     * @return Kupon[]
     */
    private function getCoupons(
        string $type = Kupon::TYPE_STANDARD,
        string $whereSQL = '',
        string $orderSQL = '',
        string $limitSQL = ''
    ): array {
        $raw = $this->getRawCoupons($type, $whereSQL, $orderSQL, $limitSQL);
        $res = [];
        foreach ($raw as $item) {
            $res[] = $this->getCoupon((int)$item->kKupon);
        }

        return $res;
    }

    /**
     * @param string $type
     * @param string $whereSQL
     * @return stdClass[]
     */
    private function getExportableCoupons(string $type = Kupon::TYPE_STANDARD, string $whereSQL = ''): array
    {
        $coupons = $this->getRawCoupons($type, $whereSQL);
        foreach ($coupons as $rawCoupon) {
            foreach ($this->getCouponNames((int)$rawCoupon->kKupon) as $iso => $name) {
                $rawCoupon->{'cName_' . $iso} = $name;
            }
        }

        return $coupons;
    }

    /**
     * Get an instance of an existing coupon with some enhanced information that can be displayed
     *
     * @param int $id
     * @return Kupon $oKupon
     */
    private function getCoupon(int $id): Kupon
    {
        $coupon = new Kupon($id);
        $coupon->augment();

        return $coupon;
    }

    /**
     * Create a fresh Kupon instance with default values to be edited
     *
     * @param string $type - Kupon::TYPE_STANDRAD, Kupon::TYPE_SHIPPING, Kupon::TYPE_NEWCUSTOMER
     * @return Kupon
     */
    private function createNewCoupon(string $type): Kupon
    {
        $coupon                        = new Kupon();
        $coupon->cKuponTyp             = $type;
        $coupon->cName                 = '';
        $coupon->fWert                 = 0.0;
        $coupon->cWertTyp              = 'festpreis';
        $coupon->cZusatzgebuehren      = 'N';
        $coupon->nGanzenWKRabattieren  = 1;
        $coupon->kSteuerklasse         = 1;
        $coupon->fMindestbestellwert   = 0.0;
        $coupon->cCode                 = '';
        $coupon->cLieferlaender        = '';
        $coupon->nVerwendungen         = 0;
        $coupon->nVerwendungenProKunde = 0;
        $coupon->cArtikel              = '';
        $coupon->kKundengruppe         = -1;
        $coupon->dGueltigAb            = \date_create()->format('Y-m-d H:i');
        $coupon->dGueltigBis           = '';
        $coupon->cAktiv                = 'Y';
        $coupon->cHersteller           = '-1';
        $coupon->cKategorien           = '-1';
        $coupon->cKunden               = '-1';
        $coupon->kKupon                = 0;

        $coupon->augment();

        return $coupon;
    }

    /**
     * Read coupon settings from the edit page form and create a Kupon instance of it
     *
     * @return Kupon
     * @throws Exception
     */
    private function createCouponFromInput(): Kupon
    {
        $input                         = Text::filterXSS($_POST);
        $coupon                        = new Kupon(Request::postInt('kKuponBearbeiten'));
        $coupon->cKuponTyp             = $input['cKuponTyp'];
        $coupon->cName                 = \htmlspecialchars($input['cName'], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET);
        $coupon->fWert                 = !empty($input['fWert']) ? (float)\str_replace(',', '.', $input['fWert']) : null;
        $coupon->cWertTyp              = !empty($input['cWertTyp']) ? $input['cWertTyp'] : null;
        $coupon->cZusatzgebuehren      = !empty($input['cZusatzgebuehren']) ? $input['cZusatzgebuehren'] : 'N';
        $coupon->nGanzenWKRabattieren  = Request::postInt('nGanzenWKRabattieren');
        $coupon->kSteuerklasse         = !empty($input['kSteuerklasse']) ? (int)$input['kSteuerklasse'] : null;
        $coupon->fMindestbestellwert   = (float)\str_replace(',', '.', $input['fMindestbestellwert']);
        $coupon->cCode                 = !empty($input['cCode']) ? $input['cCode'] : '';
        $coupon->cLieferlaender        = !empty($input['cLieferlaender'])
            ? \mb_convert_case($input['cLieferlaender'], \MB_CASE_UPPER)
            : '';
        $coupon->nVerwendungen         = Request::postInt('nVerwendungen');
        $coupon->nVerwendungenProKunde = Request::postInt('nVerwendungenProKunde');
        $coupon->cArtikel              = !empty($input['cArtikel']) ? ';' . \trim($input['cArtikel'], ";\t\n\r") . ';' : '';
        $coupon->cHersteller           = '-1';
        $coupon->kKundengruppe         = Request::postInt('kKundengruppe');
        $coupon->dGueltigAb            = $this->normalizeDate(!empty($input['dGueltigAb'])
            ? $input['dGueltigAb']
            : \date_create()->format('Y-m-d H:i') . ':00');
        $coupon->dGueltigBis           = $this->normalizeDate(!empty($input['dGueltigBis']) ? $input['dGueltigBis'] : '');
        $coupon->cAktiv                = Request::postVar('cAktiv') === 'Y' ? 'Y' : 'N';
        $coupon->cKategorien           = '-1';
        if ($coupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER) {
            $coupon->cKunden = '-1';
        }
        if (Request::postVar('bOpenEnd') === 'Y') {
            $coupon->dGueltigBis = null;
        } elseif (!empty($input['dDauerTage'])) {
            $coupon->dGueltigBis     = '';
            $actualTimestamp         = \date_create();
            $actualTimestampEndofDay = \date_time_set($actualTimestamp, 23, 59, 59);
            $setDays                 = new DateInterval('P' . $input['dDauerTage'] . 'D');
            $coupon->dGueltigBis     = \date_add($actualTimestampEndofDay, $setDays)->format('Y-m-d H:i:s');
        }
        $manufacturers = \array_map('\intval', ($input['kHersteller'] ?? []));
        $categories    = \array_map('\intval', ($input['kKategorien'] ?? []));
        if (!\in_array(-1, $manufacturers, true)) {
            $coupon->cHersteller = Text::createSSK($input['kHersteller']);
        }
        if (!\in_array(-1, $categories, true)) {
            $coupon->cKategorien = Text::createSSK($input['kKategorien']);
        }
        if (!empty($input['cKunden']) && $input['cKunden'] !== '-1') {
            $coupon->cKunden = \trim($input['cKunden'], ";\t\n\r") . ';';
        }
        if (isset($input['couponCreation'])) {
            $massCreationCoupon                  = new stdClass();
            $massCreationCoupon->cActiv          = Request::postInt('couponCreation');
            $massCreationCoupon->numberOfCoupons = ($massCreationCoupon->cActiv === 1 && !empty($input['numberOfCoupons']))
                ? (int)$input['numberOfCoupons']
                : 2;
            $massCreationCoupon->lowerCase       = ($massCreationCoupon->cActiv === 1 && !empty($input['lowerCase']));
            $massCreationCoupon->upperCase       = ($massCreationCoupon->cActiv === 1 && !empty($input['upperCase']));
            $massCreationCoupon->numbersHash     = ($massCreationCoupon->cActiv === 1 && !empty($input['numbersHash']));
            $massCreationCoupon->hashLength      = ($massCreationCoupon->cActiv === 1 && !empty($input['hashLength']))
                ? (int)$input['hashLength']
                : 4;
            $massCreationCoupon->prefixHash      = ($massCreationCoupon->cActiv === 1 && !empty($input['prefixHash']))
                ? $input['prefixHash']
                : '';
            $massCreationCoupon->suffixHash      = ($massCreationCoupon->cActiv === 1 && !empty($input['suffixHash']))
                ? $input['suffixHash']
                : '';
            $coupon->massCreationCoupon          = $massCreationCoupon;
        }

        return $coupon;
    }

    /**
     * Get the number of existing coupons of type $cKuponTyp
     *
     * @param string $type
     * @param string $whereSQL
     * @return int
     */
    private function getCouponCount(string $type = Kupon::TYPE_STANDARD, string $whereSQL = ''): int
    {
        return $this->db->getSingleInt(
            'SELECT COUNT(kKupon) AS cnt
            FROM tkupon
            WHERE cKuponTyp = :tp' .
            ($whereSQL !== '' ? ' AND ' . $whereSQL : ''),
            'cnt',
            ['tp' => $type]
        );
    }

    /**
     * Save a new or already existing coupon in the DB
     *
     * @param Kupon           $coupon
     * @param LanguageModel[] $languages
     * @return int - 0 on failure ; kKupon on success
     */
    private function saveCoupon(Kupon $coupon, array $languages)
    {
        if ((int)$coupon->kKupon > 0) {
            // vorhandener Kupon
            $res = $coupon->update() === -1 ? 0 : $coupon->kKupon;
        } else {
            // neuer Kupon
            $coupon->nVerwendungenBisher = 0;
            $coupon->dErstellt           = 'NOW()';
            if (isset($coupon->massCreationCoupon)) {
                $massCreationCoupon = $coupon->massCreationCoupon;
                $coupon->kKupon     = [];
                unset($coupon->massCreationCoupon, $_POST['informieren']);
                for ($i = 1; $i <= $massCreationCoupon->numberOfCoupons; $i++) {
                    if ($coupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER) {
                        $coupon->cCode = $coupon->generateCode(
                            $massCreationCoupon->hashLength,
                            $massCreationCoupon->lowerCase,
                            $massCreationCoupon->upperCase,
                            $massCreationCoupon->numbersHash,
                            $massCreationCoupon->prefixHash,
                            $massCreationCoupon->suffixHash
                        );
                    }
                    unset($coupon->translationList);
                    $coupon->kKupon[] = (int)$coupon->save();
                }
            } else {
                if ($coupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER && $coupon->cCode === '') {
                    $coupon->cCode = $coupon->generateCode();
                }
                unset($coupon->translationList);
                $coupon->kKupon = (int)$coupon->save();
            }
            $res = $coupon->kKupon;
        }

        if ($res > 0) {
            // Kupon-Sprachen aktualisieren
            if (\is_array($coupon->kKupon)) {
                foreach ($coupon->kKupon as $couponID) {
                    $this->db->delete('tkuponsprache', 'kKupon', $couponID);
                    foreach ($languages as $language) {
                        $code          = $language->getIso();
                        $postVarName   = 'cName_' . $code;
                        $localizedName = Request::postVar($postVarName, '') !== ''
                            ? \htmlspecialchars($_POST[$postVarName], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET)
                            : $coupon->cName;

                        $localized              = new stdClass();
                        $localized->kKupon      = $couponID;
                        $localized->cISOSprache = $code;
                        $localized->cName       = Text::filterXSS($localizedName);
                        $this->db->insert('tkuponsprache', $localized);
                    }
                }
            } else {
                $this->db->delete('tkuponsprache', 'kKupon', $coupon->kKupon);
                foreach ($languages as $language) {
                    $code          = $language->getIso();
                    $postVarName   = 'cName_' . $code;
                    $localizedName = Request::postVar($postVarName, '') !== ''
                        ? \htmlspecialchars($_POST[$postVarName], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET)
                        : $coupon->cName;

                    $localized              = new stdClass();
                    $localized->kKupon      = $coupon->kKupon;
                    $localized->cISOSprache = $code;
                    $localized->cName       = Text::filterXSS($localizedName);
                    $this->db->insert('tkuponsprache', $localized);
                }
            }
        }

        return $res;
    }

    /**
     * Send notification emails to all customers admitted to this Kupon
     *
     * @param Kupon $coupon
     * @deprecated since 5.2.0 (disabled via template SHOP-5794)
     */
    private function informCouponCustomers(Kupon $coupon): void
    {
        $coupon->augment();
        $defaultLang     = $this->db->select('tsprache', 'cShopStandard', 'Y');
        $defaultCurrency = $this->db->select('twaehrung', 'cStandard', 'Y');
        $defaultOptions  = Artikel::getDefaultOptions();
        // lokalisierter Kuponwert und MBW
        $coupon->cLocalizedWert = $coupon->cWertTyp === 'festpreis'
            ? Preise::getLocalizedPriceString($coupon->fWert, $defaultCurrency, false)
            : $coupon->fWert . ' %';
        $coupon->cLocalizedMBW  = Preise::getLocalizedPriceString($coupon->fMindestbestellwert, $defaultCurrency, false);
        // kKunde-Array aller auserwaehlten Kunden
        $customerIDs     = Text::parseSSKint($coupon->cKunden);
        $customerData    = $this->db->getInts(
            'SELECT kKunde
                FROM tkunde
                WHERE TRUE
                    ' . ((int)$coupon->kKundengruppe === -1
                    ? 'AND TRUE'
                    : 'AND kKundengruppe = ' . (int)$coupon->kKundengruppe) . '
                    ' . ($coupon->cKunden === '-1'
                    ? 'AND TRUE'
                    : 'AND kKunde IN (' . \implode(',', $customerIDs) . ')'),
            'kKunde'
        );
        $productIDs      = [];
        $manufacturerIDs = Text::parseSSK($coupon->cHersteller);
        $itemNumbers     = Text::parseSSK($coupon->cArtikel);
        if (\count($itemNumbers) > 0) {
            $itemNumbers = \array_map(static function ($e) {
                return '"' . $e . '"';
            }, $itemNumbers);
            $productIDs  = $this->db->getInts(
                'SELECT kArtikel
                    FROM tartikel
                    WHERE cArtNr IN (' . \implode(',', $itemNumbers) . ')',
                'kArtikel'
            );
        }
        foreach ($customerData as $customerID) {
            $customer = new Customer($customerID);
            $language = Shop::Lang()->getIsoFromLangID($customer->kSprache);
            if (!$language) {
                $language = $defaultLang;
            }
            $localized  = $this->db->select(
                'tkuponsprache',
                ['kKupon', 'cISOSprache'],
                [$coupon->kKupon, $language->cISO]
            );
            $categories = [];
            if ($coupon->cKategorien !== '-1') {
                foreach (Text::parseSSKint($coupon->cKategorien) as $categoryID) {
                    if ($categoryID <= 0) {
                        continue;
                    }
                    $category       = new Kategorie($categoryID, $customer->kSprache, $customer->kKundengruppe);
                    $category->cURL = $category->cURLFull;
                    $categories[]   = $category;
                }
            }
            $products = [];
            foreach ($productIDs as $productID) {
                $product = new Artikel($this->db);
                $product->fuelleArtikel(
                    $productID,
                    $defaultOptions,
                    $customer->kKundengruppe,
                    $customer->kSprache,
                    true
                );
                $products[] = $product;
            }
            $manufacturers = [];
            foreach ($manufacturerIDs as $manufacturerID) {
                $manufacturers[] = new Hersteller($manufacturerID, $customer->kSprache);
            }
            // put all together
            $coupon->Kategorien      = $categories;
            $coupon->Artikel         = $products;
            $coupon->AngezeigterName = $localized->cName;
            $coupon->Hersteller      = $manufacturers;
            $obj                     = new stdClass();
            $obj->tkupon             = $coupon;
            $obj->tkunde             = $customer;
            $mailer                  = Shop::Container()->get(Mailer::class);
            $mail                    = new Mail();
            $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_KUPON, $obj));
        }
    }

    /**
     * Set all Coupons with an outdated dGueltigBis to cAktiv = 'N'
     */
    private function deactivateOutdatedCoupons(): void
    {
        $this->db->query(
            "UPDATE tkupon
            SET cAktiv = 'N'
            WHERE dGueltigBis > 0
            AND dGueltigBis <= NOW()"
        );
    }

    /**
     * Set all Coupons that reached nVerwendungenBisher to nVerwendungen to cAktiv = 'N'
     */
    private function deactivateExhaustedCoupons(): void
    {
        $this->db->query(
            "UPDATE tkupon
            SET cAktiv = 'N'
            WHERE nVerwendungen > 0
            AND nVerwendungenBisher >= nVerwendungen"
        );
    }
}
