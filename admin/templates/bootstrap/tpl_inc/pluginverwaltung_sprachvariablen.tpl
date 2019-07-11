<script type="text/javascript">
    function ackCheck(kPluginSprachvariable, kPlugin)
    {
        var bCheck = confirm('{__('sureResetLangVar')}');

        if(bCheck) {
            window.location.href = 'pluginverwaltung.php?pluginverwaltung_sprachvariable=1&kPlugin=' + kPlugin +
                '&kPluginSprachvariable=' + kPluginSprachvariable + '&token={$smarty.session.jtl_token}';
        }
    }
</script>
{include file='tpl_inc/seite_header.tpl' cTitel=__('pluginverwaltung') cBeschreibung=__('pluginverwaltungDesc')}
<div id="content" class="container-fluid">
    {if $plugin->getLocalization()->getLangVars()->count() > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_sprachvariable" value="1" />
            <input type="hidden" name="kPlugin" value="{$kPlugin}" />
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('pluginverwaltungLocales')}</div>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">{__('pluginName')}</th>
                            <th class="tleft">{__('description')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $plugin->getLocalization()->getLangVars() as $var}
                            <tr>
                                <td><i>{$var->name}</i></td>
                                <td>{__($var->description)}</td>
                            </tr>
                            {foreach $pluginLanguages as $lang}
                                <tr>
                                    {assign var=cISOSprache value=strtoupper($lang->getIso())}
                                    <td><label for="lv-{$var->id}_{$cISOSprache}">{$lang->getLocalizedName()}</label></td>
                                    <td>
                                        {if isset($var->values[$cISOSprache]) && $var->values[$cISOSprache]|strlen > 0}
                                            <input id="lv-{$var->id}_{$cISOSprache}" class="form-control" style="width: 350px;" name="{$var->id}_{$cISOSprache}" type="text" value="{$var->values[$cISOSprache]|escape:'html'}" />
                                        {else}
                                            <input id="lv-{$var->id}_{$cISOSprache}" class="form-control" style="width: 350px;" name="{$var->id}_{$cISOSprache}" type="text" value="" />
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                            <tr>
                                <td>&nbsp;</td>
                                <td><a onclick="ackCheck({$var->id}, {$kPlugin}); return false;" class="btn btn-danger button reset"><i class="fa fa-warning"></i> {__('pluginLocalesStd')}</a></td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                </div>
            </div>
        </form>
    {/if}
</div>
