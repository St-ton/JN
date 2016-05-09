<script type="text/javascript">
    $(document).ready(function () {ldelim}
        xajax_getRemoteDataAjax('{$JTLURL_GET_SHOPHELP}', 'oHelp_arr', 'widgets/help_data.tpl', 'help_data_wrapper');
    {rdelim});
</script>

<div class="widget-custom-data widget-help">
    <ul class="linklist">
        <li>
            <img src="{$currentTemplateDir}gfx/layout/wikipedia.gif" alt="" title="" />
            <a href="http://guide.jtl-software.de/jtl/JTL-Shop:Installation:Erste_Schritte" target="_blank">JTL-Shop - Erste Schritte</a>
        </li>
        <div id="help_data_wrapper">
            <p class="ajax_preloader">Wird geladen...</p>
        </div>
    </ul>
</div>