<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'browsererkennung.php';
require_once PFAD_ROOT . PFAD_PHPQUERY . 'phpquery.class.php';
require_once PFAD_ROOT . PFAD_SMARTY . 'SmartyBC.class.php';

/**
 * Class JTLSmarty
 */
class JTLSmarty extends SmartyBC
{
    /**
     * @var JTLCache|null
     */
    public $jtlCache = null;

    /**
     * @var null|array
     */
    public $config = null;

    /**
     * @var array
     */
    public $_cache_include_info;

    /**
     * @var int
     */
    public $error_reporting = 0;

    /**
     * @var Template
     */
    public $template;

    /**
     * @var JTLSmarty|null
     */
    public static $_instance = null;

    /**
     * @var string
     */
    public $context = 'frontend';

    /**
     * @var array
     */
    private static $_replacer = array(
        'productdetails/index.tpl'                              => 'artikel.tpl',
        'productwizard/index.tpl'                               => 'auswahlassistent.tpl',
        'checkout/order_completed.tpl'                          => 'bestellabschluss.tpl',
        'checkout/index.tpl'                                    => 'bestellvorgang.tpl',
        'productdetails/review_form.tpl'                        => 'bewertung_formular.tpl',
        'account/index.tpl'                                     => 'jtl.tpl',
        'contact/index.tpl'                                     => 'kontakt.tpl',
        'blog/index.tpl'                                        => 'news.tpl',
        'newsletter/index.tpl'                                  => 'newsletter.tpl',
        'account/password.tpl'                                  => 'passwort_vergessen.tpl',
        'checkout/download_popup.tpl'                           => 'popup.tpl',
        'register/index.tpl'                                    => 'registrieren.tpl',
        'layout/index.tpl'                                      => 'seite.tpl',
        'productlist/index.tpl'                                 => 'suche.tpl',
        'productdetails/recommendation.tpl'                     => 'tpl_inc/artikel_artikelweiterempfehlenformular.tpl',
        'productdetails/reviews.tpl'                            => 'tpl_inc/artikel_bewertung.tpl',
        'productdetails/review_item.tpl'                        => 'tpl_inc/artikel_bewertung_kommentar.tpl',
        'productdetails/download.tpl'                           => 'tpl_inc/artikel_downloads.tpl',
        'productdetails/finance.tpl'                            => 'tpl_inc/artikel_finanzierung.tpl',
        'productdetails/finance_popup.tpl'                      => 'tpl_inc/artikel_finanzierung_popup.tpl',
        'productdetails/question_on_item.tpl'                   => 'tpl_inc/artikel_fragezumproduktformular.tpl',
        'productdetails/pushed.tpl'                             => 'tpl_inc/artikel_hinzugefuegt.tpl',
        'productdetails/details.tpl'                            => 'tpl_inc/artikel_inc.tpl',
        'productdetails/actions.tpl'                            => 'tpl_inc/artikel_inc_aktionen.tpl',
        'productdetails/attributes.tpl'                         => 'tpl_inc/artikel_inc_attribute.tpl',
        'productdetails/image.tpl'                              => 'tpl_inc/artikel_inc_bild.tpl',
        'productdetails/bundle.tpl'                             => 'tpl_inc/artikel_inc_bundle.tpl',
        'productdetails/form.tpl'                               => 'tpl_inc/artikel_inc_form.tpl',
        'productdetails/stock.tpl'                              => 'tpl_inc/artikel_inc_lagerbestand.tpl',
        'productdetails/rating.tpl'                             => 'tpl_inc/artikel_inc_stars.tpl',
        'productdetails/tabs.tpl'                               => 'tpl_inc/artikel_inc_tabs.tpl',
        'productdetails/tags.tpl'                               => 'tpl_inc/artikel_inc_tags.tpl',
        'productdetails/variation.tpl'                          => 'tpl_inc/artikel_inc_variationen.tpl',
        'productdetails/variation_value.tpl'                    => 'tpl_inc/artikel_inc_variationen_wert.tpl',
        'productdetails/basket.tpl'                             => 'tpl_inc/artikel_inc_warenkorb.tpl',
        'productlist/item_box.tpl'                              => 'tpl_inc/artikel_item_box.tpl',
        'productlist/item_list.tpl'                             => 'tpl_inc/artikel_item_list.tpl',
        'productdetails/config.tpl'                             => 'tpl_inc/artikel_konfigurator.tpl',
        'productdetails/config_summary.tpl'                     => 'tpl_inc/artikel_konfigurator_summary.tpl',
        'productdetails/matrix.tpl'                             => 'tpl_inc/artikel_matrix.tpl',
        'productdetails/mediafile.tpl'                          => 'tpl_inc/artikel_mediendatei.tpl',
        'productdetails/popups.tpl'                             => 'tpl_inc/artikel_popups.tpl',
        'productdetails/price.tpl'                              => 'tpl_inc/artikel_preis.tpl',
        'productdetails/price_history.tpl'                      => 'tpl_inc/artikel_preisverlauf.tpl',
        'productdetails/availability_notification_form.tpl'     => 'tpl_inc/artikel_produktverfuegbarformular.tpl',
        'productdetails/slider.tpl'                             => 'tpl_inc/artikel_slider.tpl',
        'productdetails/upload.tpl'                             => 'tpl_inc/artikel_uploads.tpl',
        'productdetails/variation_dependencies.tpl'             => 'tpl_inc/artikel_variations_abhaengigkeiten.tpl',
        'productdetails/warehouse.tpl'                          => 'tpl_inc/artikel_warenlager.tpl',
        'productdetails/redirect.tpl'                           => 'tpl_inc/artikel_weiterleitung.tpl',
        'productwizard/form.tpl'                                => 'tpl_inc/auswahlassistent_inc.tpl',
        'account/retrospective_payment.tpl'                     => 'tpl_inc/bestellab_again_zusatzschritt.tpl',
        'checkout/conversion_tracking.tpl'                      => 'tpl_inc/bestellabschluss_conversion_tracking.tpl',
        'checkout/inc_order_completed.tpl'                      => 'tpl_inc/bestellabschluss_fertig.tpl',
        'checkout/inc_trustedshops_excellence.tpl'              => 'tpl_inc/bestellabschluss_trustedshops.tpl',
        'checkout/inc_paymentmodules.tpl'                       => 'tpl_inc/bestellabschluss_weiterleitung.tpl',
        'checkout/step0_login_or_register.tpl'                  => 'tpl_inc/bestellvorgang_accountwahl.tpl',
        'checkout/step5_confirmation.tpl'                       => 'tpl_inc/bestellvorgang_bestaetigung.tpl',
        'checkout/step2_delivery_address.tpl'                   => 'tpl_inc/bestellvorgang_lieferadresse.tpl',
        'checkout/inc_order_items.tpl'                          => 'tpl_inc/bestellvorgang_positionen.tpl',
        'checkout/inc_steps.tpl'                                => 'tpl_inc/bestellvorgang_steps.tpl',
        'checkout/step1_proceed_as_guest.tpl'                   => 'tpl_inc/bestellvorgang_unregistriert_formular.tpl',
        'checkout/step3_shipping_options.tpl'                   => 'tpl_inc/bestellvorgang_versand.tpl',
        'checkout/step4_payment_options.tpl'                    => 'tpl_inc/bestellvorgang_zahlung.tpl',
        'checkout/step4_payment_additional.tpl'                 => 'tpl_inc/bestellvorgang_zahlung_zusatzschritt.tpl',
        'checkout/modules/creditcard.tpl'                       => 'tpl_inc/bestellvorgang_zahlungsart_kreditkarte.tpl',
        'checkout/modules/direct_debit.tpl'                     => 'tpl_inc/bestellvorgang_zahlungsart_lastschrift.tpl',
        'checkout/step6_init_payment.tpl'                       => 'tpl_inc/bestellvorgang_zahlungsvorgang.tpl',
        'boxes/box_bestseller.tpl'                              => 'tpl_inc/boxes/box_bestseller.tpl',
        'boxes/box_container.tpl'                               => 'tpl_inc/boxes/box_container.tpl',
        'boxes/box_custom.tpl'                                  => 'tpl_inc/boxes/box_eigene.tpl',
        'boxes/box_custom_empty.tpl'                            => 'tpl_inc/boxes/box_eigene_leer.tpl',
        'boxes/box_coming_soon.tpl'                             => 'tpl_inc/boxes/box_erscheinende_produkte.tpl',
        'boxes/box_filter_rating.tpl'                           => 'tpl_inc/boxes/box_filter_bewertung.tpl',
        'boxes/box_filter_characteristics.tpl'                  => 'tpl_inc/boxes/box_filter_merkmale.tpl',
        'boxes/box_filter_pricerange.tpl'                       => 'tpl_inc/boxes/box_filter_preisspanne.tpl',
        'boxes/box_filter_search.tpl'                           => 'tpl_inc/boxes/box_filter_suche.tpl',
        'boxes/box_filter_search_special.tpl'                   => 'tpl_inc/boxes/box_filter_suchspecial.tpl',
        'boxes/box_filter_tag.tpl'                              => 'tpl_inc/boxes/box_filter_tag.tpl',
        'boxes/box_characteristics_global.tpl'                  => 'tpl_inc/boxes/box_globale_merkmale.tpl',
        'boxes/box_manufacturers.tpl'                           => 'tpl_inc/boxes/box_hersteller.tpl',
        'boxes/box_info.tpl'                                    => 'tpl_inc/boxes/box_informationen.tpl',
        'boxes/box_categories.tpl'                              => 'tpl_inc/boxes/box_kategorien.tpl',
        'boxes/box_config.tpl'                                  => 'tpl_inc/boxes/box_konfig.tpl',
        'boxes/box_linkgroups.tpl'                              => 'tpl_inc/boxes/box_linkgruppe.tpl',
        'boxes/box_login.tpl'                                   => 'tpl_inc/boxes/box_login.tpl',
        'boxes/box_new_in_stock.tpl'                            => 'tpl_inc/boxes/box_neu_im_sortiment.tpl',
        'boxes/box_news_categories.tpl'                         => 'tpl_inc/boxes/box_news_kategorien.tpl',
        'boxes/box_news_month.tpl'                              => 'tpl_inc/boxes/box_news_monat.tpl',
        'boxes/box_priceradar.tpl'                              => 'tpl_inc/boxes/box_preisradar.tpl',
        'boxes/box_direct_purchase.tpl'                         => 'tpl_inc/boxes/box_schnelleinkauf.tpl',
        'boxes/box_special_offer.tpl'                           => 'tpl_inc/boxes/box_sonderangebote.tpl',
        'boxes/box_search_cloud.tpl'                            => 'tpl_inc/boxes/box_suchwolke.tpl',
        'boxes/box_tag_cloud.tpl'                               => 'tpl_inc/boxes/box_tagwolke.tpl',
        'boxes/box_top_offer.tpl'                               => 'tpl_inc/boxes/box_top_angebot.tpl',
        'boxes/box_top_rated.tpl'                               => 'tpl_inc/boxes/box_top_bewertet.tpl',
        'boxes/box_trustedshops_reviews.tpl'                    => 'tpl_inc/boxes/box_trustedshops_kundenbewertung.tpl',
        'boxes/box_trustedshops_seal.tpl'                       => 'tpl_inc/boxes/box_trustedshops_siegel.tpl',
        'boxes/box_poll.tpl'                                    => 'tpl_inc/boxes/box_umfrage.tpl',
        'boxes/box_comparelist.tpl'                             => 'tpl_inc/boxes/box_vergleichsliste.tpl',
        'boxes/box_basket.tpl'                                  => 'tpl_inc/boxes/box_warenkorb.tpl',
        'boxes/box_wishlist.tpl'                                => 'tpl_inc/boxes/box_wunschliste.tpl',
        'boxes/box_last_seen.tpl'                               => 'tpl_inc/boxes/box_zuletzt_angesehen.tpl',
        'snippets/categories_recursive.tpl'                     => 'tpl_inc/categories_recursive.tpl',
        'snippets/filter/review.tpl'                            => 'tpl_inc/filter/filter_bewertung.tpl',
        'snippets/filter/characteristic.tpl'                    => 'tpl_inc/filter/filter_merkmale.tpl',
        'snippets/filter/pricerange.tpl'                        => 'tpl_inc/filter/filter_preisspanne.tpl',
        'snippets/filter/search.tpl'                            => 'tpl_inc/filter/filter_suche.tpl',
        'snippets/filter/special.tpl'                           => 'tpl_inc/filter/filter_suchspecial.tpl',
        'snippets/filter/tag.tpl'                               => 'tpl_inc/filter/filter_tag.tpl',
        'layout/footer.tpl'                                     => 'tpl_inc/footer.tpl',
        'layout/header.tpl'                                     => 'tpl_inc/header.tpl',
        'layout/breadcrumb.tpl'                                 => 'tpl_inc/inc_breadcrumb.tpl',
        'snippets/extension.tpl'                                => 'tpl_inc/inc_extension.tpl',
        'checkout/coupon_form.tpl'                              => 'tpl_inc/inc_kupon_guthaben.tpl',
        'checkout/inc_delivery_address.tpl'                     => 'tpl_inc/inc_lieferadresse.tpl',
        'checkout/inc_billing_address.tpl'                      => 'tpl_inc/inc_rechnungsadresse.tpl',
        'productlist/result_options.tpl'                        => 'tpl_inc/inc_result_options.tpl',
        'layout/footnotes.tpl'                                  => 'tpl_inc/inc_seite.tpl',
        'snippets/trustbadge.tpl'                               => 'tpl_inc/inc_trustedshops.tpl',
        'account/delete_account.tpl'                            => 'tpl_inc/jtl_account_loeschen.tpl',
        'account/order_details.tpl'                             => 'tpl_inc/jtl_bestellung.tpl',
        'account/order_item.tpl'                                => 'tpl_inc/jtl_bestellung_position.tpl',
        'account/downloads.tpl'                                 => 'tpl_inc/jtl_downloads.tpl',
        'account/customers_recruiting.tpl'                      => 'tpl_inc/jtl_kundenwerbenkunden.tpl',
        'account/login.tpl'                                     => 'tpl_inc/jtl_login.tpl',
        'account/my_account.tpl'                                => 'tpl_inc/jtl_meinkonto.tpl',
        'account/change_password.tpl'                           => 'tpl_inc/jtl_passwort_aendern.tpl',
        'account/address_form.tpl'                              => 'tpl_inc/jtl_rechnungsdaten.tpl',
        'account/uploads.tpl'                                   => 'tpl_inc/jtl_uploads.tpl',
        'account/wishlist.tpl'                                  => 'tpl_inc/jtl_wunschliste.tpl',
        'account/wishlist_email_form.tpl'                       => 'tpl_inc/jtl_wunschliste_emailversand.tpl',
        'checkout/inc_billing_address_form.tpl'                 => 'tpl_inc/kundenformular.tpl',
        'snippets/linkgroup_list.tpl'                           => 'tpl_inc/linkgroup_list.tpl',
        'checkout/modules/billpay/bestellabschluss.tpl'         => 'tpl_inc/modules/billpay/bestellabschluss.tpl',
        'checkout/modules/billpay/paylater.tpl'                 => 'tpl_inc/modules/billpay/paylater.tpl',
        'checkout/modules/billpay/raten.tpl'                    => 'tpl_inc/modules/billpay/raten.tpl',
        'checkout/modules/billpay/zusatzschritt.tpl'            => 'tpl_inc/modules/billpay/zusatzschritt.tpl',
        'checkout/modules/eos/bestellabschluss.tpl'             => 'tpl_inc/modules/eos/bestellabschluss.tpl',
        'checkout/modules/eos/eos.css'                          => 'tpl_inc/modules/eos/eos.css',
        'checkout/modules/heidelpay/bestellabschluss.tpl'       => 'tpl_inc/modules/heidelpay/bestellabschluss.tpl',
        'checkout/modules/iclear/bestellabschluss.tpl'          => 'tpl_inc/modules/iclear/bestellabschluss.tpl',
        'checkout/modules/invoice.tpl'                          => 'tpl_inc/modules/invoice.tpl',
        'checkout/modules/moneybookers_qc/bestellabschluss.tpl' => 'tpl_inc/modules/moneybookers_qc/bestellabschluss.tpl',
        'checkout/modules/paymentpartner/bestellabschluss.tpl'  => 'tpl_inc/modules/paymentpartner/bestellabschluss.tpl',
        'checkout/modules/paypal/bestellabschluss.tpl'          => 'tpl_inc/modules/paypal/bestellabschluss.tpl',
        'checkout/modules/postfinance/bestellabschluss.tpl'     => 'tpl_inc/modules/postfinance/bestellabschluss.tpl',
        'checkout/modules/uos/bestellabschluss.tpl'             => 'tpl_inc/modules/uos/bestellabschluss.tpl',
        'checkout/modules/ut/bestellabschluss.tpl'              => 'tpl_inc/modules/ut/bestellabschluss.tpl',
        'checkout/modules/wirecard/bestellabschluss.tpl'        => 'tpl_inc/modules/wirecard/bestellabschluss.tpl',
        'blog/details.tpl'                                      => 'tpl_inc/news_detailansicht.tpl',
        'blog/month_overview.tpl'                               => 'tpl_inc/news_monatsuebersicht.tpl',
        'blog/overview.tpl'                                     => 'tpl_inc/news_uebersicht.tpl',
        'account/download_preview.tpl'                          => 'tpl_inc/popup_download_vorschau.tpl',
        'register/form.tpl'                                     => 'tpl_inc/registrieren_formular.tpl',
        'page/404.tpl'                                          => 'tpl_inc/seite_404.tpl',
        'page/free_gift.tpl'                                    => 'tpl_inc/seite_gratisgeschenk.tpl',
        'page/manufacturers.tpl'                                => 'tpl_inc/seite_hersteller.tpl',
        'page/livesearch.tpl'                                   => 'tpl_inc/seite_livesuche.tpl',
        'page/news_archive.tpl'                                 => 'tpl_inc/seite_newsarchiv.tpl',
        'page/newsletter_archive.tpl'                           => 'tpl_inc/seite_newsletterarchiv.tpl',
        'page/sitemap.tpl'                                      => 'tpl_inc/seite_sitemap.tpl',
        'page/index.tpl'                                        => 'tpl_inc/seite_startseite.tpl',
        'page/tagging.tpl'                                      => 'tpl_inc/seite_tagging.tpl',
        'page/shipping.tpl'                                     => 'tpl_inc/seite_versand.tpl',
        'productlist/bestseller.tpl'                            => 'tpl_inc/suche_bestseller.tpl',
        'productlist/financing.tpl'                             => 'tpl_inc/suche_finanzierung.tpl',
        'productlist/footer.tpl'                                => 'tpl_inc/suche_footer.tpl',
        'productlist/header.tpl'                                => 'tpl_inc/suche_header.tpl',
        'poll/progress.tpl'                                     => 'tpl_inc/umfrage_durchfuehren.tpl',
        'poll/result.tpl'                                       => 'tpl_inc/umfrage_ergebnis.tpl',
        'poll/overview.tpl'                                     => 'tpl_inc/umfrage_uebersicht.tpl',
        'basket/cart_dropdown.tpl'                              => 'tpl_inc/warenkorb_mini.tpl',
        'basket/cart_dropdown_label.tpl'                        => 'tpl_inc/warenkorb_mini_label.tpl',
        'poll/index.tpl'                                        => 'umfrage.tpl',
        'comparelist/index.tpl'                                 => 'vergleichsliste.tpl',
        'basket/index.tpl'                                      => 'warenkorb.tpl',
        'snippets/maintenance.tpl'                              => 'wartung.tpl',
        'snippets/wishlist.tpl'                                 => 'wunschliste.tpl'
    );

    /**
     * @var int
     */
    public $_file_perms = 0664;

    /**
     * @var bool
     */
    private static $isCached = false;

    /**
     * @var string
     */
    public $template_dir;

    /**
     * @var bool
     */
    public static $isChildTemplate = false;

    /**
     * modified constructor with custom initialisation
     *
     * @param bool   $fast_init - set to true when init from backend to avoid setting session data
     * @param bool   $isAdmin
     * @param bool   $tplCache
     * @param string $context
     */
    public function __construct($fast_init = false, $isAdmin = false, $tplCache = true, $context = 'frontend')
    {
        parent::__construct(array());
        Smarty::$_CHARSET = JTL_CHARSET;
        if (defined('SMARTY_USE_SUB_DIRS') && is_bool(SMARTY_USE_SUB_DIRS)) {
            $this->setUseSubDirs(SMARTY_USE_SUB_DIRS);
        }
        $this->setErrorReporting(SMARTY_LOG_LEVEL)
             ->setForceCompile(SMARTY_FORCE_COMPILE ? true : false)
             ->setDebugging(SMARTY_DEBUG_CONSOLE ? true : false);

        $this->config = Shop::getSettings(array(CONF_TEMPLATE, CONF_CACHING));
        $template     = ($isAdmin) ? AdminTemplate::getInstance() : Template::getInstance();
        $cTemplate    = $template->getDir();
        $parent       = null;
        if ($isAdmin === false) {
            $parent      = $template->getParent();
            $_compileDir = PFAD_ROOT . PFAD_COMPILEDIR . $cTemplate . '/';
            if (!file_exists($_compileDir)) {
                mkdir($_compileDir);
            }
            $this->setTemplateDir(array($this->context => PFAD_ROOT . PFAD_TEMPLATES . $cTemplate . '/'))
                 ->setCompileDir($_compileDir)
                 ->setCacheDir(PFAD_ROOT . PFAD_COMPILEDIR . $cTemplate . '/' . 'page_cache/')
                 ->setPluginsDir(SMARTY_PLUGINS_DIR);

            if ($parent !== null) {
                self::$isChildTemplate = true;
                $this->addTemplateDir(PFAD_ROOT . PFAD_TEMPLATES . $parent, $parent . '/')
                     ->assign('parent_template_path', PFAD_ROOT . PFAD_TEMPLATES . $parent . '/')
                     ->assign('parentTemplateDir', PFAD_TEMPLATES . $parent . '/');
            }

            $this->template_dir = PFAD_ROOT . PFAD_TEMPLATES . $cTemplate . '/';
        } else {
            $_compileDir = PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR;
            if (!file_exists($_compileDir)) {
                mkdir($_compileDir);
            }
            $this->context = 'backend';
            $this->setCaching(false)
                 ->setDebugging(SMARTY_DEBUG_CONSOLE ? true : false)
                 ->setCompileCheck(SMARTY_FORCE_COMPILE ? true : false) //work-around for smarty 3.1.27 - templates always get parsed when configLoad() is called, @todo: removed when using fixed smarty version
                 ->setTemplateDir(array($this->context => PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $cTemplate))
                 ->setCompileDir($_compileDir)
                 ->setConfigDir(PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $cTemplate . '/lang/')
                 ->setPluginsDir(SMARTY_PLUGINS_DIR)
                 ->configLoad('german.conf', 'global');
            unset($this->config['caching']['page_cache']);
        }
        $this->template = $template;

        if ($fast_init === false) {
            $this->registerPlugin('function', 'lang', array($this, '__gibSprachWert'))
                 ->registerPlugin('modifier', 'replace_delim', array($this, 'replaceDelimiters'))
                 ->registerPlugin('modifier', 'count_characters', array($this, 'countCharacters'))
                 ->registerPlugin('modifier', 'string_format', array($this, 'stringFormat'))
                 ->registerPlugin('modifier', 'string_date_format', array($this, 'dateFormat'))
                 ->registerPlugin('modifier', 'truncate', array($this, 'truncate'));

            if ($isAdmin === false) {
                $this->registerFilter('output', array($this, '__outputFilter'))
                     ->registerFilter('output', array($this, '__cacheOutputFilter'));
                $this->cache_lifetime = (isset($cacheOptions['expiration']) && ((int) $cacheOptions['expiration'] > 0)) ? $cacheOptions['expiration'] : 86400;
                //assign variables moved from $_SESSION to cache to smarty
                $linkHelper = LinkHelper::getInstance();
                $linkGroups = $linkHelper->getLinkGroups();
                if ($linkGroups === null) {
                    //this can happen when there is a $_SESSION active and object cache is being flushed
                    //since setzeLinks() is only executed in class.core.Session.php
                    $linkGroups = setzeLinks();
                }
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.helper.Hersteller.php';
                $manufacturerHelper = HerstellerHelper::getInstance();
                $manufacturers      = $manufacturerHelper->getManufacturers();
                $this->assign('linkgroups', $linkGroups)
                     ->assign('manufacturers', $manufacturers);

                $this->template_class = 'jtlTplClass';
            }
            if (!$isAdmin) {
                $this->setCachingParams(false, $this->config);
            }
            $_tplDir = $this->getTemplateDir($this->context);
            if (file_exists($_tplDir . 'php/functions_custom.php')) {
                global $smarty;
                $smarty = $this;
                require_once $_tplDir . 'php/functions_custom.php';
            } elseif (file_exists($_tplDir . 'php/functions.php')) {
                global $smarty;
                $smarty = $this;
                require_once $_tplDir . 'php/functions.php';
            } elseif ($parent !== null && file_exists(PFAD_ROOT . PFAD_TEMPLATES . $parent . '/php/functions.php')) {
                global $smarty;
                $smarty = $this;
                require_once PFAD_ROOT . PFAD_TEMPLATES . $parent . '/php/functions.php';
            }
        }
        if ($context === 'frontend' || $context === 'backend') {
            self::$_instance = $this;
        }
    }

    /**
     * set options
     *
     * @param bool  $force
     * @param array $config
     * @return $this
     */
    public function setCachingParams($force = false, $config = null)
    {
        $caching      = self::CACHING_OFF;
        $compileCheck = true;
        //instantiate new cache - we use different options here
        if ($config === null) {
            $config = Shop::getSettings(array(CONF_CACHING));
        }
        if (isset($config['caching']['caching_page_cache'])) {
            if ($config['caching']['caching_page_cache'] === '1' || $config['caching']['caching_page_cache'] === '2' || $config['caching']['caching_page_cache'] === '3') {
                $caching = self::CACHING_LIFETIME_CURRENT;
            }
            if ($config['caching']['caching_page_cache'] === '2' && !empty($_SESSION['Kunde']->kKunde)) {
                //for guests only
                $caching = self::CACHING_OFF;
            } elseif ($config['caching']['caching_page_cache'] === '3' && isset($_SESSION['Warenkorb']->PositionenArr) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
                //with empty carts only
                $caching = self::CACHING_OFF;
            }
        }
        if (isset($config['caching']['compile_check']) && $config['caching']['compile_check'] === 'N') {
            $compileCheck = false;
        }
        if ($caching === 1 || $force === true) {
            if (!file_exists($this->getCacheDir())) {
                mkdir($this->getCacheDir());
            }
            if (isset($config['caching']['advanced_page_cache']) && $config['caching']['advanced_page_cache'] === 'Y') {
                $this->registerCacheResource('jtlSmartyCache', new jtlSmartyCache($config));
                $this->caching_type = 'jtlSmartyCache';
            }
            $caching = self::CACHING_LIFETIME_CURRENT;
        }
        $this->setCaching($caching)
             ->setCompileCheck($compileCheck);

        return $this;
    }

    /**
     * @param bool $fast_init
     * @param bool $isAdmin
     * @return JTLSmarty|null
     */
    public static function getInstance($fast_init = false, $isAdmin = false)
    {
        return (self::$_instance === null) ? new self($fast_init, $isAdmin) : self::$_instance;
    }

    /**
     * phpquery output filter
     *
     * @param string $tplOutput
     * @return string
     */
    public function __outputFilter($tplOutput)
    {
        $hookList = Plugin::getHookList();
        if ((isset($hookList[HOOK_SMARTY_OUTPUTFILTER]) &&
                is_array($hookList[HOOK_SMARTY_OUTPUTFILTER]) &&
                count($hookList[HOOK_SMARTY_OUTPUTFILTER]) > 0) ||
            $this->template->isMobileTemplateActive()
        ) {
            $this->unregisterFilter('output', array($this, '__outputFilter'));
            $GLOBALS['doc'] = phpQuery::newDocumentHTML($tplOutput, JTL_CHARSET);
            if ($this->template->isMobileTemplateActive()) {
                executeHook(HOOK_SMARTY_OUTPUTFILTER_MOBILE);
            } else {
                executeHook(HOOK_SMARTY_OUTPUTFILTER);
            }
            $this->registerFilter('output', array($this, '__outputFilter'));
            $tplOutput = $GLOBALS['doc']->htmlOuter();
        }
        if (isset($this->config['template']['general']['minify_html']) && $this->config['template']['general']['minify_html'] === 'Y') {
            $minifyCSS = (isset($this->config['template']['general']['minify_html_css']) && $this->config['template']['general']['minify_html_css'] === 'Y');
            $minifyJS  = (isset($this->config['template']['general']['minify_html_js']) && $this->config['template']['general']['minify_html_js'] === 'Y');
            $tplOutput = $this->minify_html($tplOutput, $minifyCSS, $minifyJS);
        }

        return $tplOutput;
    }

    /**
     * @param null|string $template
     * @param null|string $cache_id
     * @param null|string $compile_id
     * @param null $parent
     * @return bool
     */
    public function isCached($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        $res            = parent::isCached($template, $cache_id, $compile_id, $parent);
        self::$isCached = $res;
        if (isset($this->config['caching']['page_cache_debugging']) && $this->config['caching']['page_cache_debugging'] === 'Y') {
            header('JTL-Cached: ' . (($res === true) ? 'true' : 'false'));
        }

        return $res;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setCaching($mode)
    {
        $this->caching = $mode;

        return $this;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setDebugging($mode)
    {
        $this->debugging = $mode;

        return $this;
    }

    /**
     * output filter to access cached template parts
     *
     * @param string $tplOutput
     * @return string
     */
    public function __cacheOutputFilter($tplOutput)
    {
        //only execute phpquery if this hook is used by plugins
        $hookList = Plugin::getHookList();
        if (isset($hookList[HOOK_SMARTY_OUTPUTFILTER_CACHE]) &&
            is_array($hookList[HOOK_SMARTY_OUTPUTFILTER_CACHE]) &&
            count($hookList[HOOK_SMARTY_OUTPUTFILTER_CACHE]) > 0
        ) {
            $doc = phpQuery::newDocumentHTML($tplOutput, JTL_CHARSET);
            executeHook(HOOK_SMARTY_OUTPUTFILTER_CACHE, array('smarty' => $this));
            $tplOutput = $doc->htmlOuter();
        }

        return $tplOutput;
    }

    /**
     * html minification
     *
     * @param string $html
     * @param bool   $minifyCSS
     * @param bool   $minifyJS
     * @return string
     */
    private function minify_html($html, $minifyCSS = false, $minifyJS = false)
    {
        require_once PFAD_ROOT . PFAD_MINIFY . '/lib/Minify/Loader.php';
        Minify_Loader::register();
        $options = array();
        if ($minifyCSS === true) {
            $options['cssMinifier'] = array('Minify_CSS', 'minify');
        }
        if ($minifyJS === true) {
            $options['jsMinifier'] = array('JSMin', 'minify');
        }
        $minify = new Minify_HTML($html, $options);

        return $minify->process();
    }

    /**
     * translation
     *
     * @param array                    $params
     * @param Smarty_Internal_Template $template
     * @return void|string
     */
    public function __gibSprachWert($params, Smarty_Internal_Template $template)
    {
        $cValue = '';
        if (!isset($params['section'])) {
            $params['section'] = 'global';
        }
        if (isset($params['section']) && isset($params['key'])) {
            $cValue = Shop::Lang()->get($params['key'], $params['section']);
            // Für vsprintf ein String der :: exploded wird
            if (isset($params['printf']) && strlen($params['printf']) > 0) {
                $cValue = vsprintf($cValue, explode(':::', $params['printf']));
            }
        }
        if (SMARTY_SHOW_LANGKEY) {
            $cValue = '#' . $params['section'] . '.' . $params['key'] . '#';
        }
        if (isset($params['assign'])) {
            $template->assign($params['assign'], $cValue);
        } else {
            return $cValue;
        }
    }

    /**
     * @param string $text
     * @return int
     */
    public function countCharacters($text)
    {
        return strlen($text);
    }

    /**
     * @param string $string
     * @param string $format
     * @return string
     */
    public function stringFormat($string, $format)
    {
        return sprintf($format, $string);
    }

    /**
     * @param string $string
     * @param string $format
     * @param string $default_date
     * @return string
     */
    public function dateFormat($string, $format = '%b %e, %Y', $default_date = '')
    {
        if ($string != '') {
            $timestamp = smarty_make_timestamp($string);
        } elseif ($default_date != '') {
            $timestamp = smarty_make_timestamp($default_date);
        } else {
            return $string;
        }
        if (DIRECTORY_SEPARATOR == '\\') {
            $_win_from = array('%D', '%h', '%n', '%r', '%R', '%t', '%T');
            $_win_to   = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
            if (strpos($format, '%e') !== false) {
                $_win_from[] = '%e';
                $_win_to[]   = sprintf('%\' 2d', date('j', $timestamp));
            }
            if (strpos($format, '%l') !== false) {
                $_win_from[] = '%l';
                $_win_to[]   = sprintf('%\' 2d', date('h', $timestamp));
            }
            $format = str_replace($_win_from, $_win_to, $format);
        }

        return strftime($format, $timestamp);
    }

    /**
     * @param        $string
     * @param int    $length
     * @param string $etc
     * @param bool   $break_words
     * @param bool   $middle
     * @return mixed|string
     */
    public function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
    {
        if ($length == 0) {
            return '';
        }
        if (strlen($string) > $length) {
            $length -= min($length, strlen($etc));
            if (!$break_words && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
            }
            if (!$middle) {
                return substr($string, 0, $length) . $etc;
            }

            return substr($string, 0, $length / 2) . $etc . substr($string, -$length / 2);
        }

        return $string;
    }

    /**
     * @param string $cText
     * @return string
     */
    public function replaceDelimiters($cText)
    {
        $Einstellungen = Shop::getSettings(array(CONF_GLOBAL));
        $cReplace      = $Einstellungen['global']['global_dezimaltrennzeichen_sonstigeangaben'];
        if (strlen($cReplace) === 0 || $cReplace !== ',' || $cReplace !== '.') {
            $cReplace = ',';
        }

        return str_replace('.', $cReplace, $cText);
    }

    /**
     * @param string $cFilename
     * @return string
     */
    public function getCustomFile($cFilename)
    {
        if (self::$isChildTemplate === true || !isset($this->config['template']['general']['use_customtpl']) || $this->config['template']['general']['use_customtpl'] !== 'Y') {
            //disabled on child templates for now
            return $cFilename;
        }
        $cFile    = basename($cFilename, '.tpl');
        $cSubPath = dirname($cFilename);
        if (strpos($cSubPath, PFAD_ROOT) === false) {
            $cCustomFile = $this->getTemplateDir($this->context) . (($cSubPath === '.') ? '' : ($cSubPath . '/')) . $cFile . '_custom.tpl';
        } else {
            $cCustomFile = $cSubPath . '/' . $cFile . '_custom.tpl';
        }
        if (file_exists($cCustomFile)) {
            $cFilename = $cCustomFile;
        }

        return $cFilename;
    }

    /**
     * @param string $cFilename
     * @return string
     */
    public function getFallbackFile($cFilename)
    {
        if (!self::$isChildTemplate && TEMPLATE_COMPATIBILITY === true && !file_exists($this->getTemplateDir($this->context) . $cFilename)) {
            if (array_key_exists($cFilename, self::$_replacer)) {
                $cFilename = self::$_replacer[$cFilename];
            }
        }

        return $cFilename;
    }

    /**
     * fetches a rendered Smarty template
     *
     * @param  string $template the resource handle of the template file or template object
     * @param  mixed  $cache_id cache id to be used with this template
     * @param  mixed  $compile_id compile id to be used with this template
     * @param  object $parent next higher level of Smarty variables
     * @param  bool   $display true: display, false: fetch
     * @param  bool   $merge_tpl_vars not used - left for BC
     * @param  bool   $no_output_filter not used - left for BC
     *
     * @throws Exception
     * @throws SmartyException
     * @return string rendered template output
     */
    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = true)
    {
        $template = $this->getResourceName($template);
        //disable caching when we don't have a valid cache ID
        if ($display && $cache_id === null) {
            $this->caching = self::CACHING_OFF;
        }
        //disable outputfilter when just including/fetching and not displaying
        if ($display) {
            $no_output_filter = false;
            if (self::$isCached) {
                //do not execute normal output filter when the template is cached
                $this->unregisterFilter('output', array($this, '__outputFilter'));
            } else {
                //do not execute cache output filter when template is not cached
                $this->unregisterFilter('output', array($this, '__cacheOutputFilter'));
            }
        }
        if ($cache_id !== null && is_object($cache_id)) {
            $parent   = $cache_id;
            $cache_id = null;
        }
        if ($parent === null) {
            $parent = $this;
        }
        // get template object
        $_template = is_object($template) ? $template : $this->createTemplate($template, $cache_id, $compile_id, $parent, false);
        // set caching in template object
        $_template->caching = $this->caching;

        return $_template->render(true, $no_output_filter, $display);
    }

    /**
     * generates a unique cache id for every given resource
     *
     * @param string      $resource_name
     * @param array       $conditions
     * @param string|null $cache_id
     * @return null|string
     */
    public function getCacheID($resource_name, $conditions, $cache_id = null)
    {
        if ($this->caching === self::CACHING_OFF || !defined('SHOW_PAGE_CACHE') || SHOW_PAGE_CACHE === false || isAjaxRequest()) {
            return;
        }
        if ($resource_name === 'productlist/index.tpl' || $resource_name === 'layout/index.tpl' ||
            $resource_name === 'productdetails/index.tpl' || $resource_name === 'blog/index.tpl' ||
            $resource_name === 'suche.tpl' || $resource_name === 'seite.tpl' ||
            $resource_name === 'artikel.tpl' || $resource_name === 'filter.tpl' || $resource_name === 'news.tpl'
        ) {
            if ($cache_id === null) {
                $customerGroup = (isset($_SESSION['Kundengruppe'])) ? '|cgrp' . $_SESSION['Kundengruppe']->kKundengruppe : '';
                $lang          = (isset(Shop::$kSprache)) ? '|lang' . Shop::$kSprache : '';
                $sslStatus     = (function_exists('pruefeSSL')) ? '|ssl' . pruefeSSL() : '';
                //news
                if (($resource_name === 'blog/index.tpl' || $resource_name === $this->getCustomFile('blog/index.tpl') || $resource_name === 'news.tpl') &&
                    isset($conditions['news'])
                ) {
                    $newsId  = (isset($conditions['news']['kNews'])) ? 'news|nid' . $conditions['news']['kNews'] : '';
                    $newLang = (isset($conditions['news']['lang'])) ? 'lang' . $conditions['news']['lang'] : '';
                    if (isset($_SESSION['NewsNaviFilter'])) {
                        $nSort = '|sort' . $_SESSION['NewsNaviFilter']->nSort;
                        $nNum  = '|num' . $_SESSION['NewsNaviFilter']->nAnzahl;
                        $date  = '|date' . $_SESSION['NewsNaviFilter']->cDatum;
                        $cat   = '|cid' . $_SESSION['NewsNaviFilter']->nNewsKat;
                    } else {
                        $nSort = $nNum = $date = $cat = '';
                    }
                    $cache_id = 'news|' . $newsId . $newLang . $nSort . $nNum . $date . $cat;
                }
                //page
                if (($resource_name === 'seite.tpl' || $resource_name === 'layout/index.tpl') && isset($conditions['link']) &&
                    $conditions['link'] !== null && is_object($conditions['link'])
                ) {
                    $kLink      = (isset($conditions['link']->kLink)) ? 'link|lid' . $conditions['link']->kLink : '';
                    $naviFilter = $this->getTemplateVars('NaviFilter');
                    $pageNumber = (isset($naviFilter->nSeite)) ? '|pnum' . $naviFilter->nSeite : '|pnum0';
                    $sortType   = (isset($conditions['link']->nSort)) ? '|nsort' . $conditions['link']->nSort : '';
                    $cache_id   = 'page|' . $kLink . $pageNumber . $sortType;
                } elseif (($resource_name === 'productlist/index.tpl' || $resource_name === 'suche.tpl') &&
                    isset($conditions['naviFilter']) && $conditions['naviFilter'] !== null && is_object($conditions['naviFilter'])
                ) { //filter.php
                    //category ID
                    $categoryId = (isset($conditions['naviFilter']->Kategorie)) ?
                        'category|cid' . $conditions['naviFilter']->Kategorie->kKategorie :
                        '';
                    //tag page
                    $tagPage = (isset($conditions['naviFilter']->Tag)) ?
                        'tag|tid' . $conditions['naviFilter']->Tag->kTag :
                        '';
                    //current page when paginating
                    $pageNumber = (isset($conditions['naviFilter']->Suche) && $conditions['naviFilter']->Suche->cSuche !== '') ?
                        '|search' . $conditions['naviFilter']->Suche->cSuche :
                        '';
                    //custom sort
                    $sort = '';
                    if (isset($_SESSION['Usersortierung'])) {
                        $sort = '|nsort' . $_SESSION['Usersortierung'];
                    } elseif (isset($_SESSION['UsersortierungVorSuche'])) {
                        $sort = '|nsort' . $_SESSION['UsersortierungVorSuche'];
                    } elseif (isset($conditions['oSuchergebnisse']->Sortierung)) {
                        $sort = '|nsort' . $conditions['oSuchergebnisse']->Sortierung;
                    }
                    //custom view mode/articles per page
                    $extendedView = (isset($_SESSION['ArtikelProSeite'])) ?
                        ('|nanz' . $_SESSION['ArtikelProSeite']) :
                        '';
                    if (!empty(Shop::$nDarstellung)) {
                        $extendedView .= '|ndarst' . Shop::$nDarstellung;
                    } elseif (isset($_SESSION['oErweiterteDarstellung']->nDarstellung)) {
                        $extendedView .= '|ndarst' . $_SESSION['oErweiterteDarstellung']->nDarstellung;
                    }
                    $attributeFilter = '';
                    if (!empty($conditions['naviFilter']->MerkmalFilter)) {
                        $attributeFilter = '|';
                        foreach ($conditions['naviFilter']->MerkmalFilter as $filter) {
                            $attributeFilter .= 'atv' . $filter->kMerkmalWert . '-atid' . $filter->kMerkmal;
                        }
                    }
                    $searchFilter = (isset($conditions['naviFilter']->SuchFilter) && count($conditions['naviFilter']->SuchFilter) > 0) ?
                        '|sfid' . $conditions['naviFilter']->SuchFilter->kKey :
                        '';
                    $searchSpecial = (isset($conditions['naviFilter']->Suchspecial) && count($conditions['naviFilter']->Suchspecial) > 0) ?
                        '|ssid' . $conditions['naviFilter']->Suchspecial->kKey :
                        '';
                    $jtlSearch = (isset($_GET['fq0'])) ?
                        '|js' . md5(implode('.', $_GET)) :
                        '';
                    $searchSpecialFilter = (isset($conditions['naviFilter']->SuchspecialFilter) && count($conditions['naviFilter']->SuchspecialFilter) > 0) ?
                        '|ssf' . $conditions['naviFilter']->SuchspecialFilter->kKey :
                        '';
                    $ratingFilter = (isset($conditions['naviFilter']->BewertungFilter)) ?
                        'stars' . $conditions['naviFilter']->BewertungFilter->nSterne :
                        '';
                    $priceRangeFilter = (isset($conditions['naviFilter']->PreisspannenFilter) && count($conditions['naviFilter']->PreisspannenFilter) > 0) ?
                        '|price' . $conditions['naviFilter']->PreisspannenFilter->cWert :
                        '';
                    $tagFilter = '';
                    if (isset($conditions['naviFilter']->TagFilter) && count($conditions['naviFilter']->TagFilter) > 0) {
                        $tagFilter = '|tag-';
                        foreach ($conditions['naviFilter']->TagFilter as $tag) {
                            $tagFilter .= $tag->kTag;
                        }
                    }
                    $manufacturer = (isset($conditions['naviFilter']->Hersteller)) ?
                        'manufacturer|mid' . $conditions['naviFilter']->Hersteller->kHersteller :
                        '';
                    $manufacturerFilter = (isset($conditions['naviFilter']->HerstellerFilter)) ?
                        '|mid' . $conditions['naviFilter']->HerstellerFilter->kHersteller :
                        '';
                    $naviPage = (isset($conditions['naviFilter']->nSeite)) ?
                        '|np' . $conditions['naviFilter']->nSeite :
                        '';
                    $cache_id = $manufacturer . $categoryId . $tagPage . $pageNumber .
                        $naviPage . $attributeFilter . $manufacturerFilter . $searchFilter .
                        $searchSpecial . $searchSpecialFilter . $ratingFilter . $priceRangeFilter .
                        $tagFilter . $jtlSearch . $sort . $extendedView;
                } elseif (($resource_name === 'productdetails/index.tpl' || $resource_name === 'artikel.tpl') &&
                    isset($conditions['article']) && $conditions['article'] !== null && is_object($conditions['article'])
                ) {
                    if (!empty(Shop::$kVariKindArtikel)) {
                        $articleId = 'aid' . Shop::$kVariKindArtikel;
                    } else {
                        $articleId = (isset($conditions['article']->kArtikel)) ? 'aid' . $conditions['article']->kArtikel : '';
                    }
                    $taxClass = '';
                    if (isset($_SESSION['Steuersatz'])) {
                        $taxClass = '|';
                        foreach ($_SESSION['Steuersatz'] as $_k => $_v) {
                            $taxClass .= $_k . '-' . $_v;
                        }
                    }
                    $cache_id = 'article|' . $articleId . $taxClass;
                }
                $cache_id = 'jtlc|' . $cache_id . $lang . $customerGroup . $sslStatus . ((isset($_GET['exclusive_content'])) ? '|e' : '');
                //allow cache_id modification
                executeHook(HOOK_SMARTY_GENERATE_CACHE_ID, array(
                        'resource'   => $resource_name,
                        'conditions' => $conditions,
                        'cache_id'   => &$cache_id
                    )
                );

                return $cache_id;
            }
        }
        $this->caching = self::CACHING_OFF;

        return;
    }

    /**
     * @param string $resource_name
     * @return string
     */
    public function getResourceName($resource_name)
    {
        $resource_custom_name   = $this->getCustomFile($resource_name);
        $resource_fallback_name = $this->getFallbackFile($resource_custom_name);
        $resource_cfb_name      = $this->getCustomFile($resource_fallback_name);

        executeHook(HOOK_SMARTY_FETCH_TEMPLATE, array(
            'original' => &$resource_name,
            'custom'   => &$resource_custom_name,
            'fallback' => &$resource_fallback_name,
            'out'      => &$resource_cfb_name
        ));

        return $resource_cfb_name;
    }

    /**
     * @param bool $use_sub_dirs
     * @return $this
     */
    public function setUseSubDirs($use_sub_dirs)
    {
        parent::setUseSubDirs($use_sub_dirs);

        return $this;
    }

    /**
     * @param bool $force_compile
     * @return $this
     */
    public function setForceCompile($force_compile)
    {
        parent::setForceCompile($force_compile);

        return $this;
    }

    /**
     * @param bool $compile_check
     * @return $this
     */
    public function setCompileCheck($compile_check)
    {
        parent::setCompileCheck($compile_check);

        return $this;
    }

    /**
     * @param int $error_reporting
     * @return $this
     */
    public function setErrorReporting($error_reporting)
    {
        parent::setErrorReporting($error_reporting);

        return $this;
    }

    /**
     * @return bool
     */
    public static function getIsChildTemplate()
    {
        return self::$isChildTemplate;
    }
}

/**
 * Class jtlTplClass
 */
class jtlTplClass extends Smarty_Internal_Template
{
    /**
     * {include} override for _custom files
     *
     * @param string $template
     * @param mixed  $cache_id
     * @param mixed  $compile_id
     * @param int    $caching
     * @param int    $cache_lifetime
     * @param mixed  $data
     * @param int    $parent_scope
     * @return string
     */
    public function getSubTemplate($template, $cache_id, $compile_id, $caching, $cache_lifetime, $data, $parent_scope)
    {
        return parent::getSubTemplate($this->smarty->getResourceName($template), $cache_id, $compile_id, $caching, $cache_lifetime, $data, $parent_scope);
    }

    /**
     * fetches rendered template
     * rewritten with the second param ($no_output_filter) set to true to avoid running the output filter
     *
     * @throws Exception
     * @throws SmartyException
     * @return string rendered template output
     */
    public function fetch()
    {
        return $this->render(true, true, false);
    }
}

/**
 * Class jtlSmartyCache
 */
class jtlSmartyCache extends Smarty_CacheResource_KeyValueStore
{
    /**
     * @var JTLCache|null
     */
    protected $jtlCache = null;

    /**
     * the cache tags to identify page cache entries within the object cache
     *
     * @var array
     */
    private $cacheTag = array('pg_cch');

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->jtlCache = new JTLCache(array(), true);
        $this->jtlCache->setOptions(array(
            'activated' => true,
            'method'    => $config['caching']['caching_method'],
            'prefix'    => 'jcp_' . ((defined('DB_NAME')) ? DB_NAME . '_' : '')
        ))->init();
    }

    /**
     * Read values for a set of keys from cache
     *
     * @param array $keys list of keys to fetch
     * @return array list of values with the given keys used as indexes
     * @return bool true on success, false on failure
     */
    protected function read(array $keys)
    {
        return $this->jtlCache->getMulti($keys);
    }

    /**
     * Save values for a set of keys to cache
     *
     * @param array $keys list of values to save
     * @param int   $expire expiration time
     * @return bool true on success, false on failure
     */
    protected function write(array $keys, $expire = null)
    {
        return $this->jtlCache->setMulti($keys, $this->cacheTag, $expire);
    }

    /**
     * Remove values from cache
     *
     * @param array $keys list of keys to delete
     * @return bool true on success, false on failure
     */
    protected function delete(array $keys)
    {
        foreach ($keys as $k) {
            $this->jtlCache->flush($k);
        }

        return true;
    }

    /**
     * Remove *all* values from cache
     *
     * @return bool true on success, false on failure
     */
    protected function purge()
    {
        return $this->jtlCache->flushTags($this->cacheTag);
    }
}
