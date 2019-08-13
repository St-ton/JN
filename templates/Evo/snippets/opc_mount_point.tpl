{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{$title = $title|default:$id}

{if $opc->isEditMode()}
    <div class="opc-area opc-rootarea" data-area-id="{$id}" title="{$title}"></div>
{elseif $opcPageService->getCurPage()->getAreaList()->hasArea($id)}
    {$opcPageService->getCurPage()->getAreaList()->getArea($id)->getFinalHtml()}
{/if}
