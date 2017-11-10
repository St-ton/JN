{if !empty($oCMSPage->cFinalHtml_arr[$id]) && empty($smarty.get.editpage)}
    <div id="{$id}" class="jle-editable">
        {$oCMSPage->cFinalHtml_arr[$id]}
    </div>
{elseif !empty($smarty.get.editpage)}
    <div id="{$id}" class="jle-editable"></div>
{/if}
