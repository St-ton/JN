{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    <h1>{$CWunschliste->cName}{if $isCurrenctCustomer === false && isset($CWunschliste->oKunde->cVorname)} {lang key='from' section='product rating' alt_section='login,productDetails,productOverview,global,'} {$CWunschliste->oKunde->cVorname}{/if}</h1>

    {include file='snippets/extension.tpl'}

    {if $step === 'wunschliste versenden' && $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
        <h1>{lang key='wishlistViaEmail' section='login'}</h1>
        {row}
            {col cols=12}
                {block name='wishlist-email-form'}
                    {card}
                        <div class="h3">{block name='wishlist-email-form-title'}{$CWunschliste->cName}{/block}</div>
                        {block name='wishlist-email-form-body'}
                            {form method="post" action="{get_static_route id='wunschliste.php'}" name="Wunschliste"}
                                {input type="hidden" name="wlvm" value="1"}
                                {input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"}
                                {input type="hidden" name="send" value="1"}
                                {formgroup
                                    label-for="wishlist-email"
                                    label="{lang key='wishlistEmails' section='login'}{if $Einstellungen.global.global_wunschliste_max_email > 0} | {lang key='wishlistEmailCount' section='login'}: {$Einstellungen.global.global_wunschliste_max_email}{/if}"
                                }
                                    {textarea id="wishlist-email" name="email" rows="5" style="width:100%"}{/textarea}
                                {/formgroup}
                                {row}
                                    {col cols=12}
                                        {button name="action" type="submit" value="sendViaMail" variant="primary"}
                                            {lang key='wishlistSend' section='login'}
                                        {/button}
                                    {/col}
                                {/row}
                            {/form}
                        {/block}
                    {/card}
                {/block}
            {/col}
        {/row}
    {else}
        {if $hasItems === true || !empty($wlsearch)}
            <div id="wishlist-search">
                {form
                    method="post"
                    action="{get_static_route id='wunschliste.php'}"
                    name="WunschlisteSuche"
                    class="form-inline"
                }
                    {if $CWunschliste->nOeffentlich == 1 && !empty($cURLID)}
                        {input type="hidden" name="wlid" value="{$cURLID}"}
                    {else}
                        {input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"}
                    {/if}
                    {inputgroup}
                        {input name="cSuche" size="35" type="text" value="{$wlsearch}" placeholder="{lang key='wishlistSearch' section='login'}"}
                        {inputgroupaddon append=true}
                            {button name="action" value="search" type="submit"}
                                <i class="fa fa-search"></i> {lang key='wishlistSearchBTN' section='login'}
                            {/button}
                        {/inputgroupaddon}
                        {if !empty($wlsearch)}
                            {inputgroupaddon append=true}
                                {link href="{get_static_route id='wunschliste.php'}?wl={$CWunschliste->kWunschliste}" class="btn btn-secondary"}
                                    {lang key='wishlistRemoveSearch' section='login'}
                                {/link}
                            {/inputgroupaddon}
                        {/if}
                    {/inputgroup}
                {/form}
            </div>
        {/if}
        {form
            method="post"
            action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}"
            name="Wunschliste"
            class="basket_wrapper{if $hasItems === true} mt-3{/if}"
        }
            {block name='wishlist'}
                {input type="hidden" name="wla" value="1"}
                {input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"}
                {if !empty($wlsearch)}
                    {input type="hidden" name="wlsearch" value="1"}
                    {input type="hidden" name="cSuche" value="{$wlsearch}"}
                {/if}
                {if !empty($CWunschliste->CWunschlistePos_arr)}
                    {if $isCurrenctCustomer === true}
                        <div id="edit-wishlist-name">
                            {inputgroup}
                                {inputgroupaddon prepend=true}
                                    {inputgrouptext}
                                        {lang key='name' section='global'}
                                    {/inputgrouptext}
                                {/inputgroupaddon}
                                {input id="wishlist-name" type="text"placeholder="name" name="wishlistName" value="{$CWunschliste->cName}"}
                            {/inputgroup}
                        </div>
                    {/if}
                    <table class="table table-striped mb-3">
                        <thead>
                        <tr>
                            <th>{lang key='wishlistProduct' section='login'}</th>
                            <th class="d-none d-sm-table-cell">&nbsp;</th>
                            <th>{lang key='wishlistComment' section='login'}</th>
                            <th class="text-center">{lang key='wishlistPosCount' section='login'}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $CWunschliste->CWunschlistePos_arr as $wlPosition}
                            <tr>
                                <td class="img-col d-none d-sm-table-cell">
                                    {link href="{$wlPosition->Artikel->cURLFull}"}
                                        {image alt="{$wlPosition->Artikel->cName}" src="{$wlPosition->Artikel->cVorschaubildURL}" fluid=true}
                                    {/link}
                                </td>
                                <td>
                                    {link href="{$wlPosition->Artikel->cURL}"}{$wlPosition->cArtikelName}{/link}
                                    {if $wlPosition->Artikel->getOption('nShowOnlyOnSEORequest', 0) === 1}
                                        <p>{lang key='productOutOfStock' section='productDetails'}</p>
                                    {elseif $wlPosition->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                        <p>{lang key='priceOnApplication' section='global'}</p>
                                    {else}
                                        {include file='productdetails/price.tpl' Artikel=$wlPosition->Artikel tplscope='wishlist'}
                                    {/if}
                                    {foreach $wlPosition->CWunschlistePosEigenschaft_arr as $CWunschlistePosEigenschaft}
                                        {if $CWunschlistePosEigenschaft->cFreifeldWert}
                                            <p>
                                            <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                            {$CWunschlistePosEigenschaft->cFreifeldWert}{if $wlPosition->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}</p>{/if}
                                        {else}
                                            <p>
                                            <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                            {$CWunschlistePosEigenschaft->cEigenschaftWertName}{if $wlPosition->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}</p>{/if}
                                        {/if}
                                    {/foreach}
                                </td>
                                <td>
                                    {textarea readonly=($isCurrenctCustomer !== true) rows="4" name="Kommentar_{$wlPosition->kWunschlistePos}"}
                                        {$wlPosition->cKommentar}
                                    {/textarea}
                                </td>
                                {if $wlPosition->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                    <td></td>
                                    <td class="text-right">
                                        {link
                                            href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlplo={$wlPosition->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}"
                                            class="btn btn-secondary"
                                            title="{lang key='wishlistremoveItem' section='login'}"
                                        }
                                            <span class="fa fa-trash"></span>
                                        {/link}
                                    </td>
                                {else}
                                    <td>
                                        {input
                                            readonly=($isCurrenctCustomer !== true)
                                            name="Anzahl_{$wlPosition->kWunschlistePos}"
                                            class="wunschliste_anzahl" type="text"
                                            size="1"
                                            value="{$wlPosition->fAnzahl|replace_delim}"
                                        }
                                        <br/>{$wlPosition->Artikel->cEinheit}
                                    </td>
                                    <td class="text-right">
                                        {buttongroup vertical=true}
                                            {if $wlPosition->Artikel->bHasKonfig}
                                                {link href="{$wlPosition->Artikel->cURLFull}" class="btn btn-primary"
                                                   title="{lang key='product' section='global'} {lang key='configure' section='global'}"}
                                                    <span class="fa fa-cogs"></span>
                                                {/link}
                                            {else}
                                                {button
                                                    name="addToCart"
                                                    value="{$wlPosition->kWunschlistePos}"
                                                    variant="primary"
                                                    title="{lang key='wishlistaddToCart' section='login'}"
                                                }
                                                    <span class="fas fa-shopping-cart"></span>
                                                {/button}
                                            {/if}
                                            {if $isCurrenctCustomer === true}
                                                {button
                                                    name="remove" value="{$wlPosition->kWunschlistePos}"
                                                    title="{lang key='wishlistremoveItem' section='login'}"
                                                }
                                                    <span class="fa fa-trash"></span>
                                                {/button}
                                            {/if}
                                        {/buttongroup}
                                    </td>
                                {/if}
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    {row}
                        {col cols=12}
                            {buttongroup class="float-right mb-3"}
                                {button variant="primary" type="submit" name="action" value="addAllToCart"}
                                    <i class="fas fa-shopping-cart"></i> <span class="d-none d-sm-inline-block">{lang key='wishlistAddAllToCart' section='login'}</span>
                                {/button}
                                {if $isCurrenctCustomer === true}
                                    {button type="submit" title="{lang key='wishlistUpdate' section='login'}" name="action" value="update"}
                                        <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block">{lang key='wishlistUpdate' section='login'}</span>
                                    {/button}
                                    {button type="submit" name="action" value="removeAll"}
                                        <i class="fa fa-trash"></i> <span class="d-none d-sm-inline-block">{lang key='wishlistDelAll' section='login'}</span>
                                    {/button}
                                {/if}
                            {/buttongroup}
                        {/col}
                    {/row}
                {else}
                    {alert variant="info"}{lang key='noEntriesAvailable' section='global'}{/alert}
                {/if}
            {/block}
        {/form}
        {if $isCurrenctCustomer === true}
            {card class="wishlist-actions mt3"}
                <div class="h5">
                    {block name='wishlist-title'}
                        {if $CWunschliste->nOeffentlich == 1}{lang key='wishlistURL' section='login'}{/if}
                    {/block}
                </div>
                {block name='wishlist-body'}
                    {if $CWunschliste->nOeffentlich == 1}
                        {form method="post" action="{get_static_route id='wunschliste.php'}"}
                            {input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"}
                            {inputgroup}
                                {input
                                    type="text"
                                    name="wishlist-url"
                                    readonly=true
                                    value="{get_static_route id='wunschliste.php'}?wlid={$CWunschliste->cURLID}"
                                 }
                                {if $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
                                    {inputgroupaddon append=true}
                                        {button
                                            type="submit"
                                            name="action"
                                            value="sendViaMail"
                                            disabled=(!$hasItems)
                                            title="{lang key='wishlistViaEmail' section='login'}"
                                        }
                                           <i class="fa fa-envelope"></i>
                                       {/button}
                                    {/inputgroupaddon}
                                {/if}
                            {/inputgroup}
                        {/form}
                    {else}
                        {lang key='wishlistNoticePrivate' section='login'}&nbsp;
                    {/if}
                {/block}
            {/card}

            {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
                {block name='account-wishlist'}
                    {card id="wishlist"}
                        <div class="h3">
                            {block name='account-wishlist-title'}{lang key='yourWishlist' section='login'}{/block}
                        </div>
                        {block name='account-wishlist-body'}
                            {if !empty($oWunschliste_arr[0]->kWunschliste)}
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>{lang key='wishlistName' section='login'}</th>
                                        <th>{lang key='wishlistStandard' section='login'}</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oWunschliste_arr as $Wunschliste}
                                        <tr>
                                            <td>
                                                {link href="{get_static_route id='wunschliste.php'}{if $Wunschliste->nStandard != 1}?wl={$Wunschliste->kWunschliste}{/if}"}
                                                    {$Wunschliste->cName}
                                                {/link}
                                            </td>
                                            <td>{if $Wunschliste->nStandard == 1}{lang key='active' section='global'}{/if} {if $Wunschliste->nStandard == 0}{lang key='inactive' section='global'}{/if}</td>
                                            <td class="text-right">
                                                {form method="post" action="{get_static_route id='wunschliste.php'}"}
                                                    {input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"}
                                                    {input type="hidden" name="kWunschlisteTarget" value="{$Wunschliste->kWunschliste}"}
                                                    {buttongroup size="sm"}
                                                        {if $Wunschliste->nStandard != 1}
                                                            {button name="action" value="setAsDefault"}
                                                                <i class="fa fa-check"></i> <span class="d-none d-sm-inline-block">{lang key='wishlistStandard' section='login'}</span>
                                                            {/button}
                                                        {else}
                                                            {button variant="success" class="disabled" name="action" value="setAsDefault"}
                                                                <i class="fa fa-check"></i> <span class="d-none d-sm-inline-block">{lang key='wishlistStandard' section='login'}</span>
                                                            {/button}
                                                        {/if}
                                                        {if $Wunschliste->nOeffentlich == 1}
                                                            {button
                                                                type="submit"
                                                                name="action"
                                                                value="setPrivate"
                                                                title="{lang key='wishlistSetPrivate' section='login'}"
                                                            }
                                                                <i class="fa fa-eye-slash"></i> <span class="d-none d-sm-inline-block">{lang key='wishlistSetPrivate' section='login'}</span>
                                                            {/button}
                                                        {/if}
                                                        {if $Wunschliste->nOeffentlich == 0}
                                                            {button type="submit" name="action" value="setPublic"}
                                                                <i class="fa fa-eye"></i> <span class="d-none d-sm-inline-block">{lang key='wishlistSetPublic' section='login'}</span>
                                                            {/button}
                                                        {/if}
                                                        {button type="submit" variant="danger" name="action" value="delete" title="{lang key='wishlisteDelete' section='login'}"}
                                                            <i class="fa fa-trash"></i>
                                                        {/button}
                                                    {/buttongroup}
                                                {/form}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            {/if}
                            {form method="post" action="{get_static_route id='wunschliste.php'}" class="form-inline"}
                                {input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"}
                                {inputgroup}
                                    {input name="cWunschlisteName" type="text" class="input-sm" placeholder="{lang key='wishlistAddNew' section='login'}" size="25"}
                                    {inputgroupaddon append=true}
                                        {button type="submit" variant="default" size="sm" name="action" value="createNew"}
                                            <i class="fa fa-save"></i> {lang key='wishlistSaveNew' section='login'}
                                        {/button}
                                    {/inputgroupaddon}
                                {/inputgroup}
                            {/form}
                        {/block}
                    {/card}
                {/block}
            {/if}
        {/if}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
