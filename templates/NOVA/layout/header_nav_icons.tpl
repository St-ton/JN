{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{navitem tag="div" class="d-flex d-md-none mr-2" aria=['expanded' => 'false'] data=['toggle' => 'collapse', 'target' => '#nav-search-collapse']}
    <i class="fas fa-search"></i>
{/navitem}
{navitem tag="div" class="mr-2 mr-md-3" aria=['expanded' => 'false'] data=['toggle' => 'collapse', 'target' => '#nav-account-collapse']}
    <span class="fas fa-user mr-md-3"></span>
    {if empty($smarty.session.Kunde->kKunde)}
        <span class="d-none d-md-inline-block">{lang key='login'}</span>
    {else}
        <span class="d-none d-md-inline-block">{lang key='hello'}, {if $smarty.session.Kunde->cAnrede === 'w'}{lang key='salutationW'}{elseif $smarty.session.Kunde->cAnrede === 'm'}{lang key='salutationM'}{/if} {$smarty.session.Kunde->cNachname}</span>
    {/if}
{/navitem}
{include file='layout/header_shop_nav_compare.tpl'}
{include file='layout/header_shop_nav_wish.tpl'}
{include file='layout/header_shop_nav_account.tpl'}
<div class="cart-icon-dropdown">
    {include file='basket/cart_dropdown_label.tpl'}
</div>