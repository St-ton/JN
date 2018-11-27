{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='store'}

{include file='tpl_inc/seite_header.tpl' cTitel=#store# cBeschreibung=#storeDesc# cDokuURL=#storeUrl#}

<div id="content" class="container-fluid">
{if isset($error)}
    <div class="alert alert-danger">{$error}</div>
{/if}
    <form action="store.php" method="POST">
        {$jtl_token}
        {if $hasAuth}
            <button name="action" value="revoke" class="btn btn-danger">{#storeRevoke#}</button>
        {else}
            <button name="action" value="redirect" class="btn btn-primary">{#storeLink#}</button>
        {/if}
    </form>
</div>

{include file='tpl_inc/footer.tpl'}