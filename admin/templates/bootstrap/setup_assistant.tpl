{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='shopsitemap'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('setupAssistant') cBeschreibung=__('setupAssistantDesc') cDokuURL=__('setupAssistantURL')}
<script type="text/javascript">
    $(window).on('load',function(){
        $('#modal-setup-assistant').modal('show');
    });
</script>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-setup-assistant">
    {__('launchSetup')}
</button>
<div class="modal fade" id="modal-setup-assistant" tabindex="-1" role="dialog" aria-labelledby="modal-setup-assistantTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <form action="setup.php">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span class="fal fa-times"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <img src="{$templateBaseURL}gfx/JTL-Shop-Logo-rgb.png" width="101" height="32" alt="JTL-Shop">
                    <span class="h1 mt-3">{__('setupAssistant')}</span>

                    <div class="setup-steps steps">
                        <div class="step" data-setup-step="1">1</div>
                        <div class="step" data-setup-step="2">2</div>
                        <div class="step" data-setup-step="3">3</div>
                        <div class="step" data-setup-step="4">4</div>
                    </div>

                    <div class="setup-slide row align-items-center mt-lg-7" data-setup-slide="0">
                        <div class="col-md-6 col-lg-4">
                            <span class="setup-subheadline">{__('welcome')}</span>
                            <p>{__('welcomeDesc')}</p>
                            <button type="button" class="btn btn-primary min-w-sm mt-5 mt-lg-7" data-setup-next>{__('beginSetup')}</button>
                        </div>
                        <div class="col-md-6 mx-md-auto col-xl-5 d-none d-md-block text-center">
                            <img class="img-fluid" src="{$templateBaseURL}img/setup-assistant-roboter.svg" width="416" height="216" alt="{__('setupAssistant')}">
                        </div>
                    </div>

                    <div class="setup-slide row" data-setup-slide="1">
                        <div class="col-lg-4 mb-5 mb-lg-0">
                            <span class="setup-subheadline">{__('stepOne')}</span>
                            <p class="text-muted">{__('stepOneDesc')}</p>
                        </div>
                        <div class="col-lg-6 ml-lg-auto col-xl-7 mt-lg-n5">
                            <div class="row">
                                <div class="col-12">
                                    <span class="subheading1 form-title">{__('shopSettings')}</span>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group-lg mb-4">
                                        <span class="form-title">{__('shopName')}:<span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{__('shopNameDesc')}"></span></span>
                                        <input type="text" class="form-control rounded-pill" id="shop-name" placeholder="" data-setup-summary-id="shop-name">
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group-lg mb-4">
                                        <span class="form-title">{__('masterEmail')}:<span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{__('masterEmailDesc')}"></span></span>
                                        <input type="text" class="form-control rounded-pill" id="master-email" placeholder="" data-setup-summary-id="master-email">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="secure-default-settings" data-setup-summary-id="secure-default-settings" data-setup-summary-text="{__('secureDefaultSettings')}">
                                        <label class="custom-control-label" for="secure-default-settings">
                                            {__('secureDefaultSettings')}
                                            <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{__('secureDefaultSettingsDesc')}"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <span class="subheading1 form-title mt-5">{__('vatSettings')}</span>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group-lg">
                                        <span class="form-title">{__('vatIDCompany')}:<span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{__('vatIDCompanyTitle')}"></span></span>
                                        <input type="text" class="form-control rounded-pill" id="ustid" placeholder="" data-setup-summary-id="ustid">
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="form-group-lg">
                                        <span class="form-title">{__('smallEntrepreneur')}:<span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{__('vatSmallEntrepreneurTitle')}"></span></span>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="steuereinstellung" data-setup-summary-id="steuer" data-setup-summary-text="{__('vatSmallEntrepreneur')}">
                                            <label class="custom-control-label" for="steuereinstellung">{__('vatSmallEntrepreneur')}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group-lg">
                                        <span class="form-title">{__('customerGroupDesc')}:<span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{__('customerGroupDescTitle')}"></span></span>

                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" id="kundengruppe-b2b" name="kundengruppe" value="b2b" data-setup-summary-id="kundengruppe" data-setup-summary-text="{__('customerGroupB2B')}">
                                            <label class="custom-control-label" for="kundengruppe-b2b">{__('customerGroupB2B')}</label>
                                        </div>

                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" id="kundengruppe-b2c" name="kundengruppe" value="b2c" data-setup-summary-id="kundengruppe" data-setup-summary-text="{__('customerGroupB2C')}">
                                            <label class="custom-control-label" for="kundengruppe-b2c">{__('customerGroupB2C')}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="setup-slide row" data-setup-slide="2">
                        <div class="col-lg-4 mb-5 mb-lg-0">
                            <span class="setup-subheadline">{__('stepTwo')}</span>
                            <p class="text-muted">{__('stepTwoDesc')}</p>
                        </div>
                        <div class="col-lg-6 ml-lg-auto col-xl-7 mt-lg-n5">
                            <span class="form-title">
                                {__('weRecommend')}:
                                <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{__('weRecommendLegalDesc')}"></span>
                            </span>

                            <div class="form-group-list">
                                {for $i=1 to 7}
                                <div class="form-group-list-item">
                                    <div class="form-row">
                                        <div class="col-xl-3">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="rechtstext-klarna1" data-setup-summary-id="rechtstexteplugins" data-setup-summary-text="Klarna 1">
                                                <label class="custom-control-label" for="rechtstext-klarna1">
                                                    <img src="placeholder/klarna-logo.png" width="108" height="42" alt="Klarna">
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl">
                                            <p class="text-muted">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt</p>
                                            <a href="#">{__('getToKnowMore')}</a>
                                        </div>
                                    </div>
                                </div>
                                {/for}
                            </div>
                        </div>
                    </div>

                    <div class="setup-slide row" data-setup-slide="3">
                        <div class="col-lg-4 mb-5 mb-lg-0">
                            <span class="setup-subheadline">{__('stepThree')}</span>
                            <p class="text-muted">{__('stepThreeDesc')}</p>
                        </div>
                        <div class="col-lg-6 ml-lg-auto col-xl-7 mt-lg-n5">
                            <span class="form-title">
                                {__('weRecommend')}:
                                <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{__('weRecommendPaymentDesc')}"></span>
                            </span>

                            <div class="form-group-list">
                                {for $i=1 to 7}
                                <div class="form-group-list-item">
                                    <div class="form-row">
                                        <div class="col-xl-3">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="rechtstext-paypal-1-1" data-setup-summary-id="zahlungsartplugins" data-setup-summary-text="Paypal 1">
                                                <label class="custom-control-label" for="rechtstext-paypal-1-1">
                                                    <img src="placeholder/klarna-logo.png" width="108" height="42" alt="Paypal">
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl">
                                            <p class="text-muted">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt</p>
                                            <a href="#">{__('getToKnowMore')}</a>
                                        </div>
                                    </div>
                                </div>
                                {/for}
                            </div>
                        </div>
                    </div>

                    <div class="setup-slide row" data-setup-slide="4">
                        <div class="col-lg-4 mb-5 mb-lg-0">
                            <span class="setup-subheadline">{__('stepFour')}</span>
                            <p class="text-muted">{__('stepFourDesc')}</p>
                        </div>
                        <div class="col-lg-6 ml-lg-auto col-xl-7 mt-lg-n5">
                            <div class="table-responsive">
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm mt-n1 text-primary" data-setup-step="1">
                                                <span class="icon-hover">
                                                    <span class="fal fa-edit"></span>
                                                    <span class="fas fa-edit"></span>
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="form-title mb-0">{__('shopName')}</span>
                                        </td>
                                        <td>
                                            <span data-setup-summary-placeholder="shop-name">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="form-title mb-0">{__('masterEmail')}</span>
                                        </td>
                                        <td>
                                            <span data-setup-summary-placeholder="master-email">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="form-title mb-0">{__('secureDefaultSettings')}</span>
                                        </td>
                                        <td>
                                            <span data-setup-summary-placeholder="secure-default-settings">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="form-title mb-0">{__('customerGroup')}</span>
                                        </td>
                                        <td>
                                            <span data-setup-summary-placeholder="kundengruppe">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="form-title mb-0">{__('vatIDCompany')}</span>
                                        </td>
                                        <td>
                                            <span data-setup-summary-placeholder="ustid">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="form-title mb-0">{__('vatSettings')}</span>
                                        </td>
                                        <td>
                                            <span data-setup-summary-placeholder="steuer">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="pb-3"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm mt-n1 text-primary" data-setup-step="2">
                                                <span class="icon-hover">
                                                    <span class="fal fa-edit"></span>
                                                    <span class="fas fa-edit"></span>
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="form-title mb-0">{__('legalTexts')}</span>
                                        </td>
                                        <td>
                                            <span data-setup-summary-placeholder="rechtstexteplugins">-</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="pb-3"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href="#" class="btn btn-link btn-sm mt-n1 text-primary" data-setup-step="3">
                                                <span class="icon-hover">
                                                    <span class="fal fa-edit"></span>
                                                    <span class="fas fa-edit"></span>
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="form-title mb-0">{__('paymentMethods')}</span>
                                        </td>
                                        <td>
                                            <span data-setup-summary-placeholder="zahlungsartplugins">-</span>
                                            <span class="fal fa-exclamation-triangle text-warning" data-toggle="tooltip" title="{__('paymentPluginInstalled')}"></span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="setup-slide row" data-setup-slide="5">
                        <div class="col-lg-4 mb-5 mb-lg-0">
                            <span class="setup-subheadline">{__('thankYouText')}</span>
                            <p>{__('thankYouTextDesc')}</p>

                            <div class="note small mt-5">
                                <span class="fal fa-exclamation-triangle text-warning mr-2"></span>
                                {__('thankYouTextPluginsInstalled')}
                                <span class="form-title mb-0 mt-4">{__('installedPlugins')}:</span>
                            </div>
                        </div>
                        <div class="col-lg-6 ml-lg-auto col-xl-7 mt-lg-n5">
                            <img class="img-fluid img-setup-guide d-none d-lg-block" src="{$templateBaseURL}img/setup-assistant-guide.svg" width="188" height="135" alt="Guide">
                            <p class="mt-n3">{__('finalizeHelpNotice')}</p>

                            <div class="form-row">
                                <div class="col-lg-6 mb-2">
                                    <a href="#" class="card setup-card h-100">
                                        <div class="card-body">
                                            <span class="setup-subheadline mt-0 mb-2">{__('recommendationAppearance')}</span>
                                            <p class="text-muted small m-0">{__('recommendationAppearanceDesc')}</p>
                                            <span class="icon-hover text-primary icon-more">
												<span class="fal fa-long-arrow-right"></span>
												<span class="fas fa-long-arrow-right"></span>
											</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <a href="#" class="card setup-card h-100">
                                        <div class="card-body">
                                            <span class="setup-subheadline mt-0 mb-2">{__('recommendationFormsAndTexts')}</span>
                                            <p class="text-muted small m-0">{__('recommendationFormsAndTextsDesc')}</p>
                                            <span class="icon-hover text-primary icon-more">
												<span class="fal fa-long-arrow-right"></span>
												<span class="fas fa-long-arrow-right"></span>
											</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <a href="#" class="card setup-card h-100">
                                        <div class="card-body">
                                            <span class="setup-subheadline mt-0 mb-2">{__('recommendationSystem')}</span>
                                            <p class="text-muted small m-0">{__('recommendationSystemDesc')}</p>
                                            <span class="icon-hover text-primary icon-more">
												<span class="fal fa-long-arrow-right"></span>
												<span class="fas fa-long-arrow-right"></span>
											</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <a href="#" class="card setup-card h-100">
                                        <div class="card-body">
                                            <span class="setup-subheadline mt-0 mb-2">{__('recommendationExtendShop')}</span>
                                            <p class="text-muted small m-0">{__('recommendationExtendShopDesc')}</p>
                                            <span class="icon-hover text-primary icon-more">
												<span class="fal fa-long-arrow-right"></span>
												<span class="fas fa-long-arrow-right"></span>
											</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <a href="#" class="card setup-card h-100">
                                        <div class="card-body">
                                            <span class="setup-subheadline mt-0 mb-2">{__('recommendationMarketingSeo')}</span>
                                            <p class="text-muted small m-0">{__('recommendationMarketingSeoDesc')}</p>
                                            <span class="icon-hover text-primary icon-more">
												<span class="fal fa-long-arrow-right"></span>
												<span class="fas fa-long-arrow-right"></span>
											</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-2">
                                    <a href="#" class="card setup-card h-100">
                                        <div class="card-body">
                                            <span class="setup-subheadline mt-0 mb-2">{__('recommendationJTLSearch')}</span>
                                            <p class="text-muted small m-0">{__('recommendationJTLSearchDesc')}</p>
                                            <span class="icon-hover text-primary icon-more">
												<span class="fal fa-long-arrow-right"></span>
												<span class="fas fa-long-arrow-right"></span>
											</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col">
                            <button type="button" class="btn btn-outline-primary min-w-sm my-2 my-sm-0 w-100 w-sm-auto" data-setup-prev>{__('back')}</button>
                        </div>
                        <div class="col text-right">
                            <button type="button" class="btn btn-primary min-w-sm ml-sm-3 my-2 my-sm-0 w-100 w-sm-auto" data-setup-next>{__('next')}</button>
                            <button type="submit" class="btn btn-primary min-w-sm ml-sm-3 my-2 my-sm-0 w-100 w-sm-auto" data-setup-submit>{__('confirm')}</button>
                            <button type="button" class="btn btn-primary min-w-sm ml-sm-3 my-2 my-sm-0 w-100 w-sm-auto" data-setup-close data-dismiss="modal">{__('finalize')}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
