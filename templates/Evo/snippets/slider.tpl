{if isset($oSlider) && count($oSlider->oSlide_arr) > 0}
    <div class="slider-wrapper theme-{$oSlider->cTheme}">
        <div id="slider-{$oSlider->kSlider}" class="nivoSlider">
            {foreach from=$oSlider->oSlide_arr item=oSlide}
                {assign var="slideTitle" value=$oSlide->cTitel}
                {if !empty($oSlide->cText)}
                    {assign var="slideTitle" value="#slide_caption_{$oSlide->kSlide}"}
                {/if}
                {if !empty($oSlide->cLink)}
                    <a href="{$oSlide->cLink}"{if !empty($oSlide->cText)} title="{$oSlide->cText}"{/if} class="slide">
                {else}
                    <div class="slide">
                {/if}
                
                <img alt="{$oSlide->cTitel}" title="{$slideTitle}" src="{$oSlide->cBildAbsolut}" {if !empty($oSlide->cThumbnailAbsolut) && $oSlider->bThumbnail == '1'} data-thumb="{$oSlide->cThumbnailAbsolut}"{/if}/>
                
                {if !empty($oSlide->cLink)}
                    </a>
                {else}
                    </div>
                {/if}
            {/foreach}
        </div>
        {* slide captions outside of .nivoSlider *}
        {foreach from=$oSlider->oSlide_arr item=oSlide}
            {if !empty($oSlide->cText)}
                <div id="slide_caption_{$oSlide->kSlide}" class="htmlcaption hidden">
                    {if isset($oSlide->cTitel)}<strong class="title">{$oSlide->cTitel}</strong>{/if}
                    <p class="desc">{$oSlide->cText}</p>
                </div>
            {/if}
        {/foreach}
    </div>
    <script type="text/javascript">
        jtl.ready(function () {
            $('a.slide').click(function() {
                if (!this.href.match(new RegExp('^'+location.protocol+'\\/\\/'+location.host))) {
                    this.target = '_blank';
                }
            });
            var slider = $('#slider-{$oSlider->kSlider}');
            slider.nivoSlider( {ldelim}
                effect: '{$oSlider->cEffects|replace:';':','}',
                animSpeed: {$oSlider->nAnimationSpeed},
                pauseTime: {$oSlider->nPauseTime},
                directionNav: {$oSlider->bDirectionNav},
                controlNav: {$oSlider->bControlNav},
                controlNavThumbs: {$oSlider->bThumbnail},
                pauseOnHover: {$oSlider->bPauseOnHover},
                prevText: '{lang key="sliderPrev" section="media"}',
                nextText: '{lang key="sliderNext" section="media"}',
                randomStart: {$oSlider->bRandomStart},
                afterLoad: function() {ldelim}
                    slider.addClass('loaded');
                {rdelim}
            {rdelim});
        {rdelim});
    </script>
{/if}