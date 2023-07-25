{$wrapper=$wrapper|default:true}
{if $wrapper}
<div class="col-auto ml-sm-n4 order-2 order-sm-3 d-flex align-items-center">
{/if}
    {if !empty($cnf->getDescription())}
        {getHelpDesc cDesc=$cnf->getDescription() cID=$cnf->getID()}
    {/if}
    {include file='snippets/einstellungen_log_icon.tpl' cnf=$cnf}
    {include file='snippets/einstellungen_reset_icon.tpl' cnf=$cnf}
{if $wrapper}
</div>
{/if}
