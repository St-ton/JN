{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {if $step === 'umfrage_uebersicht'}
        {include file='poll/overview.tpl'}
    {elseif $step === 'umfrage_durchfuehren'}
        {if $opcPageService->getCurPage()->isReplace()}
            {include file='snippets/opc_mount_point.tpl' id='opc_replace_all'}
        {else}
            {include file='poll/progress.tpl'}
        {/if}
    {elseif $step === 'umfrage_ergebnis'}
        {include file='poll/result.tpl'}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}