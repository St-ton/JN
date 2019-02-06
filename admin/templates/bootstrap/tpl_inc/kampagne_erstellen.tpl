<script type="text/javascript">
    {literal}
        function changeWertSelect(currentSelect)
        {
            switch ($(currentSelect).val()) {
                case '0':
                    $('#static-value-input-group').show();
                    break;
                case '1':
                    $('#static-value-input-group').hide();
                    break;
            }
        }
    {/literal}
</script>

{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('kampagneEdit')}
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('kampagneCreate')}
{/if}

<form method="post" action="kampagne.php">
    {$jtl_token}
    <input type="hidden" name="tab" value="uebersicht">
    <input type="hidden" name="erstellen_speichern" value="1">
    {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0}
        <input type="hidden" name="kKampagne" value="{$oKampagne->kKampagne}">
    {/if}
    <div class="panel panel-default settings">
        <div class="panel-body">
            <div class="input-group">
                <span class="input-group-addon"><label for="cName">{__('kampagneName')}</label></span>
                <span class="input-group-wrap">
                    <input id="cName" class="form-control" name="cName" type="text"
                           value="{if isset($oKampagne->cName)}{$oKampagne->cName}{/if}"
                            {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if}>
                </span>
            </div>
            <div class="input-group">
                <span class="input-group-addon"><label for="cParameter">{__('kampagneParam')}</label></span>
                <span class="input-group-wrap">
                    <input id="cParameter" class="form-control" name="cParameter" type="text"
                           value="{if isset($oKampagne->cParameter)}{$oKampagne->cParameter}{/if}">
                </span>
            </div>
            <div class="input-group">
                <span class="input-group-addon"><label for="cWertSelect">{__('kampagneValueType')}</label></span>
                <span class="input-group-wrap">
                    <select name="nDynamisch" class="form-control combo" id="cWertSelect"
                            onChange="changeWertSelect(this);"
                            {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if}>
                        <option value="0"{if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 0} selected{/if}>{__('fixedValue')}</option>
                        <option value="1"{if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1} selected{/if}>{__('dynamic')}</option>
                    </select>
                </span>
            </div>
            <div class="input-group" id="static-value-input-group">
                <span class="input-group-addon"><label for="cWert">{__('kampagneValueStatic')}</label></span>
                <span class="input-group-wrap">
                    <input id="cWert" class="form-control" name="cWert" type="text"
                           value="{if isset($oKampagne->cWert)}{$oKampagne->cWert}{/if}"
                           {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if} />
                </span>
            </div>
            <div class="input-group">
                <span class="input-group-addon"><label for="nAktiv">{__('activated')}</label></span>
                <span class="input-group-wrap">
                    <select id="nAktiv" name="nAktiv" class="combo form-control">
                        <option value="0"{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 0} selected{/if}>{__('no')}</option>
                        <option value="1"{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 1} selected{/if}>{__('yes')}</option>
                    </select>
                </span>
            </div>
        </div>
        <div class="panel-footer">
            <div class="btn-group">
                <button name="submitSave" type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                <a href="kampagne.php?tab=uebersicht" class="button btn btn-default"><i class="fa fa-angle-double-left"></i> {__('back')}</a>
            </div>
        </div>
    </div>
</form>