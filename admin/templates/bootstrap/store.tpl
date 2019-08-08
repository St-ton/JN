{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('store') cBeschreibung=__('storeDesc') cDokuURL=__('storeUrl')}

<div id="content">
{if isset($error)}
    <div class="alert alert-danger">{$error}</div>
{/if}
    <form action="store.php" method="POST">
        {$jtl_token}
        {if $hasAuth}
            <button name="action" value="revoke" class="btn btn-danger">{__('storeRevoke')}</button>
        {else}
            <button name="action" value="redirect" class="btn btn-primary">{__('storeLink')}</button>
        {/if}
    </form>
</div>

{include file='tpl_inc/footer.tpl'}
