{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='login'}
{config_load file="$lang.conf" section='shopupdate'}

{if permission('DASHBOARD_VIEW')}
    <script type="text/javascript" src="../includes/libs/flashchart/js/json/json2.js"></script>
    <script type="text/javascript" src="../includes/libs/flashchart/js/swfobject.js"></script>
    <script type="text/javascript">
    function slideToggle() {ldelim}
        if ($('#settings').is(':hidden')) {ldelim}
            xajax_getAvailableWidgetsAjax();
            $('#settings').slideDown('fast');
            $('.column_wrapper').slideUp('fast');
        {rdelim} else  {ldelim}
            $('#settings').slideUp('fast');
            $('.column_wrapper').slideDown('fast');
        {rdelim}
    {rdelim}

    function registerWidgetSettings() {ldelim}
        $('.widget_item a.add').click(function() {ldelim}
            var kWidget = $(this).attr('ref'),
                myCallback = xajax.callback.create();
            myCallback.onComplete = function(obj) {ldelim}
                window.location.href='index.php?kWidget=' + kWidget;
            {rdelim};
            xajax.call('addWidgetAjax', {ldelim} parameters: [kWidget], callback: myCallback, context: this {rdelim} );
        {rdelim});
    {rdelim}

    $(function() {ldelim}
        xajax_truncateJtllog();
    {rdelim});
    </script>

    <div class="row dashboard-conf">
        <div class="col-md-12">
             <a href="#" class='btn btn-default pull-right' onClick="slideToggle();return false;"><i class="fa fa-cogs"></i> Einstellungen</a>
        </div>
    </div>

    <div id="content" class="nomargin clearall">

        {if $oPermissionStat->nCountInValid > 0}
            <div class="alert alert-danger" role="alert">
                <strong><i class="fa fa-warning"></i> Es sind {$oPermissionStat->nCountInValid} Verzeichnisse nicht beschreibbar. Eine &Uuml;bersicht finden Sie im <a href="permissioncheck.php">Verzeichnis-Check</a>.</strong>
            </div>
        {/if}

        {if $bInstallExists && !$updateMessage}
            <div class="alert alert-warning" role="alert">
                <strong><i class="fa fa-warning"></i> {#deleteInstallDir#}</strong>
            </div>
        {/if}

        {if $bProfilerActive}
            <div class="alert alert-info" role="alert">
                <strong><i class="fa fa-info-circle"></i> Achtung! Der {$profilerType}-Profiler ist aktiv und kann zu starken Leistungseinbu&szlig;en im Shop f&uuml;hren.</strong>
            </div>
        {/if}
      
        {if isset($bTemplateDiffers) && $bTemplateDiffers && !$updateMessage}
            <div class="alert alert-info" role="alert">
                <strong><i class="fa fa-info-circle"></i> {#templateDiffers#}</strong>
            </div>
        {/if}

        <div class="widget_settings_wrapper" id="settings">
            <div class="widget_settings">
                {foreach from=$oAvailableWidget_arr item=oAvailableWidget}
                    <div class="widget_item">
                        <p class="title">{$oAvailableWidget->cTitle}</p>
                        <p class="desc">{$oAvailableWidget->cDescription}</p>
                        <a href="#" class="add" ref="{$oAvailableWidget->kWidget}"><i class="fa fa-plus-square"></i></a>
                    </div>
                {/foreach}
                {if $oAvailableWidget_arr|@count == 0}
                    <div class="widget_item">
                        <p class="title">Keine weiteren Widgets vorhanden.</p>
                    </div>
                {/if}
            </div>
        </div>

        <div class='column_wrapper clear'>
            {include file='tpl_inc/widget_container.tpl' eContainer='left'}
            {include file='tpl_inc/widget_container.tpl' eContainer='center'}
            {include file='tpl_inc/widget_container.tpl' eContainer='right'}
        </div>
    </div>

    <script type="text/javascript" src="{$currentTemplateDir}js/inettuts.js"></script>
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=#dashboard#}
    <div class="alert alert-success">
        <strong>Es stehen keine weiteren Informationen zur Verf&uuml;gung.</strong>
    </div>
{/if}

{include file='tpl_inc/footer.tpl'}
