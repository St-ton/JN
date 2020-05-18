{$subscription = $licData->getSubscription()}
{if $licData->isExpired()}<span class="badge badge-danger">{__('License expired on %s', $licData->getValidUntil()->format('d.m.Y'))}</span>
{elseif $subscription->isExpired() && $subscription->getValidUntil() !== null}<span class="badge badge-danger">{__('Subscription expired on %s', $subscription->getValidUntil()->format('d.m.Y'))}</span>
{elseif $subscription->isExpired() === false && $subscription->getValidUntil() !== null}{__('Valid until %s', $subscription->getValidUntil()->format('d.m.Y'))}
{elseif $licData->getValidUntil() !== null}{__('Valid until %s', $licData->getValidUntil()->format('d.m.Y'))}
{else}&dash;
{/if}
