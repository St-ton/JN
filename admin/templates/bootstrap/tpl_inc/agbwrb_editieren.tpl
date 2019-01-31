{include file='tpl_inc/seite_header.tpl' cTitel=__('agbwrb') cBeschreibung=__('trustedShopInfo')}
<div id="content" class="container-fluid">
    <div class="ocontainer">
        <form name="umfrage" method="post" action="agbwrb.php">
            {$jtl_token}
            <input type="hidden" name="agbwrb" value="1" />
            <input type="hidden" name="agbwrb_editieren_speichern" value="1" />
            <input type="hidden" name="kKundengruppe" value="{if isset($kKundengruppe)}{$kKundengruppe}{/if}" />

            {if isset($oAGBWRB->kText) && $oAGBWRB->kText > 0}
                <input type="hidden" name="kText" value="{if isset($oAGBWRB->kText)}{$oAGBWRB->kText}{/if}" />
            {/if}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{__('agbwrb')} {foreach $Sprachen as $sprache}{if $sprache->kSprache == $smarty.session.kSprache}({$sprache->cNameDeutsch}){/if}{/foreach}{if isset($kKundengruppe)} {__('forCustomerGroup')} {$kKundengruppe} {__('edit')}{/if}</h3>
                </div>
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
                <div class="panel-footer">
                    <button name="agbwrbsubmit" type="submit" value="{__('agbwrbSave')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('agbwrbSave')}</button>
                </div>
            </div>
        </form>
    </div>
</div>