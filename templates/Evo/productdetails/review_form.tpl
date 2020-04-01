{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{include file='snippets/extension.tpl'}

{block name='content'}
    <h1>{lang key='productRating' section='product rating'}</h1>
    {block name='productdetails-review-form'}
    <div class="panel-wrap">
        <form method="post" action="{get_static_route id='bewertung.php'}#tab-votes" class="evo-validate">
            {$jtl_token}
            {$alertList->displayAlertByKey('productNotBuyed')}
            {$alertList->displayAlertByKey('loginFirst')}
            {if $ratingAllowed}
                <div class="alert alert-info">{lang key='shareYourRatingGuidelines' section='product rating'}.</div>
                <div class="vmiddle">
                    {if !empty($Artikel->Bilder[0]->cPfadMini)}
                        <img src="{$Artikel->Bilder[0]->cURLMini}" class="image vmiddle" />
                    {/if}
                    <span class="vmiddle">{$Artikel->cName}</span>
                </div>
                <hr>
                <div class="form-group">
                    <select name="nSterne" id="stars" class="form-control" required>
                        <option value="" disabled>{lang key='starPlural' section='product rating'}</option>
                        {$ratings = [5,4,3,2,1]}
                        {foreach $ratings as $rating}
                            <option value="{$rating}"{if isset($oBewertung->nSterne) && (int)$oBewertung->nSterne === $rating} selected{/if}>
                                {$rating}
                                {if (int)$rating === 1}
                                    {lang key='starSingular' section='product rating'}
                                {else}
                                    {lang key='starPlural' section='product rating'}
                                {/if}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="form-group float-label-control">
                    <label for="headline">{lang key='headline' section='product rating'}</label>
                    <input type="text" name="cTitel"
                           value="{$oBewertung->cTitel|default:""}"
                           id="headline" class="form-control" required>
                </div>
                <div class="form-group float-label-control">
                    <label for="comment">{lang key='comment' section='product rating'}</label>
                    <textarea name="cText" cols="80" rows="8" id="comment" class="form-control"
                              required>{$oBewertung->cText|default:""}</textarea>
                </div>
                <input name="bfh" type="hidden" value="1">
                <input name="a" type="hidden" value="{$Artikel->kArtikel}">
                <input name="submit" type="submit" value="{lang key='submitRating' section='product rating'}" class="submit btn btn-primary">
            {/if}
        </form>
    </div>
    {/block}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
