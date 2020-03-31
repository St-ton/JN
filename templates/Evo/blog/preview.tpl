<div itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting" class="panel panel-default panel-blog-post">
    <div class="panel-heading hide-overflow">
        <div class="panel-title">
            <a itemprop="url" href="{$newsItem->getURL()}">
                <strong><span itemprop="headline">{$newsItem->getTitle()}</span></strong>
            </a>
            <meta itemprop="mainEntityOfPage" content="{$ShopURL}/{$newsItem->getURL()}">
            <div class="text-muted pull-right v-box">
                {assign var='dDate' value=$newsItem->getDateValidFrom()->format('Y-m-d H:i:s')}
                {if $newsItem->getAuthor() !== null}
                    <div class="hidden-xs v-box">{include file='snippets/author.tpl' oAuthor=$newsItem->getAuthor()}</div>
                {else}
                    <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="hidden">
                        <span itemprop="name">{$meta_publisher}</span>
                        <meta itemprop="url" content="{$ShopURL}">
                        <meta itemprop="logo" content="{$ShopLogoURL}">
                    </div>
                {/if}
                <time itemprop="dateModified" class="hidden">{$newsItem->getDateCreated()->format('d.m.Y H:i')}</time>
                <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time><span class="v-box">{$newsItem->getDateCreated()->format('d.m.Y H:i')}</span>
                {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                    |
                    <a class="v-box" href="{$newsItem->getURL()}#comments" title="{lang key='readComments' section='news'}">
                        <span class="fa fa-comments"></span>
                        <span class="sr-only">
                            {if $newsItem->getCommentCount() === 1}
                                {lang key='newsComment' section='news'}
                            {else}
                                {lang key='newsComments' section='news'}
                            {/if}
                        </span>
                        <em itemprop="commentCount">{$newsItem->getCommentCount()}</em>
                    </a>
                {/if}
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class=" row">
            {if !empty($newsItem->getPreviewImage())}
                <div class="col-sm-4 col-xs-12">
                    <a href="{$newsItem->getURL()}" title="{$newsItem->getTitle()|escape:'quotes'}">
                        <img src="{$imageBaseURL}{$newsItem->getPreviewImage()}"
                             alt="{$newsItem->getTitle()|escape:'quotes'} - {$newsItem->getMetaTitle()|escape:'quotes'}"
                             class="img-responsive center-block"/>
                        <meta itemprop="image" content="{$imageBaseURL}{$newsItem->getPreviewImage()}">
                    </a>
                </div>
            {/if}
            <div itemprop="description" class="{if !empty($newsItem->getPreviewImage())}col-sm-8 {/if}col-xs-12">
                {if $newsItem->getPreview()|strip_tags|strlen > 0}
                    {$newsItem->getPreview()|strip_tags}
                {else}
                    {$newsItem->getContent()|strip_tags|truncate:200:''}
                {/if}
                <span class="pull-right top17">
                    <a class="news-more-link" href="{$newsItem->getURL()}">{lang key='moreLink' section='news'}</a>
                </span>
            </div>
        </div>
    </div>
</div>
