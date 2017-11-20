<div{$styleString}>
    {if count($properties.slides) > 0}
        <div class="slider-wrapper theme-{$properties['slider-theme']}">
            <div id="slider-{$properties['slider-id']}" class="nivoSlider">
                {foreach from=$properties.slides item=slide}
                    {assign var="slideTitle" value=$slide.cTitle}
                    {if !empty($slide.cText)}
                        {assign var="slideTitle" value="#slider-{$properties['slider-id']}_slide_caption_{$slide.nSort}"}
                    {/if}
                    {if !empty($slide.url)}
                        {if $renderLinks && !empty($slide.cLink)}
                            <a href="{$slide.cLink}"{if !empty($slide.cText)} title="{$slide.cTitle}"{/if} class="slide">
                        {else}
                            <div class="slide">
                        {/if}
                                <img alt="{$slide.cTitle}" title="{$slideTitle}" src="{$slide.url}"/>
                        {if $renderLinks && !empty($slide.cLink)}
                            </a>
                        {else}
                            </div>
                        {/if}
                    {/if}
                {/foreach}
            </div>
            {* slide captions outside of .nivoSlider *}
            {foreach  from=$properties.slides item=slide}
                {if !empty($slide.cText)}
                    <div id="slider-{$properties['slider-id']}_slide_caption_{$slide.nSort}" class="htmlcaption hidden">
                        {if isset($slide.cTitle)}<strong class="title">{$slide.cTitle}</strong>{/if}
                        <p class="desc">{$slide.cText}</p>
                    </div>
                {/if}
            {/foreach}
        </div>
        <script type="text/javascript">
            {if $properties['slider-kenburns'] === 'false'}
                jtl.ready(function () {
                    var slider = $('#slider-{$properties['slider-id']}');
                    $('a.slide').click(function () {
                        if (!this.href.match(new RegExp('^' + location.protocol + '\\/\\/' + location.host))) {
                            this.target = '_blank';
                            }
                        });
                    slider.nivoSlider({
                        effect: {if $properties['slider-effects-random'] === 'true'}'random'
                        {else}'{foreach name="effects" from=$properties['effects'] item=effect}{$effect}{if !$smarty.foreach.effects.last},{/if}{/foreach}'{/if},
                        animSpeed: {if !empty($properties['slider-animation-speed'])}{$properties['slider-animation-speed']}{else}500{/if},
                        pauseTime: {if !empty($properties['slider-animation-pause'])}{$properties['slider-animation-pause']}{else}3000{/if},
                        directionNav: {$properties['slider-direction-navigation']},
                        controlNav: {$properties['slider-navigation']},
                        controlNavThumbs: false,
                        pauseOnHover: {$properties['slider-pause']},
                        prevText: 'prev',
                        nextText: 'next',
                        randomStart: true,
                        afterLoad: function () {
                            slider.addClass('loaded');
                            }
                        });
                    });
            {else}
                var pauseTime = {$properties['slider-animation-pause']};    // pauseTime must be set here
                var animSpeed = {$properties['slider-animation-speed']};    // animSpeed must be set here
                var zoomFactor = 30;                                        // 30% zoom as default
                var durationFactor = 1.25;                                  // firstslide pausetime adjustment factor

                function KBInit () {
                    $('#slider-{$properties["slider-id"]}  img').css('visibility', 'hidden');
                    $('#slider-{$properties["slider-id"]}  .nivo-nextNav').trigger('click');
                    $('#slider-{$properties["slider-id"]} , .nivo-control').css('opacity',1);
                    setTimeout (function(){
                        $('#slider-{$properties["slider-id"]} , .nivo-control').animate({ldelim}opacity: 1{rdelim},animSpeed);
                        },0);
                    $('#slider-{$properties["slider-id"]} .nivo-control').on('click', function() {
                        setTimeout (function(){
                            $('#slider-{$properties["slider-id"]} .nivo-main-image').css('opacity',0);
                            },0);
                        durationFactor = 1.25;
                        });
                    $('#slider-{$properties["slider-id"]} .nivo-prevNav, #slider-{$properties["slider-id"]} .nivo-nextNav').on('click', function() {
                        setTimeout (function(){
                            $('#slider-{$properties["slider-id"]} .nivo-main-image').css('opacity',0);
                            },20);
                        durationFactor = 1.25;
                        });
                    }

                function NivoKenBurns () {
                    $('#slider-{$properties["slider-id"]} .nivo-main-image').css('opacity',1);
                    setTimeout (function(){
                        $('#slider-{$properties["slider-id"]}  .nivo-slice img').css('width',100+zoomFactor+'%');
                        },10);
                    setTimeout (function(){
                        var nivoWidth=$('#slider-{$properties["slider-id"]} ').width(), nivoHeight=$('#slider-{$properties["slider-id"]} ').height();
                        var xScope=nivoWidth*zoomFactor/100, yScope=nivoHeight*zoomFactor/105;
                        var xStart=-xScope*Math.floor(Math.random()*2);
                        var yStart=-yScope*Math.floor(Math.random()*2);
                        $('#slider-{$properties["slider-id"]}  .nivo-slice img').css('left',xStart).css('top',yStart).animate({ldelim}width:'100%', left:0, top:0{rdelim},pauseTime*durationFactor);durationFactor=1.02;
                        $('#slider-{$properties["slider-id"]} .nivo-main-image').css('cssText','left:0 !important;top:0 !important;');
                        },10);
                    }

                jtl.ready(function () {
                    var slider = $('#slider-{$properties['slider-id']}');
                    var endSlide=$('#slider-{$properties["slider-id"]}  img').length-1;
                    $('a.slide').click(function() {
                        if (!this.href.match(new RegExp('^'+location.protocol+'\\/\\/'+location.host))) {
                            this.target = '_blank';
                            }
                        });
                    slider.nivoSlider( {
                        effect: 'fade',
                        animSpeed: animSpeed,
                        pauseTime: pauseTime,
                        directionNav: true,
                        controlNav: false,
                        controlNavThumbs: false,
                        pauseOnHover: false,
                        prevText: 'prev',
                        nextText: 'next',
                        manualAdvance: false,
                        randomStart: false,
                        startSlide: endSlide,
                        slices: 1,
                        beforeChange: function (){
                            NivoKenBurns();
                            },
                        afterLoad: function (){
                            KBInit();
                            slider.addClass('loaded');
                            }
                        });
                    });
            {/if}
        </script>
    {else}
        <img class="img-responsive" src="{$noImageUrl}">
    {/if}
</div>