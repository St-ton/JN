{lang key='showAllBestsellers' assign='moreTitle'}
{lang key='bestsellers' assign='slidertitle'}
{include file='snippets/product_slider.tpl'
    id='boxslider-bestsellers'
    productlist=$oBox->getProducts()->elemente
    title=$slidertitle
    tplscope='box'
    moreLink=$oBox->getURL()
    moreTitle=$moreTitle}
