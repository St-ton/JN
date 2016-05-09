<script type="text/javascript">
    function changeStatType(elem) {ldelim}
        window.location.href = "statistik.php?s=" + elem.options[elem.selectedIndex].value;
    {rdelim}
</script>
{if $nTyp == $STATS_ADMIN_TYPE_BESUCHER}
{assign var="cTitel" value=#statisticTitle#|cat:": "|cat:#statisticBesucher#}
{assign var="cURL" value=#statisticBesucherURL#}
{elseif $nTyp == $STATS_ADMIN_TYPE_KUNDENHERKUNFT}
{assign var="cTitel" value=#statisticTitle#|cat:": "|cat:#statisticKundenherkunft#}
{assign var="cURL" value=#statisticKundenherkunftURL#}
{elseif $nTyp == $STATS_ADMIN_TYPE_SUCHMASCHINE}
{assign var="cTitel" value=#statisticTitle#|cat:": "|cat:#statisticSuchmaschine#}
{assign var="cURL" value=#statisticSuchmaschineURL#}
{elseif $nTyp == $STATS_ADMIN_TYPE_UMSATZ}
{assign var="cTitel" value=#statisticTitle#|cat:": "|cat:#statisticUmsatz#}
{assign var="cURL" value=#statisticUmsatzURL#}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=#statisticDesc# cDokuURL=$cURL}
<div id="content" class="container-fluid">
    <div class="block">
        <div class="input-group p25">
            <span class="input-group-addon">
                <label for="statType">Statistiktyp:</label>
            </span>
            <span class="input-group-wrap last">
                <select class="form-control" name="statType" id="statType" onChange="changeStatType(this);">
                    <option value="{$STATS_ADMIN_TYPE_BESUCHER}"{if $nTyp == $STATS_ADMIN_TYPE_BESUCHER} selected{/if}>Besucher</option>
                    <option value="{$STATS_ADMIN_TYPE_KUNDENHERKUNFT}"{if $nTyp == $STATS_ADMIN_TYPE_KUNDENHERKUNFT} selected{/if}>Kundenherkunft</option>
                    <option value="{$STATS_ADMIN_TYPE_SUCHMASCHINE}"{if $nTyp == $STATS_ADMIN_TYPE_SUCHMASCHINE} selected{/if}>Suchmaschinen</option>
                    <option value="{$STATS_ADMIN_TYPE_UMSATZ}"{if $nTyp == $STATS_ADMIN_TYPE_UMSATZ} selected{/if}>Umsatz</option>
                </select>
            </span>
        </div>
    </div>

    <div class="ocontainer">
        <form method="post" action="statistik.php" class="form-horizontal">
            {$jtl_token}
            <div class="row">
                <div class="col-xs-9">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <strong>Von:</strong>
                        </div>
                        <div class="input-group-btn input-group-wrap">
                            <select name="cTagVon" class="form-control">
                                <option value="0">TAG</option>
                                {section name=tagvon start=1 loop=32 step=1}
                                    <option value="{$smarty.section.tagvon.index}"{if $cPostVar_arr.cTagVon == $smarty.section.tagvon.index} selected{/if}>{$smarty.section.tagvon.index}</option>
                                {/section}
                            </select>
                        </div>
                        <div class="input-group-btn input-group-wrap">
                            <select name="cMonatVon" class="form-control">
                                <option value="0">MONAT</option>
                                {section name=monatvon start=1 loop=13 step=1}
                                    <option value="{$smarty.section.monatvon.index}"{if $cPostVar_arr.cMonatVon == $smarty.section.monatvon.index} selected{/if}>{$smarty.section.monatvon.index}</option>
                                {/section}
                            </select>
                        </div>
                        <div class="input-group-btn input-group-wrap">
                            <select name="cJahrVon" class="form-control">
                                <option value="0">JAHR</option>
                                {section name=jahrvon start=2009 loop=2021 step=1}
                                    <option value="{$smarty.section.jahrvon.index}"{if $cPostVar_arr.cJahrVon == $smarty.section.jahrvon.index} selected{/if}>{$smarty.section.jahrvon.index}</option>
                                {/section}
                            </select>
                        </div>
                        <div class="input-group-addon">
                            <strong>- Bis:</strong>
                        </div>

                        <div class="input-group-btn input-group-wrap">
                            <select name="cTagBis" class="form-control">
                                <option value="0">TAG</option>
                                {section name=tagbis start=1 loop=32 step=1}
                                    <option value="{$smarty.section.tagbis.index}"{if $cPostVar_arr.cTagBis == $smarty.section.tagbis.index} selected{/if}>{$smarty.section.tagbis.index}</option>
                                {/section}
                            </select>
                        </div>
                        <div class="input-group-btn input-group-wrap">
                            <select name="cMonatBis" class="form-control">
                                <option value="0">MONAT</option>
                                {section name=monatbis start=1 loop=13 step=1}
                                    <option value="{$smarty.section.monatbis.index}"{if $cPostVar_arr.cMonatBis == $smarty.section.monatbis.index} selected{/if}>{$smarty.section.monatbis.index}</option>
                                {/section}
                            </select>
                        </div>
                        <div class="input-group-btn input-group-wrap">
                            <select name="cJahrBis" class="form-control">
                                <option value="0">JAHR</option>
                                {section name=jahrbis start=2009 loop=2021 step=1}
                                    <option value="{$smarty.section.jahrbis.index}"{if $cPostVar_arr.cJahrBis == $smarty.section.jahrbis.index} selected{/if}>{$smarty.section.jahrbis.index}</option>
                                {/section}
                            </select>
                        </div>
                        <div class="input-group-btn">
                            <button name="btnDatum" type="submit" value="Filtern" class="btn btn-primary">Filtern</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-9">
                    <div class="input-group">
                        <div class="input-group-btn">
                            <button name="btnZeit" type="submit" value="1" class="btn {if $btnZeit == 1}btn-primary{else}btn-default{/if}">
                                Heute
                            </button>
                            <button name="btnZeit" type="submit" value="2" class="btn {if $btnZeit == 2}btn-primary{else}btn-default{/if}">
                                diese Woche
                            </button>
                            <button name="btnZeit" type="submit" value="3" class="btn {if $btnZeit == 3}btn-primary{else}btn-default{/if}">
                                letzte Woche
                            </button>
                            <button name="btnZeit" type="submit" value="4" class="btn {if $btnZeit == 4}btn-primary{else}btn-default{/if}">
                                diesen Monat
                            </button>
                            <button name="btnZeit" type="submit" value="5" class="btn {if $btnZeit == 5}btn-primary{else}btn-default{/if}">
                                letzten Monat
                            </button>
                            <button name="btnZeit" type="submit" value="6" class="btn {if $btnZeit == 6}btn-primary{else}btn-default{/if}">
                                dieses Jahr
                            </button>
                            <button name="btnZeit" type="submit" value="7" class="btn {if $btnZeit == 7}btn-primary{else}btn-default{/if}">
                                letztes Jahr
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>