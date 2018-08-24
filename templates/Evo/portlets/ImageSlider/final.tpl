<div {$instance->getAttributeString()}>
    {if $instance->getProperty('slides') > 0}
        <div class="slider-wrapper theme-{$instance->getProperty('slider-theme')}">
            <div id="{$instance->getProperty('uid')}" class="nivoSlider">
                {foreach from=$instance->getProperty('slides') item=slide}
                    {if !empty($slide.cText)}
                        {assign var="slideTitle" value=$slide.cTitle}
                    {else}
                        {assign var="slideTitle" value=''}
                    {/if}
                    {assign var="slideTitle" value="#{$instance->getProperty('uid')}_slide_caption_{$slide.nSort}"}
                    {if !empty($slide.url)}
                        {if !empty($slide['target-url'])}
                            <a href="{$slide['target-url']}"{if !empty($slide.cTitle)} title="{$slide.cTitle}"{/if} class="slide">
                        {else}
                            <div class="slide">
                        {/if}
                            <img srcset="{$slide['img_attr']['srcset']}"
                                 sizes="{$slide['img_attr']['srcsizes']}"
                                 src="{$slide['img_attr']['src']}"
                                 data-desc="{$slide['desc']}"
                                 alt="{$slide['img_attr']['alt']}"
                                 title="{$slideTitle}">
                        {if !empty($slide['target-url'])}
                            </a>
                        {else}
                            </div>
                        {/if}
                    {/if}
                {/foreach}
            </div>
            {* slide captions outside of .nivoSlider *}
            {foreach  from=$instance->getProperty('slides') item=slide}
                {if !empty($slide.cTitle) || !empty($slide.desc)}
                    <div id="{$instance->getProperty('uid')}_slide_caption_{$slide.nSort}" class="htmlcaption hidden">
                        {if !empty($slide.cTitle)}<strong class="title">{$slide.cTitle}</strong>{/if}
                        <p class="desc">{$slide.desc}</p>
                    </div>
                {/if}
            {/foreach}
        </div>
        <script type="text/javascript">
            {if !empty($instance->getProperty('slider-kenburns'))}
                var pauseTime      = {$instance->getProperty('slider-animation-pause')};    // pauseTime must be set here
                var animSpeed      = {$instance->getProperty('slider-animation-speed')};    // animSpeed must be set here
                var zoomFactor     = 30;                                        // 30% zoom as default
                var durationFactor = 1.25;                                  // firstslide pausetime adjustment factor

                function KBInit() {
                    $('#{$instance->getProperty("uid")}  img').css('visibility', 'hidden');
                    $('#{$instance->getProperty("uid")}  .nivo-nextNav').trigger('click');
                    $('#{$instance->getProperty("uid")} , .nivo-control').css('opacity', 1);
                    setTimeout(function () {
                        $('#{$instance->getProperty("uid")} , .nivo-control').animate({ldelim}opacity: 1{rdelim}, animSpeed);
                    }, 0);
                    $('#{$instance->getProperty("uid")} .nivo-control').on('click', function () {
                        setTimeout(function () {
                            $('#{$instance->getProperty("uid")} .nivo-main-image').css('opacity', 0);
                        }, 0);
                        durationFactor = 1.25;
                    });
                    $('#{$instance->getProperty("uid")} .nivo-prevNav, #{$instance->getProperty("uid")} .nivo-nextNav').on('click', function () {
                        setTimeout(function () {
                            $('#{$instance->getProperty("uid")} .nivo-main-image').css('opacity', 0);
                        }, 20);
                        durationFactor = 1.25;
                    });
                }

                function NivoKenBurns() {
                    $('#{$instance->getProperty("uid")} .nivo-main-image').css('opacity', 1);
                    setTimeout(function () {
                        $('#{$instance->getProperty("uid")}  .nivo-slice img').css('width', 100 + zoomFactor + '%');
                    }, 10);
                    setTimeout(function () {
                        var nivoWidth                                     = $('#{$instance->getProperty("uid")} ').width(),
                            nivoHeight                                    = $('#{$instance->getProperty("uid")} ').height();
                        var xScope = nivoWidth * zoomFactor / 100, yScope = nivoHeight * zoomFactor / 105;
                        var xStart                                        = -xScope * Math.floor(Math.random() * 2);
                        var yStart                                        = -yScope * Math.floor(Math.random() * 2);
                        $('#{$instance->getProperty("uid")}  .nivo-slice img').css('left', xStart).css('top', yStart).animate(
                            {ldelim}width: '100%',
                                left:         0,
                                top:          0{rdelim}, pauseTime * durationFactor);
                        durationFactor = 1.02;
                        $('#{$instance->getProperty("uid")} .nivo-main-image').css('cssText', 'left:0 !important;top:0 !important;');
                    }, 10);
                }

                jtl.ready(function () {
                    var slider   = $('#{$instance->getProperty("uid")}');
                    var endSlide = $('#{$instance->getProperty("uid")}  img').length - 1;
                    $('a.slide').click(function () {
                        if (!this.href.match(new RegExp('^' + location.protocol + '\\/\\/' + location.host))) {
                            this.target = '_blank';
                        }
                    });
                    slider.nivoSlider({
                        effect:           'fade',
                        animSpeed:        animSpeed,
                        pauseTime:        pauseTime,
                        directionNav:     true,
                        controlNav:       false,
                        controlNavThumbs: false,
                        pauseOnHover:     false,
                        prevText:         'prev',
                        nextText:         'next',
                        manualAdvance:    false,
                        randomStart:      false,
                        startSlide:       endSlide,
                        slices:           1,
                        beforeChange:     function () {
                            NivoKenBurns();
                        },
                        afterLoad:        function () {
                            KBInit();
                            slider.addClass('loaded');
                        }
                    });
                });
            {else}
                jtl.ready(function () {
                    var slider = $('#{$instance->getProperty("uid")}');
                    $('a.slide').click(function () {
                        if (!this.href.match(new RegExp('^' + location.protocol + '\\/\\/' + location.host))) {
                            this.target = '_blank';
                        }
                    });
                    slider.nivoSlider({
                        effect:           {if !empty($instance->getProperty('slider-effects-random')) && $instance->getProperty('slider-effects-random') === 'true'}'random'{else}'{foreach name="effects" from=$instance->getProperty('effects') key=effect item=effectval}{$effectval}{if !$smarty.foreach.effects.last},{/if}{/foreach}'{/if},
                        animSpeed:        {if !empty($instance->getProperty('slider-animation-speed'))}{$instance->getProperty('slider-animation-speed')}{else}500{/if},
                        pauseTime:        {if !empty($instance->getProperty('slider-animation-pause'))}{$instance->getProperty('slider-animation-pause')}{else}3000{/if},
                        directionNav:     {$instance->getProperty('slider-direction-navigation')},
                        controlNav:       {$instance->getProperty('slider-navigation')},
                        controlNavThumbs: false,
                        pauseOnHover:     {$instance->getProperty('slider-pause')},
                        prevText:         'prev',
                        nextText:         'next',
                        randomStart:      true,
                        afterLoad:        function () {
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