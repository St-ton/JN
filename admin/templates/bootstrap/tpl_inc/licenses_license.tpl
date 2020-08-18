{$licData = $license->getLicense()}
{$subscription = $licData->getSubscription()}
{if $licData->isExpired()}
    <span class="badge badge-danger">{__('License expired on %s', $licData->getValidUntil()->format('d.m.Y'))}</span>
{elseif $subscription->isExpired() && $subscription->getValidUntil() !== null}
    <span class="badge badge-danger">
        {__('Subscription expired on %s', $subscription->getValidUntil()->format('d.m.Y'))}
    </span>
{elseif $subscription->isExpired() === false && $subscription->getValidUntil() !== null}
    {if $subscription->getDaysRemaining() < 28}
        <span class="badge badge-warning">
            {__('Warning: Subscription only valid until %s', $subscription->getValidUntil()->format('d.m.Y'))}
        </span>
    {else}
        <span class="badge badge-success">
            {__('Subscription valid until %s', $subscription->getValidUntil()->format('d.m.Y'))}
        </span>
    {/if}
{elseif $licData->getValidUntil() !== null}
    {if $licData->getDaysRemaining() < 28}
        <span class="badge badge-warning">
            {__('Warning: License only valid until %s', $licData->getValidUntil()->format('d.m.Y'))}
        </span>
    {else}
        <span class="badge badge-success">
            {__('License valid until %s', $licData->getValidUntil()->format('d.m.Y'))}
        </span>
    {/if}
{else}
    <span class="badge badge-success">{__('Valid')}</span>
{/if}
{foreach $license->getLinks() as $link}
    {if ($link->getRel() === 'extendSubscription' && $license->hasSubscription())
        || ($link->getRel() === 'extendLicense' && $license->hasLicense())}
        <p class="mb-0 mt-2">
            <a class="btn btn-primary btn-sm" href="{$link->getHref()}" rel="noopener" title="{__($link->getRel())}">
                <i class="fa fa-external-link"></i> {__($link->getRel())}
            </a>
        </p>
    {/if}
{/foreach}
