{config_load file="$lang.conf" section="lang"}
{include file='tpl_inc/header.tpl'}
<script type="text/javascript">
    {literal}
    $(document).ready(function () {
        $('.keyarea').each(function (idx, item) {
            var old_height = $(this).css('height');
            $(this).bind('focus', function () {
                $(this).css('height', '60px');
            });
        });
    });
    function showSection(sectionID) {
        $('.section').each(function (idx, item) {
            $(this).hide();
        });
        $('#section' + sectionID).show();
    }
    {/literal}
</script>
{include file='tpl_inc/seite_header.tpl' cTitel=#lang# cBeschreibung=#langDesc# cDokuURL=#langURL#}
<div id="content" class="container-fluid">
    <form name="sprache" method="post" action="sprache.php">
        {$jtl_token}
        <div class="block">
            <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="{#lang#}">Installierte Sprachen:</label>
                </span>
                <input type="hidden" name="sprache" value="1" />
                <span class="input-group-wrap last">
                    <select class="form-control" name="cISO" id="{#lang#}" onchange="document.sprache.submit();">
                        <option value="">Bitte w&auml;hlen</option>
                        {foreach from=$oInstallierteSprachen item=oSprache}
                            <option value="{$oSprache->cISO}" {if $cISO == $oSprache->cISO}selected="selected"{/if}>{$oSprache->cNameDeutsch} {if $oSprache->cShopStandard === 'Y'}(Standard){/if}</option>
                        {/foreach}
                    </select>
                </span>
            </div>

        </div>
    </form>
    {if $cISO|strlen > 0}
        <ul class="nav nav-tabs" role="tablist">
            <li class="tab{if !isset($cTab) || $cTab === 'sprachvariablen'} active{/if}">
                <a data-toggle="tab" role="tab" href="#sprachvariablen">Sprachvariablen</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'suche'} active{/if}">
                <a data-toggle="tab" role="tab" href="#suche">Suche</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'hinzufuegen'} active{/if}">
                <a data-toggle="tab" role="tab" href="#hinzufuegen">Hinzuf&uuml;gen</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'ngvariablen'} active{/if}">
                <a data-toggle="tab" role="tab" href="#ngvariablen">Nicht gefundene Variablen</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'export'} active{/if}">
                <a data-toggle="tab" role="tab" href="#export">Export</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'import'} active{/if}">
                <a data-toggle="tab" role="tab" href="#import">Import</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="sprachvariablen" class="tab-pane fade{if !isset($cTab) || $cTab == 'sprachvariablen'} active in{/if}">
                <div class="block tcenter">
                    <div class="input-group p25 left">
                        <span class="input-group-addon">
                            <label for="section">Sektion:</label>
                        </span>
                        <span class="input-group-wrap last">
                            <select class="form-control" name="kSprachsektion" onchange="showSection(options[selectedIndex].value);" id="section">
                                {foreach from=$oWerte_arr item=oSektion}
                                    <option value="{$oSektion->kSprachsektion}" {if isset($kSprachsektion) && $kSprachsektion == $oSektion->kSprachsektion}selected="selected"{/if}>{$oSektion->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                </div>
                {foreach from=$oWerte_arr item=oSektion}
                    <div id="section{$oSektion->kSprachsektion}" class="container2 section">
                        <form action="sprache.php" method="post">
                            {$jtl_token}
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">{#edit#}</h3>
                                </div>
                                <table class="list table">
                                    <thead>
                                    <tr>
                                        <th style="width:20%" class="tleft">Variable ({$oSektion->oWerte_arr|@count})</th>
                                        <th style="width:70%" class="tleft">Wert</th>
                                        <th style="width:10%">Aktion</th>
                                    </tr>
                                    <tbody>
                                    {foreach from=$oSektion->oWerte_arr item=oWert}
                                        <tr>
                                            <td><label for="{$oWert->kSprachsektion}{$oWert->cName}">{$oWert->cName}</label></td>
                                            <td>
                                                <input type="hidden" name="cName[]" value="{$oWert->cName}" />
                                                <textarea rows="1" class="form-control keyarea" id="{$oWert->kSprachsektion}{$oWert->cName}" name="cWert[]">{$oWert->cWert}</textarea>
                                            </td>
                                            <td valign="top" align="center">
                                                {if !$oWert->bSystem}
                                                    <span class="btn-group">
                                                {/if}
                                                <a href="#" onclick="$('#{$oWert->kSprachsektion}{$oWert->cName}').val('{$oWert->cStandard|escape:"htmlall"}');return false;" class="button reset notext btn btn-default btn-tooltip" title="wiederherstellen">
                                                    <i class="fa fa-refresh"></i>
                                                </a>
                                                {if !$oWert->bSystem}
                                                    <a href="sprache.php?cISO={$cISO}&action=delete&kSprachsektion={$oWert->kSprachsektion}&cName={$oWert->cName}&token={$smarty.session.jtl_token}" class="btn btn-default button remove notext" title="entfernen"><i class="fa fa-trash"></i></a>
                                                    </span>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                                <input type="hidden" name="action" value="updateSection" />
                                <input type="hidden" name="cISO" value="{$cISO}" />
                                <input type="hidden" name="kSprachsektion" value="{$oSektion->kSprachsektion}" />

                                <div class="panel-footer">
                                    <button type="submit" value="Speichern" class="btn btn-primary"><i class="fa fa-trash"></i> {#save#}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                {/foreach}

                <script type="text/javascript">
                    showSection({if isset($kSprachsektion) && $kSprachsektion > 0}{$kSprachsektion}{else}{$oWerte_arr[0]->kSprachsektion}{/if});
                </script>
            </div>
            <div id="suche" class="tab-pane fade{if isset($cTab) && $cTab === 'suche'} active in{/if}">
                <form action="sprache.php" method="post" id="{if isset($oWert->cSektion)}{$oWert->cSektion}{/if}{if isset($oWert->cName)}{$oWert->cName}{/if}">
                    {$jtl_token}
                    <input type="hidden" name="action" value="search" />
                    <input type="hidden" name="cISO" value="{$cISO}" />
                    <div class="block">
                        <div class="input-group p25 left">
                            <span class="input-group-addon">
                                <label for="cSuchwort">Suchwort: </label>
                            </span>
                            <input class="form-control" type="text" id="cSuchwort" name="cSuchwort" autocomplete="off" />
                            <span class="input-group-btn">
                                <button type="submit" value="Suchen" class="btn btn-info"><i class="fa fa-search"></i> {#confSearch#}</button>
                            </span>
                        </div>
                    </div>
                </form>

                {if isset($oSuchWerte_arr) && $oSuchWerte_arr|@count > 0}
                    <form action="sprache.php" method="post">
                        {$jtl_token}
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Suchergebnisse</h3>
                            </div>
                            <table class="list table">
                                <thead>
                                <tr>
                                    <th class="tleft" style="width:20%">Sektion</th>
                                    <th class="tleft" style="width:20%">Variable ({$oSuchWerte_arr|@count})</th>
                                    <th class="tleft" style="width:50%">Wert</th>
                                    <th class="th-3" style="width:10%">Aktion</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach from=$oSuchWerte_arr item=oSuchWert}
                                    <tr>
                                        <td valign="top" style="line-height:25px">{$oSuchWert->cSektionName}</td>
                                        <td valign="top" style="line-height:25px">{$oSuchWert->cName|regex_replace:"/($cSuchwort)/i":"<font color='#d70000'>\$1</font>"}</td>
                                        <td valign="top">
                                            <textarea rows="1" class="form-control keyarea" id="suche_{$oSuchWert->kSprachsektion}{$oSuchWert->cName}" name="cWert[]">{$oSuchWert->cWert}</textarea>
                                        </td>
                                        <td valign="top" align="center">
                                            <a href="#" onclick="$('#suche_{$oSuchWert->kSprachsektion}{$oSuchWert->cName}').val('{$oSuchWert->cStandard|escape:"htmlall"}');return false;" class="button reset notext btn btn-default btn-tooltip" title="wiederherstellen">
                                                <span class="glyphicon glyphicon-repeat"></span>
                                            </a>
                                            {if !$oSuchWert->bSystem}
                                                <a href="sprache.php?cISO={$cISO}&action=delete&kSprachsektion={$oSuchWert->kSprachsektion}&cName={$oSuchWert->cName}&token={$smarty.session.jtl_token}" class="button remove notext" title="entfernen">Entfernen</a>
                                            {/if}
                                            <input type="hidden" name="kSprachsektion[]" value="{$oSuchWert->kSprachsektion}" />
                                            <input type="hidden" name="cName[]" value="{$oSuchWert->cName}" />
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                            <input type="hidden" name="update" value="1" />
                            <input type="hidden" name="cSuchwort" value="{$cSuchwort}" />
                            <input type="hidden" name="action" value="search" />
                            <input type="hidden" name="cISO" value="{$cISO}" />

                            <div class="panel-footer">
                                <button type="submit" value="Speichern" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                            </div>
                        </div>
                    </form>
                {/if}
            </div>
            <div id="hinzufuegen" class="tab-pane fade{if isset($cTab) && $cTab === 'hinzufuegen'} active in{/if}">
                <form action="sprache.php" method="post">
                    {$jtl_token}
                    <div id="settings">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Neue Sprachvariable</h3>
                            </div>
                            <div class="panel-body">
                                <div class="item input-group">
                                    <span class="input-group-addon">
                                        <label for="kSprachsektion">Sektion</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select class="form-control" id="kSprachsektion" name="kSprachsektion" onchange="showSection(options[selectedIndex].value);">
                                            {foreach from=$oWerte_arr item=oSektion}
                                                <option value="{$oSektion->kSprachsektion}" {if $oSektion->cName == "custom"}selected="selected"{/if}>{$oSektion->cName}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                </div>

                                <div class="item input-group">
                                    <span class="input-group-addon">
                                        <label for="cName">Variable</label>
                                    </span>
                                    <input class="form-control" type="text" name="cName" id="cName" />
                                </div>

                                {foreach from=$oInstallierteSprachen item=oSprache}
                                    <div class="item input-group">
                                        <span class="input-group-addon">
                                            <label for="lang_{$oSprache->cISO}">{$oSprache->cNameDeutsch}</label>
                                        </span>
                                        <input type="hidden" name="cSprachISO[]" value="{$oSprache->cISO}" />
                                        <input class="form-control" type="text" name="cWert[]" id="lang_{$oSprache->cISO}" />
                                    </div>
                                {/foreach}
                            </div>

                            <div class="panel-footer">
                                <input type="hidden" name="action" value="add" />
                                <input type="hidden" name="cISO" value="{$cISO}" />
                                <button type="submit" value="{#add#}" class="btn btn-primary"><i class="fa fa-share"></i> {#add#}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div id="ngvariablen" class="tab-pane fade{if isset($cTab) && $cTab === 'ngvariablen'} active in{/if}">
                {if $oLogWerte_arr|@count > 0}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Nicht gefundene Variablen</h3>
                        </div>
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="tleft">Sektion</th>
                                <th class="tleft">Variable</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$oLogWerte_arr item=oWert}
                                <tr>
                                    <td>{$oWert->cSektion}</td>
                                    <td>{$oWert->cName}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                        <div class="panel-footer">
                            <form action="sprache.php" method="post">
                                <input type="hidden" name="cISO" value="{$cISO}" />
                                <button type="submit" class="btn btn-danger" name="clearLog" value="1"><i class="fa fa-trash"></i> zur&uuml;cksetzen</button>
                            </form>
                        </div>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {/if}
            </div>
            <div id="export" class="tab-pane fade{if isset($cTab) && $cTab === 'export'} active in{/if}">
                <div class="block">
                    <form action="sprache.php" method="post">
                        {$jtl_token}
                        <div class="input-group p50 left">
                            <span class="input-group-addon">
                                <label for="exportTyp">Variablen: </label>
                            </span>
                            <span class="input-group-wrap">
                                <select class="form-control" name="nTyp" id="exportTyp">
                                    <option value="0">Alle Variablen</option>
                                    <option value="1">Nur Systemvariablen</option>
                                    <option value="2">Nur eigene Variablen</option>
                                </select>
                            </span>

                            <input type="hidden" name="action" value="export" />
                            <input type="hidden" name="cISO" value="{$cISO}" />
                            <span class="input-group-btn">
                                <button type="submit" value="Exportieren" class="btn btn-info">Exportieren</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
            <div id="import" class="tab-pane fade{if isset($cTab) && $cTab == 'import'} active in{/if}">
                <form action="sprache.php" method="post" enctype="multipart/form-data">
                    {$jtl_token}
                    <div id="settings">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Sprache importieren</h3>
                            </div>
                            <div class="panel-body">
                                <div class="item input-group">
                                    <span class="input-group-addon">
                                        <label for="cSprachISO">Sprache</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select name="cSprachISO" class="form-control selectBox" id="cSprachISO">
                                            {foreach from=$oVerfuegbareSprachen item=oSprache}
                                                <option value="{$oSprache->cISO}" {if $oSprache->cISO == $cISO}selected="selected"{/if}>{$oSprache->cNameDeutsch}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                </div>

                                <div class="item input-group">
                                    <span class="input-group-addon">
                                        <label for="nTyp">Typ</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select name="nTyp" id="nTyp" class="form-control">
                                            <option value="0">Vorhandene l&ouml;schen, dann importieren</option>
                                            <option value="1">Vorhandene &uuml;berschreiben, neue importieren</option>
                                            <option value="2">Vorhandene beibehalten, neue importieren</option>
                                        </select>
                                    </span>
                                </div>

                                <div class="item input-group">
                                    <span class="input-group-addon">
                                        <label for="importfile">Datei</label>
                                    </span>
                                    <input class="form-control" id="importfile" name="langfile" type="file" size="55" />
                                </div>
                            </div>
                            <div class="panel-footer">
                                <input type="hidden" name="action" value="import" />
                                <input type="hidden" name="cISO" value="{$cISO}" />
                                <button type="submit" value="Importieren" class="btn btn-primary">Importieren</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    {/if}
</div><!-- #content -->
{include file='tpl_inc/footer.tpl'}