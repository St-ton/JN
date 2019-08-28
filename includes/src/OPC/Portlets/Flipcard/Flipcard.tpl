{$uid = $instance->getUid()}

<div id="{$uid}" {if $isPreview}{$instance->getDataAttributeString()}{/if}
     {$instance->getAnimationDataAttributeString()}
     class="flipcard opc-Flipcard {$instance->getProperty('flip-dir')} {$instance->getAnimationClass()}"
     style="{$instance->getStyleString()}">
    {if $isPreview}
        <a href="#" class="opc-Flipcard-flip-btn">
            <span class="opc-Flipcard-label opc-Flipcard-label-front active">Vorderseite</span>
            <i class="fas fa-exchange-alt"></i>
            <span class="opc-Flipcard-label opc-Flipcard-label-back">Rückseite</span>
        </a>
    {/if}
    <div class="flipcard-inner">
        <div class="flipcard-face flipcard-front {if $isPreview}opc-area{/if}"
             {if $isPreview}data-area-id="front"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("front")}
            {else}
                {$instance->getSubareaFinalHtml("front")}
            {/if}
        </div>
        <div class="flipcard-face flipcard-back {if $isPreview}opc-area{/if}"
             {if $isPreview}data-area-id="back"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("back")}
            {else}
                {$instance->getSubareaFinalHtml("back")}
            {/if}
        </div>
    </div>
    {inline_script}<script>
        function initFlipcard_{$uid}()
        {
            var flipcard      = $('#{$uid}');
            var flipcardInner = flipcard.find('.flipcard-inner');

            {if $isPreview}
                flipcard.find('.opc-Flipcard-flip-btn').click(flipCard);
            {else}
                flipcard.click(flipCard);
            {/if}

            setTimeout(() => updateHeight_{$uid}());

            function flipCard(e)
            {
                flipcardInner.toggleClass('flipped');
                flipcard.find('.opc-Flipcard-label-front').toggleClass('active');
                flipcard.find('.opc-Flipcard-label-back').toggleClass('active');
                updateHeight_{$uid}();
                e.preventDefault();
            }
        }

        $(initFlipcard_{$uid});

        document.getElementById('{$uid}').updateFlipcardHeight = updateHeight_{$uid};

        function updateHeight_{$uid}()
        {
            var flipcard      = $('#{$uid}');
            var flipcardInner = flipcard.find('.flipcard-inner');
            var flipcardFaces = flipcardInner.find('.flipcard-face');
            var height        = 0;

            flipcardInner.css('height', 'auto');
            flipcardFaces.css('height', 'auto');

            flipcardInner.find('.flipcard-face').each(function(i, elm) {
                height = Math.max(height, $(elm).height());
            });

            flipcardInner.height(height);
            flipcardFaces.height(height);
        }
    </script>{/inline_script}
</div>