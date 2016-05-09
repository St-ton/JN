{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='coupons'}
{if $step === 'uebersicht'}
    {include file='tpl_inc/kupons_uebersicht.tpl'}
{elseif $step === 'neuer Kupon'}
    {include file='tpl_inc/kupons_neuer_kupon.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}