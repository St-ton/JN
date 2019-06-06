{include file='tpl_inc/seite_header.tpl' cTitel=__('lang') cBeschreibung=__('langDesc') cDokuURL=__('langURL')}
<div id="content" class="container-fluid">
    <div class="panel panel-default settings">
        <div class="panel-heading">
            <h3 class="panel-title">{__('newLangVar')}</h3>
        </div>
        <form action="sprache.php" method="post">
            {$jtl_token}
            <input type="hidden" name="tab" value="{$tab}">
            <div class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="kSprachsektion">{__('langSection')}</label>
                    </span>
                    <span class="input-group-wrap">
                        <select class="form-control" name="kSprachsektion" id="kSprachsektion">
                            {foreach $oSektion_arr as $oSektion}
                                <option value="{$oSektion->kSprachsektion}"
                                        {if $oVariable->kSprachsektion === (int)$oSektion->kSprachsektion}selected{/if}>
                                    {$oSektion->cName}
                                </option>
                            {/foreach}
                        </select>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cName">{__('variableName')}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="cName" id="cName" value="{$oVariable->cName}">
                    </span>
                </div>
                {foreach $oSprache_arr as $language}
                    {assign var=langCode value=$language->getIso()}
                    {if isset($oVariable->cWertAlt_arr[$langCode])}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="bOverwrite_{$langCode}_yes">
                                    <input type="radio" id="bOverwrite_{$langCode}_yes"
                                           name="bOverwrite_arr[{$langCode}]" value="1">
                                    {$language->getLocalizedName()} ({__('new')})
                                </label>
                            </span>
                            <span class="input-group-wrap">
                                <input type="text" class="form-control" name="cWert_arr[{$langCode}]"
                                       id="cWert_{$langCode}" value="{if !empty($oVariable->cWert_arr[$langCode])}{$oVariable->cWert_arr[$langCode]}{/if}">
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="bOverwrite_{$langCode}_no">
                                    <input type="radio" id="bOverwrite_{$langCode}_no"
                                           name="bOverwrite_arr[{$langCode}]" value="0" checked>
                                    {$language->getLocalizedName()} ({__('current')})
                                </label>
                            </span>
                                <span class="input-group-wrap">
                                <input type="text" class="form-control" name="cWertAlt_arr[{$langCode}]" disabled
                                       id="cWertAlt_{$langCode}"
                                       value="{if !empty($oVariable->cWertAlt_arr[$langCode])}{$oVariable->cWertAlt_arr[$langCode]}{/if}">
                            </span>
                        </div>
                    {else}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cWert_{$langCode}">
                                    {$language->getLocalizedName()}
                                </label>
                            </span>
                            <span class="input-group-wrap">
                                <input type="text" class="form-control" name="cWert_arr[{$langCode}]"
                                       id="cWert_{$langCode}" value="{$oVariable->cWert_arr[$langCode]|default:''}">
                            </span>
                        </div>
                    {/if}
                {/foreach}
            </div>
            <div class="panel-footer">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" name="action" value="savevar">
                        <i class="fa fa-save"></i>
                        {__('save')}
                    </button>
                    <a href="sprache.php?tab={$tab}" class="btn btn-danger">{__('goBack')}</a>
                </div>
            </div>
        </form>
    </div>
</div>
