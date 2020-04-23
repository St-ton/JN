{if $referencedItem !== null}
    {$installedVersion = $referencedItem->getInstalledVersion()}
    {if $installedVersion === null}
		<i class="far fa-circle"></i>
    {else}
		<i class="far fa-check-circle"></i> {$installedVersion}
    {/if}
    {if $referencedItem->hasUpdate()}
		<span class="update-available badge badge-success">{__('Update to version %s available', $referencedItem->getMaxInstallableVersion())}</span>
		<hr>
		<form method="post" class="update-item-form">
            {$jtl_token}
			<input type="hidden" name="item-type" value="{$license->getType()}">
			<input type="hidden" name="item-id" value="{$license->getID()}">
			<button class="update-item btn btn-primary" name="action" value="update"><i class="fas fa-refresh"></i> {__('Update')}</button>
		</form>
    {/if}
{else}
	<i class="far fa-circle"></i>
{/if}
