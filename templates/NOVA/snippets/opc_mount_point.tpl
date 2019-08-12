{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='opc-mountpoint'}
    {if $opc->isEditMode()}
        {block name='opc-mountpoint-edit'}
        <div class="opc-area opc-rootarea" data-area-id="{$id}"></div>
        {/block}
    {elseif $opcPageService->getCurPage()->getAreaList()->hasArea($id)}
        {block name='opc-mountpoint-live'}
        {$opcPageService->getCurPage()->getAreaList()->getArea($id)->getFinalHtml()}
        {/block}
    {/if}
{/block}
