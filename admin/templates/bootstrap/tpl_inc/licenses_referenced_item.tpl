{$referencedItem = $license->getReferencedItem()}
<div id="license-item-{$license->getID()}">
    {if $referencedItem !== null}
        {$licData = $license->getLicense()}
        {$subscription = $licData->getSubscription()}
        {$disabled = $licData->isExpired() || $subscription->isExpired()}
        {if isset($licenseErrorMessage)}
            <div class="alert alert-danger">{__($licenseErrorMessage)}{if isset($resultCode) && $resultCode !== 1}{__('Error code: %d', $resultCode)}{/if}</div>
        {/if}
        {$installedVersion = $referencedItem->getInstalledVersion()}
        {if $installedVersion === null}
            <i class="far fa-circle"></i> <span class="item-available badge badge-info">
                {__('Version %s available', $license->getReleases()->getAvailable()->getVersion())}
            </span>
            <hr>
            <form method="post"{if !$disabled} class="install-item-form"{/if}>
                {$jtl_token}
                <input type="hidden" name="action" value="install">
                <input type="hidden" name="item-type" value="{$license->getType()}">
                <input type="hidden" name="item-id" value="{$license->getID()}">
                <button{if $disabled} disabled{/if} class="btn btn-default btn-sm install-item" name="action" value="install">
                    <i class="fa fa-share"></i> {__('Install')}
                </button>
            </form>
        {else}
            <i class="far fa-check-circle"></i> {$installedVersion}{if $referencedItem->isActive() === false} {__('(disabled)')}{/if}
        {/if}
        {if $referencedItem->hasUpdate()}
            <span class="update-available badge badge-success">{__('Update to version %s available', $referencedItem->getMaxInstallableVersion())}</span>
            <hr>
            <form method="post"{if !$disabled} class="update-item-form"{/if}>
                {$jtl_token}
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="item-type" value="{$license->getType()}">
                <input type="hidden" name="item-id" value="{$license->getID()}">
                <button{if $disabled} disabled{/if} class="btn btn-default btn-sm update-item" name="action" value="update">
                    <i class="fas fa-refresh"></i> {__('Update')}
                </button>
            </form>
        {/if}
    {else}
        <i class="far fa-circle"></i>
    {/if}
</div>