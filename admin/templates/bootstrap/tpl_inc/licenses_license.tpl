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
    {if $link->getRel() === 'extendLicense' && ($license->hasSubscription() || $license->hasLicense())}
        <br>
        {form class='set-binding-form mt-2' style='display:inline-block'}
            <input type="hidden" name="action" value="extendLicense">
            <input type="hidden" name="url" value="{$link->getHref()}">
            <input type="hidden" name="method" value="{$link->getMethod()|default:'POST'}">
            <input type="hidden" name="exsid" value="{$license->getExsID()}">
            <input type="hidden" name="key" value="{$license->getLicense()->getKey()}">
            <button type="submit" class="btn btn-sm btn-primary extend-license"
                    data-link="{$link->getHref()}"
                    href="#"
                    title="{if $license->hasSubscription()}{__('extendSubscription')}{else}{__('extendLicense')}{/if}">
                <i class="fa fa-link"></i> {if $license->hasSubscription()}{__('extendSubscription')}{else}{__('extendLicense')}{/if}
            </button>
        {/form}
{*        <a class="btn btn-primary btn-sm" href="{$link->getHref()}" rel="noopener" title="{__($link->getRel())}">*}
{*            <i class="fa fa-external-link"></i> *}
{*        </a>*}
    {/if}
{/foreach}
