{if $opc->isEditMode()}
    <div class="opc-area opc-rootarea" data-area-id="{$id}"></div>
{elseif $opcPageService->getCurPage()->getAreaList()->hasArea($id)}
    {$opcPageService->getCurPage()->getAreaList()->getArea($id)->getFinalHtml()}
{/if}