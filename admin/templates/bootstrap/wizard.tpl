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
<div class="modal fade"
     id="modal-setup-assistant"
     tabindex="-1"
     role="dialog"
     aria-labelledby="modal-setup-assistantTitle"
     aria-hidden="true"
     data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <form method="post">
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
                        {foreach $steps as $step}
                            {$stepID = $step@index + 1}
                            <div class="step" data-setup-step="{$stepID}">{$stepID}</div>
                        {/foreach}
                        <div class="step" data-setup-step="{$stepID + 1}">{$stepID + 1}</div>
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
                    {foreach $steps as $step}
                        <div class="setup-slide row" data-setup-slide="{$step->getID()}">
                            <div class="col-lg-4 mb-5 mb-lg-0">
                                <span class="setup-subheadline">{$step->getTitle()}</span>
                                <p class="text-muted">{$step->getDescription()}</p>
                            </div>
                            <div class="col-lg-6 ml-lg-auto col-xl-7 mt-lg-n5">
                                <div class="row">
                                    {foreach $step->getQuestions() as $question}
                                        {if $question->getSubheading() !== null}
                                            <div class="col-12">
                                                <span class="subheading1 form-title">
                                                    {$question->getSubheading()}
                                                    {if $question->getSubheadingDescription() !== null}
                                                        <span class="fal fa-info-circle text-muted ml-4" data-toggle="tooltip" title="{$question->getSubheadingDescription()}"></span>
                                                    {/if}
                                                </span>
                                            </div>
                                        {/if}
                                        {include file='tpl_inc/wizard_question.tpl' question=$question}
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    {/foreach}

                    <div class="setup-slide row" data-setup-slide="{$stepID + 1}">
                        <div class="col-lg-4 mb-5 mb-lg-0">
                            <span class="setup-subheadline">{__('stepFive')}</span>
                            <p class="text-muted">{__('stepFiveDesc')}</p>
                        </div>
                        <div class="col-lg-6 ml-lg-auto col-xl-7 mt-lg-n5">
                            <div class="table-responsive">
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                    {foreach $steps as $step}
                                        {foreach $step->getQuestions() as $question}
                                            <tr>
                                                {if $question@first}
                                                    <td>
                                                        <a href="#" class="btn btn-link btn-sm mt-n1 text-primary" data-setup-step="{$step->getID()}">
                                                            <span class="icon-hover">
                                                                <span class="fal fa-edit"></span>
                                                                <span class="fas fa-edit"></span>
                                                            </span>
                                                        </a>
                                                    </td>
                                                {else}
                                                    <td></td>
                                                {/if}
                                                <td>
                                                    <span class="form-title mb-0">
                                                        {if $question->getSummaryText() !== null}
                                                            {$question->getSummaryText()}
                                                        {else}
                                                            {$question->getText()}
                                                        {/if}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span data-setup-summary-placeholder="question-{$question->getID()}">-</span>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="setup-slide row" data-setup-slide="{$stepID + 2}">
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
