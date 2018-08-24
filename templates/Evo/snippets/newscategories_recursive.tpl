{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($oNewsKategorie_arr)}
    {foreach $oNewsKategorie_arr as $oNewsKategorie}
        {if $selectedCat === (int)$oNewsKategorie->kNewsKategorie}{assign var='oCurNewsCat' value=$oNewsKategorie}{/if}
        <option value="{$oNewsKategorie->kNewsKategorie}"
            {if isset($selectedCat)}
                {if is_array($selectedCat)}
                    {foreach $selectedCat as $singleCat}
                        {if $singleCat->kNewsKategorie == $oNewsKategorie->kNewsKategorie} selected{/if}
                    {/foreach}
                {elseif $selectedCat === (int)$oNewsKategorie->kNewsKategorie} selected{/if}
            {/if}>
            {for $j=1 to $i}-&nbsp;{/for}{$oNewsKategorie->cName}
        </option>
        {if isset($oNewsKategorie->children)}
            {include file='snippets/newscategories_recursive.tpl' i=$i+1 oNewsKategorie_arr=$oNewsKategorie->children selectedCat=$selectedCat}
        {/if}
    {/foreach}
{/if}
