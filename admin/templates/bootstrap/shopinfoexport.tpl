{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="shopinfoExport"}

{include file='tpl_inc/seite_header.tpl' cTitel=#shopinfoExport# cBeschreibung=#shopinfoExportDesc# cDokuURL=#shopinfoExportURL#}
<div id="content" class="container-fluid">
    {if isset($errorNoWrite) && $errorNoWrite|strlen > 0}
        <div class="alert alert-danger">{$errorNoWrite}</div>
    {/if}
    <div class="alert alert-info">
        <p><input type="text" readonly="readonly" class="form-control" value="{$URL}" /></p>
        <p class="container-fluid2">{#priceSearchEngines#}</p>
    </div>
    <p><a href="shopinfo.php" class="btn btn-info">{#download#} {#xml#}</a></p>

    <form action="shopinfo.php" method="post">
        {$jtl_token}
        <input type="hidden" name="post" value="1" />
        <input type="hidden" name="update" value="1" />

        <div id="shopinfoExport">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#shopinfoExport#} {#options#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        <tr class="tab-1_bg">
                            <td class="TD1"><label for="shopInfo_updateInterval">{#optionsIntervall#}</label>
                                <p class="smallfont">{#optionsIntervallHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_updateInterval" name="shopInfo_updateInterval" value="{if isset($objShopInfo->shopInfo_updateInterval)}{$objShopInfo->shopInfo_updateInterval}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-2_bg">
                            <td class="TD1"><label for="shopInfo_logoURL">{#logoURL#}</label>
                                <p class="smallfont">{#logoURLHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_logoURL" name="shopInfo_logoURL" value="{if isset($objShopInfo->shopInfo_logoURL)}{$objShopInfo->shopInfo_logoURL}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-1_bg">
                            <td class="TD1"><label for="shopInfo_publicMail">{#email#}</label>
                                <p class="smallfont">{#emailHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_publicMail" name="shopInfo_publicMail" value="{if isset($objShopInfo->shopInfo_publicMail)}{$objShopInfo->shopInfo_publicMail}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-2_bg">
                            <td class="TD1"><label for="shopInfo_privateMail">{#privateEmail#}</label>
                                <p class="smallfont">{#privateEmailHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_privateMail" name="shopInfo_privateMail" value="{if isset($objShopInfo->shopInfo_privateMail)}{$objShopInfo->shopInfo_privateMail}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-1_bg">
                            <td class="TD1"><label for="shopInfo_orderPhone">{#telephone#}</label>
                                <p class="smallfont">{#telephoneHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_orderPhone" name="shopInfo_orderPhone" value="{if isset($objShopInfo->shopInfo_orderPhone)}{$objShopInfo->shopInfo_orderPhone}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-2_bg">
                            <td class="TD1"><label for="shopInfo_orderFax">{#fax#}</label>
                                <p class="smallfont">{#faxHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_orderFax" name="shopInfo_orderFax" value="{if isset($objShopInfo->shopInfo_orderFax)}{$objShopInfo->shopInfo_orderFax}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-1_bg">
                            <td class="TD1"><label for="shopInfo_hotlineNumber">{#hotline#}</label>
                                <p class="smallfont">{#hotlineHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_hotlineNumber" name="shopInfo_hotlineNumber" value="{if isset($objShopInfo->shopInfo_hotlineNumber)}{$objShopInfo->shopInfo_hotlineNumber}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-2_bg">
                            <td class="TD1"><label for="shopInfo_costPerMinute">{#minHotlinePrice#}</label>
                                <p class="smallfont">{#minHotlinePriceHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_costPerMinute" name="shopInfo_costPerMinute" value="{if isset($objShopInfo->shopInfo_costPerMinute)}{$objShopInfo->shopInfo_costPerMinute}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-1_bg">
                            <td class="TD1"><label for="shopInfo_costPerCall">{#telHotlinePrice#}</label>
                                <p class="smallfont">{#telHotlinePriceHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_costPerCall" name="shopInfo_costPerCall" value="{if isset($objShopInfo->shopInfo_costPerCall)}{$objShopInfo->shopInfo_costPerCall}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-2_bg">
                            <td class="TD1"><label for="shopInfo_installment">{#installment#}</label>
                                <p class="smallfont">{#installmentHelp#}</p>
                            </td>
                            <td class="TD2">
                                <select id="shopInfo_installment" name="shopInfo_installment" class="form-control combo">
                                    <option value="N" {if !empty($objShopInfo->shopInfo_installment) && $objShopInfo->shopInfo_installment === 'N'}selected="selected"{/if}>{#no#}</option>
                                    <option value="Y" {if !empty($objShopInfo->shopInfo_installment) && $objShopInfo->shopInfo_installment === 'Y'}selected="selected"{/if}>{#yes#}</option>
                                </select></td>
                        </tr>
                        <tr class="tab-1_bg">
                            <td class="TD1"><label for="shopInfo_payItems">{#payitem#}</label>
                                <p class="smallfont">{#payitemHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_payItems" name="shopInfo_payItems"
                                       value="{if isset($objShopInfo->shopInfo_payItems)}{$objShopInfo->shopInfo_payItems}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-2_bg">
                            <td class="TD1"><label for="shopInfo_repairservice">{#repairService#}</label>
                                <p class="smallfont">{#repairServiceHelp#}</p>
                            </td>
                            <td class="TD2">
                                <select id="shopInfo_repairservice" name="shopInfo_repairservice" class="form-control combo">
                                    <option value="N" {if !empty($objShopInfo->shopInfo_repairservice) && $objShopInfo->shopInfo_repairservice === 'N'}selected="selected"{/if}>{#no#}</option>
                                    <option value="Y" {if !empty($objShopInfo->shopInfo_repairservice) && $objShopInfo->shopInfo_repairservice === 'Y'}selected="selected"{/if}>{#yes#} {#without#}</option>
                                    <option value="Y+" {if !empty($objShopInfo->shopInfo_repairservice) && $objShopInfo->shopInfo_repairservice === 'Y+'}selected="selected"{/if}>{#yes#} {#with#}</option>
                                </select></td>
                        </tr>
                        <tr class="tab-1_bg">
                            <td class="TD1"><label for="shopInfo_giftservice">{#presentService#}</label>
                                <p class="smallfont">{#presentServiceHelp#}</p>
                            </td>
                            <td class="TD2">
                                <select id="shopInfo_giftservice" name="shopInfo_giftservice" class="form-control combo">
                                    <option value="N" {if !empty($objShopInfo->shopInfo_giftservice) && $objShopInfo->shopInfo_giftservice === 'N'}selected="selected"{/if}>{#no#}</option>
                                    <option value="Y" {if !empty($objShopInfo->shopInfo_giftservice) && $objShopInfo->shopInfo_giftservice === 'Y'}selected="selected"{/if}>{#yes#} {#without#}</option>
                                    <option value="Y+" {if !empty($objShopInfo->shopInfo_giftservice) && $objShopInfo->shopInfo_giftservice === 'Y+'}selected="selected"{/if}>{#yes#} {#with#}</option>
                                </select></td>
                        </tr>
                        <tr class="tab-2_bg">
                            <td class="TD1"><label for="shopInfo_orderTracking">{#trackBack#}</label>

                                <p class="smallfont">{#trackBackHelp#}</p></td>
                            <td class="TD2">
                                <select id="shopInfo_orderTracking" name="shopInfo_orderTracking" class="form-control combo">
                                    <option value="N" {if !empty($objShopInfo->shopInfo_orderTracking) && $objShopInfo->shopInfo_orderTracking === 'N'}selected="selected"{/if}>{#no#}</option>
                                    <option value="Y" {if !empty($objShopInfo->shopInfo_orderTracking) && $objShopInfo->shopInfo_orderTracking === 'Y'}selected="selected"{/if}>{#yes#}</option>
                                </select></td>
                        </tr>
                        <tr class="tab-1_bg">
                            <td class="TD1"><label for="shopInfo_deliverTracking">{#deliverTrackBack#}</label>

                                <p class="smallfont">{#deliverTrackBackHelp#}</p></td>
                            <td class="TD2">
                                <select name="shopInfo_deliverTracking" class="form-control combo" id="shopInfo_deliverTracking">
                                    <option value="N" {if !empty($objShopInfo->shopInfo_deliverTracking) && $objShopInfo->shopInfo_deliverTracking === 'N'}selected="selected"{/if}>{#no#}</option>
                                    <option value="Y" {if !empty($objShopInfo->shopInfo_deliverTracking) && $objShopInfo->shopInfo_deliverTracking === 'Y'}selected="selected"{/if}>{#yes#}</option>
                                </select></td>
                        </tr>
                        <tr class="tab-2_bg">
                            <td class="TD1"><label for="shopInfo_installationAssistance">{#installationAssistance#}</label>
                                <p class="smallfont">{#installationAssistanceHelp#}</p>
                            </td>
                            <td class="TD2">
                                <select id="shopInfo_installationAssistance" name="shopInfo_installationAssistance" class="form-control combo">
                                    <option value="N" {if !empty($objShopInfo->shopInfo_installationAssistance) && $objShopInfo->shopInfo_installationAssistance === 'N'}selected="selected"{/if}>{#no#}</option>
                                    <option value="Y" {if !empty($objShopInfo->shopInfo_installationAssistance) && $objShopInfo->shopInfo_installationAssistance === 'Y'}selected="selected"{/if}>{#yes#} {#without#}</option>
                                    <option value="Y+" {if !empty($objShopInfo->shopInfo_installationAssistance) && $objShopInfo->shopInfo_installationAssistance === 'Y+'}selected="selected"{/if}>{#yes#} {#with#}</option>
                                </select></td>
                        </tr>
                        <tr class="tab-1_bg">
                            <td class="TD1"><label for="shopInfo_certificationItems">{#certificationItems#}</label>
                                <p class="smallfont">{#certificationItemsHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" type="text" id="shopInfo_certificationItems" name="shopInfo_certificationItems" value="{if isset($objShopInfo->shopInfo_certificationItems)}{$objShopInfo->shopInfo_certificationItems}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-2_bg">
                            <td class="TD1"><label for="shopInfo_trusteesItems">{#trusteesItems#}</label>
                                <p class="smallfont">{#trusteesItemsHelp#}</p>
                            </td>
                            <td class="TD2">
                                <input class="form-control" id="shopInfo_trusteesItems" type="text" name="shopInfo_trusteesItems" value="{if isset($objShopInfo->shopInfo_trusteesItems)}{$objShopInfo->shopInfo_trusteesItems}{/if}" />
                            </td>
                        </tr>
                        <tr class="tab-1_bg">
                            <td class="TD1"><strong>{#mapping#}</strong>
                                <p class="smallfont">{#mappingHelp#}</p>
                            </td>
                            <td class="TD2">
                                <table class="table">
                                    {foreach name=Kategorien from=$objKategorien item=Kategorie}
                                        <tr>
                                            <td class="TD1-a">
                                                <span class="smallfont"><label for="mapping-selector">{$Kategorie->katName}:</label></span>
                                            </td>
                                            <td class="TD2-a" style="vertical-align:top;">
                                                <select name="Mapping[]" class="form-control combo" id="mapping-selector">
                                                    {foreach name=Mappings from=$arMapping item=Mapping}
                                                        <option value="{$Kategorie->katID}_{$Mapping}" {if $Mapping==$Kategorie->mapName}selected="selected"{/if}>{$Mapping}</option>
                                                    {/foreach}
                                                </select>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <button type="submit" value="{#shopinfoExportSubmit#}" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}