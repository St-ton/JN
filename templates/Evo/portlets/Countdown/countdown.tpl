<div id="{$instance->getProperty('uid')}" {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>
    <div class="opc-area" {if $isPreview}data-area-id="cntdwn-title"{/if}>
        {if $isPreview}
            {$instance->getSubareaPreviewHtml("cntdwn-title")}
        {else}
            {$instance->getSubareaFinalHtml("cntdwn-title")}
        {/if}
    </div>
    <div class="row text-center">
        <div class="days col-xs-3">
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">Tage</div>
        </div>
        <div class="hours col-xs-3">
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">Stunden</div>
        </div>
        <div class="minutes col-xs-3">
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">Minuten</div>
        </div>
        <div class="seconds col-xs-3">
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">Sekunden</div>
        </div>
        <div class="expired col-xs-12">{$instance->getProperty('expired-text')}</div>
    </div>
    <div class="opc-area" {if $isPreview}data-area-id="cntdwn-footer"{/if}>
        {if $isPreview}
            {$instance->getSubareaPreviewHtml("cntdwn-footer")}
        {else}
            {$instance->getSubareaFinalHtml("cntdwn-footer")}
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
                    $("#{$instance->getProperty('uid')} .expired").show();
                }

                // Display the result
                $("#{$instance->getProperty('uid')} .days .cntdwn-item").html(days);
                $("#{$instance->getProperty('uid')} .hours .cntdwn-item").html(hours);
                $("#{$instance->getProperty('uid')} .minutes .cntdwn-item").html(minutes);
                $("#{$instance->getProperty('uid')} .seconds .cntdwn-item").html(seconds);
            }, 1000);
        }

        countdown("{$instance->getProperty('date')} {$instance->getProperty('time')}");
    </script>
</div>
