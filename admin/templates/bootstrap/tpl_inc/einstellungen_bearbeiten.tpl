{if isset($Sektion) && $Sektion}
    {assign var=cTitel value=__('settings')|cat:': '|cat:{__($Sektion->cName)}}
    {if isset($cSearch) && $cSearch|strlen  > 0}
        {assign var=cTitel value=$cSearch}
    {/if}
    {include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=$cPrefDesc cDokuURL=$cPrefURL}
{/if}
{if !isset($action) || !$action}
    {assign var=action value='einstellungen.php'}
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
        <form name="einstellen" method="post" action="{$action}" class="settings navbar-form">
            {$jtl_token}
            <input type="hidden" name="einstellungen_bearbeiten" value="1" />
            {if $search}
                <input type="hidden" name="cSuche" value="{$cSuche}" />
                <input type="hidden" name="einstellungen_suchen" value="1" />
            {/if}
            <input type="hidden" name="kSektion" value="{$kEinstellungenSektion}" />
            {if isset($Conf) && $Conf|@count > 0}
                {foreach $Conf as $cnf}
                    {if $cnf->cConf === 'Y'}
                        <div class="form-group form-row align-items-center {if isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
                            <label class="col col-sm-4 col-form-label text-sm-right order-1" for="{$cnf->cWertName}">{$cnf->cName}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                {if $cnf->cInputTyp === 'selectbox'}
                                    <select class="custom-select" name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                                        {foreach $cnf->ConfWerte as $wert}
                                            <option value="{$wert->cWert}" {if $cnf->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                        {/foreach}
                                    </select>
                                {elseif $cnf->cInputTyp === 'listbox'}
                                    <select name="{$cnf->cWertName}[]" id="{$cnf->cWertName}" multiple="multiple" class="custom-select combo">
                                        {foreach $cnf->ConfWerte as $wert}
                                            <option value="{$wert->cWert}" {foreach $cnf->gesetzterWert as $gesetzterWert}{if $gesetzterWert->cWert == $wert->cWert}selected{/if}{/foreach}>{$wert->cName}</option>
                                        {/foreach}
                                    </select>
                                {elseif $cnf->cInputTyp === 'pass'}
                                    <input class="form-control" autocomplete="off" type="password" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                                {elseif $cnf->cInputTyp === 'number'}
                                    <input class="form-control" type="number" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" tabindex="1" />
                                {else}
                                    <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" tabindex="1" />
                                {/if}
                            </div>
                            {if $cnf->cBeschreibung}
                                <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                    {getHelpDesc cDesc=$cnf->cBeschreibung cID=$cnf->kEinstellungenConf}
                                </div>
                            {/if}
                            {if isset($oSections[$kEinstellungenSektion]) && $oSections[$kEinstellungenSektion]->hasValueMarkup}
                                {*{$oSections[$kEinstellungenSektion]->getValueMarkup($cnf)}*}
                            {/if}
                        </div>
                    {else}
                        {if $cnf@index !== 0}
                            </div>
                        </div>
                        {/if}
                        <div class="card">
                            <div class="card-header">
                                <span class="subheading1">
                                    {$cnf->cName}
                                    {if !empty($cnf->cSektionsPfad)}
                                        <span class="path float-right">
                                            <strong>{__('settingspath')}:</strong> {$cnf->cSektionsPfad}
                                        </span>
                                    {/if}
                                </span>
                                {if isset($oSections[$cnf->kEinstellungenSektion])
                                    && $oSections[$cnf->kEinstellungenSektion]->hasSectionMarkup}
                                        {$oSections[$cnf->kEinstellungenSektion]->getSectionMarkup()}
                                {/if}
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
                            <button type="submit" value="{__('savePreferences')}" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            {else}
                <p class="alert alert-info">{__('noSearchResult')}</p>
            {/if}
        </form>
    </div>
</div>