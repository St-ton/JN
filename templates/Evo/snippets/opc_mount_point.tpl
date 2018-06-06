{if $opc->isEditMode()}
    <div class="opc-area opc-rootarea" data-area-id="{$id}"></div>
{elseif $opc->getCurPage()->getAreaList()->hasArea($id)}
    {$opc->getCurPage()->getAreaList()->getArea($id)->getFinalHtml()}
{/if}