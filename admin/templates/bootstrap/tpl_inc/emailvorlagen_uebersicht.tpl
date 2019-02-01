{include file='tpl_inc/seite_header.tpl' cTitel=__('emailTemplates') cBeschreibung=__('emailTemplatesHint') cDokuURL=__('emailTemplateURL')}
<div id="content" class="container-fluid">
    <div class="alert alert-info">
        {__('testmailsGoToEmail')}
        <strong>
            {if $Einstellungen.emails.email_master_absender}
                {$Einstellungen.emails.email_master_absender}
            {else}
                {__('noMasterEmailSpecified')}
            {/if}
        </strong>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{__('emailTemplates')}</h3>
        </div>
        <div class="panel-body table-responsive">
            <table class="list table">
                <thead>
                <tr>
                    <th class="tleft">{__('template')}</th>
                    <th>{__('type')}</th>
                    <th>{__('active')}</th>
                    <th>{__('options')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $emailvorlagen as $emailvorlage}
                    <tr>
                        <td>{$emailvorlage->cName}</td>
                        <td class="tcenter">{$emailvorlage->cMailTyp}</td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                            {if $emailvorlage->cAktiv === 'Y'}
                                <span class="label label-success success">{__('active')}</span>
                            {else}
                                {if $emailvorlage->nFehlerhaft == 1}
                                    <span class="label label-danger error">{__('faulty')}</span>
                                {else}
                                    <span class="label label-info error">{__('inactive')}</span>
                                {/if}
                            {/if}
                            </h4>
                        </td>
                        <td class="tcenter">
                            <form method="post" action="emailvorlagen.php">
                                {$jtl_token}
                                <div class="btn-group">
                                    <button type="submit" name="preview" value="{$emailvorlage->kEmailvorlage}" class="btn btn-default mail"><i class="fa fa-envelope"></i> {__('testmail')}</button>
                                    <button type="submit" name="kEmailvorlage" value="{$emailvorlage->kEmailvorlage}" class="btn btn-primary" title="{__('modify')}"><i class="fa fa-edit"></i></button>
                                    <button type="submit" name="resetConfirm" value="{$emailvorlage->kEmailvorlage}" class="btn btn-danger reset" title="{__('resetEmailTemplate')}"><i class="fa fa-refresh"></i></button>
                                </div>
                            </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    {if isset($oPluginEmailvorlage_arr) && $oPluginEmailvorlage_arr|count > 0}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{__('pluginTemplates')}</h3>
        </div>
        <div class="panel-body table-responsive">
            <table class="list table">
                <thead>
                <tr>
                    <th class="tleft">{__('template')}</th>
                    <th>{__('type')}</th>
                    <th>{__('active')}</th>
                    <th>{__('options')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $oPluginEmailvorlage_arr as $oPluginEmailvorlage}
                    <tr>
                        <td>{$oPluginEmailvorlage->cName}</td>
                        <td class="tcenter">{$oPluginEmailvorlage->cMailTyp}</td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                            {if $oPluginEmailvorlage->cAktiv === 'Y'}
                                <span class="success label label-success">{__('active')}</span>
                            {else}
                                {if $emailvorlage->nFehlerhaft == 1}
                                    <span class="label label-error error">{__('faulty')}</span>
                                {else}
                                    <span class="label label-info">{__('inactive')}</span>
                                {/if}
                            {/if}
                            </h4>
                        </td>
                        <td class="tcenter">
                            <form action="emailvorlagen.php" method="post">
                                {$jtl_token}
                                <input type="hidden" name="kPlugin" value="{$oPluginEmailvorlage->kPlugin}" />
                                <div class="btn-group">
                                    <button name="preview" value="{$oPluginEmailvorlage->kEmailvorlage}" class="btn btn-default button mail"><i class="fa fa-envelope"></i> {__('testmail')}</button>
                                    <button name="kEmailvorlage" value="{$oPluginEmailvorlage->kEmailvorlage}" class="btn btn-primary" title="{__('modify')}"><i class="fa fa-edit"></i></button>
                                    <button name="resetConfirm" value="{$oPluginEmailvorlage->kEmailvorlage}" class="btn btn-danger reset" title="{__('resetEmailTemplate')}"><i class="fa fa-refresh"></i></button>
                                </div>
                            </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    {/if}
</div>