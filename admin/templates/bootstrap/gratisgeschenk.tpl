{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="gratisgeschenk"}

{assign var=cFunAttrib value=$ART_ATTRIBUT_GRATISGESCHENKAB}

{include file='tpl_inc/seite_header.tpl' cTitel=#ggHeader# cDokuURL=#ggURL#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'aktivegeschenke'} active{/if}">
            <a data-toggle="tab" role="tab" href="#aktivegeschenke">{#ggActiveProducts#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'haeufigegeschenke'} active{/if}">
            <a data-toggle="tab" role="tab" href="#haeufigegeschenke">{#ggCommonBuyedProducts#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'letzten100geschenke'} active{/if}">
            <a data-toggle="tab" role="tab" href="#letzten100geschenke">{#ggLast100Products#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#ggSettings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="aktivegeschenke" class="tab-pane fade {if !isset($cTab) || $cTab === 'aktivegeschenke'} active in{/if}">
            {if isset($oAktiveGeschenk_arr) && $oAktiveGeschenk_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiAktiv cAnchor='aktivegeschenke'}
                <div class="settings panel panel-default table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="tleft">{#ggProductName#}</th>
                            <th class="th-2">{#ggOrderValueMin#}</th>
                            <th class="th-3">{#ggDate#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oAktiveGeschenk_arr as $oAktiveGeschenk}
                            <tr class="tab_bg{$smarty.foreach.aktivegeschenke.iteration%2}">
                                <td class="TD1">
                                    <a href="{$oAktiveGeschenk->cURLFull}" target="_blank">{$oAktiveGeschenk->cName}</a>
                                </td>
                                <td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$oAktiveGeschenk->FunktionsAttribute[$cFunAttrib]}</td>
                                <td class="tcenter">{$oAktiveGeschenk->dErstellt_de}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="haeufigegeschenke" class="tab-pane fade {if isset($cTab) && $cTab === 'haeufigegeschenke'} active in{/if}">
            {if isset($oHaeufigGeschenk_arr) && $oHaeufigGeschenk_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiHaeufig cAnchor='haeufigegeschenke'}
                <div class="settings panel panel-default table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="tleft">{#ggProductName#}</th>
                            <th class="th-2">{#ggOrderValueMin#}</th>
                            <th class="th-3">{#ggCount#}</th>
                            <th class="th-3">{#ggOrderValueAverage#}</th>
                            <th class="th-4">{#gglastOrdered#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oHaeufigGeschenk_arr as $oHaeufigGeschenk}
                            <tr class="tab_bg{$smarty.foreach.haeufigegeschenke.iteration%2}">
                                <td class="TD1">
                                    <a href="{$oAktiveGeschenk->cURLFull}" target="_blank">{$oHaeufigGeschenk->Artikel->cName}</a>
                                </td>
                                <td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$oHaeufigGeschenk->Artikel->FunktionsAttribute[$cFunAttrib]}</td>
                                <td class="tcenter">{$oHaeufigGeschenk->Artikel->nGGAnzahl} x</td>
                                <td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$oHaeufigGeschenk->avgOrderValue}</td>
                                <td class="tcenter">{$oHaeufigGeschenk->lastOrdered}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="letzten100geschenke" class="tab-pane fade {if isset($cTab) && $cTab === 'letzten100geschenke'} active in{/if}">
            {if isset($oLetzten100Geschenk_arr) && $oLetzten100Geschenk_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiLetzte100 cAnchor='letzten100geschenke'}
                <div class="settings panel panel-default table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="tleft">{#ggProductName#}</th>
                            <th class="th-2">{#ggOrderValueMin#}</th>
                            <th class="th-4">{#ggOrderValue#}</th>
                            <th class="th-4">{#ggOrdered#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oLetzten100Geschenk_arr as $oLetzten100Geschenk}
                            <tr class="tab_bg{$smarty.foreach.letzten100geschenke.iteration%2}">
                                <td class="TD1">
                                    <a href="{$oAktiveGeschenk->cURLFull}" target="_blank">{$oLetzten100Geschenk->Artikel->cName}</a>
                                </td>
                                <td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$oLetzten100Geschenk->Artikel->FunktionsAttribute[$cFunAttrib]}</td>
                                <td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$oLetzten100Geschenk->orderValue}</td>
                                <td class="tcenter">{$oLetzten100Geschenk->orderCreated}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='gratisgeschenk.php' buttonCaption=#save# title=#ggSettings# tab='einstellungen'}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}