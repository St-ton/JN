{$referencedItem = $license->getReferencedItem()}
<div id="license-item-{$license->getID()}">
    {if $referencedItem !== null}
        {if isset($licenseErrorMessage)}
            <div class="alert alert-danger">{__($licenseErrorMessage)}</div>
        {/if}
        {$installedVersion = $referencedItem->getInstalledVersion()}
        {if $installedVersion === null}
            <i class="far fa-circle"></i>
        {else}
            <i class="far fa-check-circle"></i> {$installedVersion}{if $referencedItem->isActive() === false} {__('(disabled)')}{/if}
        {/if}
        {if $referencedItem->hasUpdate()}
            <span class="update-available badge badge-success">{__('Update to version %s available', $referencedItem->getMaxInstallableVersion())}</span>
            <hr>
            <form method="post" class="update-item-form">
                {$jtl_token}
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="item-type" value="{$license->getType()}">
                <input type="hidden" name="item-id" value="{$license->getID()}">
                <button class="update-item btn btn-primary" name="action" value="update"><i class="fas fa-refresh"></i> {__('Update')}</button>
            </form>
        {/if}
    {else}
        <i class="far fa-circle"></i>
    {/if}
</div>
