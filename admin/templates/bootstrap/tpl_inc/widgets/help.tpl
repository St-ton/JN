<div class="widget-custom-data widget-help">

    <div class="row text-center">
        <div class="col-xs-6 border-right">
            <a href="https://jtl-url.de/0762z" target="_blank" rel="noopener">
                <i class="fa fa-book text-four-times text-info"></i>
                <h4>{__('doku')}</h4>
            </a>
        </div>
        <div class="col-xs-6">
            <a href="https://forum.jtl-software.de" target="_blank" rel="noopener">
                <i class="fa fa-comments-o text-four-times text-info"></i>
                <h4>{__('communityForum')}</h4>
            </a>
        </div>
    </div>
    <hr>
    <ul class="linklist">
        <li id="help_data_wrapper">
            <p class="ajax_preloader">{__('loading')}</p>
        </li>
    </ul>
</div>

<script type="text/javascript">
    $(document).ready(function () {ldelim}
        ioCall('getRemoteData', ['{$smarty.const.JTLURL_GET_SHOPHELP}', 'oHelp_arr', 'widgets/help_data.tpl', 'help_data_wrapper']);
    {rdelim});
</script>