{include file='tpl_inc/seite_header.tpl' cTitel=__('agbwrb') cBeschreibung=__('agbWrbInfo')}
<div id="content">
    <div class="ocontainer">
        <form name="umfrage" method="post" action="agbwrb.php">
            {$jtl_token}
            <input type="hidden" name="agbwrb" value="1" />
            <input type="hidden" name="agbwrb_editieren_speichern" value="1" />
            <input type="hidden" name="kKundengruppe" value="{if isset($kKundengruppe)}{$kKundengruppe}{/if}" />

            {if isset($oAGBWRB->kText) && $oAGBWRB->kText > 0}
                <input type="hidden" name="kText" value="{if isset($oAGBWRB->kText)}{$oAGBWRB->kText}{/if}" />
            {/if}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('agbwrb')} {foreach $sprachen as $language}{if $language->getId() === $smarty.session.kSprache}({$language->getLocalizedName()}){/if}{/foreach}{if isset($kKundengruppe)} {__('forCustomerGroup')} {$kKundengruppe} {__('edit')}{/if}</div>
                </div>
                <div class="card-body">
                    <table class="list table" id="formtable">
                        <tr>
                            <td><label for="cAGBContentText">{__('agb')} ({__('text')}):</label></td>
                            <td>
                                <textarea id="cAGBContentText" class="form-control" name="cAGBContentText" rows="15" cols="60">{if isset($oAGBWRB->cAGBContentText)}{$oAGBWRB->cAGBContentText}{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cAGBContentHtml">{__('agb')} ({__('html')}):</label></td>
                            <td>
                                <textarea id="cAGBContentHtml" name="cAGBContentHtml" class="form-control ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cAGBContentHtml)}{$oAGBWRB->cAGBContentHtml}{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cWRBContentText">{__('wrb')} ({__('text')}):</label></td>
                            <td>
                                <textarea id="cWRBContentText" class="form-control" name="cWRBContentText" rows="15" cols="60">{if isset($oAGBWRB->cWRBContentText)}{$oAGBWRB->cWRBContentText}{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cWRBContentHtml">{__('wrb')} ({__('html')}):</label></td>
                            <td>
                                <textarea id="cWRBContentHtml" name="cWRBContentHtml" class="form-control ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cWRBContentHtml)}{$oAGBWRB->cWRBContentHtml}{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cWRBFormContentText">{__('wrbform')} ({__('text')}):</label></td>
                            <td>
                                <textarea id="cWRBFormContentText" class="form-control" name="cWRBFormContentText" rows="15" cols="60">{if isset($oAGBWRB->cWRBFormContentText)}{$oAGBWRB->cWRBFormContentText}{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cWRBFormContentHtml">{__('wrbform')} ({__('html')}):</label></td>
                            <td>
                                <textarea id="cWRBFormContentHtml" name="cWRBFormContentHtml" class="form-control ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cWRBFormContentHtml)}{$oAGBWRB->cWRBFormContentHtml}{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cDSEContentText">{__('dse')} ({__('text')}):</label></td>
                            <td>
                                <textarea id="cDSEContentText" class="form-control" name="cDSEContentText" rows="15" cols="60">{if isset($oAGBWRB->cDSEContentText)}{$oAGBWRB->cDSEContentText}{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cDSEContentHtml">{__('dse')} ({__('html')}):</label></td>
                            <td>
                                <textarea id="cDSEContentHtml" name="cDSEContentHtml" class="form-control ckeditor" rows="15" cols="60">{if isset($oAGBWRB->cDSEContentHtml)}{$oAGBWRB->cDSEContentHtml}{/if}</textarea>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="agbwrbsubmit" type="submit" value="{__('save')}" class="btn btn-primary btn-block"><i class="fa fa-save"></i> {__('save')}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
