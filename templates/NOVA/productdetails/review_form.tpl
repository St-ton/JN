{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{include file='snippets/extension.tpl'}

{block name='content'}
    <h1>{lang key='productRating' section='product rating'}</h1>
    {block name='productdetails-review-form'}
    <div class="panel-wrap">
        {form action="{get_static_route id='bewertung.php'}#tab-votes" class="evo-validate"}
            {$alertList->displayAlertByKey('productNotBuyed')}
            {$alertList->displayAlertByKey('loginFirst')}
            {if $ratingAllowed}
                <div class="alert alert-info">{lang key='shareYourRatingGuidelines' section='product rating'}.</div>
                <div class="vmiddle">
                    {if !empty($Artikel->Bilder[0]->cPfadMini)}
                        {image alt=$Artikel->cName src=$Artikel->Bilder[0]->cURLMini class="image vmiddle"}
                    {/if}
                    <span class="vmiddle">{$Artikel->cName}</span>
                </div>
                <hr>
                {formgroup label-for="stars" label="{lang key='productRating' section='product rating'}"}
                    {select name="nSterne" id="stars" required=true}
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
                    {/select}
                {/formgroup}
                {formgroup label-for="headline" label="{lang key='headline' section='product rating'}"}
                    {input type="text" name="cTitel" value=$oBewertung->cTitel|default:'' id="headline" required=true}
                {/formgroup}
                {formgroup label-for="comment" label="{lang key='comment' section='product rating'}"}
                    {textarea name="cText" cols="80" rows="8" id="comment" required=true}{$oBewertung->cText|default:""}{/textarea}
                {/formgroup}
                {input type="hidden" name="bfh" value="1"}
                {input type="hidden" name="a" value=$Artikel->kArtikel}
                {button type="submit" value="1" variant="primary"}{lang key='submitRating' section='product rating'}{/button}
            {/if}
        {/form}
    </div>
    {/block}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
