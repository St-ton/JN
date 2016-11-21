{include file='tpl_inc/header.tpl'}

{config_load file="$lang.conf" section="auswahlassistent"}

{if $action === 'bearbeiten'}
{else}
    {include file='tpl_inc/auswahlassistent_uebersicht.tpl'}
{/if}

{include file='tpl_inc/footer.tpl'}