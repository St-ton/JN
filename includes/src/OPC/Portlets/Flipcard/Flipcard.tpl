{$uid = $instance->getUid()}

<div id="{$uid}" {if $isPreview}{$instance->getDataAttributeString()}{/if}
     {$instance->getAnimationDataAttributeString()}
     class="opc-Flipcard opc-Flipcard-{$instance->getProperty('flip-dir')} {$instance->getAnimationClass()}"
     style="{$instance->getStyleString()}">
    {if $isPreview}
        <a href="#" class="opc-Flipcard-flip-btn">
            <span class="opc-Flipcard-label opc-Flipcard-label-front active">Vorderseite</span>
            <i class="fas fa-exchange-alt"></i>
            <span class="opc-Flipcard-label opc-Flipcard-label-back">Rückseite</span>
        </a>
    {/if}
    <div class="opc-Flipcard-inner">
        <div class="opc-Flipcard-face opc-Flipcard-front {if $isPreview}opc-area{/if}"
             {if $isPreview}data-area-id="front"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("front")}
            {else}
                {$instance->getSubareaFinalHtml("front")}
            {/if}
        </div>
        <div class="opc-Flipcard-face opc-Flipcard-back {if $isPreview}opc-area{/if}"
             {if $isPreview}data-area-id="back"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("back")}
            {else}
                {$instance->getSubareaFinalHtml("back")}
            {/if}
        </div>
    </div>
    <script>
        document.getElementById('{$uid}').updateFlipcardHeight = updateHeight_{$uid};

        function initFlipcard_{$uid}()
        {
            var flipcard      = $('#{$uid}');
            var flipcardInner = flipcard.find('.opc-Flipcard-inner');

            {if $isPreview}
                flipcard.find('.opc-Flipcard-flip-btn').click(flipCard);
            {else}
                flipcard.click(flipCard);
            {/if}

            setTimeout(() => updateHeight_{$uid}());

            function flipCard(e)
            {
                flipcardInner.toggleClass('opc-Flipcard-flipped');
                flipcard.find('.opc-Flipcard-label-front').toggleClass('active');
                flipcard.find('.opc-Flipcard-label-back').toggleClass('active');
                updateHeight_{$uid}();
                e.preventDefault();
            }
        }

        function updateHeight_{$uid}()
        {
            var flipcard      = $('#{$uid}');
            var flipcardInner = flipcard.find('.opc-Flipcard-inner');
            var flipcardFaces = flipcardInner.find('.opc-Flipcard-face');
            var height        = 0;

            flipcardInner.css('height', 'auto');
            flipcardFaces.css('height', 'auto');

            flipcardInner.find('.opc-Flipcard-face').each(function(i, elm) {
                height = Math.max(height, $(elm).height());
            });

            flipcardInner.height(height);
            flipcardFaces.height(height);
        }
    </script>

    {inline_script}<script>
        $(initFlipcard_{$uid});
    </script>{/inline_script}
</div>