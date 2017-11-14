<div>
    {if count($properties.slides) > 0}
        <div class="slider-wrapper theme-{$properties['slider-theme']}{if $properties['slider-navigation']=== 'yes'} control-nav{/if}{if $properties['slider-direction-navigation']=== 'yes'} direction-nav{/if}">
            <div id="slider-{$properties['slider-id']}" class="nivoSlider">
                {foreach from=$properties.slides item=slide}
                    {if !empty($slide.url)}
                        {assign var="slideTitle" value=$slide.cTitle}

                        <div class="slide">

                            <img alt="{$slide.cTitle}" title="{$slide.cText}" src="{$slide.url}"/>

                        </div>
                    {/if}
                {/foreach}
            </div>
            {* slide captions outside of .nivoSlider *}

        </div>
        <script type="text/javascript">

            jtl.ready(function () {ldelim}
                var slider = $('#slider-{$properties['slider-id']}');
                slider.nivoSlider({ldelim}
                    effect: 'random',
                    animSpeed: {$properties['slider-animation-speed']},
                    pauseTime: {$properties['slider-animation-pause']},
                    directionNav: true,
                    controlNav: false,
                    controlNavThumbs: false,
                    pauseOnHover: true,
                    prevText: 'prev',
                    nextText: 'next',
                    randomStart: true,
                    afterLoad: function () {ldelim}
                        slider.addClass('loaded');
                        {rdelim}
                    {rdelim});
                {rdelim});

        </script>
    {else}
        <img class="img-responsive" src="{$noImageUrl}">;
    {/if}
</div>