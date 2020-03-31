{include file='snippets/extension.tpl'}

{if !empty($cNewsErr)}
    <div class="alert alert-danger">{lang key='newsRestricted' section='news'}</div>
{else}
    <article itemprop="mainEntity" itemscope itemtype="https://schema.org/BlogPosting">
        <meta itemprop="mainEntityOfPage" content="{$oNewsArchiv->getURL()}">
        {opcMountPoint id='opc_before_heading'}
        <h1 itemprop="headline">
            {$oNewsArchiv->getTitle()}
        </h1>
        {if $oNewsArchiv->getPreviewImage() !== ''}
            <meta itemprop="image" content="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}">
        {/if}
        <div class="author-meta text-muted bottom10">
            {if empty($oNewsArchiv->getDateValidFrom())}
                {assign var=dDate value=$oNewsArchiv->getDateCreated()->format('Y-m-d H:i:s')}
            {else}
                {assign var=dDate value=$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}

                {/if}
            {if $oNewsArchiv->getAuthor() !== null}
                {include file='snippets/author.tpl' oAuthor=$oNewsArchiv->getAuthor() dDate=$dDate cDate=$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}
            {else}
                <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="hidden">
                    <span itemprop="name">{$meta_publisher}</span>
                    <meta itemprop="logo" content="{$ShopLogoURL}" />
                </div>
                <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time><span class="creation-date">{$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}</span>
            {/if}
            <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time>
            {if isset($oNewsArchiv->dErstellt)}<time itemprop="dateModified" class="hidden">{$oNewsArchiv->dErstellt}</time>{/if}
        </div>

        {opcMountPoint id='opc_before_content'}
        <div itemprop="articleBody" class="row">
            <div class="col-xs-12">
                {$oNewsArchiv->getContent()}
            </div>
        </div>
        {opcMountPoint id='opc_after_content'}

        {if isset($Einstellungen.news.news_kategorie_unternewsanzeigen) && $Einstellungen.news.news_kategorie_unternewsanzeigen === 'Y' && !empty($oNewsKategorie_arr)}
            <div class="top10 news-categorylist">
                {foreach $oNewsKategorie_arr as $oNewsKategorie}
                    <a itemprop="articleSection" href="{$oNewsKategorie->cURLFull}" title="{$oNewsKategorie->cBeschreibung|strip_tags|escape:'html'|truncate:60}" class="badge">{$oNewsKategorie->cName}</a>
                {/foreach}
            </div>
        {/if}

        {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
            {if $comments|@count > 0}
                {if $oNewsArchiv->getURL() !== ''}
                    {assign var=articleURL value=$oNewsArchiv->getURL()}
                    {assign var=cParam_arr value=[]}
                {else}
                    {assign var=articleURL value='news.php'}
                    {assign var=cParam_arr value=['kNews'=>$oNewsArchiv->getID(),'n'=>$oNewsArchiv->getID()]}
                {/if}
                <hr>
                <div class="top10" id="comments">
                    <h3 class="section-heading">{lang key='newsComments' section='news'}<span itemprop="commentCount" class="hidden">{$comments|count}</span></h3>
                    {foreach $comments as $comment}
                        <blockquote class="news-comment">
                            <p itemprop="comment">
                                {$comment->getText()}
                            </p>
                            <small>{$comment->getName()}, {$comment->getDateCreated()->format('d.m.y H:i')}</small>
                        </blockquote>
                    {/foreach}
                </div>
                {include file='snippets/pagination.tpl' oPagination=$oPagiComments cThisUrl=$articleURL cParam_arr=$cParam_arr}
            {/if}

            {if $userCanComment === true}
                <hr>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="panel-wrap">
                            <div class="panel panel-default">
                                <div class="panel-heading"><h4 class="panel-title">{lang key='newsCommentAdd' section='news'}</h4></div>
                                <div class="panel-body">
                                    <form method="post" action="{if !empty($oNewsArchiv->getSEO())}{$oNewsArchiv->getURL()}{else}{get_static_route id='news.php'}{/if}" class="form evo-validate" id="news-addcomment">
                                        {$jtl_token}
                                        <input type="hidden" name="kNews" value="{$oNewsArchiv->getID()}" />
                                        <input type="hidden" name="kommentar_einfuegen" value="1" />
                                        <input type="hidden" name="n" value="{$oNewsArchiv->getID()}" />

                                        <fieldset>
                                            <div class="form-group float-label-control{if $nPlausiValue_arr.cKommentar > 0} has-error{/if}">
                                                <label class="control-label" for="comment-text"><strong>{lang key='newsComment' section='news'}</strong></label>
                                                <textarea id="comment-text" class="form-control" name="cKommentar" required></textarea>
                                                {if $nPlausiValue_arr.cKommentar > 0}
                                                    <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
                                                        {lang key='fillOut' section='global'}
                                                    </div>
                                                {/if}
                                            </div>
                                            <input class="btn btn-primary" name="speichern" type="submit" value="{lang key='newsCommentSave' section='news'}" />
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {else}
                <hr>
                <div class="alert alert-danger">{lang key='newsLogin' section='news'}</div>
            {/if}
        {/if}
    </article>
{/if}
