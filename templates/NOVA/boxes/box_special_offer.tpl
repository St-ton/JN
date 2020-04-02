{block name='boxes-box-special-offer'}
    {assign var=moreLink value=$oBox->getURL()}
    {lang key='showAllSpecialOffers' assign='moreTitle'}
    {assign var=specialOfferArticles value=$oBox->getProducts()->elemente}
    {if $specialOfferArticles|@count > 1}
        {lang key='specialOffers' assign='slidertitle'}
    {else}
        {lang key='specialOffer' assign='slidertitle'}
    {/if}
    {block name='boxes-box-special-offer-include-product-slider'}
        {include file='snippets/product_slider.tpl'
            id='boxslider-special-offer'
            productlist=$specialOfferArticles
            title=$slidertitle
            tplscope='box'
            moreLink=$moreLink
            moreTitle=$moreTitle
        }
    {/block}
{/block}
