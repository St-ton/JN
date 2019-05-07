{assign var=template value=__('template')}
{assign var=modify value=__('modify')}
{include file='tpl_inc/seite_header.tpl'
    cTitel=$template|cat: ' '|cat:$mailTemplate->getName()|cat: ' '|cat:$modify
    cBeschreibung=__('emailTemplateModifyHint')}
<div id="content" class="container-fluid">
    <form name="vorlagen_aendern" method="post" action="emailvorlagen.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="Aendern" value="1" />
        {if $mailTemplate->getPluginID() > 0}
            <input type="hidden" name="kPlugin" value="{$mailTemplate->getPluginID()}" />
        {/if}
        <input type="hidden" name="kEmailvorlage" value="{$mailTemplate->getID()}" />
        <div id="settings" class="settings">
            {if $mailTemplate->getModuleID() !== 'core_jtl_anbieterkennzeichnung'}
                <div class="settings panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{__('settings')}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cAktiv">{__('emailActive')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cAktiv" id="cAktiv" class="form-control">
                                    <option value="Y"{if $mailTemplate->getActive()} selected{/if}>
                                        {__('yes')}
                                    </option>
                                    <option value="N"{if !$mailTemplate->getActive()} selected{/if}>
                                        {__('no')}
                                    </option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cEmailOut">{__('emailOut')}</label>
                            </span>
                            <input class="form-control" id="cEmailOut" name="cEmailOut" type="text" value="{if isset($mailConfig.cEmailOut)}{$mailConfig.cEmailOut|escape}{/if}" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cEmailSenderName">{__('emailSenderName')}</label>
                            </span>
                            <input class="form-control" id="cEmailSenderName" name="cEmailSenderName" type="text" value="{if isset($mailConfig.cEmailSenderName)}{$mailConfig.cEmailSenderName|escape}{/if}" />
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cEmailCopyTo">{__('emailCopyTo')} </label>
                            </span>
                            <input class="form-control" id="cEmailCopyTo" name="cEmailCopyTo" type="text" value="{if isset($mailConfig.cEmailCopyTo)}{$mailConfig.cEmailCopyTo|escape}{/if}" />
                            <span class="input-group-addon">{__('multipleDividedColon')} </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cMailTyp">{__('mailType')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cMailTyp" id="cMailTyp" class="form-control">
                                    <option value="text/html" {if $mailTemplate->getType() === 'text/html'}selected{/if}>
                                        {__('textHtml')}
                                    </option>
                                    <option value="text" {if $mailTemplate->getType() === 'text'}selected{/if}>{__('text')}
                                    </option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAKZ">{__('emailAddAKZ')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nAKZ" name="nAKZ" class="form-control">
                                    <option value="0"{if $mailTemplate->getShowAKZ() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowAKZ() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAFK">{__('emailAddAGB')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nAFK" name="nAGB" class="form-control">
                                    <option value="0"{if $mailTemplate->getShowAGB() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowAGB() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nWRB">{__('emailAddWRB')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nWRB" name="nWRB" class="form-control">
                                    <option value="0"{if $mailTemplate->getShowWRB() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowWRB() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nWRBForm">{__('emailAddWRBForm')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nWRBForm" name="nWRBForm" class="form-control">
                                    <option value="0"{if $mailTemplate->getShowWRBForm() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowWRBForm() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nDSE">{__('emailAddDSE')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nDSE" name="nDSE" class="form-control">
                                    <option value="0"{if $mailTemplate->getShowDSE() === false} selected{/if}>{__('no')}</option>
                                    <option value="1"{if $mailTemplate->getShowDSE() === true} selected{/if}>{__('yes')}</option>
                                </select>
                            </span>
                        </div>
                    </div>
                </div>
            {else}
                <input type="hidden" name="cEmailActive" value="Y" />
                <input type="hidden" name="cMailTyp" value="text/html" />
            {/if}
            <div class="box_info panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{__('placeholder')} ({__('example')})</h3>
                </div>
                <div class="panel-body">
                    <code>
                    <span class="elem">
                        <span class="name">{ldelim}$Kunde->cAnrede{rdelim}</span><br />
                        <span class="for">{__('maleShort')}</span><br />
                    </span>
                    <span class="elem">
                        <span class="name">{ldelim}$Kunde->cAnredeLocalized{rdelim}</span><br />
                        <span class="for">{__('mister')}</span><br />
                    </span>
                    <span class="elem">
                        <span class="name">{ldelim}$Kunde->cVorname{rdelim}</span><br />
                        <span class="for">{__('firstNameStub')}</span><br />
                    </span>
                    <span class="elem">
                        <span class="name">{ldelim}$Kunde->cNachname{rdelim}</span><br />
                        <span class="for">{__('lastNameStub')}</span><br />
                    </span>
                    <span class="elem">
                        <span class="name">{ldelim}$Firma->cName{rdelim}</span><br />
                        <span class="for">{__('companyStub')}</span><br />
                    </span>
                    </code>
                </div>
            </div>
            {foreach $availableLanguages as $language}
                <div class="box_info panel panel-default">
                    {assign var=kSprache value=$language->kSprache}
                    <div class="panel-heading">
                        <h3 class="panel-title">{__('content')} {$language->cNameDeutsch}</h3>
                    </div>
                    <div class="panel-body">
                        {if $mailTemplate->getModuleID() !== 'core_jtl_anbieterkennzeichnung'}
                            <div class="item well">
                                <div class="name"><label for="cBetreff_{$kSprache}">{__('subject')}</label></div>
                                <div class="for">
                                    <input class="form-control" style="width:400px" type="text" name="cBetreff_{$kSprache}" id="cBetreff_{$kSprache}"
                                           value="{$mailTemplate->getSubject($kSprache)}" tabindex="1" />
                                </div>
                            </div>
                        {/if}
                        <div class="item well">
                            <div class="name"><label for="cContentHtml_{$kSprache}">{__('mailHtml')}</label></div>
                            <div class="for">
                                <textarea class="codemirror smarty" id="cContentHtml_{$kSprache}" name="cContentHtml_{$kSprache}"
                                          style="width:99%" rows="20">{$mailTemplate->getHTML($kSprache)}</textarea>
                            </div>
                        </div>
                        <div class="item well">
                            <div class="name"><label for="cContentText_{$kSprache}">{__('mailText')}</label></div>
                            <div class="for">
                                <textarea class="codemirror smarty" id="cContentText_{$kSprache}" name="cContentText_{$kSprache}"
                                          style="width:99%" rows="20">{$mailTemplate->getText($kSprache)}</textarea>
                            </div>
                        </div>
                        {if $mailTemplate->getAttachments($kSprache)|@count > 0}
                            <div class="item">
                                <div class="name">
                                    {__('currentFiles')}
                                    (<a href="emailvorlagen.php?kEmailvorlage={$mailTemplate->getID()}&kS={$kSprache}&a=pdfloeschen&token={$smarty.session.jtl_token}{if $mailTemplate->getPluginID() > 0}&kPlugin={$mailTemplate->getPluginID()}{/if}">{__('deleteAll')}</a>)
                                </div>
                                <div class="for">
                                    {foreach $mailTemplate->getAttachmentNames($kSprache) as $cPDF}
                                        {assign var=i value=$cPDF@iteration-1}
                                        <div>
                                            <span class="pdf">{$cPDF}.pdf</span>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        {/if}
                        {if $mailTemplate->getModuleID() !== 'core_jtl_anbieterkennzeichnung'}
                            {$attachments = $mailTemplate->getAttachmentNames($kSprache)}
                            {section name=anhaenge loop=4 start=1 step=1}
                                <div class="item well">
                                    <div class="name">
                                        <label for="cPDFS_{$smarty.section.anhaenge.index}_{$kSprache}">{__('pdf')} {$smarty.section.anhaenge.index}</label>
                                    </div>
                                    <div class="for">
                                        {math equation="x-y" x=$smarty.section.anhaenge.index y=1 assign=loopdekr}
                                        <label for="cPDFNames_{$smarty.section.anhaenge.index}_{$kSprache}">{__('filename')}</label>
                                        <input id="cPDFNames_{$smarty.section.anhaenge.index}_{$kSprache}"
                                           name="cPDFNames_{$kSprache}[]"
                                           type="text"
                                           value="{if isset($attachments[$loopdekr + 1])}{$attachments[$loopdekr + 1]}{/if}"
                                           class="form-control{if count($cFehlerAnhang_arr) > 0}{if isset($cFehlerAnhang_arr[$kSprache][$smarty.section.anhaenge.index]) && $cFehlerAnhang_arr[$kSprache][$smarty.section.anhaenge.index] == 1} fieldfillout{/if}{/if}" />
                                        <input id="cPDFS_{$smarty.section.anhaenge.index}_{$kSprache}" name="cPDFS_{$kSprache}[]" type="file" class="form-control" maxlength="2097152" style="margin-top:5px;" />
                                    </div>
                                </div>
                            {/section}
                        {/if}
                        </div>
                    </div>
            {/foreach}
            <div class="btn-group">
                <button type="submit" name="continue" value="0" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                <button type="submit" name="continue" value="1" class="btn btn-default">{__('saveAndContinue')}</button>
                <a href="emailvorlagen.php" title="{__('cancel')}" class="btn btn-danger"><i class="fa fa-exclamation"></i> {__('cancel')}</a>
            </div>
        </div>
    </form>
    {if $mailTemplate->getID() > 0}
        {getRevisions type='mail' key=$mailTemplate->getID() show=['cContentText','cContentHtml'] secondary=true data=$mailTemplate->viewCompat()}
    {/if}
</div>
