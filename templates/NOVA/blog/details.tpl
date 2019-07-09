{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='blog-details'}
    {block name='blog-details-include-extension'}
        {include file='snippets/extension.tpl'}
    {/block}

    {if !empty($cNewsErr)}
        {block name='blog-details-alert'}
            {container}
                {alert variant="danger"}{lang key='newsRestricted' section='news'}{/alert}
            {/container}
        {/block}
    {else}
        {block name='blog-details-article'}
            <article itemprop="mainEntity" itemscope itemtype="https://schema.org/BlogPosting">
                {container}
                    <meta itemprop="mainEntityOfPage" content="{$oNewsArchiv->getURL()}">
                    <p>
                        {if $oNewsArchiv->getPreviewImage() !== ''}
                            {block name='blog-details-image'}
                                {image src="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}"
                                alt="{$oNewsArchiv->getTitle()|escape:'quotes'} - {$oNewsArchiv->getMetaTitle()|escape:'quotes'}"
                                center=true fluid=true fluid-grow=true}
                                <meta itemprop="image" content="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}">
                            {/block}
                        {/if}
                        {block name='blog-details-author'}
                            <div class="author-meta text-muted my-4">
                                {if empty($oNewsArchiv->getDateValidFrom())}
                                    {assign var=dDate value=$oNewsArchiv->getDateCreated()->format('Y-m-d H:i:s')}
                                {else}
                                    {assign var=dDate value=$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}
                                {/if}
                                {if $oNewsArchiv->getAuthor() !== null}
                                    {block name='blog-details-include-author'}
                                        {include file='snippets/author.tpl' oAuthor=$oNewsArchiv->getAuthor() dDate=$dDate cDate=$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}
                                    {/block}
                                {else}
                                    <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="d-none">
                                        <span itemprop="name">{$meta_publisher}</span>
                                        <meta itemprop="logo" content="{$ShopLogoURL}" />
                                    </div>
                                    <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time><span class="creation-date">{$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}</span>
                                {/if}
                                <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time>
                                {if isset($oNewsArchiv->getDateCreated()->format('Y-m-d H:i:s'))}<time itemprop="dateModified" class="d-none">{$oNewsArchiv->getDateCreated()->format('Y-m-d H:i:s')}</time>{/if}
                            </div>
                        {/block}

                        {block name='blog-details-heading'}
                            {include file='snippets/opc_mount_point.tpl' id='opc_before_heading'}
                            <h1 itemprop="headline">
                                {$oNewsArchiv->getTitle()}
                            </h1>
                        {/block}

                        {block name='blog-details-article-content'}
                            {include file='snippets/opc_mount_point.tpl' id='opc_before_content'}
                            {row itemprop="articleBody" class="mb-4"}
                                {col cols=12 class="blog-content"}
                                    {$oNewsArchiv->getContent()}
                                {/col}
                            {/row}
                            {include file='snippets/opc_mount_point.tpl' id='opc_after_content'}
                        {/block}

                        {if isset($Einstellungen.news.news_kategorie_unternewsanzeigen) && $Einstellungen.news.news_kategorie_unternewsanzeigen === 'Y' && !empty($oNewsKategorie_arr)}
                            {block name='blog-details-sub-news'}
                                <div class="news-categorylist mb-4">
                                    {foreach $oNewsKategorie_arr as $oNewsKategorie}
                                        {link itemprop="articleSection" href="{$oNewsKategorie->cURLFull}" title="{$oNewsKategorie->cBeschreibung|strip_tags|escape:'html'|truncate:60}" class="badge"}{$oNewsKategorie->cName}{/link}
                                    {/foreach}
                                </div>
                            {/block}
                        {/if}
                    </p>
                    {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                        {block name='blog-details-article-comments'}
                            {if $oNewsKommentar_arr|@count > 0}
                                {block name='blog-details-comments-content'}
                                    {if $oNewsArchiv->getURL() !== ''}
                                        {assign var=articleURL value=$oNewsArchiv->getURL()}
                                        {assign var=cParam_arr value=[]}
                                    {else}
                                        {assign var=articleURL value='news.php'}
                                        {assign var=cParam_arr value=['kNews'=>$oNewsArchiv->getID(),'n'=>$oNewsArchiv->getID()]}
                                    {/if}
                                    <hr>
                                    <div id="comments">
                                        <div class="h3 section-heading">{lang key='newsComments' section='news'}<span itemprop="commentCount" class="d-none">{$oNewsKommentar_arr|count}</span></div>
                                        {block name='blog-details-comments'}
                                            {foreach $oNewsKommentar_arr as $oNewsKommentar}
                                                    <blockquote class="blockquote news-comment mb-4">
                                                        <p itemprop="comment" class="mb-0">{$oNewsKommentar->getText()}</p>
                                                        <footer class="blockquote-footer pt-1">
                                                            {$oNewsKommentar->getName()}, {$oNewsKommentar->getDateCreated()->format('d.m.y H:i')}
                                                        </footer>
                                                    </blockquote>
                                            {/foreach}
                                        {/block}
                                    </div>
                                    {block name='blog-details-include-pagination'}
                                        {include file='snippets/pagination.tpl' oPagination=$oPagiComments cThisUrl=$articleURL cParam_arr=$cParam_arr}
                                    {/block}
                                {/block}
                            {/if}
                            {if $userCanComment === true}
                                {block name='blog-details-form-comment'}
                                    <hr>
                                    {row}
                                        {col cols=12}
                                            {card}
                                                <div class="h4">{lang key='newsCommentAdd' section='news'}</div>
                                                {form method="post" action="{if !empty($oNewsArchiv->getSEO())}{$oNewsArchiv->getURL()}{else}{get_static_route id='news.php'}{/if}" class="form evo-validate" id="news-addcomment"}
                                                    {input type="hidden" name="kNews" value=$oNewsArchiv->getID()}
                                                    {input type="hidden" name="kommentar_einfuegen" value="1"}
                                                    {input type="hidden" name="n" value=$oNewsArchiv->getID()}

                                                    {formgroup}
                                                        {block name='blog-details-form-comment-logged-in'}
                                                            {formgroup
                                                                id="commentText"
                                                                class="{if $nPlausiValue_arr.cKommentar > 0} has-error{/if}"
                                                                label="<strong>{lang key='newsComment' section='news'}</strong>"
                                                                label-for="comment-text"
                                                                label-class="commentForm"
                                                            }
                                                                {textarea id="comment-text" name="cKommentar" required=true}{/textarea}
                                                                {if $nPlausiValue_arr.cKommentar > 0}
                                                                    <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                                                                        {lang key='fillOut' section='global'}
                                                                    </div>
                                                                {/if}
                                                            {/formgroup}
                                                            {button variant="primary" name="speichern" type="submit" class="float-right"}
                                                                {lang key='newsCommentSave' section='news'}
                                                            {/button}
                                                        {/block}
                                                    {/formgroup}
                                                {/form}
                                            {/card}
                                        {/col}
                                    {/row}
                                {/block}
                            {else}
                                {block name='blog-details-alert-login'}
                                    {alert variant="warning"}{lang key='newsLogin' section='news'}{/alert}
                                {/block}
                            {/if}
                        {/block}
                    {/if}
                {/container}
            </article>
        {/block}
    {/if}
{/block}
