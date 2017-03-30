<script type="text/javascript">
    $(document).ready(function () {ldelim}
        ioCall('getRemoteDataIO', ['{$JTLURL_GET_SHOPPATCH}?v={$nVersionDB}', 'oPatch_arr', 'widgets/patch_data.tpl', 'patch_data_wrapper']);
    {rdelim});
</script>

<div class="widget-custom-data widget-patch">
    <div id="patch_data_wrapper">
        <p class="ajax_preloader">Wird geladen...</p>
    </div>
</div>