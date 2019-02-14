{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{include file='snippets/extension.tpl'}

{if !empty($cNewsErr)}
    {alert variant="danger"}{lang key='newsRestricted' section='news'}{/alert}
{else}
    {include file='snippets/opc_mount_point.tpl' id='opc_news_article_prepend'}
    <article itemprop="mainEntity" itemscope itemtype="https://schema.org/BlogPosting">
        <meta itemprop="mainEntityOfPage" content="{$oNewsArchiv->getURL()}">
        <p>
            {if $oNewsArchiv->getPreviewImage() !== ''}
                {image src="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}"
                alt="{$oNewsArchiv->getTitle()|escape:'quotes'} - {$oNewsArchiv->getMetaTitle()|escape:'quotes'}"
                center=true fluid=true fluid-grow=true}
                <meta itemprop="image" content="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}">
            {/if}
            <div class="author-meta text-muted my-4">
                {if empty($oNewsArchiv->getDateValidFrom())}
                    {assign var=dDate value=$oNewsArchiv->getDateCreated()->format('Y-m-d H:i:s')}
                {else}
                    {assign var=dDate value=$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}
                {/if}
                {if $oNewsArchiv->getAuthor() !== null}
                    {include file='snippets/author.tpl' oAuthor=$oNewsArchiv->getAuthor() dDate=$dDate cDate=$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}
                {else}
                    <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="d-none">
                        <span itemprop="name">{$meta_publisher}</span>
                        <meta itemprop="logo" content="{$imageBaseURL}{$ShopLogoURL}" />
                    </div>
                    <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time><span class="creation-date">{$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}</span>
                {/if}
                <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time>
                {if isset($oNewsArchiv->getDateCreated()->format('Y-m-d H:i:s'))}<time itemprop="dateModified" class="d-none">{$oNewsArchiv->getDateCreated()->format('Y-m-d H:i:s')}</time>{/if}
            </div>

            <h1 itemprop="headline">
                {$oNewsArchiv->getTitle()}
            </h1>

            {include file='snippets/opc_mount_point.tpl' id='opc_news_content_prepend'}
            {row itemprop="articleBody" class="mb-4"}
                {col cols=12 class="blog-content"}
                    {$oNewsArchiv->getContent()}
                {/col}
            {/row}
            {include file='snippets/opc_mount_point.tpl' id='opc_news_content_append'}

            {if isset($Einstellungen.news.news_kategorie_unternewsanzeigen) && $Einstellungen.news.news_kategorie_unternewsanzeigen === 'Y' && !empty($oNewsKategorie_arr)}
                <div class="news-categorylist mb-4">
                    {foreach $oNewsKategorie_arr as $oNewsKategorie}
                        {link itemprop="articleSection" href="{$oNewsKategorie->cURLFull}" title="{$oNewsKategorie->cBeschreibung|strip_tags|escape:'html'|truncate:60}" class="badge"}{$oNewsKategorie->cName}{/link}
                    {/foreach}
                </div>
            {/if}
        </p>
        {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
            {if $oNewsKommentar_arr|@count > 0}
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
                    {foreach $oNewsKommentar_arr as $oNewsKommentar}
                        <blockquote class="blockquote news-comment mb-4">
                            <p itemprop="comment" class="mb-0">{$oNewsKommentar->getText()}</p>
                            <footer class="blockquote-footer pt-1">
                                {$oNewsKommentar->getName()}, {$oNewsKommentar->getDateCreated()->format('d.m.y H:i')}
                            </footer>
                        </blockquote>
                    {/foreach}
                </div>
                {include file='snippets/pagination.tpl' oPagination=$oPagiComments cThisUrl=$articleURL cParam_arr=$cParam_arr}
            {/if}

            {if ($Einstellungen.news.news_kommentare_eingeloggt === 'Y' && !empty($smarty.session.Kunde->kKunde)) || $Einstellungen.news.news_kommentare_eingeloggt !== 'Y'}
                <hr>
                {row}
                    {col cols=12}
                        {card}
                            <div class="h4">{lang key='newsCommentAdd' section='news'}</div>
                            {form method="post" action="{if !empty($oNewsArchiv->getSEO())}{$oNewsArchiv->getURL()}{else}{get_static_route id='news.php'}{/if}" class="form evo-validate" id="news-addcomment"}
                                {$jtl_token}
                                {input type="hidden" name="kNews" value="{$oNewsArchiv->getID()}"}
                                {input type="hidden" name="kommentar_einfuegen" value="1"}
                                {input type="hidden" name="n" value="{$oNewsArchiv->getID()}"}

                                {formgroup}
                                    {if $Einstellungen.news.news_kommentare_eingeloggt === 'N'}
                                        {if empty($smarty.session.Kunde->kKunde)}
                                            {row}
                                                {col cols=12 md=6}
                                                    {include file='snippets/form_group_simple.tpl'
                                                        options=[
                                                            'text', 'comment-name', 'cName',
                                                            {$cPostVar_arr.cName|default:null}, {lang key='newsName' section='news'},
                                                            true
                                                        ]
                                                    }
                                                {/col}
                                                {col cols=12 md=6}
                                                    {include file='snippets/form_group_simple.tpl'
                                                        options=[
                                                            'email', 'comment-email', 'cEmail',
                                                            {$cPostVar_arr.cEmail|default:null}, {lang key='newsEmail' section='news'},
                                                            true
                                                        ]
                                                    }
                                                {/col}
                                            {/row}
                                        {/if}
                                        {formgroup
                                            id="commentText"
                                            class="{if $nPlausiValue_arr.cKommentar > 0} has-error{/if}"
                                            label="{lang key='newsComment' section='news'}"
                                            label-form="comment-text"
                                            label-class="commentForm"
                                        }
                                            {textarea id="comment-text" required=true name="cKommentar"}{if !empty($cPostVar_arr.cKommentar)}{$cPostVar_arr.cKommentar}{/if}{/textarea}
                                            {if $nPlausiValue_arr.cKommentar > 0}
                                                <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                                                    {lang key='fillOut' section='global'}
                                                </div>
                                            {/if}
                                        {/formgroup}

                                        {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true)
                                            && isset($Einstellungen.news.news_sicherheitscode)
                                            && $Einstellungen.news.news_sicherheitscode !== 'N'
                                            && empty($smarty.session.Kunde->kKunde)
                                        }
                                            <div class="form-group{if !empty($nPlausiValue_arr.captcha)} has-error{/if}">
                                                {captchaMarkup getBody=true}
                                            </div>
                                        {/if}

                                        {input class="btn btn-primary" name="speichern" type="submit" value="{lang key='newsCommentSave' section='news'}"}
                                    {elseif $Einstellungen.news.news_kommentare_eingeloggt === 'Y' && !empty($smarty.session.Kunde->kKunde)}
                                        {formgroup
                                            id="commentText"
                                            class="{if $nPlausiValue_arr.cKommentar > 0} has-error{/if}"
                                            label="<strong>{lang key='newsComment' section='news'}</strong>"
                                            label-for="comment-text"
                                            label-class="commentForm"
                                        }
                                            {textarea id="comment-text" class="form-control" name="cKommentar" required=true}{/textarea}
                                            {if $nPlausiValue_arr.cKommentar > 0}
                                                <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                                                    {lang key='fillOut' section='global'}
                                                </div>
                                            {/if}
                                        {/formgroup}
                                        {button variant="primary" name="speichern" type="submit" class="float-right"}
                                            {lang key='newsCommentSave' section='news'}
                                        {/button}
                                    {/if}
                                {/formgroup}
                            {/form}
                        {/card}
                    {/col}
                {/row}
            {else}
                {alert variant="warning"}{lang key='newsLogin' section='news'}{/alert}
            {/if}
        {/if}
        {include file='snippets/opc_mount_point.tpl' id='opc_news_comments_append'}
    </article>
{/if}
