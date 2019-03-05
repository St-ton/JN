{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    <div class="h1">
        {if $isCurrenctCustomer === false && isset($CWunschliste->oKunde->cVorname)}
            {$CWunschliste->cName} {lang key='from' section='product rating' alt_section='login,productDetails,productOverview,global,'} {$CWunschliste->oKunde->cVorname}
        {else}
            {lang key='myWishlists'}
        {/if}
    </div>

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
                {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
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
        {if $isCurrenctCustomer === true}
            {row}
            {col cols=1}
            {dropdown variant="light" text="<i class='fas fa-ellipsis-v'></i>"}
            {dropdownitem}
            {button variant='light' data=["toggle" => "collapse", "target"=>"#edit-wishlist-name"]}
            {lang key='rename'}
            {/button}
            {/dropdownitem}
            {dropdownitem}
            {form
            method="post"
            action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}"
            name="Wunschliste"
            }
            {input type="hidden" name="wla" value="1"}
            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
            {button type="submit" variant="light" name="action" value="removeAll"}
            {lang key='wlRemoveAllProducts' section='wishlist'}
            {/button}
            {/form}
            {/dropdownitem}
            {dropdownitem}
            {form
            method="post"
            action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}"
            name="Wunschliste"
            }
            {input type="hidden" name="wla" value="1"}
            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
            {button type="submit" variant="light" name="action" value="addAllToCart"}
            {lang key='wishlistAddAllToCart' section='login'}
            {/button}
            {/form}
            {/dropdownitem}
            {dropdownitem}
            {form method="post" action="{get_static_route id='wunschliste.php'}"}
            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
            {input type="hidden" name="kWunschlisteTarget" value=$CWunschliste->kWunschliste}
            {button type="submit" variant="light" name="action" value="delete"}
            {lang key='wlDelete' section='wishlist'}
            {/button}
            {/form}
            {/dropdownitem}
            {if $CWunschliste->nStandard != 1}
                {dropdownitem}
                {form method="post" action="{get_static_route id='wunschliste.php'}"}
                {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                {input type="hidden" name="kWunschlisteTarget" value=$CWunschliste->kWunschliste}
                {button type="submit"
                name="action"
                variant="light"
                value="setAsDefault"
                title="{lang key='setAsStandardWishlist' section='wishlist'}"
                data=["toggle" => "tooltip", "placement" => "bottom"]
                }
                {lang key='activate'}
                {/button}
                {/form}
                {/dropdownitem}
            {/if}
            {dropdownitem}
            {button variant='light' data=["toggle" => "collapse", "target"=>"#create-new-wishlist"]}
            {lang key='wishlistAddNew' section='login'}
            {/button}
            {/dropdownitem}
            {/dropdown}
            {/col}
            {col cols=5}
            {dropdown id='wlName' variant='light' text=$CWunschliste->cName}
            {foreach $oWunschliste_arr as $wishlist}
                {dropdownitem href="{get_static_route id='wunschliste.php'}{if $wishlist->nStandard != 1}?wl={$wishlist->kWunschliste}{/if}" rel="nofollow" }
                {$wishlist->cName}
                {/dropdownitem}
            {/foreach}
            {/dropdown}
            {/col}
            {col cols=6}
            {if $hasItems === true || !empty($wlsearch)}
                <div id="wishlist-search">
                    {form
                    method="post"
                    action="{get_static_route id='wunschliste.php'}"
                    name="WunschlisteSuche"
                    class="form-inline"
                    }
                    {if $CWunschliste->nOeffentlich == 1 && !empty($cURLID)}
                        {input type="hidden" name="wlid" value=cURLID}
                    {else}
                        {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                    {/if}
                    {inputgroup}
                    {input name="cSuche" size="35" type="text" value=$wlsearch placeholder="{lang key='wishlistSearch' section='login'}"}
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
            {/col}
            {/row}
            <hr class="mt-2 mb-2">
            {row}
            {col cols=10}
            {form method="post" action="{get_static_route id='wunschliste.php'}"}
            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
            {input type="hidden" name="kWunschlisteTarget" value=$CWunschliste->kWunschliste}
            {if $CWunschliste->nOeffentlich == 1}
                {button
                type="submit"
                name="action"
                value="setPrivate"
                title="{lang key='wishlistSetPrivate' section='login'}"
                data=["toggle" => "tooltip", "placement" => "bottom"]
                }
                    <i class="fa fa-eye-slash"></i>
                {/button}
                {lang key='wishlistNoticePublic' section='login'}&nbsp;
            {elseif $CWunschliste->nOeffentlich == 0}
                {button
                type="submit"
                name="action"
                value="setPublic"
                title="{lang key='wishlistSetPublic' section='login'}"
                data=["toggle" => "tooltip", "placement" => "bottom"]
                }
                    <i class="fa fa-eye"></i>
                {/button}
                {lang key='wishlistNoticePrivate' section='login'}&nbsp;
            {/if}
            {/form}
            {/col}
            {col cols=2}
            {count($CWunschliste->CWunschlistePos_arr)} {lang key='products'}
            {/col}
            {/row}

            {if $CWunschliste->nOeffentlich == 1}
                {row class='mt-3'}
                {col cols=12}
                {form method="post" action="{get_static_route id='wunschliste.php'}"}
                {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
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
                {/col}
                {/row}
            {/if}

            <hr class="mt-2 mb-2">
            {row}
            {col cols=12}
            {collapse id="edit-wishlist-name" visible=false class='mb-3'}
            {form
            method="post"
            action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}"
            name="Wunschliste"
            }
            {input type="hidden" name="wla" value="1"}
            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
            {input type="hidden" name="action" value="update"}
            {inputgroup}
            {inputgroupaddon prepend=true}
            {inputgrouptext}
            {lang key='name' section='global'}
            {/inputgrouptext}
            {/inputgroupaddon}
            {input id="wishlist-name" type="text" placeholder="name" name="WunschlisteName" value=$CWunschliste->cName}
            {inputgroupaddon append=true}
            {input type="submit" value="{lang key='rename'}"}
            {/inputgroupaddon}
            {/inputgroup}
            {/form}
            {/collapse}
            {/col}
            {/row}
            {row}
            {col cols=12}
            {collapse id="create-new-wishlist" visible=false class='mb-3'}
            {form method="post" action="{get_static_route id='wunschliste.php'}"}
            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
            {inputgroup}
            {input name="cWunschlisteName" type="text" class="input-sm" placeholder="{lang key='wishlistAddNew' section='login'}" size="35"}
            {inputgroupaddon append=true}
            {button type="submit" size="sm" name="action" value="createNew"}
                <i class="fa fa-save"></i> {lang key='wishlistSaveNew' section='login'}
            {/button}
            {/inputgroupaddon}
            {/inputgroup}
            {/form}
            {/collapse}
            {/col}
            {/row}
        {/if}



        {form
        method="post"
        action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}"
        name="Wunschliste"
        class="basket_wrapper{if $hasItems === true} mt-3{/if}"
        }
        {block name='wishlist'}
            {input type="hidden" name="wla" value="1"}
            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
            {if !empty($wlsearch)}
                {input type="hidden" name="wlsearch" value="1"}
                {input type="hidden" name="cSuche" value=$wlsearch}
            {/if}
            {if !empty($CWunschliste->CWunschlistePos_arr)}
                {foreach $CWunschliste->CWunschlistePos_arr as $wlPosition}
                    {row class='row-table mb-3'}
                    {col cols=12 md=2 class="text-center d-none d-sm-block"}
                    {link href=$wlPosition->Artikel->cURLFull}
                    {image alt=$wlPosition->Artikel->cName src=$wlPosition->Artikel->cVorschaubildURL fluid=true}
                    {/link}
                    {/col}
                    {col cols=12 md=4 class="product-detail text-center text-md-left"}
                    {link href=$wlPosition->Artikel->cURL}{$wlPosition->cArtikelName}{/link}
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
                    {/col}
                    {col cols=12 md=4}
                    {textarea readonly=($isCurrenctCustomer !== true) rows="4" name="Kommentar_{$wlPosition->kWunschlistePos}"}{$wlPosition->cKommentar}{/textarea}
                    {/col}
                    {if $wlPosition->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                        {col class='text-right'}
                        {link
                        href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlplo={$wlPosition->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}"
                        class="btn btn-secondary"
                        title="{lang key='wishlistremoveItem' section='login'}"
                        }
                            <span class="fa fa-trash"></span>
                        {/link}
                        {/col}
                    {else}
                        {col md=1}
                        {input
                        readonly=($isCurrenctCustomer !== true)
                        name="Anzahl_{$wlPosition->kWunschlistePos}"
                        class="wunschliste_anzahl" type="text"
                        size="1"
                        value="{$wlPosition->fAnzahl|replace_delim}"
                        }
                            <br/>{$wlPosition->Artikel->cEinheit}
                        {/col}
                        {col md=1 class='text-right'}
                        {buttongroup vertical=true}
                        {if $wlPosition->Artikel->bHasKonfig}
                            {link href=$wlPosition->Artikel->cURLFull class="btn btn-primary"
                            title="{lang key='product' section='global'} {lang key='configure' section='global'}"}
                                <span class="fa fa-cogs"></span>
                            {/link}
                        {else}
                            {button
                            name="addToCart"
                            value=$wlPosition->kWunschlistePos
                            variant="primary"
                            title="{lang key='wishlistaddToCart' section='login'}"
                            }
                                <span class="fas fa-shopping-cart"></span>
                            {/button}
                        {/if}
                        {if $isCurrenctCustomer === true}
                            {button
                            type="submit"
                            name="remove" value=$wlPosition->kWunschlistePos
                            title="{lang key='wishlistremoveItem' section='login'}"
                            }
                                <span class="fa fa-trash"></span>
                            {/button}
                        {/if}
                        {/buttongroup}
                        {/col}
                    {/if}
                    {/row}
                {/foreach}
                {row}
                {col cols=12}
                {buttongroup class="float-right mb-3"}
                {if $isCurrenctCustomer === true}
                    {button type="submit" title="{lang key='wishlistUpdate' section='login'}" name="action" value="update"}
                        <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block">{lang key='wishlistUpdate' section='login'}</span>
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
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
