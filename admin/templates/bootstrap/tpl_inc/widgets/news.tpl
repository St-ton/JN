<script type="text/javascript">
    $(document).ready(function () {
        ioCall('getRemoteData', ['{$smarty.const.JTLURL_GET_SHOPNEWS}', 'oNews_arr', 'widgets/news_data.tpl', 'news_data_wrapper']);
    });
</script>

<div class="widget-custom-data">
    <div id="news_data_wrapper">
        <p class="ajax_preloader">{__('loading')}</p>
    </div>
</div>