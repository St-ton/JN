{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($oSlider) && count($oSlider->getSlides()) > 0}
    {opcMountPoint id='opc_before_slider'}
    <div class="slider-wrapper theme-{$oSlider->getTheme()}{if $oSlider->getControlNav()} control-nav{/if}{if $oSlider->getDirectionNav()} direction-nav{/if}{if $oSlider->getThumbnail()} thumbnail-nav{/if}">
        <div id="slider-{$oSlider->getID()}" class="nivoSlider">
            {foreach $oSlider->getSlides() as $oSlide}
                {assign var='slideTitle' value=$oSlide->getTitle()}
                {if !empty($oSlide->getText())}
                    {assign var='slideTitle' value="#slide_caption_{$oSlide->getID()}"}
                {/if}
                {if !empty($oSlide->getLink())}
                    <a href="{$oSlide->getLink()}"{if !empty($oSlide->getText())} title="{$oSlide->getText()|strip_tags}"{/if} class="slide">
                {else}
                    <div class="slide">
                {/if}

                <img alt="{$oSlide->getTitle()}" title="{$slideTitle}" src="{$oSlide->getAbsoluteImage()}"{if !empty($oSlide->getAbsoluteThumbnail()) && $oSlider->getThumbnail()} data-thumb="{$oSlide->getAbsoluteThumbnail()}"{/if}/>

                {if !empty($oSlide->getLink())}
                    </a>
                {else}
                    </div>
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
    <script type="text/javascript">
        {if $oSlider->getUseKB() === false}
            jtl.ready(function () {ldelim}
                var slider = $('#slider-{$oSlider->getID()}');
                $('a.slide').click(function () {ldelim}
                    if (!this.href.match(new RegExp('^' + location.protocol + '\\/\\/' + location.host))) {ldelim}
                        this.target = '_blank';
                        {rdelim}
                    {rdelim});
                slider.nivoSlider({ldelim}
                    effect: '{$oSlider->getEffects()|replace:';':','}',
                    animSpeed: {$oSlider->getAnimationSpeed()},
                    pauseTime: {$oSlider->getPauseTime()},
                    directionNav: {if $oSlider->getDirectionNav()}true{else}false{/if},
                    controlNav: {if $oSlider->getControlNav()}true{else}false{/if},
                    controlNavThumbs: {if $oSlider->getThumbnail()}true{else}false{/if},
                    pauseOnHover: {if $oSlider->getPauseOnHover()}true{else}false{/if},
                    prevText: '{lang key='sliderPrev' section='media'}',
                    nextText: '{lang key='sliderNext' section='media'}',
                    randomStart: {if $oSlider->getRandomStart()}true{else}false{/if},
                    afterLoad: function () {ldelim}
                        slider.addClass('loaded');
                    {rdelim}
                {rdelim});
            {rdelim});
        {else}
            var pauseTime = {$oSlider->getPauseTime()},
                animSpeed = {$oSlider->getAnimationSpeed()},
                zoomFactor = 30,
                durationFactor = 1.25;

            function KBInit () {ldelim}
                $('.nivoSlider img').css('visibility', 'hidden');
                $('.nivoSlider .nivo-nextNav').trigger('click');
                $('.nivoSlider, .nivo-control').css('opacity',1);
                setTimeout (function(){ldelim}
                    $('.nivoSlider, .nivo-control').animate({ldelim}opacity: 1{rdelim},animSpeed);
                {rdelim},0);
                $('.nivo-control').on('click', function() {ldelim}
                    setTimeout (function(){ldelim}
                        $('.nivo-main-image').css('opacity',0);
                    {rdelim},0);
                    durationFactor = 1.25;
                {rdelim});
                $('.nivo-prevNav, .nivo-nextNav').on('click', function() {ldelim}
                    setTimeout (function(){ldelim}
                        $('.nivo-main-image').css('opacity',0);
                    {rdelim},20);
                    durationFactor = 1.25;
                {rdelim});
            {rdelim}

            function NivoKenBurns () {ldelim}
                $('.nivo-main-image').css('opacity',1);
                setTimeout (function(){ldelim}
                    $('.nivoSlider .nivo-slice img').css('width',100+zoomFactor+'%');
                {rdelim},10);
                setTimeout (function(){ldelim}
                    var nivoWidth=$('.nivoSlider').width(), nivoHeight=$('.nivoSlider').height();
                    var xScope=nivoWidth*zoomFactor/100, yScope=nivoHeight*zoomFactor/105;
                    var xStart=-xScope*Math.floor(Math.random()*2);
                    var yStart=-yScope*Math.floor(Math.random()*2);
                    $('.nivoSlider .nivo-slice img').css('left',xStart).css('top',yStart).animate({ldelim}width:'100%', left:0, top:0{rdelim},pauseTime*durationFactor);durationFactor=1.02;
                    $('.nivo-main-image').css('cssText','left:0 !important;top:0 !important;');
                {rdelim},10);
            {rdelim}

            jtl.ready(function () {ldelim}
                var slider = $('#slider-{$oSlider->getID()}');
                var endSlide=$('.nivoSlider img').length-1;
                $('a.slide').click(function() {ldelim}
                    if (!this.href.match(new RegExp('^'+location.protocol+'\\/\\/'+location.host))) {ldelim}
                        this.target = '_blank';
                    {rdelim}
                {rdelim});
                slider.nivoSlider( {ldelim}
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
                    beforeChange: function (){ldelim}NivoKenBurns();{rdelim},
                    afterLoad: function (){ldelim}
                        KBInit();
                        slider.addClass('loaded');
                    {rdelim}
                {rdelim});
            {rdelim});
        {/if}
    </script>
{/if}
