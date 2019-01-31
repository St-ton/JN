<script type="text/javascript">
    function changeStatType(elem) {ldelim}
        window.location.href = "statistik.php?s=" + elem.options[elem.selectedIndex].value;
    {rdelim}
</script>
{if $nTyp == $STATS_ADMIN_TYPE_BESUCHER}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('statisticBesucher')}
    {assign var=cURL value=__('statisticBesucherURL')}
{elseif $nTyp == $STATS_ADMIN_TYPE_KUNDENHERKUNFT}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('statisticKundenherkunft')}
    {assign var=cURL value=__('statisticKundenherkunftURL')}
{elseif $nTyp == $STATS_ADMIN_TYPE_SUCHMASCHINE}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('statisticSuchmaschine')}
    {assign var=cURL value=__('statisticSuchmaschineURL')}
{elseif $nTyp == $STATS_ADMIN_TYPE_UMSATZ}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('statisticUmsatz')}
    {assign var=cURL value=__('statisticUmsatzURL')}
{else $nTyp == $STATS_ADMIN_TYPE_EINSTIEGSSEITEN}
    {assign var=cTitel value=__('statisticTitle')|cat:': '|cat:__('statisticEinstiegsseite')}
    {assign var=cURL value=__('statisticEinstiegsseiteURL')}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('statisticDesc') cDokuURL=$cURL}
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
                    <option value="{$STATS_ADMIN_TYPE_EINSTIEGSSEITEN}"{if $nTyp == $STATS_ADMIN_TYPE_EINSTIEGSSEITEN} selected{/if}>Einstiegsseiten</option>
                </select>
            </span>
        </div>
    </div>

    <div class="ocontainer">
        {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter cParam_arr=['s' => $nTyp]}