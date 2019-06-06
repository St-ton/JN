{include file='tpl_inc/seite_header.tpl' cTitel=__('contactformSubject') cBeschreibung=__('contanctformSubjectDesc')}
<div id="content">
    <form name="einstellen" method="post" action="kontaktformular.php">
        {$jtl_token}
        <input type="hidden" name="kKontaktBetreff" value="{if isset($Betreff->kKontaktBetreff)}{$Betreff->kKontaktBetreff}{/if}" />
        <input type="hidden" name="betreff" value="1" />
        <div class="panel panel-default">
            <div class="panel-header">
                <h3 class="panel-title"></h3>
            </div>
            <div class="panel-body">
                <div class="settings">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cName">{__('subject')}</label>
                        </span>
                        <input type="text" class="form-control" name="cName" id="cName" value="{if isset($Betreff->cName)}{$Betreff->cName}{/if}" tabindex="1" required />
                    </div>
                    {foreach $sprachen as $language}
                        {assign var=cISO value=$language->getIso()}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cName_{$cISO}">{__('showedName')} ({$language->getLocalizedName()})</label>
                            </span>
                            <input type="text" class="form-control" name="cName_{$cISO}" id="cName_{$cISO}" value="{if isset($Betreffname[$cISO])}{$Betreffname[$cISO]}{/if}" tabindex="2" />
                        </div>
                    {/foreach}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cMail">{__('mail')}</label>
                        </span>
                        <input type="text" class="form-control" name="cMail" id="cMail" value="{if isset($Betreff->cMail)}{$Betreff->cMail}{/if}" tabindex="3" required />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cKundengruppen">{__('restrictedToCustomerGroups')}</label>
                        </span>
                        <select class="form-control" name="cKundengruppen[]" multiple="multiple" id="cKundengruppen">
                            <option value="0" {if isset($gesetzteKundengruppen[0]) && $gesetzteKundengruppen[0] === true}selected{/if}>{__('allCustomerGroups')}</option>
                            {foreach $kundengruppen as $kundengruppe}
                                {assign var=kKundengruppe value=$kundengruppe->kKundengruppe}
                                <option value="{$kundengruppe->kKundengruppe}" {if isset($gesetzteKundengruppen[$kKundengruppe])}selected{/if}>{$kundengruppe->cName}</option>
                            {/foreach}
                        </select>
                        <span class="input-group-addon">{getHelpDesc cDesc=__('multipleChoice')}</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nSort">{__('sortNo')}</label>
                        </span>
                        <input type="text" class="form-control" name="nSort" id="nSort" value="{if isset($Betreff->nSort)}{$Betreff->nSort}{/if}" tabindex="4" />
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
            </div>
        </div>
    </form>
</div>
