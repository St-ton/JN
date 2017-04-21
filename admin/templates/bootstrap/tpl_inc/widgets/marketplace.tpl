<script type="text/javascript">
    $(document).ready(function() {
        ioCall(
            'getRemoteData', ['{$JTLURL_GET_SHOPMARKETPLACE}?v={$nVersionDB}', 'oMarketplace_arr',
            'widgets/marketplace_data.tpl', 'marketplace_data_wrapper']
        );
        {if $cPluginCheck|strlen > 0}
            ioCall(
                'getRemoteData',
                [
                    '{$JTLURL_GET_SHOPMARKETPLACE}', 'oMarketplaceUpdates_arr', 'widgets/marketplace_update_data.tpl',
                    'marketplace_update_data_wrapper', 'check={$cPluginCheck}', 'marketplace_showUpdateCount'
                ]
            );
        {/if}
    });
    
    function marketplace_showUpdateCount(oMarketplaceUpdates_arr) {
        if(typeof(oMarketplaceUpdates_arr) === 'object') {
            $('#marketplace_update_data_count').html(oMarketplaceUpdates_arr.length);
        }
    }
</script>

<div class="widget-custom-data widget-patch tabber" id="widget-tabber">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'uebersicht'} active{/if}">
            <a data-toggle="tab" role="tab" href="#overview">&Uuml;bersicht</a>
        </li>
        <li class="tab">
            <a data-toggle="tab" role="tab" href="#newversions">Plugins mit neuen Versionen <span id="marketplace_update_data_count" class="badge">0</span></a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="overview" class="tab-pane fade{if !isset($cTab) || $cTab == 'uebersicht'} active in{/if}">
            <div id="marketplace_data_wrapper">
                <p class="ajax_preloader">Wird geladen...</p>
            </div>
        </div>
        <div id="newversions" class="tab-pane fade">
            {if $cPluginCheck|strlen > 0}
            <div id="marketplace_update_data_wrapper">
                <p class="ajax_preloader">Wird geladen...</p>
            </div>
            {/if}
        </div>
    </div>
</div>