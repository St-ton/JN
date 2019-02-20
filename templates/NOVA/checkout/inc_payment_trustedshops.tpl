{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($oTrustedShops->oKaeuferschutzProdukte->item) && $oTrustedShops->oKaeuferschutzProdukte->item|@count > 0 && $Einstellungen.trustedshops.trustedshops_nutzen === 'Y'}
    <div id="ts-buyerprotection">
        {row}
            {col cols=10}
                {if $oTrustedShops->oKaeuferschutzProdukte->item|@count > 1}
                    {checkbox id="trusted_bTS" name="bTS" value="1"}
                        <span class="content">
                            <span class="title">{lang key='trustedShopsBuyerProtection'} ({lang key='trustedShopsRecommended'})</span>
                        </span>
                    {/checkbox}

                    {select name="cKaeuferschutzProdukt"}
                        {foreach $oTrustedShops->oKaeuferschutzProdukte->item as $oItem}
                            <option value="{$oItem->tsProductID}"{if $oTrustedShops->cVorausgewaehltesProdukt == $oItem->tsProductID} selected{/if}>{lang key='trustedShopsBuyerProtection'} {lang key='trustedShopsTo'} {$oItem->protectedAmountDecimalLocalized}
                                ({$oItem->grossFeeLocalized} {$oItem->cFeeTxt})
                            </option>
                        {/foreach}
                    {/select}
                {elseif $oTrustedShops->oKaeuferschutzProdukte->item|@count == 1}
                    {checkbox id="trusted_bTS" name="bTS" value="1"}
                        <span class="content">
                            <span class="title">{lang key='trustedShopsBuyerProtection'} {lang key='trustedShopsTo'} {$oTrustedShops->oKaeuferschutzProdukte->item[0]->protectedAmountDecimalLocalized}
                                ({$oTrustedShops->oKaeuferschutzProdukte->item[0]->grossFeeLocalized} {$oTrustedShops->oKaeuferschutzProdukte->item[0]->cFeeTxt})
                            </span>
                        </span>
                    {/checkbox}
                    {input name="cKaeuferschutzProdukt" type="hidden" value=$oTrustedShops->oKaeuferschutzProdukte->item[0]->tsProductID}
                {/if}
                <p class="small text-muted mt-2">
                    {assign var=cISOSprache value=$oTrustedShops->cISOSprache}
                    {if !empty($oTrustedShops->cBoxText[$cISOSprache])}
                        {$oTrustedShops->cBoxText[$cISOSprache]}
                    {else}
                        {assign var=cISOSprache value='default'}
                        {$oTrustedShops->cBoxText[$cISOSprache]}
                    {/if}
                </p>
            {/col}
            {col cols=2}
                {link href=$oTrustedShops->cLogoURL target="_blank"}
                    {image src="{$ShopURL}/{$PFAD_GFX_TRUSTEDSHOPS}ts_logo.jpg" alt="{lang key='trustedShopsBuyerProtection'}" fluid=true}
                {/link}
            {/col}
        {/row}
    </div>
{/if}
