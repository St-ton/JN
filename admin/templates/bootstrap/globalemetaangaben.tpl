{config_load file="$lang.conf" section='globalemetaangaben'}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('globalemetaangaben') cBeschreibung=__('globalemetaangabenDesc') cDokuURL=__('globalemetaangabenUrl')}
{assign var=currentLanguage value=''}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="globalemetaangaben.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="input-group p25 left">
                {include file='tpl_inc/language_switcher.tpl'}
            </div>
        </form>
    </div>
    <form method="post" action="globalemetaangaben.php">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <div class="settings">
            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">{$currentLanguage}</h3></div>
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="Title">{__('title')}</label>
                        </span>
                        <input type="text" class="form-control" id="Title" name="Title" value="{if isset($oMetaangaben_arr.Title)}{$oMetaangaben_arr.Title}{/if}" tabindex="1" />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="Meta_Description">{__('globalemetaangabenMetaDesc')}</label>
                        </span>
                        <input type="text" class="form-control" id="Meta_Description" name="Meta_Description" value="{if isset($oMetaangaben_arr.Meta_Description)}{$oMetaangaben_arr.Meta_Description}{/if}" tabindex="1" />
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="Meta_Keywords">{__('globalemetaangabenKeywords')}</label>
                        </span>
                        <input type="text" class="form-control" id="Meta_Keywords" name="Meta_Keywords" value="{if isset($oMetaangaben_arr.Meta_Keywords)}{$oMetaangaben_arr.Meta_Keywords}{/if}" tabindex="1" />
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="Meta_Description_Praefix">{__('globalemetaangabenMetaDescPraefix')}</label>
                        </span>
                        <input type="text" class="form-control" id="Meta_Description_Praefix" name="Meta_Description_Praefix" value="{if isset($oMetaangaben_arr.Meta_Description_Praefix)}{$oMetaangaben_arr.Meta_Description_Praefix}{/if}" tabindex="1" />
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="keywords">{__('excludeKeywords')} ({__('spaceSeparated')})</label>
                        </span>
                        <textarea class="form-control" id="keywords" name="keywords">{if isset($keywords->cKeywords)}{$keywords->cKeywords}{/if}</textarea>
                    </div>
                </div>
            </div>

            {assign var=open value=false}
            {foreach $oConfig_arr as $oConfig}
                {if $oConfig->cConf === 'Y'}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="{$oConfig->cWertName}">{$oConfig->cName}</label>
                        </span>
                        {if $oConfig->cInputTyp === 'selectbox'}
                            <span class="input-group-wrap">
                                <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="form-control combo">
                                    {foreach $oConfig->ConfWerte as $wert}
                                        <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            </span>
                        {elseif $oConfig->cInputTyp === 'number'}
                            <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                        {else}
                            <input class="form-control" type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                        {/if}
                        {if $oConfig->cBeschreibung}
                            <span class="input-group-addon">{getHelpDesc cDesc=$oConfig->cBeschreibung}</span>
                        {/if}
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="panel panel-default">
                        {if $oConfig->cName}
                            <div class="panel-heading"><h3 class="panel-title">{__('settings')}</h3></div>
                        {/if}
                        <div class="panel-body">
                        {assign var=open value=true}
                {/if}
            {/foreach}
            {if $open}
                </div>
            </div>
            {/if}
        </div>

        <div class="submit">
            <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
