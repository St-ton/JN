{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($oSlider) && count($oSlider->getSlides()) > 0}
    <div class="slider-wrapper theme-{$oSlider->getTheme()}
                {if $oSlider->getControlNav()} control-nav{/if}
                {if $oSlider->getDirectionNav()} direction-nav{/if}
                {if $oSlider->getThumbnail()} thumbnail-nav{/if}">
        <div id="slider-{$oSlider->getID()}" class="nivoSlider">
            {foreach $oSlider->getSlides() as $oSlide}
                {assign var='slideTitle' value=$oSlide->getTitle()}
                {if !empty($oSlide->getText())}
                    {assign var='slideTitle' value="#slide_caption_{$oSlide->getID()}"}
                {/if}
                {if !empty($oSlide->getLink())}
                    <a href="{$oSlide->getLink()}"
                       {if !empty($oSlide->getText())}
                           title="{$oSlide->getText()|strip_tags}"
                       {/if}
                       class="slide">
                {else}
                    <div class="slide">
                {/if}

                <img alt="{$oSlide->getTitle()}" title="{$slideTitle}"
                     src="{$oSlide->getAbsoluteImage()}"
                     {if !empty($oSlide->getAbsoluteThumbnail()) && $oSlider->getThumbnail()}
                        data-thumb="{$oSlide->getAbsoluteThumbnail()}"
                     {/if}>

                {if empty($oSlide->getLink())}
                    </div>
                {else}
                    </a>
                {/if}
            {/foreach}
        </div>
        {* slide captions outside of .nivoSlider *}
        {foreach $oSlider->getSlides() as $oSlide}
            {if !empty($oSlide->getText())}
                <div id="slide_caption_{$oSlide->getID()}" class="htmlcaption hidden">
                    {if isset($oSlide->getTitle())}<strong class="title">{$oSlide->getTitle()}</strong>{/if}
                    <p class="desc">{$oSlide->getText()}</p>
                </div>
            {/if}
        {/foreach}
    </div>
    <script>
        {if $oSlider->getUseKB() === false}
            jtl.ready(function () {
                var slider = $('#slider-{$oSlider->getID()}');
                $('a.slide').click(function () {
                    if (!this.href.match(new RegExp('^' + location.protocol + '\\/\\/' + location.host))) {
                        this.target = '_blank';
                    }
                });
                slider.nivoSlider({
                    effect:           '{$oSlider->getEffects()|replace:';':','}',
                    animSpeed:        {$oSlider->getAnimationSpeed()},
                    pauseTime:        {$oSlider->getPauseTime()},
                    directionNav:     {if $oSlider->getDirectionNav()}true{else}false{/if},
                    controlNav:       {if $oSlider->getControlNav()}true{else}false{/if},
                    controlNavThumbs: {if $oSlider->getThumbnail()}true{else}false{/if},
                    pauseOnHover:     {if $oSlider->getPauseOnHover()}true{else}false{/if},
                    prevText:         '{lang key='sliderPrev' section='media'}',
                    nextText:         '{lang key='sliderNext' section='media'}',
                    randomStart:      {if $oSlider->getRandomStart()}true{else}false{/if},
                    afterLoad: function () {
                        slider.addClass('loaded');
                    }
                });
            });
        {else}
            var pauseTime = {$oSlider->getPauseTime()},
                animSpeed = {$oSlider->getAnimationSpeed()},
                zoomFactor = 30,
                durationFactor = 1.25;

            jtl.ready(function () {
                var slider = $('#slider-{$oSlider->getID()}');
                var endSlide=$('#slider-{$oSlider->getID()} img').length - 1;

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
                    beforeChange: function ()
                    {
                        $('#slider-{$oSlider->getID()} .nivo-main-image').css('opacity', 1);

                        setTimeout (function() {
                            $('#slider-{$oSlider->getID()} .nivo-slice img').css('width', 100 + zoomFactor + '%');
                        }, 10);

                        setTimeout (function() {
                            var nivoWidth  = $('#slider-{$oSlider->getID()}').width();
                            var nivoHeight = $('#slider-{$oSlider->getID()}').height();
                            var xScope = nivoWidth  * zoomFactor / 100;
                            var yScope = nivoHeight * zoomFactor / 105;
                            var xStart = -xScope * Math.floor(Math.random() * 2);
                            var yStart = -yScope * Math.floor(Math.random() * 2);

                            $('#slider-{$oSlider->getID()} .nivo-slice img')
                                .css('left', xStart).css('top',yStart)
                                .animate({
                                    width:'100%',
                                    left:0, top:0
                                }, pauseTime * durationFactor);

                            durationFactor = 1.02;

                            $('#slider-{$oSlider->getID()} .nivo-main-image')
                                .css('cssText','left:0 !important;top:0 !important;');
                        }, 10);
                    },
                    afterLoad: function ()
                    {
                        $('#slider-{$oSlider->getID()} img').css('visibility', 'hidden');
                        $('#slider-{$oSlider->getID()} .nivo-nextNav').trigger('click');
                        $('#slider-{$oSlider->getID()}, #slider-{$oSlider->getID()} .nivo-control').css('opacity',1);

                        setTimeout (function() {
                            $('#slider-{$oSlider->getID()}, #slider-{$oSlider->getID()} .nivo-control')
                                .animate({
                                    opacity: 1
                                }, animSpeed);
                        }, 0);

                        $('#slider-{$oSlider->getID()} .nivo-control').on('click', function() {
                            setTimeout (function(){
                                $('#slider-{$oSlider->getID()} .nivo-main-image')
                                    .css('opacity', 0);
                            }, 0);
                            durationFactor = 1.25;
                        });

                        $('#slider-{$oSlider->getID()} .nivo-prevNav, #slider-{$oSlider->getID()} .nivo-nextNav')
                            .on('click', function() {
                                setTimeout (function() {
                                    $('#slider-{$oSlider->getID()} .nivo-main-image').css('opacity',0);
                                }, 20);
                                durationFactor = 1.25;
                            });

                        slider.addClass('loaded');
                    }
                });
            });
        {/if}
    </script>
{/if}
