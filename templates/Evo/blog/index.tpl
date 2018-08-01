{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {if $step === 'news_uebersicht'}
        {include file='blog/overview.tpl'}
    {elseif $step === 'news_monatsuebersicht'}
        {include file='blog/overview.tpl'}
    {elseif $step === 'news_kategorieuebersicht'}
        {include file='blog/overview.tpl'}
    {elseif $step === 'news_detailansicht'}
        {if $opcPageService->getCurPage()->isReplace()}
            {include file='snippets/opc_mount_point.tpl' id='opc_replace_all'}
        {else}
            {include file='blog/details.tpl'}
        {/if}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}