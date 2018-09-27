{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($hinweis)}
    <div class="alert alert-info">{$hinweis}</div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">{$fehler}</div>
{/if}
{include file='snippets/extension.tpl'}

{if !empty($cNewsErr)}
    <div class="alert alert-danger">{lang key='newsRestricted' section='news'}</div>
{else}
    {include file='snippets/opc_mount_point.tpl' id='opc_news_article_prepend'}
    <article itemprop="mainEntity" itemscope itemtype="https://schema.org/BlogPosting">
        <meta itemprop="mainEntityOfPage" content="{$oNewsArchiv->getURL()}">
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
                    <meta itemprop="logo" content="{$imageBaseURL}{$ShopLogoURL}" />
                </div>
                <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time><span class="creation-date">{$oNewsArchiv->getDateValidFrom()->format('Y-m-d H:i:s')}</span>
            {/if}
            <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time>
            {if isset($oNewsArchiv->dErstellt)}<time itemprop="dateModified" class="hidden">{$oNewsArchiv->dErstellt}</time>{/if}
        </div>

        {include file='snippets/opc_mount_point.tpl' id='opc_news_content_prepend'}
        <div itemprop="articleBody" class="row">
            <div class="col-xs-12">
                {$oNewsArchiv->getContent()}
            </div>
        </div>
        {include file='snippets/opc_mount_point.tpl' id='opc_news_content_append'}

        {if isset($Einstellungen.news.news_kategorie_unternewsanzeigen) && $Einstellungen.news.news_kategorie_unternewsanzeigen === 'Y' && !empty($oNewsKategorie_arr)}
            <div class="top10 news-categorylist">
                {foreach $oNewsKategorie_arr as $oNewsKategorie}
                    <a itemprop="articleSection" href="{$oNewsKategorie->cURLFull}" title="{$oNewsKategorie->cBeschreibung|strip_tags|escape:'html'|truncate:60}" class="badge">{$oNewsKategorie->cName}</a>
                {/foreach}
            </div>
        {/if}

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
                <div class="top10" id="comments">
                    <h3 class="section-heading">{lang key='newsComments' section='news'}<span itemprop="commentCount" class="hidden">{$oNewsKommentar_arr|count}</span></h3>
                    {foreach $oNewsKommentar_arr as $oNewsKommentar}
                        <blockquote class="news-comment">
                            <p itemprop="comment">
                                {$oNewsKommentar->getText()}
                            </p>
                            <small>
                                {*{if !empty($oNewsKommentar->cVorname)}*}
                                    {*{$oNewsKommentar->cVorname} {$oNewsKommentar->cNachname|truncate:1:''}.,*}
                                {*{else}*}
                                    {$oNewsKommentar->getName()},
                                {*{/if}*}
                                {*{if $smarty.session.cISOSprache === 'ger'}*}
                                    {*{$oNewsKommentar->dErstellt_de}*}
                                {*{else}*}
                                    {*{$oNewsKommentar->dErstellt}*}
                                {*{/if}*}
                                {$oNewsKommentar->getDateCreated()->format('d.m.y H:i')}
                            </small>
                        </blockquote>
                    {/foreach}
                </div>
                {include file='snippets/pagination.tpl' oPagination=$oPagiComments cThisUrl=$articleURL cParam_arr=$cParam_arr}
            {/if}

            {if ($Einstellungen.news.news_kommentare_eingeloggt === 'Y' && !empty($smarty.session.Kunde->kKunde)) || $Einstellungen.news.news_kommentare_eingeloggt !== 'Y'}
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
                                            {if $Einstellungen.news.news_kommentare_eingeloggt === 'N'}
                                                {if empty($smarty.session.Kunde->kKunde)}
                                                    <div class="row">
                                                        <div class="col-xs-12 col-md-6">
                                                            {include file='snippets/form_group_simple.tpl'
                                                                options=[
                                                                    'text', 'comment-name', 'cName',
                                                                    {$cPostVar_arr.cName|default:null}, {lang key='newsName' section='news'},
                                                                    true
                                                                ]
                                                            }
                                                        </div>
                                                        <div class="col-xs-12 col-md-6">
                                                            {include file='snippets/form_group_simple.tpl'
                                                                options=[
                                                                    'email', 'comment-email', 'cEmail',
                                                                    {$cPostVar_arr.cEmail|default:null}, {lang key='newsEmail' section='news'},
                                                                    true
                                                                ]
                                                            }
                                                        </div>
                                                    </div>
                                                {/if}

                                                <div id="commentText" class="form-group float-label-control{if $nPlausiValue_arr.cKommentar > 0} has-error{/if}">
                                                    <label class="control-label commentForm" for="comment-text">{lang key='newsComment' section='news'}</label>
                                                    <textarea id="comment-text" required class="form-control" name="cKommentar">{if !empty($cPostVar_arr.cKommentar)}{$cPostVar_arr.cKommentar}{/if}</textarea>
                                                    {if $nPlausiValue_arr.cKommentar > 0}
                                                        <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
                                                            {lang key='fillOut' section='global'}
                                                        </div>
                                                    {/if}
                                                </div>

                                                {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
                                                    isset($Einstellungen.news.news_sicherheitscode) && $Einstellungen.news.news_sicherheitscode !== 'N' && empty($smarty.session.Kunde->kKunde)}
                                                    <div class="form-group float-label-control{if !empty($nPlausiValue_arr.captcha)} has-error{/if}">
                                                        {captchaMarkup getBody=true}
                                                    </div>
                                                {/if}

                                                <input class="btn btn-primary" name="speichern" type="submit" value="{lang key='newsCommentSave' section='news'}" />
                                            {elseif $Einstellungen.news.news_kommentare_eingeloggt === 'Y' && !empty($smarty.session.Kunde->kKunde)}
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
                                            {/if}
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
        {include file='snippets/opc_mount_point.tpl' id='opc_news_comments_append'}
    </article>
{/if}
