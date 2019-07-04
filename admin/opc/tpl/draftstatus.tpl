{$draftStatus = $page->getStatus(0)}
{if $draftStatus === 0}
    {if $page->getPublishTo() === null}
        <span class="opc-public">{__('publicSince')}</span>
        {$page->getPublishFrom()|date_format:'%d.%m.%Y - %H:%M'}
    {else}
        <span class="opc-public">{__('publicUntill')}</span>
        {$page->getPublishTo()|date_format:'%d.%m.%Y - %H:%M'}
    {/if}
{elseif $draftStatus === 1}
    <span class="opc-planned">{__('plannedFrom')}</span>
    {$page->getPublishFrom()|date_format:'%d.%m.%Y - %H:%M'}
{elseif $draftStatus === 2}
    <span class="opc-status-draft">{__('noPublicationPlanned')}</span>
{elseif $draftStatus === 3}
    <span class="opc-backdate">{__('expiredOn')}</span>
    {$page->getPublishTo()|date_format:'%d.%m.%Y - %H:%M'}
{/if}