{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="auswahlassistent"}
{include file='tpl_inc/seite_header.tpl' cTitel=#auswahlassistent# cBeschreibung=#auswahlassistentDesc# cDokuURL=#auswahlassistentURL#}
<div id="content">
    {if !isset($noModule) || !$noModule}
    <div class="block">
        <form name="sprache" method="post" action="auswahlassistent.php">
            {$jtl_token}
            <input id="{#changeLanguage#}" type="hidden" name="sprachwechsel" value="1" />
            <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="lang-changer">{#changeLanguage#}:</strong></label>
                </span>
                <span class="input-group-wrap last">
                    <select id="lang-changer" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'uebersicht'} active{/if}">
            <a data-toggle="tab" role="tab" href="#overview">{#aaOverview#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'frage'} active{/if}">
            <a data-toggle="tab" role="tab" href="#question">{#aaQuestion#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'gruppe'} active{/if}">
            <a data-toggle="tab" role="tab" href="#group">{#aaGroup#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#config">{#aaConfig#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="overview" class="tab-pane fade{if !isset($cTab) || $cTab === 'uebersicht'} active in{/if}">
            {if isset($oAuswahlAssistentGruppe_arr) && $oAuswahlAssistentGruppe_arr|@count > 0}
                <div id="payment">
                    <div id="tabellenLivesuche">
                        <form name="uebersichtForm" method="post" action="auswahlassistent.php">
                            {$jtl_token}
                            <input type="hidden" name="tab" value="uebersicht" />
                            <input type="hidden" name="a" value="delGrp" />
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">{#aaOverview#}</h3>
                                </div>
                                <table class="list table">
                                    <thead>
                                    <tr>
                                        <th class="check">&nbsp;</th>
                                        <th class="tleft">{#aaName#}</th>
                                        <th class="tcenter">{#aaLocation#}</th>
                                        <th class="tcenter">{#aaLanguage#}</th>
                                        <th class="tcenter">{#aaActive#}</th>
                                        <th class="tright">&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach name=auswahlgruppen from=$oAuswahlAssistentGruppe_arr item=oAuswahlAssistentGruppe}
                                        <tr>
                                            <td class="check">
                                                <input name="kAuswahlAssistentGruppe_arr[]" type="checkbox" value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}" />
                                            </td>
                                            <td class="tleft">{$oAuswahlAssistentGruppe->cName}</td>
                                            <td class="tcenter">
                                                {foreach name=anzeigeort from=$oAuswahlAssistentGruppe->oAuswahlAssistentOrt_arr item=oAuswahlAssistentOrt}
                                                    {$oAuswahlAssistentOrt->cOrt}{if !$smarty.foreach.anzeigeort.last}, {/if}
                                                {/foreach}
                                            </td>
                                            <td class="tcenter">{$oAuswahlAssistentGruppe->cSprache}</td>
                                            <td class="tcenter">{if $oAuswahlAssistentGruppe->nAktiv}
                                                    <span class="success">{#yes#}</span>{else}<span class="error">{#no#}</span>{/if}
                                            </td>
                                            <td class="tright" width="265">
                                                {if isset($oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr) && $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr|@count > 0}
                                                    <div class="btn-group">
                                                    <a class="btn btn-default button down" id="btn_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}">Fragen anzeigen</a>
                                                {else}
                                                    <div>
                                                {/if}
                                                <a href="auswahlassistent.php?a=editGrp&g={$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}&token={$smarty.session.jtl_token}" class="btn btn-default edit"><i class="fa fa-edit"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    {if isset($oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr) && $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr|@count > 0}
                                        <tr>
                                            <td class="tleft" colspan="6" id="row_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}" style="display: none;">
                                                <table class="list table">
                                                    <tr>
                                                        <th class="tleft">{#aaQuestionName#}</th>
                                                        <th class="tcenter">{#aaMerkmal#}</th>
                                                        <th class="tcenter">{#aaSort#}</th>
                                                        <th class="tcenter">{#aaActive#}</th>
                                                        <th class="tright">&nbsp;</th>
                                                    </tr>
                                                    {foreach name=auswahlfragen from=$oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr item=oAuswahlAssistentFrage}
                                                        <tr class="tab_bg{$smarty.foreach.auswahlfragen.iteration%2}">
                                                            <td class="tleft">{$oAuswahlAssistentFrage->cFrage}</td>
                                                            <td class="tcenter">{$oAuswahlAssistentFrage->oMerkmal->cName}</td>
                                                            <td class="tcenter">{$oAuswahlAssistentFrage->nSort}</td>
                                                            <td class="tcenter">{if $oAuswahlAssistentFrage->nAktiv}
                                                                <span class="success">{#yes#}</span>{else}
                                                                <span class="error">{#no#}</span>{/if}
                                                            </td>
                                                            <td class="tright" style="width:250px">
                                                                <div class="btn-group">
                                                                    <a href="auswahlassistent.php?a=editQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}&token={$smarty.session.jtl_token}" class="btn btn-default edit"><i class="fa fa-edit"></i></a>
                                                                    <a href="auswahlassistent.php?a=delQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}&token={$smarty.session.jtl_token}" class="btn btn-danger remove"><i class="fa fa-trash"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                </table>
                                            </td>
                                        </tr>
                                        <script>
                                            $("#btn_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").click(function () {ldelim}
                                                $("#row_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").slideToggle('slow', 'linear');
                                            {rdelim});
                                        </script>
                                    {/if}
                                    {/foreach}
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td class="check">
                                            <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                        </td>
                                        <td colspan="5" class="tleft"><label for="ALLMSGS">{#globalSelectAll#}</label></td>
                                    </tr>
                                    </tfoot>
                                </table>
                                <div class="panel-footer">
                                    <button name="aaDelete" type="submit" class="btn btn-danger" value="{#aaDelete#}"><i class="fa fa-trash"></i> {#aaDelete#}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <!-- #overview -->
        <div id="question" class="tab-pane fade{if isset($cTab) && $cTab === 'frage'} active in{/if}">
            <form class="navbar-form settings" method="post" action="auswahlassistent.php">
                {$jtl_token}
                <input name="speichern" type="hidden" value="1">
                <input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
                <input name="tab" type="hidden" value="frage">
                <input name="a" type="hidden" value="addQuest">
                {if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}
                    <input class="form-control" name="kAuswahlAssistentFrage" type="hidden" value="{if isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0}{$kAuswahlAssistentFrage}{else}{$oFrage->kAuswahlAssistentFrage}{/if}">
                {/if}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#aaQuestion#}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cFrage">{#aaQuestionName#}{if isset($cPlausi_arr.cName)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                            </span>
                            <input id="cFrage" class="form-control{if isset($cPlausi_arr.cFrage)} fieldfillout{/if}" name="cFrage" type="text" value="{if isset($cPost_arr.cFrage)}{$cPost_arr.cFrage}{elseif isset($oFrage->cFrage)}{$oFrage->cFrage}{/if}">
                            <span class="input-group-addon">{getHelpDesc cDesc="Wie soll die Frage lauten?"}</span>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="kAuswahlAssistentGruppe">Gruppe{if isset($cPlausi_arr.kAuswahlAssistentGruppe)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="kAuswahlAssistentGruppe" name="kAuswahlAssistentGruppe" class="form-control{if isset($cPlausi_arr.kAuswahlAssistentGruppe)} fieldfillout{/if}">
                                    <option value="-1">{#aaChoose#}</option>
                                    {foreach name=gruppen from=$oAuswahlAssistentGruppe_arr item=oAuswahlAssistentGruppe}
                                        <option value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"{if isset($oAuswahlAssistentGruppe->kAuswahlAssistentGruppe) && ((isset($cPost_arr.kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $cPost_arr.kAuswahlAssistentGruppe) || (isset($oFrage->kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $oFrage->kAuswahlAssistentGruppe))} selected{/if}>{$oAuswahlAssistentGruppe->cName}</option>
                                    {/foreach}
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc="In welche Gruppe soll die Frage hinzugef&uuml;gt werden?"}</span>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="kMM">Merkmal {if isset($cPlausi_arr.kMerkmal) && $cPlausi_arr.kMerkmal == 1} <span class="fillout">{#FillOut#}</span>{/if}
                                    {if isset($cPlausi_arr.kMerkmal) && $cPlausi_arr.kMerkmal == 2 }<span class="fillout">{#aaMerkmalTaken#}</span>{/if}
                                </label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="kMM" name="kMerkmal" class="form-control{if isset($cPlausi_arr.kMerkmal)} fieldfillout{/if}">
                                    <option value="-1">{#aaChoose#}</option>
                                    {foreach name=merkmale from=$oMerkmal_arr item=oMerkmal}
                                        <option value="{$oMerkmal->kMerkmal}"{if (isset($cPost_arr.kMerkmal) && $oMerkmal->kMerkmal == $cPost_arr.kMerkmal) || (isset($oFrage->kMerkmal) && $oMerkmal->kMerkmal == $oFrage->kMerkmal)} selected{/if}>{$oMerkmal->cName}</option>
                                    {/foreach}
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc="Welches Merkmal soll die Frage erhalten?"}</span>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nSort">Sortierung{if isset($cPlausi_arr.nSort)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                            </span>
                            <input id="nSort" class="form-control{if isset($cPlausi_arr.nSort)} fieldfillout{/if}" name="nSort" type="text" value="{if isset($cPost_arr.nSort)}{$cPost_arr.nSort}{elseif isset($oFrage->nSort)}{$oFrage->nSort}{else}1{/if}">
                            <span class="input-group-addon">{getHelpDesc cDesc="An welcher Position soll die Frage stehen? (Umso h&ouml;her desto weiter unten, z.b. 3)"}</span>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAktiv">Aktiv</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nAktiv" class="form-control" name="nAktiv">
                                    <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 1)} selected{/if}>
                                        Ja
                                    </option>
                                    <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 0)} selected{/if}>
                                        Nein
                                    </option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc="Soll die Frage aktiviert sein? (Aktivierte Fragen werden angezeigt)"}</span>
                        </div>
                    </div>
                    <div class="panel-footer">
                        {if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}
                            {assign var=btnTitle value=#aaEdit#}
                        {else}
                            {assign var=btnTitle value=#save#}
                        {/if}
                        <button name="speichernSubmit" type="submit" value="{$btnTitle}" class="btn btn-primary"><i class="fa fa-save"></i> {$btnTitle}</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- #question -->
        <div id="group" class="tab-pane fade{if isset($cTab) && $cTab === 'gruppe'} active in{/if}">
            <form class="navbar-form settings" method="post" action="auswahlassistent.php">
                {$jtl_token}
                <input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
                <input name="tab" type="hidden" value="gruppe">
                <input name="a" type="hidden" value="addGrp">
                {if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}
                    <input class="form-control" name="kAuswahlAssistentGruppe" type="hidden" value="{if isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0}{$kAuswahlAssistentGruppe}{else}{$oGruppe->kAuswahlAssistentGruppe}{/if}">
                {/if}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#aaGroup#}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cName">{#aaName#}{if isset($cPlausi_arr.cName)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                            </span>
                            <input name="cName" id="cName" type="text" class="form-control{if isset($cPlausi_arr.cName)} fieldfillout{/if}" value="{if isset($cPost_arr.cName)}{$cPost_arr.cName}{elseif isset($oGruppe->cName)}{$oGruppe->cName}{/if}">
                            <span class="input-group-addon">{getHelpDesc cDesc="Welchen Namen soll die Gruppe erhalten?"}</span>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cBeschreibung">{#aaDesc#}</label>
                            </span>
                            <textarea id="cBeschreibung" name="cBeschreibung" class="form-control description">{if isset($cPost_arr.cBeschreibung)}{$cPost_arr.cBeschreibung}{elseif isset($oGruppe->cBeschreibung)}{$oGruppe->cBeschreibung}{/if}</textarea>
                            <span class="input-group-addon">{getHelpDesc cDesc="Wie soll die Beschreibung lauten?"}</span>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="assign_categories_list">{#aaKat#}{if isset($cPlausi_arr.cOrt)} <span class="fillout">{#FillOut#}</span>{/if}
                                    {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie != 3} <span class="fillout">{#aaKatSyntax#}</span>{/if}
                                    {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie == 3} <span class="fillout">{#aaKatTaken#}</span>{/if}
                                </label>
                            </span>
                            <input name="cKategorie" id="assign_categories_list" type="text" class="form-control{if isset($cPlausi_arr.cOrt)} fieldfillout{/if}" value="{if isset($cPost_arr.cKategorie)}{$cPost_arr.cKategorie}{elseif isset($oGruppe->cKategorie)}{$oGruppe->cKategorie}{/if}">
                            <span class="input-group-addon"><a href="#" class="button edit" id="show_categories_list">Kategorien verwalten</a> {getHelpDesc cDesc="In welcher Kategorie soll die Gruppe angezeigt werden?"}</span>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="kLink_arr">{#aaSpecialSite#}{if isset($cPlausi_arr.cOrt)} <span class="fillout">{#FillOut#}</span>{/if}
                                    {if isset($cPlausi_arr.kLink_arr)} <span class="fillout">{#aaLinkTaken#}</span>{/if}</label>
                            </span>
                            <span class="input-group-wrap">
                                {if $oLink_arr|count > 0}
                                    <select id="kLink_arr" name="kLink_arr[]"  class="form-control{if isset($cPlausi_arr.cOrt)} fieldfillout{/if}" multiple>
                                        {foreach name="links" from=$oLink_arr item=oLink}
                                            {assign var=bAOSelect value=false}
                                            {if isset($oGruppe->oAuswahlAssistentOrt_arr) && $oGruppe->oAuswahlAssistentOrt_arr|@count > 0}
                                                {foreach name=gruppelinks from=$oGruppe->oAuswahlAssistentOrt_arr item=oAuswahlAssistentOrt}
                                                    {if $oLink->kLink == $oAuswahlAssistentOrt->kKey && $oAuswahlAssistentOrt->cKey == $AUSWAHLASSISTENT_ORT_LINK}
                                                        {assign var=bAOSelect value=true}
                                                    {/if}
                                                {/foreach}
                                            {elseif isset($cPost_arr.kLink_arr) && $cPost_arr.kLink_arr|@count > 0}
                                                {foreach name=gruppelinks from=$cPost_arr.kLink_arr item=kLink}
                                                    {if $kLink == $oLink->kLink}
                                                        {assign var=bAOSelect value=true}
                                                    {/if}
                                                {/foreach}
                                            {/if}
                                            <option value="{$oLink->kLink}"{if $bAOSelect} selected{/if}>{$oLink->cName}</option>
                                        {/foreach}
                                    </select>
                                {else}
                                    <input type="text" disabled value="Keine Spezialseite &quot;Auswahlassistent&quot; vorhanden." class="form-control" />
                                {/if}
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc="Auf welcher Spezialseite soll die Gruppe angezeigt werden? (Mehrfachauswahl und Abwahl mit STRG m&ouml;glich)"}</span>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nStartseite">{#aaStartSite#}{if isset($cPlausi_arr.cOrt)} <span class="fillout">{#FillOut#}</span>{/if}
                                    {if isset($cPlausi_arr.nStartseite)} <span class="fillout">{#aaStartseiteTaken#}</span>{/if}
                                </label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nStartseite" name="nStartseite"  class="form-control{if isset($cPlausi_arr.cOrt)} fieldfillout{/if}">
                                    <option value="0"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 0) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 0)} selected{/if}>
                                        Nein
                                    </option>
                                    <option value="1"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 1) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 1)} selected{/if}>
                                        Ja
                                    </option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc="Soll die Gruppe auf der Startseite angezeigt werden? (Es darf immer nur eine Gruppe auf der Startseite aktiv sein)"}</span>
                        </div>

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAktiv">{#aaActive#}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nAktiv" class="form-control" name="nAktiv">
                                    <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 1)} selected{/if}>
                                        Ja
                                    </option>
                                    <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 0)} selected{/if}>
                                        Nein
                                    </option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc="Soll die Checkbox im Frontend aktiv und somit sichtbar sein?"}</span>
                        </div>
                    </div>
                    <div class="panel-footer">
                        {if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}
                            {assign var=saveTitle value=#aaEdit#}
                        {else}
                            {assign var=saveTitle value=#save#}
                        {/if}
                        <button name="speicherGruppe" type="submit" value="{$saveTitle}" class="btn btn-primary"><i class="fa fa-save"></i> {$saveTitle}</button>
                    </div>
                </div>
                <div id="ajax_list_picker" class="ajax_list_picker categories">{include file="tpl_inc/popup_kategoriesuche.tpl"}</div>
            </form>
        </div>
        <!-- #group -->
        <div id="config" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='auswahlassistent.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
        <!-- #config -->
    </div>
    <!-- .tab-content -->
    {else}
        <div class="alert alert-danger">{#noModuleAvailable#}</div>
    {/if}
</div><!-- #content -->

{include file='tpl_inc/footer.tpl'}