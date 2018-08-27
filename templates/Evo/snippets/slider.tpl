{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($oSlider) && count($oSlider->oSlide_arr) > 0}
    <div class="slider-wrapper theme-{$oSlider->cTheme}{if $oSlider->bControlNav} control-nav{/if}{if $oSlider->bDirectionNav} direction-nav{/if}{if $oSlider->bThumbnail} thumbnail-nav{/if}">
        <div id="slider-{$oSlider->kSlider}" class="nivoSlider">
            {foreach $oSlider->oSlide_arr as $oSlide}
                {assign var='slideTitle' value=$oSlide->cTitel}
                {if !empty($oSlide->cText)}
                    {assign var='slideTitle' value="#slide_caption_{$oSlide->kSlide}"}
                {/if}
                {if !empty($oSlide->cLink)}
                    <a href="{$oSlide->cLink}"{if !empty($oSlide->cText)} title="{$oSlide->cText|strip_tags}"{/if} class="slide">
                {else}
                    <div class="slide">
                {/if}

                <img alt="{$oSlide->cTitel}" title="{$slideTitle}" src="{$oSlide->cBildAbsolut}"{if !empty($oSlide->cThumbnailAbsolut) && $oSlider->bThumbnail == '1'} data-thumb="{$oSlide->cThumbnailAbsolut}"{/if}/>

                {if empty($oSlide->cLink)}
                    </div>
                {else}
                    </a>
                {/if}
            {/foreach}
        </div>
        {* slide captions outside of .nivoSlider *}
        {foreach $oSlider->oSlide_arr as $oSlide}
            {if !empty($oSlide->cText)}
                <div id="slide_caption_{$oSlide->kSlide}" class="htmlcaption hidden">
                    {if isset($oSlide->cTitel)}<strong class="title">{$oSlide->cTitel}</strong>{/if}
                    <p class="desc">{$oSlide->cText}</p>
                </div>
            {/if}
        {/foreach}
    </div>
    <script type="text/javascript">
        {if empty($oSlider->bUseKB)}
            jtl.ready(function () {
                var slider = $('#slider-{$oSlider->kSlider}');
                $('a.slide').click(function () {
                    if (!this.href.match(new RegExp('^' + location.protocol + '\\/\\/' + location.host))) {
                        this.target = '_blank';
                        }
                    });
                slider.nivoSlider({
                    effect: '{$oSlider->cEffects|replace:';':','}',
                    animSpeed: {$oSlider->nAnimationSpeed},
                    pauseTime: {$oSlider->nPauseTime},
                    directionNav: {$oSlider->bDirectionNav},
                    controlNav: {$oSlider->bControlNav},
                    controlNavThumbs: {$oSlider->bThumbnail},
                    pauseOnHover: {$oSlider->bPauseOnHover},
                    prevText: '{lang key='sliderPrev' section='media'}',
                    nextText: '{lang key='sliderNext' section='media'}',
                    randomStart: {$oSlider->bRandomStart},
                    afterLoad: function () {
                        slider.addClass('loaded');
                    }
                });
            });
        {else}
            var pauseTime = {$oSlider->nPauseTime};         // pauseTime must be set here
            var animSpeed = {$oSlider->nAnimationSpeed};    // animSpeed must be set here
            var zoomFactor = 30;                            // 30% zoom as default
            var durationFactor = 1.25;                      // firstslide pausetime adjustment factor

            jtl.ready(function () {
                var slider = $('#slider-{$oSlider->kSlider}');
                var endSlide=$('#slider-{$oSlider->kSlider} img').length-1;
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
                    prevText: '{lang key='sliderPrev' section='media'}',
                    nextText: '{lang key='sliderNext' section='media'}',
                    manualAdvance: false,
                    randomStart: false,
                    startSlide: endSlide,
                    slices: 1,
                    beforeChange: function (){
                        $('#slider-{$oSlider->kSlider} .nivo-main-image').css('opacity',1);
                        setTimeout (function(){
                            $('#slider-{$oSlider->kSlider} .nivo-slice img').css('width',100+zoomFactor+'%');
                        },10);
                        setTimeout (function(){
                            var nivoWidth=$('#slider-{$oSlider->kSlider}').width(), nivoHeight=$('#slider-{$oSlider->kSlider}').height();
                            var xScope=nivoWidth*zoomFactor/100, yScope=nivoHeight*zoomFactor/105;
                            var xStart=-xScope*Math.floor(Math.random()*2);
                            var yStart=-yScope*Math.floor(Math.random()*2);
                            $('#slider-{$oSlider->kSlider} .nivo-slice img').css('left',xStart).css('top',yStart).animate({ width:'100%', left:0, top:0 },pauseTime*durationFactor);durationFactor=1.02;
                            $('#slider-{$oSlider->kSlider} .nivo-main-image').css('cssText','left:0 !important;top:0 !important;');
                        },10);
                    },
                    afterLoad: function (){
                        $('#slider-{$oSlider->kSlider} img').css('visibility', 'hidden');
                        $('#slider-{$oSlider->kSlider} .nivo-nextNav').trigger('click');
                        $('#slider-{$oSlider->kSlider}, #slider-{$oSlider->kSlider} .nivo-control').css('opacity',1);
                        setTimeout (function(){
                            $('#slider-{$oSlider->kSlider}, #slider-{$oSlider->kSlider} .nivo-control').animate({ opacity: 1 },animSpeed);
                        },0);
                        $('#slider-{$oSlider->kSlider} .nivo-control').on('click', function() {
                            setTimeout (function(){
                                $('#slider-{$oSlider->kSlider} .nivo-main-image').css('opacity',0);
                            },0);
                            durationFactor = 1.25;
                        });
                        $('#slider-{$oSlider->kSlider} .nivo-prevNav, #slider-{$oSlider->kSlider} .nivo-nextNav').on('click', function() {
                            setTimeout (function(){
                                $('#slider-{$oSlider->kSlider} .nivo-main-image').css('opacity',0);
                            },20);
                            durationFactor = 1.25;
                        });
                        slider.addClass('loaded');
                    }
                });
            });
        {/if}
    </script>
{/if}
