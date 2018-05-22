<div id="flp-{$instance->getProperty('uid')}" {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if} >
    {if $isPreview}<i class="fa fa-exchange"></i>{/if}
    <div class="card">
        <div class="opc-area face front" {if $isPreview}data-area-id="flp-{$instance->getProperty('uid')}-front"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("flp-{$instance->getProperty('uid')}-front")}
            {else}
                {$instance->getSubareaFinalHtml("flp-{$instance->getProperty('uid')}-front")}
            {/if}
        </div>
        <div class="opc-area face back" {if $isPreview}data-area-id="flp-{$instance->getProperty('uid')}-back"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("flp-{$instance->getProperty('uid')}-back")}
            {else}
                {$instance->getSubareaFinalHtml("flp-{$instance->getProperty('uid')}-back")}
            {/if}
        </div>
    </div>
    <script>
        function setCardHeight() {
            var max_h = 0;
            $('#flp-{$instance->getProperty("uid")} .face').each(function (e) {
                max_h = Math.max($(this).prop("scrollHeight"), max_h);
            });
            $('#flp-{$instance->getProperty("uid")} .card').css('min-height',max_h);
        }

        {if $isPreview}
            $('#flp-{$instance->getProperty("uid")} i.fa-exchange').click(function () {
                var card = $('.flip');
                if (card.hasClass('flipped')) {
                    card.removeClass('flipped');
                } else {
                    card.addClass('flipped');
                }
                setCardHeight();
            });
        {else}
            $('#flp-{$instance->getProperty("uid")}').click(function () {
                var card = $(this);
                if (card.hasClass('flipped')) {
                    card.removeClass('flipped');
                } else {
                    card.addClass('flipped');
                }
            });
        {/if}

        $(document).ready(function () {
            setCardHeight();
        });
    </script>
</div>
