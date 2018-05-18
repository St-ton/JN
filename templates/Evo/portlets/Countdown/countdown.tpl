<div id="cnt-{$instance->getProperty('uid')}" {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>
    <div class="opc-area" {if $isPreview}data-area-id="cnt-{$instance->getProperty('uid')}-title"{/if}>
        {if $isPreview}
            {$instance->getSubareaPreviewHtml("cnt-{$instance->getProperty('uid')}-title")}
        {else}
            {$instance->getSubareaFinalHtml("cnt-{$instance->getProperty('uid')}-title")}
        {/if}
    </div>
    <div class="row text-center">
        <div class="days col-xs-3">
            <div class="cnt-item"></div>
            <div class="cnt-unit">Tage</div>
        </div>
        <div class="hours col-xs-3">
            <div class="cnt-item"></div>
            <div class="cnt-unit">Stunden</div>
        </div>
        <div class="minutes col-xs-3">
            <div class="cnt-item"></div>
            <div class="cnt-unit">Minuten</div>
        </div>
        <div class="seconds col-xs-3">
            <div class="cnt-item"></div>
            <div class="cnt-unit">Sekunden</div>
        </div>
        <div class="expired col-xs-12">{$instance->getProperty('expired-text')}</div>
    </div>
    <div class="opc-area" {if $isPreview}data-area-id="cnt-{$instance->getProperty('uid')}-footer"{/if}>
        {if $isPreview}
            {$instance->getSubareaPreviewHtml("cnt-{$instance->getProperty('uid')}-footer")}
        {else}
            {$instance->getSubareaFinalHtml("cnt-{$instance->getProperty('uid')}-footer")}
        {/if}
    </div>
    <script>
        // Set the date we're counting down to
        var countDownDate = new Date("{$instance->getProperty('date')} {$instance->getProperty('time')}").getTime();

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
                $("#cnt-{$instance->getProperty('uid')} .expired").show();
            }

            // Display the result
            $("#cnt-{$instance->getProperty('uid')} .days .cnt-item").html(days);
            $("#cnt-{$instance->getProperty('uid')} .hours .cnt-item").html(hours);
            $("#cnt-{$instance->getProperty('uid')} .minutes .cnt-item").html(minutes);
            $("#cnt-{$instance->getProperty('uid')} .seconds .cnt-item").html(seconds);
        }, 1000);
    </script>
</div>
