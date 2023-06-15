<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Alert\Alert;
use JTL\Backend\AdminFavorite;
use JTL\Backend\AdminIO;
use JTL\Backend\JSONAPI;
use JTL\Backend\Notification;
use JTL\Backend\Settings\Manager as SettingsManager;
use JTL\Backend\TwoFA;
use JTL\Backend\Wizard\WizardIO;
use JTL\Catalog\Currency;
use JTL\Checkout\ShippingSurcharge;
use JTL\Checkout\ShippingSurchargeArea;
use JTL\Checkout\Versandart;
use JTL\Checkout\ZipValidator;
use JTL\Customer\Import;
use JTL\Export\SyntaxChecker as ExportSyntaxChecker;
use JTL\Filter\States\BaseSearchQuery;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\IO\IOError;
use JTL\IO\IOResponse;
use JTL\Jtllog;
use JTL\Language\LanguageHelper;
use JTL\Link\Admin\LinkAdmin;
use JTL\Mail\Validator\SyntaxChecker;
use JTL\Media\Manager;
use JTL\Plugin\Helper;
use JTL\Redirect;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use JTL\Update\DBMigrationHelper;
use JTL\Update\UpdateIO;
use JTL\Widgets\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SmartyException;
use stdClass;

/**
 * Class IOController
 * @package JTL\Router\Controller\Backend
 */
class IOController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        \ob_start();
        $io = AdminIO::getInstance();
        if (!$this->account->getIsAuthenticated()) {
            return $io->getResponse(new IOError('Not authenticated as admin.', 401));
        }
        if (!Form::validateToken()) {
            return $io->getResponse(new IOError('CSRF validation failed.', 403));
        }

        $jsonApi = JSONAPI::getInstance($this->db, $this->cache);
        $io->setAccount($this->account);
        $images         = new Manager($this->db, $this->getText);
        $updateIO       = new UpdateIO($this->db, $this->getText);
        $wizardIO       = new WizardIO($this->db, $this->cache, $this->alertService, $this->getText);
        $settings       = new SettingsManager($this->db, $smarty, $this->account, $this->getText, $this->alertService);
        $widgets        = new Controller($this->db, $this->cache, $this->getText, $smarty, $this->account);
        $customerImport = new Import($this->db);

        $searchController = new SearchController(
            $this->db,
            $this->cache,
            $this->alertService,
            $this->account,
            $this->getText
        );
        $searchController->setSmarty($smarty);

        try {
            Shop::Container()->getOPC()->registerAdminIOFunctions($io);
            Shop::Container()->getOPCPageService()->registerAdminIOFunctions($io);
        } catch (Exception $e) {
            return $io->getResponse(new IOError($e->getMessage(), $e->getCode()));
        }
        try {
            $io
                ->register(
                    'notifyImportedCustomers',
                    [$customerImport, 'notifyCustomers'],
                    null,
                    'IMPORT_CUSTOMER_VIEW'
                )
                ->register('getPages', [$jsonApi, 'getPages'])
                ->register('getCategories', [$jsonApi, 'getCategories'])
                ->register('getProducts', [$jsonApi, 'getProducts'])
                ->register('getManufacturers', [$jsonApi, 'getManufacturers'])
                ->register('getCustomers', [$jsonApi, 'getCustomers'])
                ->register('getSeos', [$jsonApi, 'getSeos'])
                ->register('getAttributes', [$jsonApi, 'getAttributes'])
                ->register('getSettingLog', [$settings, 'getSettingLog'])
                ->register('isDuplicateSpecialLink', [LinkAdmin::class, 'isDuplicateSpecialLink'])
                ->register('getCurrencyConversion', [$this, 'getCurrencyConversionIO'])
                ->register('setCurrencyConversionTooltip', [$this, 'setCurrencyConversionTooltipIO'])
                ->register('getNotifyDropIO', [Notification::class, 'getNotifyDropIO'])
                ->register('getNewTwoFA', [TwoFA::class, 'getNewTwoFA'])
                ->register('genTwoFAEmergencyCodes', [TwoFA::class, 'genTwoFAEmergencyCodes'])
                ->register('setWidgetPosition', [$widgets, 'setWidgetPosition'], null, 'DASHBOARD_VIEW')
                ->register('closeWidget', [$widgets, 'closeWidget'], null, 'DASHBOARD_VIEW')
                ->register('addWidget', [$widgets, 'addWidget'], null, 'DASHBOARD_VIEW')
                ->register('expandWidget', [$widgets, 'expandWidget'], null, 'DASHBOARD_VIEW')
                ->register('getAvailableWidgets', [$widgets, 'getAvailableWidgetsIO'], null, 'DASHBOARD_VIEW')
                ->register('getRemoteData', [$widgets, 'getRemoteDataIO'], null, 'DASHBOARD_VIEW')
                ->register('getShopInfo', [$widgets, 'getShopInfoIO'], null, 'DASHBOARD_VIEW')
                ->register('truncateJtllog', [Jtllog::class, 'truncateLog'], null, 'DASHBOARD_VIEW')
                ->register('addFav', [$this, 'addFav'])
                ->register('reloadFavs', [$this, 'reloadFavs'])
                ->register('loadStats', [$images, 'loadStats'], null, 'DISPLAY_IMAGES_VIEW')
                ->register('cleanupStorage', [$images, 'cleanupStorage'], null, 'DISPLAY_IMAGES_VIEW')
                ->register('clearImageCache', [$images, 'clearImageCache'], null, 'DISPLAY_IMAGES_VIEW')
                ->register('generateImageCache', [$images, 'generateImageCache'], null, 'DISPLAY_IMAGES_VIEW')
                ->register('dbUpdateIO', [$updateIO, 'update'], null, 'SHOP_UPDATE_VIEW')
                ->register('dbupdaterBackup', [$updateIO, 'backup'], null, 'SHOP_UPDATE_VIEW')
                ->register('dbupdaterDownload', [$updateIO, 'download'], null, 'SHOP_UPDATE_VIEW')
                ->register('dbupdaterStatusTpl', [$updateIO, 'getStatus'], null, 'SHOP_UPDATE_VIEW')
                ->register('dbupdaterMigration', [$updateIO, 'executeMigration'], null, 'SHOP_UPDATE_VIEW')
                ->register('finishWizard', [$wizardIO, 'answerQuestions'], null, 'WIZARD_VIEW')
                ->register('validateStepWizard', [$wizardIO, 'validateStep'], null, 'WIZARD_VIEW')
                ->register(
                    'migrateToInnoDB_utf8',
                    [DBMigrationHelper::class, 'doMigrateToInnoDB_utf8'],
                    null,
                    'DBCHECK_VIEW'
                )
                ->register('redirectCheckAvailability', [Redirect::class, 'checkAvailability'])
                ->register('updateRedirectState', [$this, 'updateRedirectState'], null, 'REDIRECT_VIEW')
                ->register('getRandomPassword', [$this, 'getRandomPassword'], null, 'ACCOUNT_VIEW')
                ->register('saveBannerAreas', [BannerController::class, 'saveAreasIO'], null, 'DISPLAY_BANNER_VIEW')
                ->register('createSearchIndex', [$this, 'createSearchIndex'], null, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
                ->register('clearSearchCache', [$this, 'clearSearchCache'], null, 'SETTINGS_ARTICLEOVERVIEW_VIEW')
                ->register('adminSearch', [$searchController, 'adminSearch'], null, 'SETTINGS_SEARCH_VIEW')
                ->register('saveShippingSurcharge', [$this, 'saveShippingSurcharge'], null, 'ORDER_SHIPMENT_VIEW')
                ->register('deleteShippingSurcharge', [$this, 'deleteShippingSurcharge'], null, 'ORDER_SHIPMENT_VIEW')
                ->register(
                    'deleteShippingSurchargeZIP',
                    [$this, 'deleteShippingSurchargeZIP'],
                    null,
                    'ORDER_SHIPMENT_VIEW'
                )
                ->register(
                    'createShippingSurchargeZIP',
                    [$this, 'createShippingSurchargeZIP'],
                    null,
                    'ORDER_SHIPMENT_VIEW'
                )
                ->register('getShippingSurcharge', [$this, 'getShippingSurcharge'], null, 'ORDER_SHIPMENT_VIEW')
                ->register(
                    'exportformatSyntaxCheck',
                    [ExportSyntaxChecker::class, 'ioCheckSyntax'],
                    null,
                    'EXPORT_FORMATS_VIEW'
                )
                ->register('testExport', [ExportSyntaxChecker::class, 'testExport'], null, 'EXPORT_FORMATS_VIEW')
                ->register(
                    'mailvorlageSyntaxCheck',
                    [SyntaxChecker::class, 'ioCheckSyntax'],
                    null,
                    'CONTENT_EMAIL_TEMPLATE_VIEW'
                )
                ->register('notificationAction', [Notification::class, 'ioNotification'])
                ->register('pluginTestLoading', [Helper::class, 'ioTestLoading']);
        } catch (Exception $e) {
            return $io->getResponse(new IOError($e->getMessage(), $e->getCode()));
        }

        $req = $_REQUEST['io'];

        \executeHook(\HOOK_IO_HANDLE_REQUEST_ADMIN, [
            'io'      => &$io,
            'request' => &$req
        ]);

        if (\ob_get_length() > 0) {
            \ob_end_clean();
        }

        return $io->getResponse($io->handleRequest($req));
    }

    /**
     * @param float  $netPrice
     * @param float  $grossPrice
     * @param string $targetID
     * @return IOResponse
     */
    public function getCurrencyConversionIO($netPrice, $grossPrice, $targetID): IOResponse
    {
        $response = new IOResponse();
        $response->assignDom($targetID, 'innerHTML', Currency::getCurrencyConversion($netPrice, $grossPrice));

        return $response;
    }

    /**
     * @param float  $netPrice
     * @param float  $grossPrice
     * @param string $tooltipID
     * @return IOResponse
     */
    public function setCurrencyConversionTooltipIO($netPrice, $grossPrice, $tooltipID): IOResponse
    {
        $response = new IOResponse();
        $response->assignVar('originalTilte', Currency::getCurrencyConversion($netPrice, $grossPrice));

        return $response;
    }

    /**
     * @param string $title
     * @param string $url
     * @return array|IOError
     */
    public function addFav(string $title, string $url): array|IOError
    {
        $success = false;
        $adminID = $this->account->getID();
        if (!empty($title) && !empty($url)) {
            $success = (new AdminFavorite($this->db))->add($adminID, $title, $url);
        }

        if ($success) {
            $result = [
                'title' => $title,
                'url'   => $url
            ];
        } else {
            $result = new IOError('Unauthorized', 401);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function reloadFavs(): array
    {
        $tpl = $this->smarty->assign('favorites', $this->account->favorites())
            ->fetch('tpl_inc/favs_drop.tpl');

        return ['tpl' => $tpl];
    }

    /**
     * @return IOResponse
     * @throws Exception
     */
    public function getRandomPassword(): IOResponse
    {
        $response = new IOResponse();
        $password = Shop::Container()->getPasswordService()->generate(\PASSWORD_DEFAULT_LENGTH);
        $response->assignDom('cPass', 'value', $password);

        return $response;
    }
    /**
     * @param string $idx
     * @param string $create
     * @return array|IOError
     */
    public function createSearchIndex($idx, $create)
    {
        $this->getText->loadAdminLocale('pages/sucheinstellungen');
        $idx      = \mb_convert_case(Text::xssClean($idx), \MB_CASE_LOWER);
        $notice   = '';
        $errorMsg = '';
        if (!\in_array($idx, ['tartikel', 'tartikelsprache'], true)) {
            return new IOError(\__('errorIndexInvalid'), 403);
        }
        $keyName = 'idx_' . $idx . '_fulltext';
        try {
            if ($this->db->getSingleObject(
                'SHOW INDEX FROM ' . $idx . ' WHERE KEY_NAME = :keyName',
                ['keyName' => $keyName]
            )) {
                $this->db->query('ALTER TABLE ' . $idx . ' DROP KEY ' . $keyName);
            }
        } catch (Exception) {
            // Fehler beim Index löschen ignorieren
        }

        if ($create === 'Y') {
            $searchRows = \array_map(static function ($item) {
                $items = \explode('.', $item, 2);

                return $items[1];
            }, BaseSearchQuery::getSearchRows());

            switch ($idx) {
                case 'tartikel':
                    $rows = \array_intersect(
                        $searchRows,
                        [
                            'cName',
                            'cSeo',
                            'cSuchbegriffe',
                            'cArtNr',
                            'cKurzBeschreibung',
                            'cBeschreibung',
                            'cBarcode',
                            'cISBN',
                            'cHAN',
                            'cAnmerkung'
                        ]
                    );
                    break;
                case 'tartikelsprache':
                    $rows = \array_intersect($searchRows, ['cName', 'cSeo', 'cKurzBeschreibung', 'cBeschreibung']);
                    break;
                default:
                    return new IOError(\__('errorIndexInvalid'), 403);
            }

            /** @noinspection SqlWithoutWhere */
            $this->db->query('UPDATE tsuchcache SET dGueltigBis = DATE_ADD(NOW(), INTERVAL 10 MINUTE)');
            $res = $this->db->getPDOStatement(
                'ALTER TABLE ' . $idx . ' ADD FULLTEXT KEY idx_' . $idx . '_fulltext (' . \implode(', ', $rows) . ')'
            );

            if ($res->queryString === null) {
                $errorMsg     = \__('errorIndexNotCreatable');
                $shopSettings = Shopsetting::getInstance();
                $settings     = $shopSettings[Shopsetting::mapSettingName(\CONF_ARTIKELUEBERSICHT)];

                if ($settings['suche_fulltext'] !== 'N') {
                    $settings['suche_fulltext'] = 'N';
                    $this->db->update(
                        'teinstellungen',
                        ['kEinstellungenSektion', 'cName'],
                        [\CONF_ARTIKELUEBERSICHT, 'suche_fulltext'],
                        (object)['cWert' => 'N']
                    );
                    $this->cache->flushTags([
                        \CACHING_GROUP_OPTION,
                        \CACHING_GROUP_CORE,
                        \CACHING_GROUP_ARTICLE,
                        \CACHING_GROUP_CATEGORY
                    ]);
                    $shopSettings->reset();
                }
            } else {
                $notice = \sprintf(\__('successIndexCreate'), $idx);
            }
        } else {
            $notice = \sprintf(\__('successIndexDelete'), $idx);
        }

        return $errorMsg !== '' ? new IOError($errorMsg) : ['hinweis' => $notice];
    }

    /**
     * @return array
     * @noinspection SqlWithoutWhere
     */
    public function clearSearchCache(): array
    {
        $this->db->query('DELETE FROM tsuchcachetreffer');
        $this->db->query('DELETE FROM tsuchcache');
        $this->getText->loadAdminLocale('pages/sucheinstellungen');

        return ['hinweis' => \__('successSearchCacheDelete')];
    }

    /**
     * @param int $redirectID
     * @return bool
     */
    public function updateRedirectState(int $redirectID): bool
    {
        $url       = $this->db->select('tredirect', 'kRedirect', $redirectID)->cToUrl;
        $available = $url !== '' && Redirect::checkAvailability($url) ? 'y' : 'n';
        $this->db->update('tredirect', 'kRedirect', $redirectID, (object)['cAvailable' => $available]);

        return $available === 'y';
    }

    /**
     * @param int $id
     * @return stdClass
     * @throws SmartyException
     */
    public function getShippingSurcharge(int $id): stdClass
    {
        $this->getText->loadAdminLocale('pages/versandarten');
        $result       = new stdClass();
        $result->body = $this->smarty->assign('sprachen', LanguageHelper::getAllLanguages(0, true))
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
    public function saveShippingSurcharge(array $data): stdClass
    {
        $this->getText->loadAdminLocale('pages/versandarten');
        $post = [];
        foreach ($data as $item) {
            $post[$item['name']] = $item['value'];
        }
        $surcharge = (float)\str_replace(',', '.', $post['fZuschlag']);

        if (!$post['cName']) {
            $this->alertService->addError(\__('errorListNameMissing'), 'errorListNameMissing');
        }
        if (empty($surcharge)) {
            $this->alertService->addError(\__('errorListPriceMissing'), 'errorListPriceMissing');
        }
        if (!$this->alertService->alertTypeExists(Alert::TYPE_ERROR)) {
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
        $message = $this->smarty->assign('alertList', $this->alertService)
            ->fetch('snippets/alert_list.tpl');

        $this->cache->flushTags([
            \CACHING_GROUP_OBJECT,
            \CACHING_GROUP_OPTION,
            \CACHING_GROUP_ARTICLE
        ]);

        return (object)[
            'title'          => isset($surchargeTMP) ? $surchargeTMP->getTitle() : '',
            'priceLocalized' => isset($surchargeTMP) ? $surchargeTMP->getPriceLocalized() : '',
            'id'             => isset($surchargeTMP) ? $surchargeTMP->getID() : '',
            'reload'         => empty($post['kVersandzuschlag']),
            'message'        => $message,
            'error'          => $this->alertService->alertTypeExists(Alert::TYPE_ERROR)
        ];
    }

    /**
     * @param int $surchargeID
     * @return stdClass
     */
    public function deleteShippingSurcharge(int $surchargeID): stdClass
    {
        $this->db->queryPrepared(
            'DELETE tversandzuschlag, tversandzuschlagsprache, tversandzuschlagplz
                FROM tversandzuschlag
                LEFT JOIN tversandzuschlagsprache USING(kVersandzuschlag)
                LEFT JOIN tversandzuschlagplz USING(kVersandzuschlag)
                WHERE tversandzuschlag.kVersandzuschlag = :surchargeID',
            ['surchargeID' => $surchargeID]
        );
        $this->cache->flushTags([
            \CACHING_GROUP_OBJECT,
            \CACHING_GROUP_OPTION,
            \CACHING_GROUP_ARTICLE
        ]);

        return (object)['surchargeID' => $surchargeID];
    }

    /**
     * @param int    $surchargeID
     * @param string $ZIP
     * @return stdClass
     */
    public function deleteShippingSurchargeZIP(int $surchargeID, string $ZIP): stdClass
    {
        $partsZIP = \explode('-', $ZIP);
        if (\count($partsZIP) === 1) {
            $this->db->queryPrepared(
                'DELETE 
                    FROM tversandzuschlagplz
                    WHERE kVersandzuschlag = :surchargeID
                      AND cPLZ = :ZIP',
                [
                    'surchargeID' => $surchargeID,
                    'ZIP'         => $partsZIP[0]
                ]
            );
        } elseif (\count($partsZIP) === 2) {
            $this->db->queryPrepared(
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
        $this->cache->flushTags([
            \CACHING_GROUP_OBJECT,
            \CACHING_GROUP_OPTION,
            \CACHING_GROUP_ARTICLE
        ]);

        return (object)['surchargeID' => $surchargeID, 'ZIP' => $ZIP];
    }

    /**
     * @param array $data
     * @return stdClass
     * @throws SmartyException
     */
    public function createShippingSurchargeZIP(array $data): stdClass
    {
        $this > $this->getText->loadAdminLocale('pages/versandarten');

        $post = [];
        foreach ($data as $item) {
            $post[$item['name']] = $item['value'];
        }
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
            ->first(static function (ShippingSurcharge $surchargeTMP) use ($surchargeZip): bool {
                return ($surchargeTMP->hasZIPCode($surchargeZip->cPLZ)
                    || $surchargeTMP->hasZIPCode($surchargeZip->cPLZAb)
                    || $surchargeTMP->hasZIPCode($surchargeZip->cPLZBis)
                    || $surchargeTMP->areaOverlapsWithZIPCode($surchargeZip->cPLZAb, $surchargeZip->cPLZBis)
                );
            });
        if ($area !== null && !$area->lettersMatch()) {
            $this->alertService->addError(\__('errorZIPsDoNotMatch'), 'errorZIPsDoNotMatch');
        } elseif (empty($surchargeZip->cPLZ) && empty($surchargeZip->cPLZAb)) {
            $error = $zipValidator->getError();
            if ($error !== '') {
                $this->alertService->addError($error, 'errorZIPValidator');
            } else {
                $this->alertService->addError(\__('errorZIPMissing'), 'errorZIPMissing');
            }
        } elseif ($zipMatchSurcharge !== null) {
            $this->alertService->addError(
                \sprintf(
                    isset($surchargeZip->cPLZ) ? \__('errorZIPOverlap') : \__('errorZIPAreaOverlap'),
                    $surchargeZip->cPLZ ?? $surchargeZip->cPLZAb . ' - ' . $surchargeZip->cPLZBis,
                    $zipMatchSurcharge->getTitle()
                ),
                'errorZIPOverlap'
            );
        } elseif ($this->db->insert('tversandzuschlagplz', $surchargeZip)) {
            $this->alertService->addSuccess(\__('successZIPAdd'), 'successZIPAdd');
        }
        $this->cache->flushTags([
            \CACHING_GROUP_OBJECT,
            \CACHING_GROUP_OPTION,
            \CACHING_GROUP_ARTICLE
        ]);

        $message = $this->smarty->assign('alertList', $this->alertService)
            ->fetch('snippets/alert_list.tpl');
        $badges  = $this->smarty->assign('surcharge', new ShippingSurcharge($surcharge->getID()))
            ->fetch('snippets/zuschlagliste_plz_badges.tpl');

        return (object)['message' => $message, 'badges' => $badges, 'surchargeID' => $surcharge->getID()];
    }
}
