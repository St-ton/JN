<div id="{$propname}" class="input-group colorpicker-component {$class}">
    <input class="form-control" name="{$propname}" value="{$prop}" {if $required}required{/if}>
    <span class="input-group-addon"><i></i></span>
</div>
<script>
    $('#{$propname}').colorpicker({
        format: 'rgba',
        colorSelectors: {
            '#ffffff': '#ffffff',
            '#777777': '#777777',
            '#337ab7': '#337ab7',
            '#5cb85c': '#5cb85c',
            '#5cbcf6': '#5cbcf6',
            '#f0ad4e': '#f0ad4e',
            '#d9534f': '#d9534f',
            '#000000': '#000000',
        }
    });
</script>