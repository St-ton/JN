{block name="header"}
    {include file='layout/header.tpl'}
{/block}

{block name="content"}
    {if $step === 'umfrage_uebersicht'}
        {include file='poll/overview.tpl'}
    {elseif $step === 'umfrage_durchfuehren'}
        {if (!empty($oLiveEditParams->oContent['editor_replace_all']) && empty($smarty.get.editpage))}
            {$oLiveEditParams->oContent['editor_replace_all']}
        {elseif (!empty($smarty.get.editpage) && !empty($smarty.get.action) && $smarty.get.action === 'replace')}
            <div id="editor_replace_all" class="jle-editable"></div>
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