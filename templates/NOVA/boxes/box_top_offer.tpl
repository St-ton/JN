{block name='boxes-box-top-offer'}
    {lang key='topOffer' assign='slidertitle'}
    {assign var=moreLink value=$oBox->getURL()}
    {lang key='showAllTopOffers' assign='moreTitle'}
    {block name='boxes-box-top-offer-include-product-slider'}
        {include file='snippets/product_slider.tpl'
            id='boxslider-topoffer'
            productlist=$oBox->getProducts()->elemente
            title=$slidertitle
            tplscope='box'
            moreLink=$moreLink
            moreTitle=$moreTitle
        }
    {/block}
{/block}
