{$draftStatus = $page->getStatus(0)}
{if $draftStatus === 0}
    {if $page->getPublishTo() === null}
        <span class="opc-public">öffentlich seit</span>
        {$page->getPublishFrom()|date_format:'%d.%m.%Y - %H:%M'}
    {else}
        <span class="opc-public">öffentlich bis</span>
        {$page->getPublishTo()|date_format:'%d.%m.%Y - %H:%M'}
    {/if}
{elseif $draftStatus === 1}
    <span class="opc-planned">geplant ab</span>
    {$page->getPublishFrom()|date_format:'%d.%m.%Y - %H:%M'}
{elseif $draftStatus === 2}
    <span class="opc-status-draft">keine Veröffentlichung geplant</span>
{elseif $draftStatus === 3}
    <span class="opc-backdate">abgelaufen am</span>
    {$page->getPublishTo()|date_format:'%d.%m.%Y - %H:%M'}
{/if}