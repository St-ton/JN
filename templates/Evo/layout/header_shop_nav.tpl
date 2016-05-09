{strip}
<ul class="header-shop-nav nav navbar-nav force-float horizontal pull-right">
    {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
        <li class="language-dropdown dropdown visible-xs">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-language"></i>
                <span class="caret"></span>
            </a>
            <ul id="language-dropdown-small" class="dropdown-menu dropdown-menu-right">
            {foreach from=$smarty.session.Sprachen item=Sprache}
                {if $Sprache->kSprache == $smarty.session.kSprache}
                    <li class="active lang-{$lang} visible-xs"><a>{if $lang === 'ger'}{$Sprache->cNameDeutsch}{else}{$Sprache->cNameEnglisch}{/if}</a></li>
                {/if}
            {/foreach}
            {foreach from=$smarty.session.Sprachen item=oSprache}
                {if $oSprache->kSprache != $smarty.session.kSprache}
                    <li>
                        <a href="{$oSprache->cURL}" class="link_lang {$oSprache->cISO}" rel="nofollow">{if $lang === 'ger'}{$oSprache->cNameDeutsch}{else}{$oSprache->cNameEnglisch}{/if}</a>
                    </li>
                {/if}
            {/foreach}
            </ul>
        </li>
    {/if}
    {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1}
        <li class="currency-dropdown dropdown visible-xs">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">{if $smarty.session.Waehrung->cName === 'EUR'}<i class="fa fa-eur" title="{$smarty.session.Waehrung->cName}"></i>{else}{$smarty.session.Waehrung->cName}{/if} <span class="caret"></span></a>
            <ul id="currency-dropdown-small" class="dropdown-menu dropdown-menu-right">
                {foreach from=$smarty.session.Waehrungen item=oWaehrung}
                    <li>
                        <a href="{$oWaehrung->cURL}" rel="nofollow">{$oWaehrung->cName}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}
    {block name="navbar-productsearch"}
        <li id="search">
            <form action="navi.php" method="GET">
                <div class="input-group">
                    <input name="qs" type="text" class="form-control ac_input" placeholder="{lang key='search'}" autocomplete="off" />
                    <span class="input-group-addon">
                        <button type="submit">
                            <span class="fa fa-search"></span>
                        </button>
                    </span>
                </div>
            </form>
        </li>
    {/block}{* /navbar-productsearch *}

    {nocache}
    {block name="navbar-top-user"}
    {*  ACCOUNT *}
    <li class="dropdown hidden-xs">
        {if empty($smarty.session.Kunde->kKunde)}
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-user"></i> <span class="hidden-xs hidden-sm"> {lang key="login" section="global"} </span> <i class="caret"></i>
            </a>
            <ul id="login-dropdown" class="dropdown-menu dropdown-menu-right">
                <li>
                    <form action="{$ShopURLSSL}/jtl.php" method="post" class="form">
                        {$jtl_token}
                        <fieldset id="quick-login">
                            <div class="form-group">
                                <input type="text" name="email" id="email_quick" class="form-control" placeholder="{lang key='emailadress'}"/>
                            </div>
                            <div class="form-group">
                                <input type="password" name="passwort" id="password_quick" class="form-control" placeholder="{lang key='password'}"/>
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="login" value="1"/>
                                {if !empty($oRedirect->cURL)}
                                    {foreach name=parameter from=$oRedirect->oParameter_arr item=oParameter}
                                        <input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}" />
                                    {/foreach}
                                    <input type="hidden" name="r" value="{$oRedirect->nRedirect} "/>
                                    <input type="hidden" name="cURL" value="{$oRedirect->cURL}" />
                                {/if}
                                <button type="submit" id="submit-btn" class="btn btn-primary btn-block">{lang key="login" section="global"}</button>
                            </div>
                        </fieldset>
                    </form>
                </li>
                <li><a href="pass.php" rel="nofollow">{lang key="forgotPassword" section="global"}</a></li>
                <li>
                    <a href="registrieren.php">{lang key="newHere" section="global"} {lang key="registerNow" section="global"}</a>
                </li>
            </ul>
        {else}
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <span class="fa fa-user"></span>
                <span class="hidden-xs hidden-sm hidden-md"> {lang key="hello" section="global"} {if $smarty.session.Kunde->cAnrede === 'w'}{$Anrede_w}{elseif $smarty.session.Kunde->cAnrede === 'm'}{$Anrede_m}{/if} {$smarty.session.Kunde->cNachname}</span>
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li>
                    <a href="jtl.php">{lang key="myAccount" section="global"}</a>
                </li>
                <li>
                    <a href="jtl.php?logout=1">{lang key="logOut" section="global"}</a>
                </li>
            </ul>
        {/if}
    </li>
    {*  ACCOUNT END *}

    {*  COMPARE LIST *}
    {if isset($smarty.session.Vergleichsliste) && $smarty.session.Vergleichsliste->oArtikel_arr|count > 1}
    <li class="hidden-xs compare-list-menu">
        <a href="vergleichsliste.php" title="{lang key="compare" sektion="global"}"{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'} target="_blank"{/if} class="link_to_comparelist{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"><span class="fa fa-tasks"></span><sup class="badge"><em>{$smarty.session.Vergleichsliste->oArtikel_arr|count}</em></sup></a>
    </li>
    {/if}
    {*  COMPARE LIST *}

    {*  WISH LIST *}
    {if isset($smarty.session.Wunschliste->kWunschliste) && $smarty.session.Wunschliste->CWunschlistePos_arr|count > 0}
    <li class="hidden-xs wish-list-menu">
        <a href="jtl.php?wl={$smarty.session.Wunschliste->kWunschliste}" title="{lang key="goToWishlist" sektion="global"}"><span class="fa fa-heart"></span><sup class="badge"><em>{$smarty.session.Wunschliste->CWunschlistePos_arr|count}</em></sup></a>
    </li>
    {/if}
    {*  WISH LIST *}

    {*  CART *}
    <li class="hidden-xs cart-menu dropdown{if $WarenkorbArtikelanzahl >= 1} items{/if}{if $nSeitenTyp == 3} current{/if}" data-toggle="basket-items">
        {include file='basket/cart_dropdown_label.tpl'}
    </li>
    {*  CART END *}
    {/block}{* /navbar-top-user *}
    {/nocache}
</ul>{* /shop-nav *}
{/strip}