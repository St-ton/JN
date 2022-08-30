{$draftStatus = $page->getStatus(0)}
{if $draftStatus === 0}
    {if $page->getPublishTo() === null}
        <span class="opc-public">{__('activeSince')}</span>
        {$page->getPublishFrom()|date_format:'d.m.Y - H:i'}
    {else}
        <span class="opc-public">{__('activeUntil')}</span>
        {$page->getPublishTo()|date_format:'d.m.Y - H:i'}
    {/if}
{elseif $draftStatus === 1}
    <span class="opc-planned">{__('scheduledFor')}</span>
    {$page->getPublishFrom()|date_format:'d.m.Y - H:i'}
{elseif $draftStatus === 2}
    <span class="opc-status-draft">{__('notScheduled')}</span>
{elseif $draftStatus === 3}
    <span class="opc-backdate">{__('expiredOn')}</span>
    {$page->getPublishTo()|date_format:'d.m.Y - H:i'}
{/if}