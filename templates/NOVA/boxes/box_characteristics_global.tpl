{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-characteristics-global'}
    {foreach $oBox->getItems() as $oMerkmal}
        {card
            class="box box-global-characteristics mb-4"
            id="sidebox{$oBox->getID()}-{$oMerkmal->kMerkmal}"
        }
            {block name='boxes-box-characteristics-global-content'}
                {block name='boxes-box-characteristics-global-title'}
                    <div class="productlist-filter-headline align-items-center d-flex">
                        {if !empty($oMerkmal->cBildpfadKlein) && $oMerkmal->cBildpfadKlein !== $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN}
                            <img src='{$oMerkmal->cBildURLKlein}' class='vmiddle'/>
                        {/if}
                        <span>
                            {$oMerkmal->cName}
                        </span>
                    </div>
                {/block}
                {if ($oMerkmal->cTyp === 'SELECTBOX') && $oMerkmal->oMerkmalWert_arr|@count > 1}
                    {block name='boxes-box-characteristics-global-characteristics-select'}
                        {dropdown variant="link" text=$oMerkmal->cName}
                            {foreach $oMerkmal->oMerkmalWert_arr as $oMerkmalWert}
                                {dropdownitem href=$oMerkmalWert->cSeo}
                                    {if ($oMerkmal->cTyp === 'BILD' || $oMerkmal->cTyp === 'BILD-TEXT') && $oMerkmalWert->nBildKleinVorhanden === 1}
                                        {image src=$oMerkmalWert->cBildURLKlein alt=$oMerkmalWert->cWert|escape:'quotes'}
                                    {/if}
                                    {if $oMerkmal->cTyp !== 'BILD'}
                                        {$oMerkmalWert->cWert}
                                    {/if}
                                {/dropdownitem}
                            {/foreach}
                        {/dropdown}
                    {/block}
                {else}
                    {block name='boxes-box-characteristics-global-characteristics-link'}
                        {nav vertical=true}
                            {foreach $oMerkmal->oMerkmalWert_arr as $oMerkmalWert}
                                {navitem}
                                    {link href=$oMerkmalWert->cURL
                                        class="{if $NaviFilter->hasCharacteristicValue() && isset($oMerkmalWert->kMerkmalWert) && $NaviFilter->getCharacteristicValue()->getValue() == $oMerkmalWert->kMerkmalWert}active{/if}"
                                    }
                                        {if ($oMerkmal->cTyp === 'BILD' || $oMerkmal->cTyp === 'BILD-TEXT') && $oMerkmalWert->nBildKleinVorhanden === 1}
                                           {image src=$oMerkmalWert->cBildURLKlein alt=$oMerkmalWert->cWert|escape:'quotes'}
                                        {/if}
                                        {if $oMerkmal->cTyp !== 'BILD'}
                                            {$oMerkmalWert->cWert}
                                        {/if}
                                    {/link}
                                {/navitem}
                            {/foreach}
                        {/nav}
                    {/block}
                {/if}
            {/block}
        {/card}
    {/foreach}
{/block}
