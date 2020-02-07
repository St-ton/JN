{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='blog-preview'}
    <div itemprop="blogPost" itemscope=true itemtype="https://schema.org/BlogPosting" class="newsbox h-100 border-bottom border-sm-bottom-0 pb-5 position-relative">
        <meta itemprop="mainEntityOfPage" content="{$ShopURL}/{$newsItem->getURL()}">
        {block name='blog-preview-news-header'}
            <div class="newsbox-header">
                {if !empty($newsItem->getPreviewImage())}
                    {block name='blog-preview-news-image'}
                        <div class="newsbox-image">
                            {image webp=true lazy=true fluid-grow=true
                                src=$newsItem->getImage(\JTL\Media\Image::SIZE_MD)
                                srcset="{$newsItem->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_news_mini_breite}w,
                                    {$newsItem->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_news_klein_breite}w,
                                    {$newsItem->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_news_normal_breite}w,
                                    {$newsItem->getImage(\JTL\Media\Image::SIZE_LG)} {$Einstellungen.bilder.bilder_news_gross_breite}w"
                                sizes="auto"
                                alt="{$newsItem->getTitle()|escape:'quotes'} - {$newsItem->getMetaTitle()|escape:'quotes'}"
                            }
                        </div>
                        <meta itemprop="image" content="{$imageBaseURL}{$newsItem->getPreviewImage()}">
                    {/block}
                {/if}
            </div>
        {/block}
        {block name='blog-preview-news-body'}
            <div class="newsbox-body">
                {row}
                    {col cols=6}
                        {assign var=dDate value=$newsItem->getDateValidFrom()->format('Y-m-d')}
                        {block name='blog-preview-author'}
                            {if $newsItem->getAuthor() !== null}
                                <div class="d-none d-sm-inline-block align-middle">
                                    {block name='blog-preview-include-author'}
                                        {include file='snippets/author.tpl' oAuthor=$newsItem->getAuthor()}
                                    {/block}
                                </div>
                            {else}
                                <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="d-none">
                                    <span itemprop="name">{$meta_publisher}</span>
                                    <meta itemprop="url" content="{$ShopURL}">
                                    <meta itemprop="logo" content="{$ShopLogoURL}">
                                </div>
                            {/if}
                            <time itemprop="dateModified" class="d-none">{$newsItem->getDateCreated()->format('Y-m-d')}</time>
                            <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time>
                            <span class="align-middle">{$newsItem->getDateValidFrom()->format('d.m.Y')}</span>
                        {/block}
                    {/col}
                    {col cols="auto" class="ml-auto"}
                        {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                            {block name='blog-preview-comments'}
                                {link class="align-middle no-deco" href="{$newsItem->getURL()}#comments" title="{lang key='readComments' section='news'}"}
                                    <span class="fas fa-comments"></span>
                                    <span class="sr-only">
                                            {if $newsItem->getCommentCount() === 1}
                                                {lang key='newsComment' section='news'}
                                            {else}
                                                {lang key='newsComments' section='news'}
                                            {/if}
                                        </span>
                                    <span itemprop="commentCount">{$newsItem->getCommentCount()}</span>
                                {/link}
                            {/block}
                        {/if}
                    {/col}
                {/row}
                {block name='blog-preview-heading'}
                    {link itemprop="url" href=$newsItem->getURL() title=$newsItem->getTitle()|escape:'quotes' class="newsbox-headline stretched-link"}
                        <span itemprop="headline">{$newsItem->getTitle()}</span>
                    {/link}
                {/block}
                {block name='blog-preview-description'}
                    <p itemprop="description">
                        {if $newsItem->getPreview()|strip_tags|strlen > 0}
                            {$newsItem->getPreview()|strip_tags}
                        {else}
                            {$newsItem->getContent()|strip_tags|truncate:200:''}
                        {/if}
                    </p>
                {/block}
            </div>
        {/block}
        {block name='blog-preview-news-footer'}
            <div class="newsbox-footer">
                {lang key='moreLink' section='news'}
                <i class="fas fa-long-arrow-alt-right ml-2"></i>
            </div>
        {/block}
    </div>
{/block}
