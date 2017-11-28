{if empty($smarty.get.editpage)}
    {if !empty($oCMSPage->cFinalHtml_arr[$id])}
        <div id="{$id}">
            {$oCMSPage->cFinalHtml_arr[$id]}
        </div>
    {/if}
{else}
    <div id="{$id}" class="cle-area"></div>
{/if}

