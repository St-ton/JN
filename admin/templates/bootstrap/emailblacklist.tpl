{config_load file="$lang.conf" section='emailblacklist'}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('emailblacklist') cBeschreibung=__('emailblacklistDesc') cDokuURL=__('emailblacklistURL')}
<div id="content">
    <form method="post" action="emailblacklist.php">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <input type="hidden" name="emailblacklist" value="1" />
        <div id="settings">
            {assign var=open value=false}
            {foreach $oConfig_arr as $oConfig}
                {if $oConfig->cConf === 'Y'}
                    <div class="item form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$oConfig->cWertName}">{$oConfig->cName}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            {if $oConfig->cInputTyp === 'selectbox'}
                                <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="custom-select combo">
                                    {foreach $oConfig->ConfWerte as $wert}
                                        <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $oConfig->cInputTyp === 'number'}
                                <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                            {else}
                                <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{$oConfig->gesetzterWert}" tabindex="1" />
                            {/if}
                        </div>
                        {if $oConfig->cBeschreibung}
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=$oConfig->cBeschreibung}</div>
                        {/if}
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="card">
                    {if $oConfig->cName}
                        <div class="card-header">
                            <div class="subheading1">{$oConfig->cName}</div>
                            <hr class="mb-n3">
                        </div>
                    {/if}
                        <div class="card-body">
                    {assign var=open value=true}
                {/if}
            {/foreach}
            {if $open}
                    </div>
                </div>
            {/if}
        </div>

        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('emailblacklistEmail')} {__('emailblacklistSeperator')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <textarea class="form-control" name="cEmail" cols="50" rows="10">{if isset($oEmailBlacklist_arr)}{foreach $oEmailBlacklist_arr as $oEmailBlacklist}{$oEmailBlacklist->cEmail}{if !$oEmailBlacklist@last};{/if}{/foreach}{/if}</textarea>
            </div>
        </div>
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                        <i class="fa fa-save"></i> {__('save')}
                    </button>
                </div>
            </div>
        </div>
    </form>
    {if isset($oEmailBlacklistBlock_arr) && $oEmailBlacklistBlock_arr|@count > 0}
        <div class="card">
            <div class="card-header">
                <div class="card-title">{__('emailblacklistBlockedEmails')}</div>
            </div>
            <div class="card-body">
                {foreach $oEmailBlacklistBlock_arr as $entry}
                    {$entry->cEmail} ({$entry->dLetzterBlock})<br />
                {/foreach}
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
