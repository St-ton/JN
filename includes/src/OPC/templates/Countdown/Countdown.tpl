<div id="{$instance->getUid()}"
     class="countdown {$instance->getAnimationClass()}"
     style="{$instance->getStyleString()}"
     {$instance->getAnimationDataAttributeString()}
     {if $isPreview}{$instance->getDataAttributeString()}{/if}
>
    <div class="opc-area" {if $isPreview}data-area-id="cntdwn-title"{/if}>
        {if $isPreview}
            {$instance->getSubareaPreviewHtml('cntdwn-title')}
        {else}
            {$instance->getSubareaFinalHtml('cntdwn-title')}
        {/if}
    </div>
    {row class='text-center'}
        {col cols=3 class='days'}
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">Tage</div>
        {/col}
        {col cols=3 class='hours'}
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">Stunden</div>
        {/col}
        {col cols=3 class='minutes'}
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">Minuten</div>
        {/col}
        {col cols=3 class='seconds'}
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">Sekunden</div>
        {/col}
        {col cols=12 class='expired'}
            {$instance->getProperty('expired-text')}
        {/col}
    {/row}
    <div class="opc-area" {if $isPreview}data-area-id="cntdwn-footer"{/if}>
        {if $isPreview}
            {$instance->getSubareaPreviewHtml('cntdwn-footer')}
        {else}
            {$instance->getSubareaFinalHtml('cntdwn-footer')}
        {/if}
    </div>
    <script>
        function countdown(date) {
            // Set the date we're counting down to
            var countDownDate = new Date(date).getTime();
            // Update the count down every 1 second
            var x = setInterval(function() {

                // Get todays date and time
                var now = new Date().getTime();

                // Find the distance between now an the count down date
                var distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    days = 0;
                    hours = 0;
                    minutes = 0;
                    seconds = 0;
                    $("#{$instance->getUid()} .expired").show();
                }

                // Display the result
                $("#{$instance->getUid()} .days .cntdwn-item").html(days);
                $("#{$instance->getUid()} .hours .cntdwn-item").html(hours);
                $("#{$instance->getUid()} .minutes .cntdwn-item").html(minutes);
                $("#{$instance->getUid()} .seconds .cntdwn-item").html(seconds);
            }, 1000);
        }

        countdown("{$instance->getProperty('date')} {$instance->getProperty('time')}");
    </script>
</div>
