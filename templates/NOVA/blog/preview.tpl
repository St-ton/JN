{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div itemprop="blogPost" itemscope=true itemtype="https://schema.org/BlogPosting" class="mb-5">
    <meta itemprop="mainEntityOfPage" content="{$ShopURL}/{$oNewsUebersicht->getURL()}">
    {row}
        {col cols=12 class="mb-3"}
            {if !empty($oNewsUebersicht->getPreviewImage())}
                {link href="{$oNewsUebersicht->getURL()}" title="{$oNewsUebersicht->getTitle()|escape:'quotes'}"}
                    <div class="nws-preview">
                        {image src="{$imageBaseURL}{$oNewsUebersicht->getPreviewImage()}"
                            alt="{$oNewsUebersicht->getTitle()|escape:'quotes'} - {$oNewsUebersicht->getMetaTitle()|escape:'quotes'}"
                        }
                    </div>
                    <meta itemprop="image" content="{$imageBaseURL}{$oNewsUebersicht->getPreviewImage()}">
                {/link}
            {/if}
        {/col}
        {col cols=6}
        {assign var='dDate' value=$oNewsUebersicht->getDateValidFrom()->format('Y-m-d')}
        {if $oNewsUebersicht->getAuthor() !== null}
            <div class="d-none d-sm-inline-block align-middle">{include file='snippets/author.tpl' oAuthor=$oNewsUebersicht->getAuthor()}</div>
        {else}
            <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="d-none">
                <span itemprop="name">{$meta_publisher}</span>
                <meta itemprop="url" content="{$ShopURL}">
                <meta itemprop="logo" content="{$imageBaseURL}{$ShopLogoURL}">
            </div>
        {/if}
            <time itemprop="dateModified" class="d-none">{$oNewsUebersicht->getDateCreated()->format('Y-m-d')}</time>
            <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time><span class="align-middle">{$oNewsUebersicht->getDateCreated()->format('d. M Y')}</span>
        {/col}
        {col cols=6 class="text-right"}
            {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                {link class="align-middle no-deco" href="{$oNewsUebersicht->getURL()}#comments" title="{lang key='readComments' section='news'}"}
                    <span class="fas fa-comments"></span>
                    <span class="sr-only">
                            {if $oNewsUebersicht->getCommentCount() === 1}
                                {lang key='newsComment' section='news'}
                            {else}
                                {lang key='newsComments' section='news'}
                            {/if}
                        </span>
                    <span itemprop="commentCount">{$oNewsUebersicht->getCommentCount()}</span>
                {/link}
            {/if}
        {/col}
        {col cols=12 class="mb-4"}

            {link itemprop="url" href="{$oNewsUebersicht->getURL()}" class="d-flex my-3 no-deco"}
                <strong><span itemprop="headline">{$oNewsUebersicht->getTitle()}</span></strong>
            {/link}

            <div itemprop="description">
                {if $oNewsUebersicht->getPreview()|strip_tags|strlen > 0}
                    {$oNewsUebersicht->getPreview()|strip_tags}
                {else}
                    {$oNewsUebersicht->getContent()|strip_tags|truncate:200:''}
                {/if}
                <div class="mt-2">
                    {link class="news-more-link" href="{$oNewsUebersicht->getURL()}"}{lang key='moreLink' section='news'} <i class="fas fa-long-arrow-alt-right"></i>{/link}
                </div>
            </div>
        {/col}
    {/row}
</div>
