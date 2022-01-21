{if isset($section)}
    {if isset($cSearch) && $cSearch|strlen  > 0}
        {assign var=title value=$cSearch}
    {/if}
    {include file='tpl_inc/seite_header.tpl' cTitel=$title cBeschreibung=$cPrefDesc cDokuURL=$cPrefURL}
{/if}
{$search = isset($cSuche) && !empty($cSuche)}
{if $search}
    <script>
        $(function() {
            var $element = $('.input-group.highlight');
            if ($element.length > 0) {
                var height = $element.height(),
                    offset = $element.offset().top,
                    wndHeight = $(window).height();
                if (height < wndHeight) {
                    offset = offset - ((wndHeight / 2) - (height / 2));
                }

                $('html, body').stop().animate({ scrollTop: offset }, 400);
            }
        });
    </script>
{/if}
<div id="content">
    <div id="settings">
        {if $testResult|default:null !== null}
            <div class="card">
                <div class="card-body">
                    <pre>{$testResult}</pre>
                </div>
            </div>
        {/if}
        {if isset($Conf) && $Conf|count > 0}
        <form name="einstellen" method="post" action="{$action|default:''}" class="settings navbar-form">
            {$jtl_token}
            <input type="hidden" name="einstellungen_bearbeiten" value="1" />
            {if $search}
                <input type="hidden" name="cSuche" value="{$cSuche}" />
                <input type="hidden" name="einstellungen_suchen" value="1" />
            {/if}
            <input type="hidden" name="kSektion" value="{$kEinstellungenSektion}" />
            {foreach $Conf as $cnf}
                {if $cnf->isConfigurable()}
                    <div class="form-group form-row align-items-center {if isset($cSuche) && $cnf->getID() == $cSuche} highlight{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right order-1" for="{$cnf->getValueName()}">{$cnf->getName()}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 {if $cnf->getInputType() === 'number'}config-type-number{/if}">
                            {if $cnf->getInputType() === 'selectbox'}
                                {if $cnf->getValueName() === 'kundenregistrierung_standardland' || $cnf->getValueName() === 'lieferadresse_abfragen_standardland' }
                                    <select class="custom-select" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}">
                                        {foreach $countries as $country}
                                            <option value="{$country->getISO()}" {if $cnf->getSetValue() == $country->getISO()}selected{/if}>{$country->getName()}</option>
                                        {/foreach}
                                    </select>
                                {else}
                                    <select class="custom-select" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}">
                                        {foreach $cnf->getValues() as $value}
                                            <option value="{$value->cWert}" {if $cnf->getSetValue() == $value->cWert}selected{/if}>{$value->cName}</option>
                                        {/foreach}
                                    </select>
                                {/if}
                            {elseif $cnf->getInputType() === 'listbox'}
                                <select name="{$cnf->getValueName()}[]"
                                id="{$cnf->getValueName()}"
                                multiple="multiple"
                                class="selectpicker custom-select combo"
                                data-selected-text-format="count > 2"
                                data-size="7">
                                    {foreach $cnf->getValues() as $value}
                                        <option value="{$value->cWert}" {foreach $cnf->getSetValue() as $setValue}{if $setValue->cWert == $value->cWert}selected{/if}{/foreach}>{$value->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $cnf->getInputType() === 'pass'}
                                <input class="form-control" autocomplete="off" type="password" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}" value="{$cnf->getSetValue()}" tabindex="1" />
                            {elseif $cnf->getInputType() === 'number'}
                                <div class="input-group form-counter">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                            <span class="fas fa-minus"></span>
                                        </button>
                                    </div>
                                    <input class="form-control" type="number" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}" value="{if $cnf->getSetValue() !== null}{$cnf->getSetValue()}{/if}" tabindex="1" />
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                            <span class="fas fa-plus"></span>
                                        </button>
                                    </div>
                                </div>
                            {else}
                                <input class="form-control" type="text" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}" value="{if $cnf->getSetValue() !== null}{$cnf->getSetValue()}{/if}" tabindex="1" />
                            {/if}
                        </div>
                        {include file='snippets/einstellungen_icons.tpl' cnf=$cnf}
                    </div>
                {else}
                    {if $cnf@index !== 0}
                        </div>
                    </div>
                    {/if}
                    <div class="card">
                        <div class="card-header">
                            <span class="subheading1" id="{$cnf->getValueName()}">
                                {$cnf->getName()}
                                {if !empty($cnf->cSektionsPfad)}
                                    <span class="path float-right">
                                        <strong>{__('settingspath')}:</strong> {$cnf->cSektionsPfad}
                                    </span>
                                {/if}
                            </span>
{*                            @TODO!*}
{*                            {if isset($oSections[$cnf->kEinstellungenSektion])*}
{*                                && $oSections[$cnf->kEinstellungenSektion]->hasSectionMarkup}*}
{*                                    {$oSections[$cnf->kEinstellungenSektion]->getSectionMarkup()}*}
{*                            {/if}*}
                            <hr class="mb-n3">
                        </div>
                        <div class="card-body">
                {/if}
            {/foreach}
                </div>
            </div>
            <div class="save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        {if $section->getID() === $smarty.const.CONF_EMAILS}
                            <script>
                            $(function() {
                                if ($('#email_methode').val() !== 'smtp') {
                                    $('#configTest').hide();
                                }
                                $('#email_methode').on('change', function () {
                                    var currentVal = $(this).val();
                                    if (currentVal === 'smtp') {
                                        $('#configTest').show();
                                    } else {
                                        $('#configTest').hide();
                                    }
                                });
                            });
                            </script>
                            <button type="submit" name="test_emails" value="1" class="btn btn-secondary btn-block" id="configTest">
                                {__('saveWithconfigTest')}
                            </button>
                        {/if}
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" value="{__('savePreferences')}" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </form>
        {else}
            <div class="alert alert-info">{__('noSearchResult')}</div>
        {/if}
    </div>
</div>
