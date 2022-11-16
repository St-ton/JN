<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\Backend\Permissions;
use JTL\Checkout\Versandart;
use JTL\Country\Country;
use JTL\Country\Manager;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Pagination\Pagination;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Services\JTL\CountryService;
use JTL\Services\JTL\CountryServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ShippingMethodsController
 * @package JTL\Router\Controller\Backend
 */
class ShippingMethodsController extends AbstractBackendController
{
    /**
     * @var CountryServiceInterface
     */
    private CountryServiceInterface $countryService;

    /**
     * @var stdClass
     */
    private stdClass $defaultCurrency;

    /**
     * @var mixed
     */
    private $shippingType = null;

    /**
     * @var mixed
     */
    private $shippingMethod = null;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::ORDER_SHIPMENT_VIEW);
        $this->getText->loadAdminLocale('pages/versandarten');
        Tax::setTaxRates();
        $this->step            = 'uebersicht';
        $this->defaultCurrency = $this->db->select('twaehrung', 'cStandard', 'Y');
        $taxRateKeys           = \array_keys($_SESSION['Steuersatz']);
        $this->countryService  = Shop::Container()->getCountryService();
        $this->assignScrollPosition();

        $postData                   = Text::filterXSS($_POST);
        $manager                    = new Manager(
            $this->db,
            $smarty,
            $this->countryService,
            $this->cache,
            $this->alertService,
            $this->getText
        );
        $missingShippingClassCombis = $this->getMissingShippingClassCombi();
        $smarty->assign('missingShippingClassCombis', $missingShippingClassCombis);
        if (Form::validateToken()) {
            if (Request::postInt('neu') === 1 && Request::postInt('kVersandberechnung') > 0) {
                $this->step = 'neue Versandart';
            }
            if (Request::postInt('kVersandberechnung') > 0) {
                $this->shippingType = $this->getShippingTypes(Request::verifyGPCDataInt('kVersandberechnung'));
            }
            if (Request::postInt('del') > 0) {
                $oldShippingMethod = $this->db->select('tversandart', 'kVersandart', (int)$postData['del']);
                Versandart::deleteInDB((int)$postData['del']);
                $manager->updateRegistrationCountries(\explode(' ', \trim($oldShippingMethod->cLaender ?? '')));
                $this->alertService->addSuccess(\__('successShippingMethodDelete'), 'successShippingMethodDelete');
                $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);
            }
            if (Request::postInt('edit') > 0) {
                $this->shippingType = $this->actionEdit();
            }
            if (Request::postInt('clone') > 0) {
                $this->step = 'uebersicht';
                if (Versandart::cloneShipping((int)$postData['clone'])) {
                    $this->alertService->addSuccess(
                        \__('successShippingMethodDuplicated'),
                        'successShippingMethodDuplicated'
                    );
                    $this->cache->flushTags([\CACHING_GROUP_OPTION]);
                } else {
                    $this->alertService->addError(
                        \__('errorShippingMethodDuplicated'),
                        'errorShippingMethodDuplicated'
                    );
                }
            }

            if (isset($_GET['cISO']) && Request::getInt('zuschlag') === 1 && Request::getInt('kVersandart') > 0) {
                $this->step = 'Zuschlagsliste';

                $pagination = (new Pagination('surchargeList'))
                    ->setRange(4)
                    ->setItemArray((new Versandart(Request::getInt('kVersandart')))
                        ->getShippingSurchargesForCountry($_GET['cISO']))
                    ->assemble();

                $smarty->assign('surcharges', $pagination->getPageItems())
                    ->assign('pagination', $pagination);
            }

            if (Request::postInt('neueVersandart') > 0) {
                $this->shippingMethod = $this->createOrUpdate($postData, $manager);
                if (($this->shippingMethod->methodID ?? 0) > 0
                    && Request::postVar('saveAndContinue')
                ) {
                    $this->shippingType = $this->actionEdit($this->shippingMethod->methodID);
                }
            }
            $this->cache->flush(CountryService::CACHE_ID);
        }
        if ($this->step === 'neue Versandart' && $this->shippingType !== null) {
            $this->actionCreateNew();
        }
        if ($this->step === 'uebersicht') {
            $this->actionOverview();
        }
        if ($this->step === 'Zuschlagsliste') {
            $this->actionSurchargeList();
        }
        $languages = LanguageHelper::getInstance()->gibInstallierteSprachen();
        $tmpID     = (int)($this->shippingMethod->kVersandart ?? 0);
        return $smarty->assign('fSteuersatz', $_SESSION['Steuersatz'][$taxRateKeys[0]])
            ->assign('waehrung', $this->defaultCurrency->cName)
            ->assign('oWaehrung', $this->db->select('twaehrung', 'cStandard', 'Y'))
            ->assign('continents', $this->countryService->getCountriesGroupedByContinent(
                true,
                \explode(' ', $this->shippingMethod->cLaender ?? '')
            ))
            ->assign('customerGroups', CustomerGroup::getGroups())
            ->assign('oVersandartSpracheAssoc_arr', $this->getShippingLanguage($tmpID, $languages))
            ->assign('gesetzteVersandklassen', isset($this->shippingMethod->cVersandklassen)
                ? $this->getActiveShippingClasses($this->shippingMethod->cVersandklassen)
                : null)
            ->assign('gesetzteKundengruppen', isset($this->shippingMethod->cKundengruppen)
                ? $this->getActiveCustomerGroups($this->shippingMethod->cKundengruppen)
                : null)
            ->assign('step', $this->step)
            ->assign('route', $this->route)
            ->getResponse('versandarten.tpl');
    }

    /**
     * @param float|string $price
     * @param float|string $taxRate
     * @return float
     * @former berechneVersandpreisBrutto()
     */
    private function getShippingCostsGross($price, $taxRate): float
    {
        return $price > 0
            ? \round((float)($price * ((100 + $taxRate) / 100)), 2)
            : 0.0;
    }

    /**
     * @param float|string $price
     * @param float|string $taxRate
     * @return float
     * @former berechneVersandpreisNetto()
     */
    private function getShippingCostsNet($price, $taxRate): float
    {
        return $price > 0
            ? \round($price * ((100 / (100 + $taxRate)) * 100) / 100, 2)
            : 0.0;
    }

    /**
     * @param array  $objects
     * @param string $key
     * @return array
     */
    private function reorganizeObjectArray(array $objects, string $key): array
    {
        $res = [];
        foreach ($objects as $obj) {
            $arr  = \get_object_vars($obj);
            $keys = \array_keys($arr);
            if (\in_array($key, $keys, true)) {
                $res[$obj->$key]           = new stdClass();
                $res[$obj->$key]->checked  = 'checked';
                $res[$obj->$key]->selected = 'selected';
                foreach ($keys as $k) {
                    if ($key !== $k) {
                        $res[$obj->$key]->$k = $obj->$k;
                    }
                }
            }
        }

        return $res;
    }

    /**
     * @param array $arr
     * @return array
     * @former P()
     */
    public function transformItem($arr): array
    {
        $newArr = [];
        if (\is_array($arr)) {
            foreach ($arr as $ele) {
                $newArr = $this->buildObjectData($newArr, $ele);
            }
        }

        return $newArr;
    }

    /**
     * @param array  $arr
     * @param object $key
     * @return array
     * @former bauePot()
     */
    private function buildObjectData($arr, $key): array
    {
        foreach ($arr as $val) {
            $obj                 = new stdClass();
            $obj->kVersandklasse = $val->kVersandklasse . '-' . $key->kVersandklasse;
            $obj->cName          = $val->cName . ', ' . $key->cName;
            $arr[]               = $obj;
        }
        $arr[] = $key;

        return $arr;
    }

    /**
     * @param string $shippingClasses
     * @return array
     * @former gibGesetzteVersandklassen()
     */
    private function getActiveShippingClasses(string $shippingClasses): array
    {
        if (\trim($shippingClasses) === '-1') {
            return ['alle' => true];
        }
        $gesetzteVK = [];
        $uniqueIDs  = [];
        $classes    = \explode(' ', \trim($shippingClasses));
        // $cVersandklassen is a string like "1 3-4 5-6-7 6-8 7-8 3-7 3-8 5-6 5-7"
        foreach ($classes as $idString) {
            // we want the single kVersandklasse IDs to reduce the possible amount of combinations
            foreach (\explode('-', $idString) as $kVersandklasse) {
                $uniqueIDs[] = (int)$kVersandklasse;
            }
        }
        $items = $this->transformItem($this->db->getObjects(
            'SELECT * 
                FROM tversandklasse
                WHERE kVersandklasse IN (' . \implode(',', $uniqueIDs) . ')  
                ORDER BY kVersandklasse'
        ));
        foreach ($items as $vk) {
            $gesetzteVK[$vk->kVersandklasse] = \in_array($vk->kVersandklasse, $classes, true);
        }

        return $gesetzteVK;
    }

    /**
     * @param string $shippingClasses
     * @return array
     * @former gibGesetzteVersandklassenUebersicht()
     */
    private function getActiveShippingClassesOverview(string $shippingClasses): array
    {
        if (\trim($shippingClasses) === '-1') {
            return ['Alle'];
        }
        $active    = [];
        $uniqueIDs = [];
        $classes   = \explode(' ', \trim($shippingClasses));
        // $cVersandklassen is a string like "1 3-4 5-6-7 6-8 7-8 3-7 3-8 5-6 5-7"
        foreach ($classes as $idString) {
            // we want the single kVersandklasse IDs to reduce the possible amount of combinations
            foreach (\explode('-', $idString) as $kVersandklasse) {
                $uniqueIDs[] = (int)$kVersandklasse;
            }
        }
        $items = $this->transformItem($this->db->getObjects(
            'SELECT * 
                FROM tversandklasse 
                WHERE kVersandklasse IN (' . \implode(',', $uniqueIDs) . ')
                ORDER BY kVersandklasse'
        ));
        foreach ($items as $item) {
            if (\in_array($item->kVersandklasse, $classes, true)) {
                $active[] = $item->cName;
            }
        }

        return $active;
    }

    /**
     * @param string $customerGroupsString
     * @return array
     * @former gibGesetzteKundengruppen()
     */
    private function getActiveCustomerGroups(string $customerGroupsString): array
    {
        $activeGroups = [];
        $groups       = Text::parseSSKint($customerGroupsString);
        $groupData    = $this->db->getInts(
            'SELECT kKundengruppe
            FROM tkundengruppe
            ORDER BY kKundengruppe',
            'kKundengruppe'
        );
        foreach ($groupData as $id) {
            $activeGroups[$id] = \in_array($id, $groups, true);
        }
        $activeGroups['alle'] = $customerGroupsString === '-1';

        return $activeGroups;
    }

    /**
     * @param int             $shippingMethodID
     * @param LanguageModel[] $languages
     * @return array
     */
    private function getShippingLanguage(int $shippingMethodID, array $languages): array
    {
        $localized        = [];
        $localizedMethods = $this->db->selectAll(
            'tversandartsprache',
            'kVersandart',
            $shippingMethodID
        );
        foreach ($languages as $language) {
            $localized[$language->getCode()] = new stdClass();
        }
        foreach ($localizedMethods as $localizedMethod) {
            if (isset($localizedMethod->kVersandart) && $localizedMethod->kVersandart > 0) {
                $localized[$localizedMethod->cISOSprache] = $localizedMethod;
            }
        }

        return $localized;
    }

    /**
     * @param int $feeID
     * @return array
     * @former getZuschlagNames()
     */
    private function getFeeNames(int $feeID): array
    {
        $names = [];
        if (!$feeID) {
            return $names;
        }
        $localized = $this->db->selectAll(
            'tversandzuschlagsprache',
            'kVersandzuschlag',
            $feeID
        );
        foreach ($localized as $name) {
            $names[$name->cISOSprache] = $name->cName;
        }

        return $names;
    }

    /**
     * @param array $shipClasses
     * @param int   $length
     * @return array
     */
    private function getCombinations(array $shipClasses, int $length): array
    {
        $baselen = \count($shipClasses);
        if ($baselen === 0) {
            return [];
        }
        if ($length === 1) {
            $return = [];
            foreach ($shipClasses as $b) {
                $return[] = [$b];
            }

            return $return;
        }

        // get one level lower combinations
        $oneLevelLower = $this->getCombinations($shipClasses, $length - 1);
        // for every one level lower combinations add one element to them
        // that the last element of a combination is preceeded by the element
        // which follows it in base array if there is none, does not add
        $newCombs = [];
        foreach ($oneLevelLower as $oll) {
            $lastEl = $oll[$length - 2];
            $found  = false;
            foreach ($shipClasses as $key => $b) {
                if ($b === $lastEl) {
                    $found = true;
                    continue;
                    // last element found
                }
                if ($found === true && $key < $baselen) {
                    // add to combinations with last element
                    $tmp              = $oll;
                    $newCombination   = \array_slice($tmp, 0);
                    $newCombination[] = $b;
                    $newCombs[]       = \array_slice($newCombination, 0);
                }
            }
        }

        return $newCombs;
    }

    /**
     * @return array|int -1 if too many shipping classes exist
     */
    private function getMissingShippingClassCombi()
    {
        $shippingClasses         = $this->db->selectAll('tversandklasse', [], [], 'kVersandklasse');
        $combinationsInShippings = $this->db->selectAll('tversandart', [], [], 'cVersandklassen');
        $shipClasses             = [];
        $combinationInUse        = [];

        foreach ($shippingClasses as $sc) {
            $shipClasses[] = $sc->kVersandklasse;
        }

        foreach ($combinationsInShippings as $com) {
            foreach (\explode(' ', \trim($com->cVersandklassen)) as $class) {
                $combinationInUse[] = \trim($class);
            }
        }

        // if a shipping method is valid for all classes return
        if (\in_array('-1', $combinationInUse, false)) {
            return [];
        }

        $len = \count($shipClasses);
        if ($len > \SHIPPING_CLASS_MAX_VALIDATION_COUNT) {
            return -1;
        }

        $possibleShippingClassCombinations = [];
        for ($i = 1; $i <= $len; $i++) {
            $result = $this->getCombinations($shipClasses, $i);
            foreach ($result as $c) {
                $possibleShippingClassCombinations[] = \implode('-', $c);
            }
        }
        $res = \array_diff($possibleShippingClassCombinations, $combinationInUse);
        foreach ($res as &$mscc) {
            $mscc = $this->getActiveShippingClassesOverview($mscc)[0];
        }

        return $res;
    }

    /**
     * @param int|null $shippingTypeID
     * @return array|mixed
     */
    private function getShippingTypes(int $shippingTypeID = null)
    {
        if ($shippingTypeID !== null) {
            $shippingTypes = $this->db->getCollection(
                'SELECT *
                    FROM tversandberechnung
                    WHERE kVersandberechnung = :shippingTypeID
                    ORDER BY cName',
                ['shippingTypeID' => $shippingTypeID]
            );
        } else {
            $shippingTypes = $this->db->getCollection(
                'SELECT *
                    FROM tversandberechnung
                    ORDER BY cName'
            );
        }
        $shippingTypes->each(static function (stdClass $e): void {
            $e->kVersandberechnung = (int)$e->kVersandberechnung;
            $e->cName              = \__('shippingType_' . $e->cModulId);
        });

        return $shippingTypeID === null ? $shippingTypes->toArray() : $shippingTypes->first();
    }

    /**
     * @return void
     */
    private function actionSurchargeList(): void
    {
        $iso      = $_GET['cISO'] ?? $postData['cISO'] ?? null;
        $methodID = Request::getInt('kVersandart');
        if (isset($postData['kVersandart'])) {
            $methodID = Request::postInt('kVersandart');
        }
        $this->shippingMethod = $this->db->select('tversandart', 'kVersandart', $methodID);
        $fees                 = $this->db->selectAll(
            'tversandzuschlag',
            ['kVersandart', 'cISO'],
            [(int)$this->shippingMethod->kVersandart, $iso],
            '*',
            'fZuschlag'
        );
        foreach ($fees as $item) {
            $item->kVersandzuschlag = (int)$item->kVersandzuschlag;
            $item->kVersandart      = (int)$item->kVersandart;
            $item->zuschlagplz      = $this->db->selectAll(
                'tversandzuschlagplz',
                'kVersandzuschlag',
                $item->kVersandzuschlag
            );
            $item->angezeigterName  = $this->getFeeNames($item->kVersandzuschlag);
        }
        $this->smarty->assign('Versandart', $this->shippingMethod)
            ->assign('Land', $this->countryService->getCountry($iso))
            ->assign('Zuschlaege', $fees);
    }

    private function actionOverview(): void
    {
        $taxRateKeys     = \array_keys($_SESSION['Steuersatz']);
        $customerGroups  = $this->db->getObjects(
            'SELECT kKundengruppe, cName FROM tkundengruppe ORDER BY kKundengruppe'
        );
        $shippingMethods = $this->db->getObjects('SELECT * FROM tversandart ORDER BY nSort, cName');
        foreach ($shippingMethods as $method) {
            $method->versandartzahlungsarten = $this->db->getObjects(
                'SELECT tversandartzahlungsart.*
                FROM tversandartzahlungsart
                JOIN tzahlungsart
                    ON tzahlungsart.kZahlungsart = tversandartzahlungsart.kZahlungsart
                WHERE tversandartzahlungsart.kVersandart = :sid
                ORDER BY tzahlungsart.cAnbieter, tzahlungsart.nSort, tzahlungsart.cName',
                ['sid' => (int)$method->kVersandart]
            );

            foreach ($method->versandartzahlungsarten as $smp) {
                $smp->zahlungsart  = $this->db->select(
                    'tzahlungsart',
                    'kZahlungsart',
                    (int)$smp->kZahlungsart,
                    'nActive',
                    1
                );
                $smp->cAufpreisTyp = $smp->cAufpreisTyp === 'prozent' ? '%' : '';
                $pluginID          = PluginHelper::getIDByModuleID($smp->zahlungsart->cModulId);
                if ($pluginID > 0) {
                    try {
                        $this->getText->loadPluginLocale(
                            'base',
                            PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                        );
                    } catch (InvalidArgumentException) {
                        $this->getText->loadAdminLocale('pages/zahlungsarten');
                        $this->alertService->addWarning(
                            \sprintf(
                                \__('Plugin for payment method not found'),
                                $smp->zahlungsart->cName,
                                $smp->zahlungsart->cAnbieter
                            ),
                            'notfound_' . $pluginID,
                            [
                                'linkHref' => Shop::getURL(true) . $this->route,
                                'linkText' => \__('paymentTypesOverview')
                            ]
                        );
                        continue;
                    }
                }
                $smp->zahlungsart->cName     = \__($smp->zahlungsart->cName);
                $smp->zahlungsart->cAnbieter = \__($smp->zahlungsart->cAnbieter);
            }
            $method->versandartstaffeln         = $this->db->selectAll(
                'tversandartstaffel',
                'kVersandart',
                (int)$method->kVersandart,
                '*',
                'fBis'
            );
            $method->fPreisBrutto               = $this->getShippingCostsGross(
                $method->fPreis,
                $_SESSION['Steuersatz'][$taxRateKeys[0]]
            );
            $method->fVersandkostenfreiAbXNetto = $this->getShippingCostsNet(
                $method->fVersandkostenfreiAbX,
                $_SESSION['Steuersatz'][$taxRateKeys[0]]
            );
            $method->fDeckelungBrutto           = $this->getShippingCostsGross(
                $method->fDeckelung,
                $_SESSION['Steuersatz'][$taxRateKeys[0]]
            );
            foreach ($method->versandartstaffeln as $j => $oVersandartstaffeln) {
                $method->versandartstaffeln[$j]->fPreisBrutto = $this->getShippingCostsGross(
                    $oVersandartstaffeln->fPreis,
                    $_SESSION['Steuersatz'][$taxRateKeys[0]]
                );
            }

            $method->versandberechnung = $this->getShippingTypes((int)$method->kVersandberechnung);
            $method->versandklassen    = $this->getActiveShippingClassesOverview($method->cVersandklassen);
            if ($method->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl') {
                $method->einheit = 'kg';
            }
            if ($method->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl') {
                $method->einheit = $this->defaultCurrency->cName;
            }
            if ($method->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
                $method->einheit = 'Stück';
            }
            $method->countries                  = new Collection();
            $method->shippingSurchargeCountries = \array_column($this->db->getArrays(
                'SELECT DISTINCT cISO FROM tversandzuschlag WHERE kVersandart = :shippingMethodID',
                ['shippingMethodID' => (int)$method->kVersandart]
            ), 'cISO');
            foreach (\explode(' ', \trim($method->cLaender)) as $item) {
                if (($country = $this->countryService->getCountry($item)) !== null) {
                    $method->countries->push($country);
                }
            }
            $method->countries               = $method->countries->sortBy(static function (Country $country): string {
                return $country->getName();
            });
            $method->cKundengruppenName_arr  = [];
            $method->oVersandartSprachen_arr = $this->db->selectAll(
                'tversandartsprache',
                'kVersandart',
                (int)$method->kVersandart,
                'cName',
                'cISOSprache'
            );
            foreach (Text::parseSSKint($method->cKundengruppen) as $customerGroupID) {
                if ($customerGroupID === -1) {
                    $method->cKundengruppenName_arr[] = \__('allCustomerGroups');
                } else {
                    foreach ($customerGroups as $customerGroup) {
                        if ((int)$customerGroup->kKundengruppe === $customerGroupID) {
                            $method->cKundengruppenName_arr[] = $customerGroup->cName;
                        }
                    }
                }
            }
        }

        $missingShippingClassCombis = $this->getMissingShippingClassCombi();
        if (!empty($missingShippingClassCombis)) {
            $error = $this->smarty->assign('missingShippingClassCombis', $missingShippingClassCombis)
                ->fetch('tpl_inc/versandarten_fehlende_kombis.tpl');
            $this->alertService->addError($error, 'errorMissingShippingClassCombis');
        }

        $this->smarty->assign('versandberechnungen', $this->getShippingTypes())
            ->assign('versandarten', $shippingMethods);
    }

    private function actionCreateNew(): void
    {
        if ($this->shippingType->cModulId === 'vm_versandberechnung_gewicht_jtl') {
            $this->smarty->assign('einheit', 'kg');
        }
        if ($this->shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl') {
            $this->smarty->assign('einheit', $this->defaultCurrency->cName);
        }
        if ($this->shippingType->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
            $this->smarty->assign('einheit', 'Stück');
        }
        // prevent "unusable" payment methods from displaying them in the config section (mainly the null-payment)
        $paymentMethods = $this->db->selectAll(
            'tzahlungsart',
            ['nActive', 'nNutzbar'],
            [1, 1],
            '*',
            'cAnbieter, nSort, cName, cModulId'
        );
        foreach ($paymentMethods as $paymentMethod) {
            $pluginID = PluginHelper::getIDByModuleID($paymentMethod->cModulId);
            if ($pluginID > 0) {
                try {
                    $this->getText->loadPluginLocale(
                        'base',
                        PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                    );
                } catch (InvalidArgumentException) {
                    $this->getText->loadAdminLocale('pages/zahlungsarten');
                    $this->alertService->addWarning(
                        \sprintf(
                            \__('Plugin for payment method not found'),
                            $paymentMethod->cName,
                            $paymentMethod->cAnbieter
                        ),
                        'notfound_' . $pluginID,
                        [
                            'linkHref' => Shop::getURL(true) . $this->route,
                            'linkText' => \__('paymentTypesOverview')
                        ]
                    );
                    continue;
                }
            }
            $paymentMethod->cName     = \__($paymentMethod->cName);
            $paymentMethod->cAnbieter = \__($paymentMethod->cAnbieter);
        }

        $this->smarty->assign('versandKlassen', $this->db->selectAll('tversandklasse', [], [], '*', 'kVersandklasse'))
            ->assign('zahlungsarten', $paymentMethods)
            ->assign('versandlaender', $this->countryService->getCountrylist())
            ->assign('versandberechnung', $this->shippingType)
            ->assign('waehrung', $this->defaultCurrency->cName);
    }

    /**
     * @return array|mixed
     */
    private function actionEdit(?int $shippingId = null)
    {
        $shippingId           = $shippingId ?? Request::postInt('edit');
        $this->step           = 'neue Versandart';
        $this->shippingMethod = $this->db->select('tversandart', 'kVersandart', $shippingId);
        $mappedMethods        = $this->db->selectAll(
            'tversandartzahlungsart',
            'kVersandart',
            $shippingId,
            '*',
            'kZahlungsart'
        );
        $shippingScales       = $this->db->selectAll(
            'tversandartstaffel',
            'kVersandart',
            Request::postInt('edit'),
            '*',
            'fBis'
        );
        $this->shippingType   = $this->getShippingTypes((int)$this->shippingMethod->kVersandberechnung);

        $this->shippingMethod->cVersandklassen = \trim($this->shippingMethod->cVersandklassen);

        $this->smarty->assign(
            'VersandartZahlungsarten',
            $this->reorganizeObjectArray($mappedMethods, 'kZahlungsart')
        )
            ->assign('VersandartStaffeln', $shippingScales)
            ->assign('Versandart', $this->shippingMethod)
            ->assign('gewaehlteLaender', \explode(' ', $this->shippingMethod->cLaender));

        return $this->shippingType;
    }

    /**
     * @param array   $postData
     * @param Manager $manager
     * @return stdClass
     */
    private function createOrUpdate(array $postData, Manager $manager): stdClass
    {
        $oldShippingMethod                              = null;
        $postCountries                                  = $postData['land'] ?? [];
        $languages                                      = LanguageHelper::getInstance()->gibInstallierteSprachen();
        $this->shippingMethod                           = new stdClass();
        $this->shippingMethod->cName                    = \htmlspecialchars(
            $postData['cName'],
            \ENT_COMPAT | \ENT_HTML401,
            \JTL_CHARSET
        );
        $this->shippingMethod->kVersandberechnung       = Request::postInt('kVersandberechnung');
        $this->shippingMethod->cAnzeigen                = $postData['cAnzeigen'];
        $this->shippingMethod->cBild                    = $postData['cBild'];
        $this->shippingMethod->nSort                    = Request::postInt('nSort');
        $this->shippingMethod->nMinLiefertage           = Request::postInt('nMinLiefertage');
        $this->shippingMethod->nMaxLiefertage           = Request::postInt('nMaxLiefertage');
        $this->shippingMethod->cNurAbhaengigeVersandart = $postData['cNurAbhaengigeVersandart'];
        $this->shippingMethod->cSendConfirmationMail    = $postData['cSendConfirmationMail'] ?? 'Y';
        $this->shippingMethod->cIgnoreShippingProposal  = $postData['cIgnoreShippingProposal'] ?? 'N';
        $this->shippingMethod->eSteuer                  = $postData['eSteuer'];
        $this->shippingMethod->fPreis                   = (float)\str_replace(',', '.', $postData['fPreis'] ?? '0');
        // Versandkostenfrei ab X
        $this->shippingMethod->fVersandkostenfreiAbX = Request::postInt('versandkostenfreiAktiv') === 1
            ? (float)$postData['fVersandkostenfreiAbX']
            : 0;
        // Deckelung
        $this->shippingMethod->fDeckelung = Request::postInt('versanddeckelungAktiv') === 1
            ? (float)$postData['fDeckelung']
            : 0;

        $this->shippingMethod->cLaender = '';
        foreach (\array_unique($postCountries) as $postIso) {
            $this->shippingMethod->cLaender .= $postIso . ' ';
        }

        $mappedMethods = [];
        foreach (Request::verifyGPDataIntegerArray('kZahlungsart') as $paymentMethodID) {
            $mappedMethod               = new stdClass();
            $mappedMethod->kZahlungsart = $paymentMethodID;
            if ($postData['fAufpreis_' . $paymentMethodID] != 0) {
                $mappedMethod->fAufpreis    = (float)\str_replace(
                    ',',
                    '.',
                    $postData['fAufpreis_' . $paymentMethodID]
                );
                $mappedMethod->cAufpreisTyp = $postData['cAufpreisTyp_' . $paymentMethodID];
            }
            $mappedMethods[] = $mappedMethod;
        }

        $lastScaleTo    = 0.0;
        $shippingScales = [];
        $staffelDa      = true;
        if ($this->shippingType->cModulId === 'vm_versandberechnung_gewicht_jtl'
            || $this->shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl'
            || $this->shippingType->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'
        ) {
            $staffelDa = false;
            if (\count($postData['bis']) > 0 && \count($postData['preis']) > 0) {
                $staffelDa = true;
            }
            //preisstaffel beachten
            if (!isset($postData['bis'][0], $postData['preis'][0])
                || \mb_strlen($postData['bis'][0]) === 0
                || \mb_strlen($postData['preis'][0]) === 0
            ) {
                $staffelDa = false;
            }
            if (\is_array($postData['bis']) && \is_array($postData['preis'])) {
                foreach ($postData['bis'] as $i => $fBis) {
                    if (isset($postData['preis'][$i]) && \mb_strlen($fBis) > 0) {
                        unset($oVersandstaffel);
                        $oVersandstaffel         = new stdClass();
                        $oVersandstaffel->fBis   = (float)\str_replace(',', '.', $fBis);
                        $oVersandstaffel->fPreis = (float)\str_replace(',', '.', $postData['preis'][$i]);

                        $shippingScales[] = $oVersandstaffel;
                        $lastScaleTo      = $oVersandstaffel->fBis;
                    }
                }
            }
            // Dummy Versandstaffel hinzufuegen,
            // falls Versandart nach Warenwert und Versandkostenfrei ausgewaehlt wurde
            if ($this->shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl'
                && Request::postInt('versandkostenfreiAktiv') === 1
            ) {
                $this->shippingMethod->fVersandkostenfreiAbX = $lastScaleTo + 0.01;

                $oVersandstaffel         = new stdClass();
                $oVersandstaffel->fBis   = 999999999;
                $oVersandstaffel->fPreis = 0.0;
                $shippingScales[]        = $oVersandstaffel;
            }
        }
        // Kundengruppe
        $this->shippingMethod->cKundengruppen = '';
        if (!isset($postData['kKundengruppe'])) {
            $postData['kKundengruppe'] = [-1];
        }
        if (\is_array($postData['kKundengruppe'])) {
            if (\in_array(-1, $postData['kKundengruppe'])) {
                $this->shippingMethod->cKundengruppen = '-1';
            } else {
                $this->shippingMethod->cKundengruppen = ';' . \implode(';', $postData['kKundengruppe']) . ';';
            }
        }
        // Versandklassen
        $this->shippingMethod->cVersandklassen = !empty($postData['kVersandklasse'])
        && $postData['kVersandklasse'] !== '-1'
            ? (' ' . $postData['kVersandklasse'] . ' ')
            : '-1';

        if (\count($postCountries) >= 1
            && \count($postData['kZahlungsart'] ?? []) >= 1
            && $this->shippingMethod->cName
            && $staffelDa
        ) {
            if (Request::postInt('kVersandart') === 0) {
                $methodID = $this->db->insert('tversandart', $this->shippingMethod);
                $this->alertService->addSuccess(
                    \sprintf(\__('successShippingMethodCreate'), $this->shippingMethod->cName),
                    'successShippingMethodCreate'
                );
            } else {
                //updaten
                $methodID          = Request::postInt('kVersandart');
                $oldShippingMethod = $this->db->select('tversandart', 'kVersandart', $methodID);
                $this->db->update('tversandart', 'kVersandart', $methodID, $this->shippingMethod);
                $this->db->delete('tversandartzahlungsart', 'kVersandart', $methodID);
                $this->db->delete('tversandartstaffel', 'kVersandart', $methodID);
                $this->alertService->addSuccess(
                    \sprintf(\__('successShippingMethodChange'), $this->shippingMethod->cName),
                    'successShippingMethodChange'
                );
            }
            $manager->updateRegistrationCountries(
                \array_diff(
                    $oldShippingMethod !== null
                        ? \explode(' ', \trim($oldShippingMethod->cLaender))
                        : [],
                    $postCountries
                )
            );
            if ($methodID > 0) {
                $this->shippingMethod->methodID = $methodID;
                foreach ($mappedMethods as $mappedMethod) {
                    $mappedMethod->kVersandart = $methodID;
                    $this->db->insert('tversandartzahlungsart', $mappedMethod);
                }

                foreach ($shippingScales as $scale) {
                    $scale->kVersandart = $methodID;
                    $this->db->insert('tversandartstaffel', $scale);
                }
                $localized = new stdClass();

                $localized->kVersandart = $methodID;
                foreach ($languages as $language) {
                    $code = $language->getCode();

                    $localized->cISOSprache = $code;
                    $localized->cName       = '';
                    if (!empty($postData['cName_' . $code])) {
                        $localized->cName = \htmlspecialchars(
                            $postData['cName_' . $code],
                            \ENT_COMPAT | \ENT_HTML401,
                            \JTL_CHARSET
                        );
                    }
                    $localized->cLieferdauer = '';
                    if (!empty($postData['cLieferdauer_' . $code])) {
                        $localized->cLieferdauer = \htmlspecialchars(
                            $postData['cLieferdauer_' . $code],
                            \ENT_COMPAT | \ENT_HTML401,
                            \JTL_CHARSET
                        );
                    }
                    $localized->cHinweistext = '';
                    if (!empty($postData['cHinweistext_' . $code])) {
                        $localized->cHinweistext = $postData['cHinweistext_' . $code];
                    }
                    $localized->cHinweistextShop = '';
                    if (!empty($postData['cHinweistextShop_' . $code])) {
                        $localized->cHinweistextShop = $postData['cHinweistextShop_' . $code];
                    }
                    $this->db->delete('tversandartsprache', ['kVersandart', 'cISOSprache'], [$methodID, $code]);
                    $this->db->insert('tversandartsprache', $localized);
                }
                $this->step = 'uebersicht';
            }
            $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);
        } else {
            $this->step = 'neue Versandart';
            if (!$this->shippingMethod->cName) {
                $this->alertService->addError(\__('errorShippingMethodNameMissing'), 'errorShippingMethodNameMissing');
            }
            if (\count($postCountries) < 1) {
                $this->alertService->addError(
                    \__('errorShippingMethodCountryMissing'),
                    'errorShippingMethodCountryMissing'
                );
            }
            if (\count($postData['kZahlungsart'] ?? []) < 1) {
                $this->alertService->addError(
                    \__('errorShippingMethodPaymentMissing'),
                    'errorShippingMethodPaymentMissing'
                );
            }
            if (!$staffelDa) {
                $this->alertService->addError(
                    \__('errorShippingMethodPriceMissing'),
                    'errorShippingMethodPriceMissing'
                );
            }
            if (Request::postInt('kVersandart') > 0) {
                $this->shippingMethod = $this->db->select(
                    'tversandart',
                    'kVersandart',
                    Request::postInt('kVersandart')
                );
            }

            $this->smarty->assign(
                'VersandartZahlungsarten',
                $this->reorganizeObjectArray($mappedMethods, 'kZahlungsart')
            )
                ->assign('VersandartStaffeln', $shippingScales)
                ->assign('Versandart', $this->shippingMethod)
                ->assign('gewaehlteLaender', \explode(' ', $this->shippingMethod->cLaender));
        }

        return $this->shippingMethod;
    }
}
