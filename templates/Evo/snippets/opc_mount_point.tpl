{if empty($smarty.get.editpage)}
    {if !empty($opcPage->cFinalHtml_arr[$id])}
        <div id="{$id}">
            {$opcPage->cFinalHtml_arr[$id]}
        </div>
    {/if}
{else}
    <div id="{$id}" class="cle-area cle-rootarea"></div>
{/if}

