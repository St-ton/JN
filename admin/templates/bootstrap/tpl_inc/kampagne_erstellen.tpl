<script type="text/javascript">
    function changeWertSelect(currentSelect) {ldelim}
        if (currentSelect.selectedIndex == "0")
            document.getElementById("cWertInput").style.display = "block";
        else if (currentSelect.selectedIndex == "1")
            document.getElementById("cWertInput").style.display = "none";
    {rdelim}
</script>

{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0}
    {include file='tpl_inc/seite_header.tpl' cTitel=#kampagneEdit#}
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=#kampagneCreate#}
{/if}

<div id="content" class="container-fluid">
    <form method="post" action="kampagne.php">
        {$jtl_token}
        <input type="hidden" name="tab" value="uebersicht" />
        <input type="hidden" name="erstellen_speichern" value="1" />
        {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0}
            <input type="hidden" name="kKampagne" value="{$oKampagne->kKampagne}" />
        {/if}

        <table class="kundenfeld table" id="formtable">
            <tr>
                <td><label for="cName">{#kampagneName#}</td>
                <td>
                    <input id="cName" class="form-control" name="cName" type="text" value="{if isset($oKampagne->cName)}{$oKampagne->cName}{/if}"{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if} />
                </td>
            </tr>

            <tr>
                <td><label for="cParameter">{#kampagneParam#}</td>
                <td><input id="cParameter" class="form-control" name="cParameter" type="text" value="{if isset($oKampagne->cParameter)}{$oKampagne->cParameter}{/if}" /></td>
            </tr>

            <tr>
                <td><label for="cWertSelect">{#kampagneValue#}</td>
                <td>
                    <select name="nDynamisch" class="form-control combo" id="cWertSelect" onChange="changeWertSelect(this);"{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if}>
                        <option value="0"{if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 0} selected{/if}>Fester Wert</option>
                        <option value="1"{if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1} selected{/if}>Dynamisch</option>
                    </select>
                    <div id="cWertInput" style="display: {if !isset($oKampagne->nDynamisch) || $oKampagne->nDynamisch == 0}block{else}none{/if};">
                        <label for="cWert">{#kampagneValueStatic#}: </label>
                        <input id="cWert" class="form-control" name="cWert" type="text" value="{if isset($oKampagne->cWert)}{$oKampagne->cWert}{/if}"{if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000} disabled{/if} />
                    </div>
                </td>
            </tr>

            <tr>
                <td><label for="nAktiv">{#kampagnenActive#}</label></td>
                <td>
                    <select id="nAktiv" name="nAktiv" class="combo form-control">
                        <option value="0"{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 0} selected{/if}>Nein</option>
                        <option value="1"{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 1} selected{/if}>Ja</option>
                    </select>
                </td>
            </tr>

        </table>
        <div class="submit-wrap btn-group">
            <button name="submitSave" type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
            <a href="kampagne.php?tab=uebersicht" class="button btn btn-default"><i class="fa fa-angle-double-left"></i> {#kampagneBackBTN#}</a>
        </div>
    </form>
</div>