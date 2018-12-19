{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {if $opcPageService->getCurPage()->isReplace()}
        {include file='snippets/opc_mount_point.tpl' id='opc_replace_all'}
    {else}
        {if Shop::$AktuelleSeite === 'NEWSDETAIL'}
            {include file='blog/details.tpl'}
        {else}
            {include file='blog/overview.tpl'}
        {/if}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
