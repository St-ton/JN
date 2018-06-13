<div id="{$instance->getProperty('uid')}" {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if} >
    {if $isPreview}<i class="fa fa-exchange"></i>{/if}
    <div class="card">
        <div class="{if $isPreview}opc-area {/if}face front" {if $isPreview}data-area-id="flp-front"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("flp-front")}
            {else}
                {$instance->getSubareaFinalHtml("flp-front")}
            {/if}
        </div>
        <div class="{if $isPreview}opc-area {/if}face back" {if $isPreview}data-area-id="flp-back"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("flp-back")}
            {else}
                {$instance->getSubareaFinalHtml("flp-back")}
            {/if}
        </div>
    </div>
    <script>
        function setCardHeight(id) {
            var max_h = 0;
            $('#'+id+' .face > div').each(function (e) {
                max_h = Math.max($(this).prop("scrollHeight"), max_h);
            });
            $('#'+id+' .card').css('min-height',max_h+'px');
        }

        {if $isPreview}
            $('#{$instance->getProperty("uid")} i.fa-exchange').click(function () {
                var card = $('#{$instance->getProperty("uid")}');
                if (card.hasClass('flipped')) {
                    card.removeClass('flipped');
                } else {
                    card.addClass('flipped');
                }
                setCardHeight('{$instance->getProperty("uid")}');
            });
        {else}
            $('#{$instance->getProperty("uid")}').click(function () {
                var card = $(this);
                if (card.hasClass('flipped')) {
                    card.removeClass('flipped');
                } else {
                    card.addClass('flipped');
                }
                setCardHeight('{$instance->getProperty("uid")}');
            });
        {/if}

        $(document).ready(function () {
            setCardHeight('{$instance->getProperty("uid")}');
        });
    </script>
</div>
