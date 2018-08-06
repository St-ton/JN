<div itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting" class="panel panel-default panel-blog-post">
    <div class="panel-heading hide-overflow">
        <div class="panel-title">
            <a itemprop="url" href="{$oNewsUebersicht->getURL()}">
                <strong><span itemprop="headline">{$oNewsUebersicht->getTitle()}</span></strong>
            </a>
            <meta itemprop="mainEntityOfPage" content="{$ShopURL}/{$oNewsUebersicht->getURL()}">
            <div class="text-muted pull-right v-box">
                {assign var='dDate' value=$oNewsUebersicht->getDateValidFrom()->format('Y-m-d H:i:s')}
                {if (isset($oNewsUebersicht->oAuthor))}
                    <div class="hidden-xs v-box">{include file='snippets/author.tpl' oAuthor=$oNewsUebersicht->oAuthor}</div>
                {else}
                    <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="hidden">
                        <span itemprop="name">{$meta_publisher}</span>
                        <meta itemprop="url" content="{$ShopURL}">
                        <meta itemprop="logo" content="{$imageBaseURL}{$ShopLogoURL}">
                    </div>
                {/if}
                <time itemprop="dateModified" class="hidden">{$oNewsUebersicht->getDateCreated()->format('d.m.Y H:i')}</time>
                <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time><span class="v-box">{$oNewsUebersicht->getDateCreated()->format('d.m.Y H:i')}</span>
                {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                    |
                    <a class="v-box" href="{$oNewsUebersicht->getURL()}#comments" title="{lang key='readComments' section='news'}">
                        <span class="fa fa-comments"></span>
                        <span class="sr-only">
                            {if $oNewsUebersicht->getCommentCount() === 1}
                                {lang key='newsComment' section='news'}
                            {else}
                                {lang key='newsComments' section='news'}
                            {/if}
                        </span>
                        <em itemprop="commentCount">{$oNewsUebersicht->getCommentCount()}</em>
                    </a>
                {/if}
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class=" row">
            {if !empty($oNewsUebersicht->getPreviewImage())}
                <div class="col-sm-4 col-xs-12">
                    <a href="{$oNewsUebersicht->getURL()}" title="{$oNewsUebersicht->getTitle()|escape:'quotes'}">
                        <img src="{$imageBaseURL}{$oNewsUebersicht->getPreviewImage()}"
                             alt="{$oNewsUebersicht->getTitle()|escape:'quotes'} - {$oNewsUebersicht->getMetaTitle()|escape:'quotes'}"
                             class="img-responsive center-block"/>
                        <meta itemprop="image" content="{$imageBaseURL}{$oNewsUebersicht->getPreviewImage()}">
                    </a>
                </div>
            {/if}
            <div itemprop="description" class="{if !empty($oNewsUebersicht->getPreviewImage())}col-sm-8 {/if}col-xs-12">
                {if $oNewsUebersicht->getPreview()|strip_tags|strlen > 0}
                    {$oNewsUebersicht->getPreview()|strip_tags}
                {else}
                    {$oNewsUebersicht->getContent()|strip_tags|truncate:200:''}
                {/if}
                <span class="pull-right top17">
                    <a class="news-more-link" href="{$oNewsUebersicht->getURL()}">{lang key='moreLink' section='news'}</a>
                </span>
            </div>
        </div>
    </div>
</div>