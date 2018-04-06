{if $opc->isEditMode()}
    <div class="opc-area opc-rootarea" data-area-id="{$id}"></div>
{elseif $opcPage->getAreaList()->hasArea($id)}
    {$opcPage->getAreaList()->getArea($id)->getFinalHtml()}
{/if}

