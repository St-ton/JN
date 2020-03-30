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

{assign var="inputNameTmp" value=$inputName|replace:"register[shipping_address][":""|replace:"]":""}

{if isset($invalidReason) && $invalidReason|strlen > 0}
    {assign var='hasError' value=true}
{elseif !empty($fehlendeAngaben) && isset($fehlendeAngaben.{$inputNameTmp})}
    {assign var='errCode' value=$fehlendeAngaben.{$inputNameTmp}}
    {assign var='hasError' value=true}
    {if $inputNameTmp === 'email'}
        {if $errCode == 1}
            {lang assign='invalidReason' key='fillOut' section='global'}
        {elseif $errCode == 2}
            {lang assign='invalidReason' key='invalidEmail' section='global'}
        {elseif $errCode == 3}
            {lang assign='invalidReason' key='blockedEmail' section='global'}
        {elseif $errCode == 4}
            {lang assign='invalidReason' key='noDnsEmail' section='account data'}
        {elseif $errCode == 5}
            {lang assign='invalidReason' key='emailNotAvailable' section='account data'}
        {/if}
    {elseif $inputNameTmp === 'mobil'
        || $inputNameTmp === 'tel'
        || $inputNameTmp === 'fax'}
        {if $errCode == 1}
            {lang assign='invalidReason' key='fillOut' section='global'}
        {elseif $errCode == 2}
            {lang assign='invalidReason' key='invalidTel' section='global'}
        {/if}
    {elseif $inputNameTmp === 'vorname'}
        {if $errCode == 1}
            {lang assign='invalidReason' key='fillOut' section='global'}
        {elseif $errCode == 2}
            {lang assign='invalidReason' key='firstNameNotNumeric' section='account data'}
        {/if}
    {elseif $inputNameTmp === 'nachname'}
        {if $errCode == 1}
            {lang assign='invalidReason' key='fillOut' section='global'}
        {elseif $errCode == 2}
            {lang assign='invalidReason' key='lastNameNotNumeric' section='account data'}
        {/if}
    {elseif $inputNameTmp === 'geburtstag'}
        {if $errCode == 1}
            {lang assign='invalidReason' key='fillOut' section='global'}
        {elseif $errCode == 2}
            {lang assign='invalidReason' key='invalidDateformat' section='global'}
        {elseif $errCode == 3}
            {lang assign='invalidReason' key='invalidDate' section='global'}
        {/if}
    {elseif $inputNameTmp === 'www'}
        {if $errCode == 1}
            {lang assign='invalidReason' key='fillOut' section='global'}
        {elseif $errCode == 2}
            {lang assign='invalidReason' key='invalidURL' section='global'}
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
            <span class="optional"> - {lang key='optional'}</span>
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
