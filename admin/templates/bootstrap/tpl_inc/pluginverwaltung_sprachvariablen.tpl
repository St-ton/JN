<script type="text/javascript">
    function ackCheck(kPluginSprachvariable, kPlugin)
    {ldelim}
        var bCheck = confirm("Wollen Sie Ihre Sprachvariablen wirklich wieder auf den Installationszustand zurücksetzen? *Vorsicht* Alle bisherigen editierten Sprachvariablen, gehen für diese eine Variable verloren.");

        if(bCheck)
            window.location.href = "pluginverwaltung.php?pluginverwaltung_sprachvariable=1&kPlugin=" + kPlugin + "&kPluginSprachvariable=" + kPluginSprachvariable + "&token={$smarty.session.jtl_token}";
        {rdelim}
</script>
{include file='tpl_inc/seite_header.tpl' cTitel=#pluginverwaltung# cBeschreibung=#pluginverwaltungDesc#}
<div id="content" class="container-fluid">
    {if !empty($oPluginSprachvariable_arr) && is_array($oPluginSprachvariable_arr)}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_sprachvariable" value="1" />
            <input type="hidden" name="kPlugin" value="{$kPlugin}" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#pluginverwaltungLocales#}</h3>
                </div>
                <table class="list table">
                    <thead>
                    <tr>
                        <th class="tleft">{#pluginName#}</th>
                        <th class="tleft">{#pluginDesc#}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name="pluginsprachvariablen" from=$oPluginSprachvariable_arr item=oPluginSprachvariable}
                        <tr>
                            <td><strong>{$oPluginSprachvariable->cName}</strong></td>
                            <td>{$oPluginSprachvariable->cBeschreibung}</td>
                        </tr>

                        {foreach name="sprachen" from=$oSprache_arr item=oSprache}
                            <tr>
                                <td>{$oSprache->cNameDeutsch}</td>
                                <td>
                                    {assign var=cISOSprache value=$oSprache->cISO|upper}
                                    {if isset($oPluginSprachvariable->oPluginSprachvariableSprache_arr[$cISOSprache]) && $oPluginSprachvariable->oPluginSprachvariableSprache_arr[$cISOSprache]|count_characters > 0}
                                        <input class="form-control" style="width: 300px;" name="{$oPluginSprachvariable->kPluginSprachvariable}_{$cISOSprache}" type="text" value="{$oPluginSprachvariable->oPluginSprachvariableSprache_arr[$cISOSprache]|escape:'html'}" />
                                    {else}
                                        <input class="form-control" style="width: 300px;" name="{$oPluginSprachvariable->kPluginSprachvariable}_{$cISOSprache}" type="text" value="" />
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                        <tr>
                            <td>&nbsp;</td>
                            <td><a onclick="ackCheck({$oPluginSprachvariable->kPluginSprachvariable}, {$kPlugin}); return false;" class="btn btn-danger button reset"><i class="fa fa-warning"></i> {#pluginLocalesStd#}</a></td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <div class="panel-footer">
                    <button name="speichern" type="submit" value="{#pluginBtnSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#pluginBtnSave#}</button>
                </div>
            </div>
        </form>
    {/if}
</div>