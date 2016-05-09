{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='exportformat queue'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/exportformat_queue_uebersicht.tpl'}
{elseif $step === 'erstellen'}
    {include file='tpl_inc/exportformat_queue_erstellen.tpl'}
{elseif $step === 'fertiggestellt'}
    {include file='tpl_inc/exportformat_queue_fertiggestellt.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}