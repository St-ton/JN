{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{include file='tpl_inc/header.tpl'}
<div class="backend-wrapper container">
    <div class="container-fluid">
        <div class="col-xs-12">
            {if $step === 'schritt0'}
                {include file='tpl_inc/schritt0.tpl'}
            {elseif $step === 'schritt1'}
                {include file='tpl_inc/schritt1.tpl'}
            {elseif $step === 'schritt2'}
                {include file='tpl_inc/schritt2.tpl'}
            {/if}
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}