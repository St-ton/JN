{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div>OPC MOUNT POINT: {$id}</div>
{if $opc->isEditMode()}
    <div class="opc-area opc-rootarea" data-area-id="{$id}" data-toggle="tooltip" title="{$id}"></div>
{elseif $opcPageService->getCurPage()->getAreaList()->hasArea($id)}
    {$opcPageService->getCurPage()->getAreaList()->getArea($id)->getFinalHtml()}
{/if}
