{block name="header"}
    {include file='layout/header.tpl'}
{/block}

{block name="content"}
    {if $step === 'umfrage_uebersicht'}
        {include file='poll/overview.tpl'}
    {elseif $step === 'umfrage_durchfuehren'}
        {if (!empty($oCMSPage->cFinalHtml_arr['editor_replace_all']) && empty($smarty.get.editpage))}
            {$oCMSPage->cFinalHtml_arr['editor_replace_all']}
        {elseif (!empty($smarty.get.editpage) && !empty($smarty.get.cAction) && $smarty.get.cAction === 'replace')}
            {include file='snippets/opc_mount_point.tpl' id='editor_replace_all'}
        {else}
            {include file='poll/progress.tpl'}
        {/if}
    {elseif $step === 'umfrage_ergebnis'}
        {include file='poll/result.tpl'}
    {/if}
{/block}

{block name="footer"}
    {include file='layout/footer.tpl'}
{/block}