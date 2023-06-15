{$referencedItem = $license->getReferencedItem()}
<div id="license-item-{$license->getID()}-{$license->getLicense()->getType()}">
    {if $license->isInApp()}
        {$avail = $license->getReleases()->getAvailable()}
        {if $avail !== null}
            <span class="item-available badge badge-info">
                {__('Version %s available', $avail->getVersion())}
            </span>
        {/if}
        <p class="mb-0 mt-2">{sprintf(__('Managed by %s'), $license->getParent()->getName())}</p>
    {elseif $referencedItem !== null}
        {$licData = $license->getLicense()}
        {$subscription = $licData->getSubscription()}
        {$disabled = $licData->isExpired() || ($subscription->isExpired() && !$subscription->canBeUsed()) || (!$referencedItem->isFilesMissing() && !$referencedItem->canBeUpdated())}
        {$avail = $license->getReleases()->getAvailable()}
        {if isset($licenseErrorMessage)}
            <div class="alert alert-danger">
                {__($licenseErrorMessage)}
            </div>
        {/if}
        {$installedVersion = $referencedItem->getInstalledVersion()}
        {if $installedVersion === null || $referencedItem->isFilesMissing()}
            {if $avail === null}
                {$disabled = true}
                <i class="far fa-circle"></i> <span class="badge badge-danger">{__('No version available')}</span>
            {else}
                {if $avail->getPhpVersionOK() === \JTL\License\Struct\Release::PHP_VERSION_LOW}
                    {$disabled = true}
                    <span class="badge badge-danger">{__('PHP version too low')}</span><br>
                {elseif $avail->getPhpVersionOK() === \JTL\License\Struct\Release::PHP_VERSION_HIGH}
                    {$disabled = true}
                    <span class="badge badge-danger">{__('PHP version too high')}</span><br>
                {/if}
                <i class="far fa-circle"></i> <span class="item-available badge badge-info">
                    {__('Version %s available', $avail->getVersion())}
                </span>
            {/if}
            {form method="post" class="mt-2{if !$disabled} install-item-form{/if}"}
                {if $referencedItem->isFilesMissing()}
                    <input type="hidden" name="exs-id" value="{$license->getExsID()}">
                    <input type="hidden" name="action" value="update">
                {else}
                    <input type="hidden" name="action" value="install">
                {/if}
                <input type="hidden" name="item-type" value="{$license->getType()}">
                <input type="hidden" name="item-id" value="{$license->getID()}">
                <input type="hidden" name="license-type" value="{$license->getLicense()->getType()}">
                <div class="btn-group">
                    <button{if $disabled} disabled{/if} class="btn btn-default btn-sm install-item" name="action" value="install">
                        <i class="fa fa-share"></i> {__('Install')}
                    </button>
                    {foreach $license->getLinks() as $link}
                        {if $link->getRel() === 'itemDetails'}
                            <a class="btn btn-default btn-sm" target="_blank" rel="noopener" href="{$link->getHref()}#tab-changelog">
                                <i class="fas fa-bullhorn"></i> {__('Changelog')}
                            </a>
                            {break}
                        {/if}
                    {/foreach}
                </div>
            {/form}
        {else}
            <i class="far fa-check-circle"></i> {$installedVersion}{if $referencedItem->isActive() === false} {__('(disabled)')}{/if}
        {/if}
        {if $referencedItem->hasUpdate()}
            <span class="update-available badge badge-success">
                {__('Update to version %s available', $referencedItem->getMaxInstallableVersion())}
            </span>
            {if $referencedItem->canBeUpdated() === false}
                {if $avail !== null && $avail->getPhpVersionOK() === \JTL\License\Struct\Release::PHP_VERSION_LOW}
                    <span class="badge badge-danger">{__('PHP version too low')}</span>
                {elseif $avail !== null && $avail->getPhpVersionOK() === \JTL\License\Struct\Release::PHP_VERSION_HIGH}
                    <span class="badge badge-danger">{__('PHP version too high')}</span>
                {/if}
                {if ($licData->isExpired() || $subscription->isExpired()) && !$referencedItem->isReleaseAvailable()}
                    <span class="badge badge-danger">{__('License expired')}</span>
                {elseif !$referencedItem->isShopVersionOK()}
                    <span class="badge badge-danger">{__('Shop version not compatible')}</span>
                {/if}
            {/if}
            {form method="post" class="mt-2{if !$disabled} update-item-form{/if}"}
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="item-type" value="{$license->getType()}">
                <input type="hidden" name="license-type" value="{$license->getLicense()->getType()}">
                <input type="hidden" name="item-id" value="{$license->getID()}">
                <input type="hidden" name="exs-id" value="{$license->getExsID()}">
                <div class="btn-group">
                    <button{if $disabled} disabled{/if} class="btn btn-default btn-sm update-item" name="action" value="update">
                        <i class="fas fa-refresh"></i> {__('Update')}
                    </button>
                    {foreach $license->getLinks() as $link}
                        {if $link->getRel() === 'itemDetails'}
                            <a class="btn btn-default btn-sm" target="_blank" rel="noopener" href="{$link->getHref()}#tab-changelog">
                                <i class="fas fa-bullhorn"></i> {__('Changelog')}
                            </a>
                            {break}
                        {/if}
                    {/foreach}
                </div>
            {/form}
        {/if}
    {else}
        <i class="far fa-circle"></i>
    {/if}
</div>
