{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='trennzeichen'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('Trennzeichen') cBeschreibung=__('trennzeichenDesc') cDokuURL=__('trennzeichenURL')}
<div id="content" class="container-fluid">
    <div class="block">
        {if isset($Sprachen) && $Sprachen|@count > 1}
            <form name="sprache" method="post" action="trennzeichen.php" class="inline_block">
                {$jtl_token}
                <input type="hidden" name="sprachwechsel" value="1" />
                <div class="input-group p25 left">
                    <span class="input-group-addon">
                        <label for="{__('changeLanguage')}">{__('changeLanguage')}</label>
                    </span>
                    <span class="input-group-wrap last">
                        <select id="{__('changeLanguage')}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                            {foreach $Sprachen as $sprache}
                                <option value="{$sprache->kSprache}" {if $sprache->kSprache == $smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
            </form>
        {/if}
    </div>
    <form method="post" action="trennzeichen.php">
        {$jtl_token}
        <input type="hidden" name="save" value="1" />
        <div id="settings">
            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">{__('divider')}</h3></div>
                <div class="panel-body table-responsive">
                    <table class="list table">
                    <thead>
                    <tr>
                        <th class="tleft">{__('unit')}</th>
                        <th class="tcenter">{__('countDecimals')}</th>
                        <th class="tcenter">{__('decimalsDivider')}</th>
                        <th class="tcenter">{__('thousandDivider')}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        {assign var=nDezimal_weight value="nDezimal_"|cat:$smarty.const.JTL_SEPARATOR_WEIGHT}
                        {assign var=cDezZeichen_weight value="cDezZeichen_"|cat:$smarty.const.JTL_SEPARATOR_WEIGHT}
                        {assign var=cTausenderZeichen_weight value="cTausenderZeichen_"|cat:$smarty.const.JTL_SEPARATOR_WEIGHT}
                        {if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT])}
                            <input type="hidden" name="kTrennzeichen_{$smarty.const.JTL_SEPARATOR_WEIGHT}" value="{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT]->getTrennzeichen()}" />
                        {/if}
                        <td class="tleft">{__('weight')}</td>
                        <td class="widthheight tcenter">
                            <input size="2" type="number" name="nDezimal_{$smarty.const.JTL_SEPARATOR_WEIGHT}" class="form-control{if isset($xPlausiVar_arr[$nDezimal_weight])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$nDezimal_weight])}{$xPostVar_arr[$nDezimal_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT]->getDezimalstellen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cDezZeichen_{$smarty.const.JTL_SEPARATOR_WEIGHT}" class="form-control{if isset($xPlausiVar_arr[$cDezZeichen_weight])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cDezZeichen_weight])}{$xPostVar_arr[$cDezZeichen_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT]->getDezimalZeichen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cTausenderZeichen_{$smarty.const.JTL_SEPARATOR_WEIGHT}" class="form-control{if isset($xPlausiVar_arr[$cTausenderZeichen_weight])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cTausenderZeichen_weight])}{$xPostVar_arr[$cTausenderZeichen_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_WEIGHT]->getTausenderZeichen()}{/if}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        {assign var=nDezimal_amount value="nDezimal_"|cat:$smarty.const.JTL_SEPARATOR_AMOUNT}
                        {assign var=cDezZeichen_amount value="cDezZeichen_"|cat:$smarty.const.JTL_SEPARATOR_AMOUNT}
                        {assign var=cTausenderZeichen_amount value="cTausenderZeichen_"|cat:$smarty.const.JTL_SEPARATOR_AMOUNT}
                        {if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT])}
                            <input type="hidden" name="kTrennzeichen_{$smarty.const.JTL_SEPARATOR_AMOUNT}" value="{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT]->getTrennzeichen()}" />
                        {/if}
                        <td class="tleft">{__('quantity')}</td>
                        <td class="widthheight tcenter">
                            <input size="2" type="number" name="nDezimal_{$smarty.const.JTL_SEPARATOR_AMOUNT}" class="form-control{if isset($xPlausiVar_arr[$nDezimal_amount])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$nDezimal_amount])}{$xPostVar_arr[$nDezimal_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT]->getDezimalstellen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cDezZeichen_{$smarty.const.JTL_SEPARATOR_AMOUNT}" class="form-control{if isset($xPlausiVar_arr[$cDezZeichen_amount])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cDezZeichen_amount])}{$xPostVar_arr[$cDezZeichen_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT]->getDezimalZeichen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cTausenderZeichen_{$smarty.const.JTL_SEPARATOR_AMOUNT}" class="form-control{if isset($xPlausiVar_arr[$cTausenderZeichen_amount])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cTausenderZeichen_amount])}{$xPostVar_arr[$cTausenderZeichen_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_AMOUNT]->getTausenderZeichen()}{/if}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        {assign var=nDezimal_length value="nDezimal_"|cat:$smarty.const.JTL_SEPARATOR_LENGTH}
                        {assign var=cDezZeichen_length value="cDezZeichen_"|cat:$smarty.const.JTL_SEPARATOR_LENGTH}
                        {assign var=cTausenderZeichen_length value="cTausenderZeichen_"|cat:$smarty.const.JTL_SEPARATOR_LENGTH}
                        {if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH])}
                            <input type="hidden" name="kTrennzeichen_{$smarty.const.JTL_SEPARATOR_LENGTH}" value="{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH]->getTrennzeichen()}" />
                        {/if}
                        <td class="tleft">Länge</td>
                        <td class="widthheight tcenter">
                            <input size="2" type="number" name="nDezimal_{$smarty.const.JTL_SEPARATOR_LENGTH}" class="form-control{if isset($xPlausiVar_arr[$nDezimal_length])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$nDezimal_length])}{$xPostVar_arr[$nDezimal_length]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH]->getDezimalstellen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cDezZeichen_{$smarty.const.JTL_SEPARATOR_LENGTH}" class="form-control{if isset($xPlausiVar_arr[$cDezZeichen_length])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cDezZeichen_length])}{$xPostVar_arr[$cDezZeichen_length]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH]->getDezimalZeichen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cTausenderZeichen_{$smarty.const.JTL_SEPARATOR_LENGTH}" class="form-control{if isset($xPlausiVar_arr[$cTausenderZeichen_length])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cTausenderZeichen_length])}{$xPostVar_arr[$cTausenderZeichen_length]}{else}{if isset($oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH])}{$oTrennzeichenAssoc_arr[$smarty.const.JTL_SEPARATOR_LENGTH]->getTausenderZeichen()}{/if}{/if}" />
                        </td>
                    </tr>

                    </tbody>
                </table>
                </div>
                <div class="panel-footer">
                    <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
