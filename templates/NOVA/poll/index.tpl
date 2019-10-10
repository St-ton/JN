{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='poll-index'}
    {block name='poll-index-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='poll-index-content'}
        {if $step === 'umfrage_uebersicht'}
            {block name='poll-index-include-overview'}
                {include file='poll/overview.tpl'}
            {/block}
        {elseif $step === 'umfrage_durchfuehren'}
            {block name='poll-index-include-progress'}
                {include file='poll/progress.tpl'}
            {/block}
        {elseif $step === 'umfrage_ergebnis'}
            {block name='poll-index-include-result'}
                {include file='poll/result.tpl'}
            {/block}
        {/if}
    {/block}

    {block name='poll-index-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
