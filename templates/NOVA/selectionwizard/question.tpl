{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{listgroupitem class="selection-wizard-question {if $nQuestion > $AWA->getCurQuestion()}disabled{/if}"}
    <div class="h5 selection-wizard-question-heading">
        {$oFrage->cFrage}
        {if $nQuestion < $AWA->getCurQuestion()}
            {link href="#" data=["value"=>"{$nQuestion}"] class="fa fa-edit question-edit"}{/link}
        {/if}
    </div>
    {if $nQuestion < $AWA->getCurQuestion()}
        <span class="selection-wizard-answer">
            {assign var='oWert' value=$AWA->getSelectedValue($nQuestion)}
            {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['B', 'BT']:true && $oWert->cBildpfadKlein !== ''}
                {image src="{$imageBaseURL}{$oWert->cBildpfadKlein}" alt="{$oWert->getValue()}" title="{$oWert->getValue()}"}
            {/if}
            {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['T', 'BT', 'S']:true}
                {$oWert->getValue()}
            {/if}
        </span>
    {elseif $nQuestion === $AWA->getCurQuestion()}
        {if $AWA->getConf('auswahlassistent_anzeigeformat') === 'S'}
            <label for="kMerkmalWert-{$nQuestion}" class="sr-only">{lang key='pleaseChoose' section='global'}</label>
            {select id="kMerkmalWert-{$nQuestion}"}
                <option value="-1">{lang key='pleaseChoose' section='global'}</option>
                {foreach $oFrage->oWert_arr as $oWert}
                    {if isset($oWert->nAnzahl)}
                        <option value="{$oWert->kMerkmalWert}">
                            {$oWert->getValue()}
                            {if $AWA->getConf('auswahlassistent_anzahl_anzeigen') === 'Y'}
                                ({$oWert->nAnzahl})
                            {/if}
                        </option>
                    {/if}
                {/foreach}
            {/select}
        {else}
            {foreach $oFrage->oWert_arr as $oWert}
                {if isset($oWert->nAnzahl)}
                    {link class="selection-wizard-answer no-deco" href="#" data=["value"=>"{$oWert->kMerkmalWert}"]}
                        {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['B', 'BT']:true && $oWert->cBildpfadKlein !== ''}
                            {image src="{$imageBaseURL}{$oWert->cBildpfadKlein}" alt="{$oWert->getValue()}" title="{$oWert->getValue()}"}
                        {/if}
                        {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['T', 'BT']:true}
                            {$oWert->getValue()}
                            {if $AWA->getConf('auswahlassistent_anzahl_anzeigen') === 'Y'}
                                <span class="badge-pill badge-light mr-3">{$oWert->getCount()}</span>
                            {/if}
                        {/if}
                    {/link}
                {/if}
            {/foreach}
        {/if}
    {elseif $nQuestion > $AWA->getCurQuestion()}
        {if $AWA->getConf('auswahlassistent_anzeigeformat') === 'S'}
            <label for="kMerkmalWert-{$nQuestion}" class="sr-only">{lang key='pleaseChoose' section='global'}</label>
            {select id="kMerkmalWert-{$nQuestion}" disabled="disabled"}
                <option value="-1">{lang key='pleaseChoose' section='global'}</option>
            {/select}
        {else}
            {foreach $oFrage->oWert_arr as $oWert}
                {if $oWert->getCount() > 0}
                    <span class="selection-wizard-answer">
                        {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['B', 'BT']:true && $oWert->cBildpfadKlein !== ''}
                            {image src="{$imageBaseURL}{$oWert->cBildpfadKlein}" alt="{$oWert->getValue()}" title="{$oWert->getValue()}"}
                        {/if}
                        {if $AWA->getConf('auswahlassistent_anzeigeformat')|in_array:['T', 'BT']:true}
                            {$oWert->getValue()}
                        {/if}
                    </span>
                {/if}
            {/foreach}
        {/if}
    {/if}
{/listgroupitem}
