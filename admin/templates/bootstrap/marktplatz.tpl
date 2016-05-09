{config_load file="$lang.conf" section='marktplatz'}
{include file='tpl_inc/header.tpl'}
{if !empty($error)}
    <div class="alert alert-danger">{$error}</div>
{else}
    {if $action === 'overview'}
        {include file='tpl_inc/marktplatz_uebersicht.tpl'}
    {elseif $action === 'detail'}
        {include file='tpl_inc/marktplatz_details.tpl'}
    {/if}
{/if}
{include file='tpl_inc/footer.tpl'}