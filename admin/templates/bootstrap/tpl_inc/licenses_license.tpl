{__($licData->getType())}
{if $licData->getSubscription() !== null}, {__('Valid until %s', $licData->getSubscription()->getValidUntil()->format('d.m.Y'))}
{elseif $licData->getValidUntil() !== null}, {__('Valid until %s', $licData->getValidUntil()->format('d.m.Y'))}
{/if}
