{$uid = $instance->getUid()}

<div id="{$uid}"
     class="opc-Countdown {$instance->getAnimationClass()}"
     style="{$instance->getStyleString()}"
     {$instance->getAnimationDataAttributeString()}
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
        $(() => {
            let until = new Date("{$instance->getProperty('until')}");
            let countDownDate = until.getTime();

            let timeout = setInterval(() => {
                let now      = new Date().getTime();
                let distance = countDownDate - now;
                let days     = Math.floor(distance / (1000 * 60 * 60 * 24));
                let hours    = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes  = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds  = Math.floor((distance % (1000 * 60)) / 1000);

                if (distance <= 0) {
                    clearInterval(timeout);
                    days    = 0;
                    hours   = 0;
                    minutes = 0;
                    seconds = 0;
                    $("#{$uid} .expired").show();
                }

                $("#{$uid} .days .cntdwn-item").html(days);
                $("#{$uid} .hours .cntdwn-item").html(hours);
                $("#{$uid} .minutes .cntdwn-item").html(minutes);
                $("#{$uid} .seconds .cntdwn-item").html(seconds);
            }, 1000);
        });
    </script>{/inline_script}
</div>
