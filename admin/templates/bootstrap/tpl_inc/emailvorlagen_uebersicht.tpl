{include file='tpl_inc/seite_header.tpl' cTitel=__('emailTemplates') cBeschreibung=__('emailTemplatesHint') cDokuURL=__('emailTemplateURL')}
<div id="content">
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
    {include file='tpl_inc/mailtemplate_list.tpl' heading=__('emailTemplates') mailTemplates=$mailTemplates}
    {include file='tpl_inc/mailtemplate_list.tpl' heading=__('pluginTemplates') mailTemplates=$pluginMailTemplates}
</div>
