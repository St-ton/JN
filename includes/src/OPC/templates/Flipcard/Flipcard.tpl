{$uid = $instance->getUid()}

<div id="{$uid}" {if $isPreview}{$instance->getDataAttributeString()}{/if}
     class="flipcard {$instance->getProperty('flip-dir')}"
     style="{$instance->getStyleString()}">
    {if $isPreview}
        <button type="button" class="btn btn-default">
            <i class="fa fa-exchange"></i>
            <i class="fa fa-exchange-alt"></i>
        </button>
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
    <script>
        $(function() {
            var flipcard      = $('#{$uid}');
            var flipcardInner = flipcard.find('.flipcard-inner');

            {if $isPreview}
                flipcard.find('.btn').click(flipCard);
            {else}
                flipcard.click(flipCard);
            {/if}

            updateHeight_{$uid}();

            function flipCard()
            {
                flipcardInner.toggleClass('flipped');
                updateHeight_{$uid}();
            }
        });

        $('#{$uid}')[0].updateFlipcardHeight = updateHeight_{$uid};

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
    </script>
</div>