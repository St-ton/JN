{if !empty($oLiveEditParams->oContent[$id]) && empty($smarty.get.editpage)}
    <div id="{$id}" class="jle-editable">
        {$oLiveEditParams->oContent[$id]}
    </div>
{elseif !empty($smarty.get.editpage)}
    <div id="{$id}" class="jle-editable"></div>
{/if}
