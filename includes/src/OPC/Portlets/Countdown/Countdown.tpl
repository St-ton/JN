{$uid = $instance->getUid()}

<div id="{$uid}"
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
            <div class="cntdwn-unit">{lang key='days'}</div>
        {/col}
        {col cols=3 class='hours'}
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">{lang key='hours'}</div>
        {/col}
        {col cols=3 class='minutes'}
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">{lang key='minutes'}</div>
        {/col}
        {col cols=3 class='seconds'}
            <div class="cntdwn-item"></div>
            <div class="cntdwn-unit">{lang key='seconds'}</div>
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
    {inline_script}<script>
        function countdown_{$uid}()
        {
            let date = "{$instance->getProperty('date')} {$instance->getProperty('time')}";
            // Set the date we're counting down to
            let countDownDate = new Date(date).getTime();
            // Update the count down every 1 second
            let x = setInterval(function() {

                // Get todays date and time
                let now = new Date().getTime();

                // Find the distance between now an the count down date
                let distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                let days = Math.floor(distance / (1000 * 60 * 60 * 24));
                let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    days = 0;
                    hours = 0;
                    minutes = 0;
                    seconds = 0;
                    $("#{$uid} .expired").show();
                }

                // Display the result
                $("#{$uid} .days .cntdwn-item").html(days);
                $("#{$uid} .hours .cntdwn-item").html(hours);
                $("#{$uid} .minutes .cntdwn-item").html(minutes);
                $("#{$uid} .seconds .cntdwn-item").html(seconds);
            }, 1000);
        }

        $(countdown_{$uid});
    </script>{/inline_script}
</div>
