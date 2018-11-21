{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='store'}

{include file='tpl_inc/seite_header.tpl' cTitel=#store# cBeschreibung=#storeDesc# cDokuURL=#storeUrl#}

<div id="content" class="container-fluid">
{if isset($error)}
    <div class="alert alert-danger">{$error}</div>
{/if}
    {if $hasAuth}
        <a href="store.php?revoke" class="btn btn-danger">{#storeRevoke#}</a>
    {else}
        <a href="store.php?redirect" target="_blank" class="btn btn-primary">{#storeLink#}</a>
    {/if}
</div>

{include file='tpl_inc/footer.tpl'}