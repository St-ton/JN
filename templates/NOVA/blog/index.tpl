{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='blog-index'}
    {block name='blog-index-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='blog-index-content'}
        {if $opcPageService->getCurPage()->isReplace()}
            {include file='snippets/opc_mount_point.tpl' id='opc_replace_all'}
        {else}
            {if JTL\Shop::$AktuelleSeite === 'NEWSDETAIL'}
                {block name='blog-index-include-details'}
                    {include file='blog/details.tpl'}
                {/block}
            {else}
                {block name='blog-index-overview'}
                    {include file='blog/overview.tpl'}
                {/block}
            {/if}
        {/if}
    {/block}

    {block name='blog-index-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
