<div class="form-group">
    <input type="checkbox" id="config_{$propname}" value="1" name="{$propname}"
            {if $propval == '1'}checked{/if}
            {if $required === true}required{/if}>
    <label for="config_{$propname}"
            {if !empty($propdesc.desc)}
                data-tooltip title="{$propdesc.desc|default:''}"
            {/if}>
        {$propdesc.label}
        {if !empty($propdesc.desc)}
            <i class="fas fa-info-circle fa-fw"></i>
        {/if}
    </label>
</div>

{if isset($propdesc.children)}
    <script type="module">
        import { collapseShow, collapseHide } from "{$shopUrl}/admin/opc/js/gui.js";

        const childContainer = document.getElementById('children-{$propname}');

        {if $propval === '1'}
            collapseShow(childContainer);
        {else}
            collapseHide(childContainer);
        {/if}

        document.getElementById('config_{$propname}').onchange = e => {
            if (e.target.checked === true) {
                collapseShow(childContainer);
            } else {
                collapseHide(childContainer);
            }
        };
    </script>
{/if}