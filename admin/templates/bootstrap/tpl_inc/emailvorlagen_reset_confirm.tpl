{include file='tpl_inc/seite_header.tpl' cTitel=__('emailTemplates')}
<div id="content" class="container-fluid">
    <form method="post" action="emailvorlagen.php">
        {$jtl_token}
        <input type="hidden" name="resetEmailvorlage" value="1" />
        {if $mailTemplate->getPluginID() > 0}
            <input type="hidden" name="kPlugin" value="{$mailTemplate->getPluginID()}" />
        {/if}
        <input type="hidden" name="kEmailvorlage" value="{$mailTemplate->getID()}" />
        <div class="alert alert-danger">
            <p><strong>{__('danger')}</strong>: {__('resetEmailTemplate')}</p>

            <p>{{__('sureResetEmailTemplate')}|sprintf:{__('name_'|cat:$mailTemplate->getModuleID())}}</p>
        </div>
        <div class="btn-group">
            <button name="resetConfirmJaSubmit" type="submit" value="{__('yes')}" class="btn btn-danger">
                <i class="fal fa-check text-success"></i> {__('yes')}
            </button>
            <button name="resetConfirmNeinSubmit" type="submit" value="{__('no')}" class="btn btn-info">
                <i class="fa fa-close"></i> {__('no')}
            </button>
        </div>
    </form>
</div>
