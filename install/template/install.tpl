{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/test_result.tpl'}
<div class="backend-wrapper container">
    <div class="container-fluid">
        <div class="col-xs-12 print-container">
            {if isset($step) && $step === 'schritt0'}
                {include file='tpl_inc/schritt0.tpl'}
            {elseif isset($step) && $step === 'schritt1'}
                {include file='tpl_inc/schritt1.tpl'}
            {elseif isset($step) && $step === 'schritt2'}
                {include file='tpl_inc/schritt2.tpl'}
            {elseif isset($versionAbort) && $versionAbort === true}
                {include file='tpl_inc/schritt0.tpl'}
            {/if}
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
