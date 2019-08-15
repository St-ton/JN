{$style = $instance->getProperty('listStyle')}

{if $isPreview}
    <div {$instance->getDataAttributeString()}>
        {image alt='ProductStream' src=$portlet->getTemplateUrl()|cat:'preview.'|cat:$style|cat:'.png'
            style='width: 100%; filter: grayscale(50%) opacity(60%)'}
    </div>
{elseif $style === 'list' || $style === 'gallery'}
    {$productlist = $portlet->getFilteredProducts($instance)}
    {if $style === 'list'}
        {$grid = '12'}
    {else}
        {$grid   = '6'}
        {$gridmd = '4'}
        {$gridxl = '3'}
    {/if}
    {row class=$style|cat:' product-list opc-ProductStream opc-ProductStream-'|cat:$style itemprop="mainEntity"
            itemscope=true itemtype="http://schema.org/ItemList"}
        {foreach $productlist as $Artikel}
            {col cols={$grid} md="{if isset($gridmd)}{$gridmd}{/if}" xl="{if isset($gridxl)}{$gridxl}{/if}"
                 class="product-wrapper {if !($style === 'list' && $Artikel@last)}mb-4{/if}"
                 itemprop="itemListElement" itemscope=true itemtype="http://schema.org/Product"}
                {if $style === 'list'}
                    {include file='productlist/item_list.tpl' tplscope=$style}
                {elseif $style === 'gallery'}
                    {include file='productlist/item_box.tpl' tplscope=$style}
                {/if}
            {/col}
        {/foreach}
    {/row}
{elseif $style === 'slider'}
    {$productlist = $portlet->getFilteredProducts($instance)}
    <div id="{$instance->getUid()}" class="opc-ProductStream opc-ProductStream-{$style} evo-slider">
        {foreach $productlist as $Artikel}
            <a href="{$Artikel->cURLFull}">
                <img src="{$Artikel->Bilder[0]->cURLNormal}" alt="Artikel">
            </a>
        {/foreach}
    </div>
{/if}
<script>
    addEventListener('FOO', () => {
        $('#{$instance->getUid()}').slick({
            infinite: true,
            slidesToShow: 5,
            slidesToScroll: 5,
            responsive: [
                {
                    breakpoint: 576, // xs
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    }
                },
                {
                    breakpoint: 768, // sm
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                    }
                },
                {
                    breakpoint: 992, // md
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                }
            ]
        });
    })
</script>

{*
{if $isPreview}
    <div {$instance->getAttributeString()} {$instance->getDataAttributeString()}>
        {image alt='' src=$portlet->getTemplateUrl()|cat:'preview.'|cat:$style|cat:'.png'
            style='width: 98%; filter: grayscale(50%) opacity(60%)'}
        <div style="color:#5cbcf6;font-size:40px;font-weight:bold;margin-top:-1em;line-height:1em;text-align:center;">
            Produktliste
        </div>
    </div>
{else}
    {$productlist = $portlet->getFilteredProducts($instance)}

    {if $style === 'slider'}
        {include file="./final.slider.tpl"}
    {elseif $style === 'vertSlider'}
        {$title = $instance->getProperty('sliderTitle')}

        {card no-body=true bg-variant='default' class='panel-slider box box-slider' id=$id|default:null
                style=$instance->getStyleString()}
            {if !empty($title)}
                {cardheader}
                    <h5 class="panel-title card-title">{$title}</h5>
                {/cardheader}
            {/if}
            {cardbody}
                <div class="evo-box-vertical" style="text-align:center">
                    {foreach $productlist as $product}
                        <div class="product-wrapper {if isset($style)}{$style}{/if}"
                             {if isset($Link) && $Link->getLinkType() == $smarty.const.LINKTYP_STARTSEITE}
                                 itemprop="about"
                             {else}
                                 itemprop="isRelatedTo"
                             {/if}
                             itemscope itemtype="http://schema.org/Product">
                            {include file='productlist/item_slider.tpl' Artikel=$product tplscope='box' class=''}
                        </div>
                    {/foreach}
                </div>
            {/cardbody}
        {/card}
        <script>
            function startSlider() {
                $('.evo-box-vertical:not(.slick-initialized)').slick({
                    //dots: true,
                    arrows: true,
                    lazyLoad: 'ondemand',
                    slidesToShow: {$instance->getProperty('productCount')},
                });
            }
            $(window).on('load', startSlider);
            $(startSlider);
        </script>
    {else}
        {if $style === 'gallery'}
            {$gridLG = 4}
            {$gridXS = 6}
        {else}
            {$gridXS = 12}
        {/if}

        {if $style !== 'list'}
            {$class = 'row-eq-height row-eq-img-height'}
        {/if}

        {$class = $class|default:''|cat:' '|cat:$style}

        <div id="result-wrapper" style="{$instance->getStyleString()}">
            <div {$instance->getAttributeString()}>
                {row class=$class|default:null id='product-list'}
                    {foreach $portlet->getFilteredProducts($instance) as $Artikel}
                        {col cols=$gridXS lg=$gridLG|default:false class='product-wrapper'}
                            {if $style === 'list'}
                                {include file='productlist/item_list.tpl' tplscope=$style}
                            {else}
                                {include file='productlist/item_box.tpl' tplscope=$style class='thumbnail'}
                            {/if}
                        {/col}
                    {/foreach}
                {/row}
            </div>
        </div>
    {/if}
{/if}
*}