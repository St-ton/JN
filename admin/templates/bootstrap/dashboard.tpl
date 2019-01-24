{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='login'}
{config_load file="$lang.conf" section='shopupdate'}

{if permission('DASHBOARD_VIEW')}
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

    <div id="content" class="nomargin">
        <div class="row">
            {include file='tpl_inc/widget_container.tpl' eContainer='left'}
            {include file='tpl_inc/widget_container.tpl' eContainer='center'}
            {include file='tpl_inc/widget_container.tpl' eContainer='right'}
        </div>
    </div>
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('dashboard')}
    <div class="alert alert-success">
        <strong>Es stehen keine weiteren Informationen zur Verfügung.</strong>
    </div>
{/if}

{include file='tpl_inc/footer.tpl'}
