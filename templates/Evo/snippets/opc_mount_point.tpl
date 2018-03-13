{if $opc->isEditMode()}
    <div class="opc-area opc-rootarea" data-area-id="{$id}"></div>
{elseif $opcPage->hasArea($id)}
    {$opcPage->getArea($id)->getFinalHtml()}
{/if}

