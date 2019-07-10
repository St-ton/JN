{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='login'}
{config_load file="$lang.conf" section='shopupdate'}

{if 'DASHBOARD_VIEW'|permission}
    <script type="text/javascript" src="../includes/libs/flashchart/js/json/json2.js"></script>
    <script type="text/javascript" src="../includes/libs/flashchart/js/swfobject.js"></script>
    <script type="text/javascript" src="{$currentTemplateDir}js/html.sortable.js"></script>
    <script type="text/javascript" src="{$currentTemplateDir}js/dashboard.js"></script>
    <script type="text/javascript">

    function addWidget(kWidget) {
        ioCall(
            'addWidget', [kWidget], function () {
                window.location.href='index.php?kWidget=' + kWidget;
            }
        );
    }

    $(function() {
        ioCall('truncateJtllog');
    });
    </script>

    <div id="content">
        <div class="row">
            <div class="col">
                <h1 class="content-header-headline">{__('dashboard')}</h1>
            </div>
            <div class="col-auto ml-auto">
                <div class="dropleft d-inline-block">
                    <button class="btn btn-link btn-lg px-0" type="button" id="helpcenter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="icon-hover">
                            <span class="fa fa-cog"></span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right min-w-lg" aria-labelledby="helpcenter">
                        {include file='tpl_inc/widget_selector.tpl' oAvailableWidget_arr=$oAvailableWidget_arr}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            {include file='tpl_inc/widget_container.tpl' eContainer='left'}
            {include file='tpl_inc/widget_container.tpl' eContainer='center'}
            {include file='tpl_inc/widget_container.tpl' eContainer='right'}
        </div>
    </div>
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('dashboard')}
    <div class="alert alert-success">
        <strong>{__('noMoreInfo')}</strong>
    </div>
{/if}

{include file='tpl_inc/footer.tpl'}
