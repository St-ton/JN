{assign var="title" value=$instance->getProperty('sliderTitle')}

{if $productlist|@count > 0}
    <section class="panel{if $title|strlen > 0} panel-default{/if}
                    panel-slider
                    {if isset($class) && $class|strlen > 0} {$class}{/if}"
            {if isset($id) && $id|strlen > 0} id="{$id}"{/if}>
        <div class="panel-heading">
            {if $title|strlen > 0}
                <h5 class="panel-title">
                    {$title}
                    {if !empty($moreLink)}
                        <a class="more pull-right" href="{$moreLink}" title="{$moreTitle}" data-toggle="tooltip"
                           data-placement="auto right" aria-label="{$moreTitle}">
                            <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    {/if}
                </h5>
            {/if}
        </div>
        <div{if $title|strlen > 0} class="panel-body"{/if}>
            <div class="{block name="product-slider-class"}evo-opc-slider{/block}">
                {foreach name="sliderproducts" from=$productlist item='product'}
                    <div class="product-wrapper{if isset($style)} {$style}{/if}"
                         {if isset($Link) && $Link->nLinkart == $smarty.const.LINKTYP_STARTSEITE}
                             itemprop="about"
                         {else}
                             itemprop="isRelatedTo"
                         {/if}
                         itemscope itemtype="http://schema.org/Product">
                        {include file='productlist/item_slider.tpl' Artikel=$product tplscope='' class=''}
                    </div>
                {/foreach}
            </div>
        </div>
        <script>
            $(window).on('load.opc', function () {
                $('.evo-opc-slider:not(.slick-initialized)').slick({
                    //dots: true,
                    arrows: true,
                    lazyLoad: 'ondemand',
                    slidesToShow: {$instance->getProperty('productCount')},
                });
            });
        </script>
    </section>{* /panel *}
{/if}