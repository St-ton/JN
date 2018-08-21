{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($options)}
    {assign var='inputType' value=$options[0]}
    {assign var='inputId' value=$options[1]}
    {assign var='inputName' value=$options[2]}
    {assign var='inputValue' value=$options[3]}
    {assign var='label' value=$options[4]}
    {if isset($options[5])}
        {assign var='required' value=$options[5]}
    {/if}
    {if isset($options[6])}
        {assign var='invalidReason' value=$options[6]}
    {/if}
    {if isset($options[7])}
        {assign var='autocomplete' value=$options[7]}
    {/if}
{/if}

{if !empty($required) && ($required === 'Y' || $required === true)}
    {assign var='isRequired' value=true}
{else}
    {assign var='isRequired' value=false}
{/if}

{if isset($invalidReason) && $invalidReason|strlen > 0}
    {assign var='hasError' value=true}
{elseif !empty($fehlendeAngaben) && isset($fehlendeAngaben.{$inputName})}
    {assign var='hasError' value=true}
    {if $inputName === 'email' && isset($fehlendeAngaben.email)}
        {if $fehlendeAngaben.email == 1}
            {lang assign='invalidReason' key='fillOut' section='global'}
        {elseif $fehlendeAngaben.email == 2}
            {lang assign='invalidReason' key='invalidEmail' section='global'}
        {elseif $fehlendeAngaben.email == 3}
            {lang assign='invalidReason' key='blockedEmail' section='global'}
        {elseif $fehlendeAngaben.email == 4}
            {lang assign='invalidReason' key='noDnsEmail' section='account data'}
        {elseif $fehlendeAngaben.email == 5}
            {lang assign='invalidReason' key='emailNotAvailable' section='account data'}
        {/if}
    {elseif $inputName === 'mobil' && isset($fehlendeAngaben.mobil)
        || $inputName === 'tel' && isset($fehlendeAngaben.tel)
        || $inputName === 'fax' && isset($fehlendeAngaben.fax)}
        {if $fehlendeAngaben.mobil == 1}
            {lang key='fillOut' section='global'}
        {elseif $fehlendeAngaben.mobil == 2}
            {lang key='invalidTel' section='global'}
        {/if}
    {elseif $inputName === 'vorname' && isset($fehlendeAngaben.vorname)}
        {if $fehlendeAngaben.vorname == 1}
            {lang key='fillOut' section='global'}
        {elseif $fehlendeAngaben.vorname == 2}
            {lang key='firstNameNotNumeric' section='account data'}
        {/if}
    {elseif $inputName === 'nachname' && isset($fehlendeAngaben.nachname)}
        {if $fehlendeAngaben.nachname == 1}
            {lang key='fillOut' section='global'}
        {elseif $fehlendeAngaben.nachname == 2}
            {lang key='lastNameNotNumeric' section='account data'}
        {/if}
    {else}
        {lang assign='invalidReason' key='fillOut' section='global'}
    {/if}
{else}
    {assign var='hasError' value=false}
{/if}


<div class="form-group{if $hasError} has-error{/if}">
    <label for="{$inputId}" class="control-label float-label-control">{$label}
        {if !$isRequired}
            <span class="optional"> - {lang key='conditionalFillOut' section='checkout'}</span>
        {/if}
    </label>
    <input type="{if isset($inputType)}{$inputType}{else}text{/if}" name="{$inputName}"
           value="{if isset($inputValue)}{$inputValue}{/if}" id="{$inputId}" class="form-control"
           placeholder="{if isset($placeholder)}{$placeholder}{else}{$label}{/if}"
           {if $isRequired} required{/if}
           {if !empty($autocomplete)} autocomplete="{$autocomplete}"{/if}>
    {if isset($invalidReason) && $invalidReason|strlen > 0}
        <div class="form-error-msg text-danger"><i class="fa fa-warning"></i> {$invalidReason}</div>
    {/if}
</div>
