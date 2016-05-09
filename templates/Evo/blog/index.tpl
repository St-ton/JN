{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{include file='layout/header.tpl'}

{if $step === 'news_uebersicht'}
    {include file='blog/overview.tpl'}
{elseif $step === 'news_monatsuebersicht'}
    {include file='blog/overview.tpl'}
{elseif $step === 'news_kategorieuebersicht'}
    {include file='blog/overview.tpl'}
{elseif $step === 'news_detailansicht'}
    {include file='blog/details.tpl'}
{/if}

{include file='layout/footer.tpl'}