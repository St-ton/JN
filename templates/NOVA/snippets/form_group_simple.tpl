{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-form-group-simple'}
    {if !empty($options)}
        {assign var=inputType value=$options[0]}
        {assign var=inputId value=$options[1]}
        {assign var=inputName value=$options[2]}
        {assign var=inputValue value=$options[3]}
        {assign var=label value=$options[4]}
        {if isset($options[5])}
            {assign var=required value=$options[5]}
        {/if}
        {if isset($options[6])}
            {assign var=invalidReason value=$options[6]}
        {/if}
        {if isset($options[7])}
            {assign var=autocomplete value=$options[7]}
        {/if}
    {/if}

    {assign var=isRequired value=!empty($required) && ($required === 'Y' || $required === true)}

    {assign var=inputNameTmp value=$inputName|replace:"register[shipping_address][":""|replace:"]":""}

    {if isset($invalidReason) && $invalidReason|strlen > 0}
        {assign var=hasError value=true}
    {elseif !empty($fehlendeAngaben) && isset($fehlendeAngaben.{$inputNameTmp})}
        {assign var=errCode value=$fehlendeAngaben.{$inputNameTmp}}
        {assign var=hasError value=true}
        {if $inputNameTmp === 'email'}
            {if $errCode == 1}
                {lang assign='invalidReason' key='fillOut'}
            {elseif $errCode == 2}
                {lang assign='invalidReason' key='invalidEmail'}
            {elseif $errCode == 3}
                {lang assign='invalidReason' key='blockedEmail'}
            {elseif $errCode == 4}
                {lang assign='invalidReason' key='noDnsEmail' section='account data'}
            {elseif $errCode == 5}
                {lang assign='invalidReason' key='emailNotAvailable' section='account data'}
            {/if}
        {elseif $inputNameTmp === 'mobil'
        || $inputNameTmp === 'tel'
        || $inputNameTmp === 'fax'}
            {if $errCode == 1}
                {lang assign='invalidReason' key='fillOut'}
            {elseif $errCode == 2}
                {lang assign='invalidReason' key='invalidTel'}
            {/if}
        {elseif $inputNameTmp === 'vorname'}
            {if $errCode == 1}
                {lang assign='invalidReason' key='fillOut'}
            {elseif $errCode == 2}
                {lang assign='invalidReason' key='firstNameNotNumeric' section='account data'}
            {/if}
        {elseif $inputNameTmp === 'nachname'}
            {if $errCode == 1}
                {lang assign='invalidReason' key='fillOut'}
            {elseif $errCode == 2}
                {lang assign='invalidReason' key='lastNameNotNumeric' section='account data'}
            {/if}
        {elseif $inputNameTmp === 'geburtstag'}
            {if $errCode == 1}
                {lang assign='invalidReason' key='fillOut'}
            {elseif $errCode == 2}
                {lang assign='invalidReason' key='invalidDateformat'}
            {elseif $errCode == 3}
                {lang assign='invalidReason' key='invalidDate'}
            {/if}
        {elseif $inputNameTmp === 'www'}
            {if $errCode == 1}
                {lang assign='invalidReason' key='fillOut'}
            {elseif $errCode == 2}
                {lang assign='invalidReason' key='invalidURL'}
            {/if}
        {else}
            {lang assign='invalidReason' key='fillOut'}
        {/if}
    {else}
        {assign var=hasError value=false}
    {/if}

    {formgroup label-for=$inputId
        label="{$label}{if !$isRequired}<span class='optional'> - {lang key='optional'}</span>{/if}"
        class="{if $hasError}has-error{/if}"}
        {block name='snippets-form-group-simple-error'}
            {if $hasError}
                <div class="form-error-msg text-danger">{$invalidReason}</div>
            {/if}
        {/block}
        {if isset($inputType) && $inputType === 'number'}
            {block name='snippets-form-group-simple-input-number'}
                {inputgroup}
                    {inputgroupaddon append=false data=["type"=>"minus", "field"=>"quant[1]"]}
                        -
                    {/inputgroupaddon}
                        {input type=$inputType
                            name=$inputName
                            value="{if isset($inputValue)}{$inputValue}{/if}"
                            id=$inputId
                            placeholder="{if isset($placeholder)}{$placeholder}{else}{$label}{/if}"
                            required=$isRequired
                            autocomplete="{if !empty($autocomplete)}{$autocomplete}{/if}"
                        }
                    {inputgroupaddon append=true data=["type"=>"minus", "field"=>"quant[1]"]}
                        +
                    {/inputgroupaddon}
                {/inputgroup}
            {/block}
        {else}
            {block name='snippets-form-group-simple-input-other'}
                {input type="{if isset($inputType)}{$inputType}{else}text{/if}"
                    name=$inputName
                    value="{if isset($inputValue)}{$inputValue}{/if}"
                    id=$inputId
                    placeholder="{if isset($placeholder)}{$placeholder}{else} {/if}"
                    required=$isRequired
                    autocomplete="{if !empty($autocomplete)}{$autocomplete}{/if}"
                }
            {/block}
        {/if}
    {/formgroup}
{/block}
