{config_load file="$lang.conf" section='zusatzverpackung'}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('zusatzverpackung') cBeschreibung=__('zusatzverpackungDesc') cDokuURL=__('zusatzverpackungURL')}
<div id="content">
    {if $action === 'edit'}
        {include file='tpl_inc/zusatzverpackung_bearbeiten.tpl'}
    {else}
        {include file='tpl_inc/zusatzverpackung_uebersicht.tpl'}
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
