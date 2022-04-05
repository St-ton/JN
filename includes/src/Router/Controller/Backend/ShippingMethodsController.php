<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use JTL\Alert\Alert;
use JTL\Checkout\ShippingSurcharge;
use JTL\Checkout\ShippingSurchargeArea;
use JTL\Checkout\Versandart;
use JTL\Checkout\ZipValidator;
use JTL\Country\Country;
use JTL\Country\Manager;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Pagination\Pagination;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Services\JTL\CountryService;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SmartyException;
use stdClass;

/**
 * Class ShippingMethodsController
 * @package JTL\Router\Controller\Backend
 */
class ShippingMethodsController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('ORDER_SHIPMENT_VIEW');
        $this->getText->loadAdminLocale('pages/versandarten');

        $defaultCurrency = $this->db->select('twaehrung', 'cStandard', 'Y');
        $shippingType    = null;
        $step            = 'uebersicht';
        $shippingMethod  = null;
        $taxRateKeys     = \array_keys($_SESSION['Steuersatz']);
        $countryHelper   = Shop::Container()->getCountryService();
        $languages       = LanguageHelper::getInstance()->gibInstallierteSprachen();
        $postData        = Text::filterXSS($_POST);
        $postCountries   = $postData['land'] ?? [];
        $manager         = new Manager(
            $this->db,
            $smarty,
            $countryHelper,
            $this->cache,
            $this->alertService,
            $this->getText
        );

        $missingShippingClassCombis = $this->getMissingShippingClassCombi();
        $smarty->assign('missingShippingClassCombis', $missingShippingClassCombis);

        if (Form::validateToken()) {
            if (Request::postInt('neu') === 1 && Request::postInt('kVersandberechnung') > 0) {
                $step = 'neue Versandart';
            }
            if (Request::postInt('kVersandberechnung') > 0) {
                $shippingType = $this->getShippingTypes(Request::verifyGPCDataInt('kVersandberechnung'));
            }

            if (Request::postInt('del') > 0) {
                $oldShippingMethod = $this->db->select('tversandart', 'kVersandart', (int)$postData['del']);
                Versandart::deleteInDB((int)$postData['del']);
                $manager->updateRegistrationCountries(\explode(' ', \trim($oldShippingMethod->cLaender ?? '')));
                $this->alertService->addSuccess(\__('successShippingMethodDelete'), 'successShippingMethodDelete');
                $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);
            }
            if (Request::postInt('edit') > 0) {
                $step                            = 'neue Versandart';
                $shippingMethod                  = $this->db->select('tversandart', 'kVersandart', Request::postInt('edit'));
                $VersandartZahlungsarten         = $this->db->selectAll(
                    'tversandartzahlungsart',
                    'kVersandart',
                    Request::postInt('edit'),
                    '*',
                    'kZahlungsart'
                );
                $VersandartStaffeln              = $this->db->selectAll(
                    'tversandartstaffel',
                    'kVersandart',
                    Request::postInt('edit'),
                    '*',
                    'fBis'
                );
                $shippingType                    = $this->getShippingTypes((int)$shippingMethod->kVersandberechnung);
                $shippingMethod->cVersandklassen = \trim($shippingMethod->cVersandklassen);

                $smarty->assign('VersandartZahlungsarten', $this->reorganizeObjectArray($VersandartZahlungsarten, 'kZahlungsart'))
                    ->assign('VersandartStaffeln', $VersandartStaffeln)
                    ->assign('Versandart', $shippingMethod)
                    ->assign('gewaehlteLaender', \explode(' ', $shippingMethod->cLaender));
            }

            if (Request::postInt('clone') > 0) {
                $step = 'uebersicht';
                if (Versandart::cloneShipping((int)$postData['clone'])) {
                    $this->alertService->addSuccess(\__('successShippingMethodDuplicated'), 'successShippingMethodDuplicated');
                    $this->cache->flushTags([\CACHING_GROUP_OPTION]);
                } else {
                    $this->alertService->addError(\__('errorShippingMethodDuplicated'), 'errorShippingMethodDuplicated');
                }
            }

            if (isset($_GET['cISO']) && Request::getInt('zuschlag') === 1 && Request::getInt('kVersandart') > 0) {
                $step = 'Zuschlagsliste';

                $pagination = (new Pagination('surchargeList'))
                    ->setRange(4)
                    ->setItemArray((new Versandart(Request::getInt('kVersandart')))
                        ->getShippingSurchargesForCountry($_GET['cISO']))
                    ->assemble();

                $smarty->assign('surcharges', $pagination->getPageItems())
                    ->assign('pagination', $pagination);
            }

            if (Request::postInt('neueVersandart') > 0) {
                $shippingMethod                           = new stdClass();
                $shippingMethod->cName                    = \htmlspecialchars(
                    $postData['cName'],
                    \ENT_COMPAT | \ENT_HTML401,
                    \JTL_CHARSET
                );
                $shippingMethod->kVersandberechnung       = Request::postInt('kVersandberechnung');
                $shippingMethod->cAnzeigen                = $postData['cAnzeigen'];
                $shippingMethod->cBild                    = $postData['cBild'];
                $shippingMethod->nSort                    = Request::postInt('nSort');
                $shippingMethod->nMinLiefertage           = Request::postInt('nMinLiefertage');
                $shippingMethod->nMaxLiefertage           = Request::postInt('nMaxLiefertage');
                $shippingMethod->cNurAbhaengigeVersandart = $postData['cNurAbhaengigeVersandart'];
                $shippingMethod->cSendConfirmationMail    = $postData['cSendConfirmationMail'] ?? 'Y';
                $shippingMethod->cIgnoreShippingProposal  = $postData['cIgnoreShippingProposal'] ?? 'N';
                $shippingMethod->eSteuer                  = $postData['eSteuer'];
                $shippingMethod->fPreis                   = (float)\str_replace(',', '.', $postData['fPreis'] ?? 0);
                // Versandkostenfrei ab X
                $shippingMethod->fVersandkostenfreiAbX = Request::postInt('versandkostenfreiAktiv') === 1
                    ? (float)$postData['fVersandkostenfreiAbX']
                    : 0;
                // Deckelung
                $shippingMethod->fDeckelung = Request::postInt('versanddeckelungAktiv') === 1
                    ? (float)$postData['fDeckelung']
                    : 0;

                $shippingMethod->cLaender = '';
                foreach (\array_unique($postCountries) as $postIso) {
                    $shippingMethod->cLaender .= $postIso . ' ';
                }

                $VersandartZahlungsarten = [];
                foreach (Request::verifyGPDataIntegerArray('kZahlungsart') as $kZahlungsart) {
                    $versandartzahlungsart               = new stdClass();
                    $versandartzahlungsart->kZahlungsart = $kZahlungsart;
                    if ($postData['fAufpreis_' . $kZahlungsart] != 0) {
                        $versandartzahlungsart->fAufpreis    = (float)\str_replace(
                            ',',
                            '.',
                            $postData['fAufpreis_' . $kZahlungsart]
                        );
                        $versandartzahlungsart->cAufpreisTyp = $postData['cAufpreisTyp_' . $kZahlungsart];
                    }
                    $VersandartZahlungsarten[] = $versandartzahlungsart;
                }

                $lastScaleTo        = 0.0;
                $VersandartStaffeln = [];
                $upperLimits        = []; // Haelt alle fBis der Staffel
                $staffelDa          = true;
                if ($shippingType->cModulId === 'vm_versandberechnung_gewicht_jtl'
                    || $shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl'
                    || $shippingType->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'
                ) {
                    $staffelDa = false;
                    if (count($postData['bis']) > 0 && count($postData['preis']) > 0) {
                        $staffelDa = true;
                    }
                    //preisstaffel beachten
                    if (!isset($postData['bis'][0], $postData['preis'][0])
                        || mb_strlen($postData['bis'][0]) === 0
                        || mb_strlen($postData['preis'][0]) === 0
                    ) {
                        $staffelDa = false;
                    }
                    if (\is_array($postData['bis']) && \is_array($postData['preis'])) {
                        foreach ($postData['bis'] as $i => $fBis) {
                            if (isset($postData['preis'][$i]) && mb_strlen($fBis) > 0) {
                                unset($oVersandstaffel);
                                $oVersandstaffel         = new stdClass();
                                $oVersandstaffel->fBis   = (float)\str_replace(',', '.', $fBis);
                                $oVersandstaffel->fPreis = (float)\str_replace(',', '.', $postData['preis'][$i]);

                                $VersandartStaffeln[] = $oVersandstaffel;
                                $upperLimits[]        = $oVersandstaffel->fBis;
                                $lastScaleTo          = $oVersandstaffel->fBis;
                            }
                        }
                    }
                    // Dummy Versandstaffel hinzufuegen, falls Versandart nach Warenwert und Versandkostenfrei ausgewaehlt wurde
                    if ($shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl'
                        && Request::postInt('versandkostenfreiAktiv') === 1
                    ) {
                        $shippingMethod->fVersandkostenfreiAbX = $lastScaleTo + 0.01;

                        $oVersandstaffel         = new stdClass();
                        $oVersandstaffel->fBis   = 999999999;
                        $oVersandstaffel->fPreis = 0.0;
                        $VersandartStaffeln[]    = $oVersandstaffel;
                    }
                }
                // Kundengruppe
                $shippingMethod->cKundengruppen = '';
                if (!isset($postData['kKundengruppe'])) {
                    $postData['kKundengruppe'] = [-1];
                }
                if (\is_array($postData['kKundengruppe'])) {
                    if (\in_array(-1, $postData['kKundengruppe'])) {
                        $shippingMethod->cKundengruppen = '-1';
                    } else {
                        $shippingMethod->cKundengruppen = ';' . \implode(';', $postData['kKundengruppe']) . ';';
                    }
                }
                // Versandklassen
                $shippingMethod->cVersandklassen = !empty($postData['kVersandklasse']) && $postData['kVersandklasse'] !== '-1'
                    ? (' ' . $postData['kVersandklasse'] . ' ')
                    : '-1';

                if (count($postCountries) >= 1
                    && count($postData['kZahlungsart'] ?? []) >= 1
                    && $shippingMethod->cName
                    && $staffelDa
                ) {
                    if (Request::postInt('kVersandart') === 0) {
                        $methodID = $this->db->insert('tversandart', $shippingMethod);
                        $this->alertService->addSuccess(
                            \sprintf(\__('successShippingMethodCreate'), $shippingMethod->cName),
                            'successShippingMethodCreate'
                        );
                    } else {
                        //updaten
                        $methodID          = Request::postInt('kVersandart');
                        $oldShippingMethod = $this->db->select('tversandart', 'kVersandart', $methodID);
                        $this->db->update('tversandart', 'kVersandart', $methodID, $shippingMethod);
                        $this->db->delete('tversandartzahlungsart', 'kVersandart', $methodID);
                        $this->db->delete('tversandartstaffel', 'kVersandart', $methodID);
                        $this->alertService->addSuccess(
                            \sprintf(\__('successShippingMethodChange'), $shippingMethod->cName),
                            'successShippingMethodChange'
                        );
                    }
                    $manager->updateRegistrationCountries(
                        \array_diff(
                            isset($oldShippingMethod->cLaender)
                                ? \explode(' ', \trim($oldShippingMethod->cLaender))
                                : [],
                            $postCountries
                        )
                    );
                    if ($methodID > 0) {
                        foreach ($VersandartZahlungsarten as $versandartzahlungsart) {
                            $versandartzahlungsart->kVersandart = $methodID;
                            $this->db->insert('tversandartzahlungsart', $versandartzahlungsart);
                        }

                        foreach ($VersandartStaffeln as $versandartstaffel) {
                            $versandartstaffel->kVersandart = $methodID;
                            $this->db->insert('tversandartstaffel', $versandartstaffel);
                        }
                        $versandSprache = new stdClass();

                        $versandSprache->kVersandart = $methodID;
                        foreach ($languages as $language) {
                            $code = $language->getCode();

                            $versandSprache->cISOSprache = $code;
                            $versandSprache->cName       = '';
                            if (!empty($postData['cName_' . $code])) {
                                $versandSprache->cName = \htmlspecialchars(
                                    $postData['cName_' . $code],
                                    \ENT_COMPAT | \ENT_HTML401,
                                    \JTL_CHARSET
                                );
                            }
                            $versandSprache->cLieferdauer = '';
                            if (!empty($postData['cLieferdauer_' . $code])) {
                                $versandSprache->cLieferdauer = \htmlspecialchars(
                                    $postData['cLieferdauer_' . $code],
                                    \ENT_COMPAT | \ENT_HTML401,
                                    \JTL_CHARSET
                                );
                            }
                            $versandSprache->cHinweistext = '';
                            if (!empty($postData['cHinweistext_' . $code])) {
                                $versandSprache->cHinweistext = $postData['cHinweistext_' . $code];
                            }
                            $versandSprache->cHinweistextShop = '';
                            if (!empty($postData['cHinweistextShop_' . $code])) {
                                $versandSprache->cHinweistextShop = $postData['cHinweistextShop_' . $code];
                            }
                            $this->db->delete('tversandartsprache', ['kVersandart', 'cISOSprache'], [$methodID, $code]);
                            $this->db->insert('tversandartsprache', $versandSprache);
                        }
                        $step = 'uebersicht';
                    }
                    $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);
                } else {
                    $step = 'neue Versandart';
                    if (!$shippingMethod->cName) {
                        $this->alertService->addError(\__('errorShippingMethodNameMissing'), 'errorShippingMethodNameMissing');
                    }
                    if (count($postCountries) < 1) {
                        $this->alertService->addError(\__('errorShippingMethodCountryMissing'), 'errorShippingMethodCountryMissing');
                    }
                    if (count($postData['kZahlungsart'] ?? []) < 1) {
                        $this->alertService->addError(\__('errorShippingMethodPaymentMissing'), 'errorShippingMethodPaymentMissing');
                    }
                    if (!$staffelDa) {
                        $this->alertService->addError(\__('errorShippingMethodPriceMissing'), 'errorShippingMethodPriceMissing');
                    }
                    if (Request::postInt('kVersandart') > 0) {
                        $shippingMethod = $this->db->select('tversandart', 'kVersandart', Request::postInt('kVersandart'));
                    }
                    $smarty->assign('VersandartZahlungsarten', $this->reorganizeObjectArray($VersandartZahlungsarten, 'kZahlungsart'))
                        ->assign('VersandartStaffeln', $VersandartStaffeln)
                        ->assign('Versandart', $shippingMethod)
                        ->assign('gewaehlteLaender', \explode(' ', $shippingMethod->cLaender));
                }
            }
            $this->cache->flush(CountryService::CACHE_ID);
        }
        if ($step === 'neue Versandart') {
            $versandlaender = $countryHelper->getCountrylist();
            if ($shippingType->cModulId === 'vm_versandberechnung_gewicht_jtl') {
                $smarty->assign('einheit', 'kg');
            }
            if ($shippingType->cModulId === 'vm_versandberechnung_warenwert_jtl') {
                $smarty->assign('einheit', $defaultCurrency->cName);
            }
            if ($shippingType->cModulId === 'vm_versandberechnung_artikelanzahl_jtl') {
                $smarty->assign('einheit', 'Stück');
            }
            // prevent "unusable" payment methods from displaying them in the config section (mainly the null-payment)
            $zahlungsarten = $this->db->selectAll(
                'tzahlungsart',
                ['nActive', 'nNutzbar'],
                [1, 1],
                '*',
                'cAnbieter, nSort, cName, cModulId'
            );
            foreach ($zahlungsarten as $zahlungsart) {
                $pluginID = PluginHelper::getIDByModuleID($zahlungsart->cModulId);
                if ($pluginID > 0) {
                    try {
                        $this->getText->loadPluginLocale(
                            'base',
                            PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                        );
                    } catch (InvalidArgumentException $e) {
                        $this->getText->loadAdminLocale('pages/zahlungsarten');
                        $this->alertService->addWarning(
                            \sprintf(
                                \__('Plugin for payment method not found'),
                                $zahlungsart->cName,
                                $zahlungsart->cAnbieter
                            ),
                            'notfound_' . $pluginID,
                            [
                                'linkHref' => Shop::getURL(true) . $route->getPath(),
                                'linkText' => \__('paymentTypesOverview')
                            ]
                        );
                        continue;
                    }
                }
                $zahlungsart->cName     = \__($zahlungsart->cName);
                $zahlungsart->cAnbieter = \__($zahlungsart->cAnbieter);
            }
            $tmpID = (int)($shippingMethod->kVersandart ?? 0);
            $smarty->assign('versandKlassen', $this->db->selectAll('tversandklasse', [], [], '*', 'kVersandklasse'))
                ->assign('zahlungsarten', $zahlungsarten)
                ->assign('versandlaender', $versandlaender)
                ->assign('continents', $countryHelper->getCountriesGroupedByContinent(
                    true,
                    \explode(' ', $shippingMethod->cLaender ?? '')
                ))
                ->assign('versandberechnung', $shippingType)
                ->assign('waehrung', $defaultCurrency->cName)
                ->assign('customerGroups', CustomerGroup::getGroups())
                ->assign('oVersandartSpracheAssoc_arr', $this->getShippingLanguage($tmpID, $languages))
                ->assign('gesetzteVersandklassen', isset($shippingMethod->cVersandklassen)
                    ? $this->gibGesetzteVersandklassen($shippingMethod->cVersandklassen)
                    : null)
                ->assign('gesetzteKundengruppen', isset($shippingMethod->cKundengruppen)
                    ? $this->gibGesetzteKundengruppen($shippingMethod->cKundengruppen)
                    : null);
        }
        if ($step === 'uebersicht') {
            $customerGroups  = $this->db->getObjects('SELECT kKundengruppe, cName FROM tkundengruppe ORDER BY kKundengruppe');
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
                        } catch (InvalidArgumentException $e) {
                            $this->getText->loadAdminLocale('pages/zahlungsarten');
                            $this->alertService->addWarning(
                                \sprintf(
                                    \__('Plugin for payment method not found'),
                                    $smp->zahlungsart->cName,
                                    $smp->zahlungsart->cAnbieter
                                ),
                                'notfound_' . $pluginID,
                                [
                                    'linkHref' => Shop::getURL(true) . $route->getPath(),
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
                $method->fPreisBrutto               = $this->berechneVersandpreisBrutto(
                    $method->fPreis,
                    $_SESSION['Steuersatz'][$taxRateKeys[0]]
                );
                $method->fVersandkostenfreiAbXNetto = $this->berechneVersandpreisNetto(
                    $method->fVersandkostenfreiAbX,
                    $_SESSION['Steuersatz'][$taxRateKeys[0]]
                );
                $method->fDeckelungBrutto           = $this->berechneVersandpreisBrutto(
                    $method->fDeckelung,
                    $_SESSION['Steuersatz'][$taxRateKeys[0]]
                );
                foreach ($method->versandartstaffeln as $j => $oVersandartstaffeln) {
                    $method->versandartstaffeln[$j]->fPreisBrutto = $this->berechneVersandpreisBrutto(
                        $oVersandartstaffeln->fPreis,
                        $_SESSION['Steuersatz'][$taxRateKeys[0]]
                    );
                }

                $method->versandberechnung = $this->getShippingTypes((int)$method->kVersandberechnung);
                $method->versandklassen    = $this->gibGesetzteVersandklassenUebersicht($method->cVersandklassen);
                if ($method->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl') {
                    $method->einheit = 'kg';
                }
                if ($method->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl') {
                    $method->einheit = $defaultCurrency->cName;
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
                    if (($country = $countryHelper->getCountry($item)) !== null) {
                        $method->countries->push($country);
                    }
                }
                $method->countries               = $method->countries->sortBy(static function (Country $country) {
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
                $errorMissingShippingClassCombis = $smarty->assign('missingShippingClassCombis', $missingShippingClassCombis)
                    ->fetch('tpl_inc/versandarten_fehlende_kombis.tpl');
                $this->alertService->addError($errorMissingShippingClassCombis, 'errorMissingShippingClassCombis');
            }

            $smarty->assign('versandberechnungen', $this->getShippingTypes())
                ->assign('versandarten', $shippingMethods)
                ->assign('waehrung', $defaultCurrency->cName);
        }
        if ($step === 'Zuschlagsliste') {
            $iso      = $_GET['cISO'] ?? $postData['cISO'] ?? null;
            $methodID = Request::getInt('kVersandart');
            if (isset($postData['kVersandart'])) {
                $methodID = Request::postInt('kVersandart');
            }
            $shippingMethod = $this->db->select('tversandart', 'kVersandart', $methodID);
            $fees           = $this->db->selectAll(
                'tversandzuschlag',
                ['kVersandart', 'cISO'],
                [(int)$shippingMethod->kVersandart, $iso],
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
                $item->angezeigterName  = $this->getZuschlagNames($item->kVersandzuschlag);
            }
            $smarty->assign('Versandart', $shippingMethod)
                ->assign('Zuschlaege', $fees)
                ->assign('waehrung', $defaultCurrency->cName)
                ->assign('Land', $countryHelper->getCountry($iso));
        }

        return $smarty->assign('fSteuersatz', $_SESSION['Steuersatz'][$taxRateKeys[0]])
            ->assign('oWaehrung', $this->db->select('twaehrung', 'cStandard', 'Y'))
            ->assign('step', $step)
            ->assign('route', $route->getPath())
            ->getResponse('versandarten.tpl');
    }

    /**
     * @param float|string $price
     * @param float|string $taxRate
     * @return float
     */
    private function berechneVersandpreisBrutto($price, $taxRate): float
    {
        return $price > 0
            ? \round((float)($price * ((100 + $taxRate) / 100)), 2)
            : 0.0;
    }

    /**
     * @param float|string $price
     * @param float|string $taxRate
     * @return float
     */
    private function berechneVersandpreisNetto($price, $taxRate): float
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
     */
    public function P($arr): array
    {
        $newArr = [];
        if (\is_array($arr)) {
            foreach ($arr as $ele) {
                $newArr = $this->bauePot($newArr, $ele);
            }
        }

        return $newArr;
    }

    /**
     * @param array  $arr
     * @param object $key
     * @return array
     */
    private function bauePot($arr, $key): array
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
     */
    private function gibGesetzteVersandklassen(string $shippingClasses): array
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
        $items = $this->P($this->db->getObjects(
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
     */
    private function gibGesetzteVersandklassenUebersicht($shippingClasses): array
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
        $items = $this->P($this->db->getObjects(
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
     */
    private function gibGesetzteKundengruppen(string $customerGroupsString): array
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
     */
    private function getZuschlagNames(int $feeID): array
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
        $baselen = count($shipClasses);
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

        $len = count($shipClasses);
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
            $mscc = $this->gibGesetzteVersandklassenUebersicht($mscc)[0];
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
        $shippingTypes->each(static function ($e) {
            $e->kVersandberechnung = (int)$e->kVersandberechnung;
            $e->cName              = \__('shippingType_' . $e->cModulId);
        });

        return $shippingTypeID === null ? $shippingTypes->toArray() : $shippingTypes->first();
    }

    /**
     * @param int $id
     * @return stdClass
     * @throws SmartyException
     */
    public static function getShippingSurcharge(int $id): stdClass
    {
        Shop::Container()->getGetText()->loadAdminLocale('pages/versandarten');

        $smarty       = JTLSmarty::getInstance(false, ContextType::BACKEND);
        $result       = new stdClass();
        $result->body = $smarty->assign('sprachen', LanguageHelper::getAllLanguages(0, true))
            ->assign('surchargeNew', new ShippingSurcharge($id))
            ->assign('surchargeID', $id)
            ->fetch('snippets/zuschlagliste_form.tpl');

        return $result;
    }
    /**
     * @param array $data
     * @return stdClass
     * @throws SmartyException
     */
    public static function saveShippingSurcharge(array $data): stdClass
    {
        Shop::Container()->getGetText()->loadAdminLocale('pages/versandarten');

        $alertHelper = Shop::Container()->getAlertService();
        $smarty      = JTLSmarty::getInstance(false, ContextType::BACKEND);
        $post        = [];
        foreach ($data as $item) {
            $post[$item['name']] = $item['value'];
        }
        $surcharge = (float)\str_replace(',', '.', $post['fZuschlag']);

        if (!$post['cName']) {
            $alertHelper->addError(\__('errorListNameMissing'), 'errorListNameMissing');
        }
        if (empty($surcharge)) {
            $alertHelper->addError(\__('errorListPriceMissing'), 'errorListPriceMissing');
        }
        if (!$alertHelper->alertTypeExists(Alert::TYPE_ERROR)) {
            if (empty($post['kVersandzuschlag'])) {
                $surchargeTMP = (new ShippingSurcharge())
                    ->setISO($post['cISO'])
                    ->setSurcharge($surcharge)
                    ->setShippingMethod((int)$post['kVersandart'])
                    ->setTitle($post['cName']);
            } else {
                $surchargeTMP = (new ShippingSurcharge((int)$post['kVersandzuschlag']))
                    ->setTitle($post['cName'])
                    ->setSurcharge($surcharge);
            }
            foreach (LanguageHelper::getAllLanguages(0, true) as $lang) {
                $idx = 'cName_' . $lang->getCode();
                if (isset($post[$idx])) {
                    $surchargeTMP->setName($post[$idx] ?: $post['cName'], $lang->getId());
                }
            }
            $surchargeTMP->save();
            $surchargeTMP = new ShippingSurcharge($surchargeTMP->getID());
        }
        $message = $smarty->assign('alertList', $alertHelper)
            ->fetch('snippets/alert_list.tpl');

        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OBJECT, \CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);

        return (object)[
            'title'          => isset($surchargeTMP) ? $surchargeTMP->getTitle() : '',
            'priceLocalized' => isset($surchargeTMP) ? $surchargeTMP->getPriceLocalized() : '',
            'id'             => isset($surchargeTMP) ? $surchargeTMP->getID() : '',
            'reload'         => empty($post['kVersandzuschlag']),
            'message'        => $message,
            'error'          => $alertHelper->alertTypeExists(Alert::TYPE_ERROR)
        ];
    }

    /**
     * @param int $surchargeID
     * @return stdClass
     */
    public static function deleteShippingSurcharge(int $surchargeID): stdClass
    {
        Shop::Container()->getDB()->queryPrepared(
            'DELETE tversandzuschlag, tversandzuschlagsprache, tversandzuschlagplz
            FROM tversandzuschlag
            LEFT JOIN tversandzuschlagsprache USING(kVersandzuschlag)
            LEFT JOIN tversandzuschlagplz USING(kVersandzuschlag)
            WHERE tversandzuschlag.kVersandzuschlag = :surchargeID',
            ['surchargeID' => $surchargeID]
        );
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OBJECT, \CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);

        return (object)['surchargeID' => $surchargeID];
    }

    /**
     * @param int    $surchargeID
     * @param string $ZIP
     * @return stdClass
     */
    public static function deleteShippingSurchargeZIP(int $surchargeID, string $ZIP): stdClass
    {
        $partsZIP = \explode('-', $ZIP);
        if (count($partsZIP) === 1) {
            Shop::Container()->getDB()->queryPrepared(
                'DELETE 
            FROM tversandzuschlagplz
            WHERE kVersandzuschlag = :surchargeID
              AND cPLZ = :ZIP',
                [
                    'surchargeID' => $surchargeID,
                    'ZIP'         => $partsZIP[0]
                ]
            );
        } elseif (count($partsZIP) === 2) {
            Shop::Container()->getDB()->queryPrepared(
                'DELETE 
            FROM tversandzuschlagplz
            WHERE kVersandzuschlag = :surchargeID
              AND cPLZab = :ZIPFrom
              AND cPLZbis = :ZIPTo',
                [
                    'surchargeID' => $surchargeID,
                    'ZIPFrom'     => $partsZIP[0],
                    'ZIPTo'       => $partsZIP[1]
                ]
            );
        }
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OBJECT, \CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);

        return (object)['surchargeID' => $surchargeID, 'ZIP' => $ZIP];
    }

    /**
     * @param array $data
     * @return stdClass
     * @throws SmartyException
     */
    public static function createShippingSurchargeZIP(array $data): stdClass
    {
        Shop::Container()->getGetText()->loadAdminLocale('pages/versandarten');

        $post = [];
        foreach ($data as $item) {
            $post[$item['name']] = $item['value'];
        }
        $alertHelper    = Shop::Container()->getAlertService();
        $db             = Shop::Container()->getDB();
        $smarty         = JTLSmarty::getInstance(false, ContextType::BACKEND);
        $surcharge      = new ShippingSurcharge((int)$post['kVersandzuschlag']);
        $shippingMethod = new Versandart($surcharge->getShippingMethod());
        $zipValidator   = new ZipValidator($surcharge->getISO());
        $surchargeZip   = new stdClass();

        $surchargeZip->kVersandzuschlag = $surcharge->getID();
        $surchargeZip->cPLZ             = '';
        $surchargeZip->cPLZAb           = '';
        $surchargeZip->cPLZBis          = '';
        $area                           = null;

        if (!empty($post['cPLZ'])) {
            $surchargeZip->cPLZ = $zipValidator->validateZip($post['cPLZ']);
        } elseif (!empty($post['cPLZAb']) && !empty($post['cPLZBis'])) {
            $area = new ShippingSurchargeArea($post['cPLZAb'], $post['cPLZBis']);
            if ($area->getZIPFrom() === $area->getZIPTo()) {
                $surchargeZip->cPLZ = $zipValidator->validateZip($area->getZIPFrom());
            } else {
                $surchargeZip->cPLZAb  = $zipValidator->validateZip($area->getZIPFrom());
                $surchargeZip->cPLZBis = $zipValidator->validateZip($area->getZIPTo());
            }
        }

        $zipMatchSurcharge = $shippingMethod->getShippingSurchargesForCountry($surcharge->getISO())
            ->first(static function (ShippingSurcharge $surchargeTMP) use ($surchargeZip) {
                return ($surchargeTMP->hasZIPCode($surchargeZip->cPLZ)
                    || $surchargeTMP->hasZIPCode($surchargeZip->cPLZAb)
                    || $surchargeTMP->hasZIPCode($surchargeZip->cPLZBis)
                    || $surchargeTMP->areaOverlapsWithZIPCode($surchargeZip->cPLZAb, $surchargeZip->cPLZBis)
                );
            });
        if ($area !== null && !$area->lettersMatch()) {
            $alertHelper->addError(\__('errorZIPsDoNotMatch'), 'errorZIPsDoNotMatch');
        } elseif (empty($surchargeZip->cPLZ) && empty($surchargeZip->cPLZAb)) {
            $error = $zipValidator->getError();
            if ($error !== '') {
                $alertHelper->addError($error, 'errorZIPValidator');
            } else {
                $alertHelper->addError(\__('errorZIPMissing'), 'errorZIPMissing');
            }
        } elseif ($zipMatchSurcharge !== null) {
            $alertHelper->addError(
                \sprintf(
                    isset($surchargeZip->cPLZ) ? \__('errorZIPOverlap') : \__('errorZIPAreaOverlap'),
                    $surchargeZip->cPLZ ?? $surchargeZip->cPLZAb . ' - ' . $surchargeZip->cPLZBis,
                    $zipMatchSurcharge->getTitle()
                ),
                'errorZIPOverlap'
            );
        } elseif ($db->insert('tversandzuschlagplz', $surchargeZip)) {
            $alertHelper->addSuccess(\__('successZIPAdd'), 'successZIPAdd');
        }
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OBJECT, \CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE]);

        $message = $smarty->assign('alertList', $alertHelper)
            ->fetch('snippets/alert_list.tpl');
        $badges  = $smarty->assign('surcharge', new ShippingSurcharge($surcharge->getID()))
            ->fetch('snippets/zuschlagliste_plz_badges.tpl');

        return (object)['message' => $message, 'badges' => $badges, 'surchargeID' => $surcharge->getID()];
    }
}
