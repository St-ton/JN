{if (!empty($oSeoEditParams->oContent[$id]->cContent) && empty($smarty.get.editpage))}
    <div id="{$id}" class="le-editable">
        {$oSeoEditParams->oContent[$id]->cContent}
    </div>
{elseif !empty($smarty.get.editpage)}
    <div id="{$id}" class="le-editable"></div>
{/if}
