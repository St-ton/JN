{include file='layout/header.tpl'}
<div id="maintenance-notice" class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-wrench"></i> {lang key="maintainance" section="global"}</h3>
    </div>
    <div class="panel-body">
        {* include file="snippets/extension.tpl" *}
        {lang key="maintenanceModeActive" section="global"}
    </div>
</div>
{include file='layout/footer.tpl'}