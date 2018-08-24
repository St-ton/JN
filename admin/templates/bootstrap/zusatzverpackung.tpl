{config_load file="$lang.conf" section="zusatzverpackung"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=#zusatzverpackung# cBeschreibung=#zusatzverpackungDesc# cDokuURL=#zusatzverpackungURL#}
<div id="content" class="container-fluid">
    {if $action === 'edit'}
        {include file='tpl_inc/zusatzverpackung_bearbeiten.tpl'}
    {else}
        {include file='tpl_inc/zusatzverpackung_uebersicht.tpl'}
    {/if}
</div>

{include file='tpl_inc/footer.tpl'}