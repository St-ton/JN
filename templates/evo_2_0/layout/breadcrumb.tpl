{extends file="{$parent_template_path}/layout/breadcrumb.tpl"}

{block name='breadcrumb-first-item'}
    <li class="breadcrumb-item first" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
        <a itemprop="item" href="{$oItem->getURLFull()}" title="{$oItem->getName()|escape:'html'}">
            <span itemprop="name">{$oItem->getName()|escape:'html'}</span>
        </a>
        <meta itemprop="url" content="{$oItem->getURLFull()}" />
        <meta itemprop="position" content="{$oItem@iteration}" />
    </li>
{/block}