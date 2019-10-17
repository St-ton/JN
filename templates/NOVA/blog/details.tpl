{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='blog-details'}
    {block name='blog-details-include-extension'}
        {include file='snippets/extension.tpl'}
    {/block}

    {container}
    {if !empty($cNewsErr)}
        {block name='blog-details-alert'}
            {alert variant="danger"}{lang key='newsRestricted' section='news'}{/alert}
        {/block}
    {else}
        {block name='blog-details-article'}
            <article itemprop="mainEntity" itemscope itemtype="https://schema.org/BlogPosting">
                <meta itemprop="mainEntityOfPage" content="{$newsItem->getURL()}">
                <p>
                    {block name='blog-details-heading'}
                        {opcMountPoint id='opc_before_heading'}
                        <h1 itemprop="headline" class="text-center">
                            {$newsItem->getTitle()}
                        </h1>
                    {/block}

                    {block name='blog-details-author'}
                        <div class="author-meta text-muted text-center font-size-sm">
                            {if empty($newsItem->getDateValidFrom())}
                                {assign var=dDate value=$newsItem->getDateCreated()->format('Y-m-d H:i:s')}
                            {else}
                                {assign var=dDate value=$newsItem->getDateValidFrom()->format('Y-m-d H:i:s')}
                            {/if}
                            {if $newsItem->getAuthor() !== null}
                                {block name='blog-details-include-author'}
                                    {include file='snippets/author.tpl' oAuthor=$newsItem->getAuthor() dDate=$dDate cDate=$newsItem->getDateValidFrom()->format('Y-m-d H:i:s')}
                                {/block} /
                            {else}
                                <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="d-none">
                                    <span itemprop="name">{$meta_publisher}</span>
                                    <meta itemprop="logo" content="{$ShopLogoURL}" />
                                </div>
                                <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time><span class="creation-date">{$newsItem->getDateValidFrom()->format('Y-m-d H:i:s')}</span>
                            {/if}
                            <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time>
                            {if isset($newsItem->getDateCreated()->format('Y-m-d H:i:s'))}<time itemprop="dateModified" class="d-none">{$newsItem->getDateCreated()->format('Y-m-d H:i:s')}</time>{/if}

                            {if isset($Einstellungen.news.news_kategorie_unternewsanzeigen) && $Einstellungen.news.news_kategorie_unternewsanzeigen === 'Y' && !empty($oNewsKategorie_arr)}
                                {block name='blog-details-sub-news'}
                                    <span class="news-categorylist mb-4">
                                        /
                                        {foreach $oNewsKategorie_arr as $oNewsKategorie}
                                            {link itemprop="articleSection"
                                                href="{$oNewsKategorie->cURLFull}"
                                                title="{$oNewsKategorie->cBeschreibung|strip_tags|escape:'html'|truncate:60}"
                                                class="mr-2"
                                            }
                                                {$oNewsKategorie->cName}
                                            {/link}
                                        {/foreach}
                                    </span>
                                {/block}
                            {/if}

                            {link class="no-deco" href="#comments" title="{lang key='readComments' section='news'}"}
                                /
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
                        </div>
                    {/block}

                    {if $newsItem->getPreviewImage() !== ''}
                        {block name='blog-details-image'}
                            {image webp=true lazy=true fluid-grow=true
                                src=$newsItem->getImage(\JTL\Media\Image::SIZE_MD)
                                srcset="{$newsItem->getImage(\JTL\Media\Image::SIZE_XS)} 300w,
                                    {$newsItem->getImage(\JTL\Media\Image::SIZE_SM)} 600w,
                                    {$newsItem->getImage(\JTL\Media\Image::SIZE_MD)} 1200w,
                                    {$newsItem->getImage(\JTL\Media\Image::SIZE_LG)} 1800w"
                                sizes="auto"
                                alt="{$newsItem->getTitle()|escape:'quotes'} - {$newsItem->getMetaTitle()|escape:'quotes'}"
                                center=true
                                class="mb-5"
                            }
                            <meta itemprop="image" content="{$imageBaseURL}{$newsItem->getPreviewImage()}">
                        {/block}
                    {/if}

                    {block name='blog-details-article-content'}
                        {opcMountPoint id='opc_before_content'}
                        {row itemprop="articleBody" class="mb-4"}
                            {col cols=12 class="blog-content"}
                                {$newsItem->getContent()}
                            {/col}
                        {/row}
                        {opcMountPoint id='opc_after_content'}
                    {/block}
                </p>
                {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                    {block name='blog-details-article-comments'}
                        {if $userCanComment === true}
                            {block name='blog-details-form-comment'}
                                <hr class="my-6">
                                {row}
                                    {col cols=12}
                                        <div class="h2">{lang key='newsCommentAdd' section='news'}</div>
                                        {form method="post" action="{if !empty($newsItem->getSEO())}{$newsItem->getURL()}{else}{get_static_route id='news.php'}{/if}" class="form evo-validate label-slide" id="news-addcomment"}
                                            {input type="hidden" name="kNews" value=$newsItem->getID()}
                                            {input type="hidden" name="kommentar_einfuegen" value="1"}
                                            {input type="hidden" name="n" value=$newsItem->getID()}

                                            {block name='blog-details-form-comment-logged-in'}
                                                {formgroup
                                                    id="commentText"
                                                    class="{if $nPlausiValue_arr.cKommentar > 0} has-error{/if}"
                                                    label="<strong>{lang key='newsComment' section='news'}</strong>"
                                                    label-for="comment-text"
                                                    label-class="commentForm"
                                                }
                                                    {if $Einstellungen.news.news_kommentare_freischalten === 'Y'}
                                                        <small class="form-text text-muted">{lang key='commentWillBeValidated' section='news'}</small>
                                                    {/if}
                                                    {textarea id="comment-text" name="cKommentar" required=true}{/textarea}
                                                    {if $nPlausiValue_arr.cKommentar > 0}
                                                        <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                                                            {lang key='fillOut' section='global'}
                                                        </div>
                                                    {/if}
                                                {/formgroup}
                                                {row}
                                                    {col md=4 xl=3 class='ml-auto'}
                                                        {button block=true variant="primary" name="speichern" type="submit" class="float-right"}
                                                            {lang key='newsCommentSave' section='news'}
                                                        {/button}
                                                    {/col}
                                                {/row}
                                            {/block}
                                        {/form}
                                    {/col}
                                {/row}
                            {/block}
                        {else}
                            {block name='blog-details-alert-login'}
                                {alert variant="warning"}{lang key='newsLogin' section='news'}{/alert}
                            {/block}
                        {/if}
                        {if $comments|@count > 0}
                            {block name='blog-details-comments-content'}
                                {if $newsItem->getURL() !== ''}
                                    {assign var=articleURL value=$newsItem->getURL()}
                                    {assign var=cParam_arr value=[]}
                                {else}
                                    {assign var=articleURL value='news.php'}
                                    {assign var=cParam_arr value=['kNews'=>$newsItem->getID(),'n'=>$newsItem->getID()]}
                                {/if}
                                <hr class="my-6">
                                <div id="comments">
                                    {row class="align-items-center mb-3"}
                                        {col cols="auto"}
                                            <div class="h2 section-heading">{lang key='newsComments' section='news'}
                                                <span itemprop="commentCount">
                                                    ({$comments|count})
                                                </span>
                                            </div>
                                        {/col}
                                        {col cols="6" class="ml-auto ml-auto"}
                                            {block name='blog-details-include-pagination'}
                                                {include file='snippets/pagination.tpl' oPagination=$oPagiComments cThisUrl=$articleURL cParam_arr=$cParam_arr}
                                            {/block}
                                        {/col}
                                    {/row}

                                    {block name='blog-details-comments'}
                                        {listgroup class="list-group-flush p-3 bg-info"}
                                            {foreach $comments as $comment}
                                                {listgroupitem class="bg-info m-0 border-top-0" itemprop="comment"}
                                                    <p>
                                                        {$comment->getName()}, {$comment->getDateCreated()->format('d.m.y H:i')}
                                                    </p>
                                                    {$comment->getText()}
                                                {/listgroupitem}
                                            {/foreach}
                                        {/listgroup}
                                    {/block}
                                </div>
                            {/block}
                        {/if}
                    {/block}
                {/if}
                <hr class="my-6">
                {block name='blog-details-latest-news'}
                    <div class="h2">{lang key='news' section='news'}</div>
                    {row itemprop="about" itemscope=true itemtype="http://schema.org/Blog" class="news-slider mx-0"}
                    {foreach $oNews_arr as $newsItem}
                        {col}
                        {block name='page-index-include-preview'}
                            {include file='blog/preview.tpl'}
                        {/block}
                        {/col}
                    {/foreach}
                    {/row}
                {/block}
            </article>
        {/block}
    {/if}
    {/container}
{/block}
