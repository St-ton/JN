{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='coupons'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/kupons_uebersicht.tpl'}
{elseif $step === 'bearbeiten'}
    {include file='tpl_inc/kupons_bearbeiten.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}