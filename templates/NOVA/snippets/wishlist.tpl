{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {include file='snippets/extension.tpl'}

    {if $step === 'wunschliste versenden' && $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
        <h1>{lang key='wishlistViaEmail' section='login'}</h1>
        {row}
        {col cols=12}
        {block name='wishlist-email-form'}
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
        {/block}
        {/col}
        {/row}
    {else}
        <div class="h1">
            {if $isCurrenctCustomer === false && isset($CWunschliste->oKunde->cVorname)}
                {$CWunschliste->cName} {lang key='from' section='product rating' alt_section='login,productDetails,productOverview,global,'} {$CWunschliste->oKunde->cVorname}
            {else}
                {lang key='myWishlists'}
            {/if}
        </div>

        {if $isCurrenctCustomer === true}
            {row}
                {col cols=2 md=1 class="mb-2"}
                    {dropdown variant="link" class="no-chevron wishlist-options" text="<i class='fas fa-ellipsis-v'></i>"}
                        {dropdownitem class="text-center"}
                            {button type="submit" variant="link" data=["toggle" => "collapse", "target"=>"#edit-wishlist-name"]}
                                {lang key='rename'}
                            {/button}
                        {/dropdownitem}
                        {dropdownitem class="text-center"}
                            {form
                                method="post"
                                action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}"
                                name="Wunschliste"
                            }
                                {input type="hidden" name="wla" value="1"}
                                {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                {input type="hidden" name="action" value="removeAll"}
                                {button type="submit" variant="link"}
                                    {lang key='wlRemoveAllProducts' section='wishlist'}
                                {/button}
                            {/form}
                        {/dropdownitem}
                        {dropdownitem class="text-center"}
                            {form
                                method="post"
                                action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}"
                                name="Wunschliste"
                            }
                                {input type="hidden" name="wla" value="1"}
                                {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                {input type="hidden" name="action" value="addAllToCart"}
                                {button type="submit" variant="link"}
                                    {lang key='wishlistAddAllToCart' section='login'}
                                {/button}
                            {/form}
                        {/dropdownitem}
                        {dropdownitem class="text-center"}
                            {form method="post" action="{get_static_route id='wunschliste.php'}"}
                                {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                {input type="hidden" name="kWunschlisteTarget" value=$CWunschliste->kWunschliste}
                                {input type="hidden" name="action" value="delete"}
                                {button type="submit" variant="link"}
                                    {lang key='wlDelete' section='wishlist'}
                                {/button}
                            {/form}
                        {/dropdownitem}
                        {if $CWunschliste->nStandard != 1}
                            {dropdownitem class="text-center"}
                                {form method="post" action="{get_static_route id='wunschliste.php'}"}
                                    {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                    {input type="hidden" name="kWunschlisteTarget" value=$CWunschliste->kWunschliste}
                                    {input type="hidden" name="action" value="setAsDefault"}
                                    {button type="submit"
                                        variant="link"
                                        title="{lang key='setAsStandardWishlist' section='wishlist'}"
                                        data=["toggle" => "tooltip", "placement" => "bottom"]
                                    }
                                        {lang key='activate'}
                                    {/button}
                                {/form}
                            {/dropdownitem}
                        {/if}
                        {dropdownitem class="text-center"}
                            {button type="submit"
                                variant="link"
                                data=["toggle" => "collapse", "target"=>"#create-new-wishlist"]
                            }
                                {lang key='wishlistAddNew' section='login'}
                            {/button}
                        {/dropdownitem}
                    {/dropdown}
                {/col}
                {col cols=10 md=5 class="mb-2"}
                    {dropdown id='wlName' variant='light' text=$CWunschliste->cName}
                    {foreach $oWunschliste_arr as $wishlist}
                        {dropdownitem href="{get_static_route id='wunschliste.php'}{if $wishlist->nStandard != 1}?wl={$wishlist->kWunschliste}{/if}" rel="nofollow" }
                        {$wishlist->cName}
                        {/dropdownitem}
                    {/foreach}
                    {/dropdown}
                {/col}
                {col cols=12 md=6 class="mb-2"}
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
                {col cols=8 md=10}
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
                            <span class="d-none d-sm-inline">{lang key='wishlistNoticePublic' section='login'}</span>
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
                            <span class="d-none d-sm-inline">{lang key='wishlistNoticePrivate' section='login'}</span>
                        {/if}
                    {/form}
                {/col}
                {col cols=4 md=2 class='align-bottom text-right'}
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
                                    id="wishlist-url"
                                    name="wishlist-url"
                                    readonly=true
                                    value="{get_static_route id='wunschliste.php'}?wlid={$CWunschliste->cURLID}"
                                }
                                {if $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
                                    {inputgroupaddon append=true}
                                        {button
                                            class="copyToClipboard input-like"
                                            name="copy"
                                            variant="light"
                                            value="copyToClipboard"
                                            disabled=(!$hasItems)
                                            title="{lang key='copied'}"
                                            data=["clipboard-target"=>"#wishlist-url"]
                                        }
                                            <i class="far fa-copy"></i>
                                        {/button}
                                    {/inputgroupaddon}
                                    {inputgroupaddon append=true}
                                        {button
                                            type="submit"
                                            name="action"
                                            variant="light"
                                            class="input-like"
                                            value="sendViaMail"
                                            disabled=(!$hasItems)
                                            title="{lang key='wishlistViaEmail' section='login'}"
                                        }
                                            <i class="far fa-envelope"></i>
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
                    {collapse id="create-new-wishlist" visible=($newWL === 1) class='mb-3'}
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
                {row class="gallery"}
                {foreach $CWunschliste->CWunschlistePos_arr as $wlPosition}
                    {col cols=12 md=4 class="product-wrapper mb-5"}
                        <div id="result-wrapper_buy_form_{$wlPosition->kWunschlistePos}" class="product-cell text-center hover-enabled">
                            {if $isCurrenctCustomer === true}
                                {button
                                    type="submit"
                                    variant="link"
                                    name="remove" value=$wlPosition->kWunschlistePos
                                    title="{lang key='wishlistremoveItem' section='login'}"
                                    class="wishlist-pos-delete float-right text-decoration-none mb-2 fs-large"
                                    data=["toggle"=>"tooltip"]
                                }
                                    &times;
                                {/button}
                            {/if}
                            {link class="image-box mx-auto clearer d-block" href=$wlPosition->Artikel->cURLFull}
                                {image alt=$wlPosition->Artikel->cName src=$wlPosition->Artikel->Bilder[0]->cURLNormal fluid=true}
                            {/link}
                            {link href=$wlPosition->Artikel->cURL class="caption my-2"}
                                {$wlPosition->cArtikelName}
                            {/link}
                            {if $wlPosition->Artikel->getOption('nShowOnlyOnSEORequest', 0) === 1}
                                <p class="caption text-decoration-none">{lang key='productOutOfStock' section='productDetails'}</p>
                            {elseif $wlPosition->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                <p class="caption text-decoration-none">{lang key='priceOnApplication' section='global'}</p>
                            {else}
                                {include file='productdetails/price.tpl' Artikel=$wlPosition->Artikel tplscope='wishlist'}
                            {/if}
                            <div class="product-characteristics">
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
                            </div>
                            <div class="expandable pb-3">
                                {textarea
                                    readonly=($isCurrenctCustomer !== true)
                                    rows="5"
                                    name="Kommentar_{$wlPosition->kWunschlistePos}"
                                    class="my-3"
                                }{$wlPosition->cKommentar}{/textarea}
                                {if !($wlPosition->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N')}
                                    {inputgroup}
                                        {input readonly=($isCurrenctCustomer !== true)
                                            type="{if $wlPosition->Artikel->cTeilbar === 'Y' && $wlPosition->Artikel->fAbnahmeintervall == 0}text{else}number{/if}"
                                            min="{if $wlPosition->Artikel->fMindestbestellmenge}{$wlPosition->Artikel->fMindestbestellmenge}{else}0{/if}"
                                            required=($wlPosition->Artikel->fAbnahmeintervall > 0)
                                            step="{if $wlPosition->Artikel->fAbnahmeintervall > 0}{$wlPosition->Artikel->fAbnahmeintervall}{/if}"
                                            class="quantity wunschliste_anzahl" name="Anzahl_{$wlPosition->kWunschlistePos}"
                                            aria=["label"=>"{lang key='quantity'}"]
                                            value="{$wlPosition->fAnzahl}"
                                            data=["decimals"=>"{if $wlPosition->Artikel->fAbnahmeintervall > 0}2{else}0{/if}"]
                                        }
                                        {if $wlPosition->Artikel->cEinheit}
                                            {inputgroupappend}
                                                {inputgrouptext class="unit form-control"}
                                                    {$wlPosition->Artikel->cEinheit}
                                                {/inputgrouptext}
                                            {/inputgroupappend}
                                        {/if}
                                        {inputgroupaddon append=true}
                                            {if $wlPosition->Artikel->bHasKonfig}
                                                {link href=$wlPosition->Artikel->cURLFull
                                                    class="btn btn-primary ml-3"
                                                    title="{lang key='product' section='global'} {lang key='configure' section='global'}"
                                                }
                                                    <span class="fa fa-cogs"></span>
                                                {/link}
                                            {else}
                                                {button
                                                    type="submit"
                                                    name="addToCart"
                                                    value=$wlPosition->kWunschlistePos
                                                    variant="primary"
                                                    class="ml-3"
                                                    title="{lang key='wishlistaddToCart' section='login'}"
                                                }
                                                    <span class="fas fa-shopping-cart"></span>
                                                {/button}
                                            {/if}
                                        {/inputgroupaddon}
                                    {/inputgroup}
                                {/if}
                            </div>
                        </div>
                    {/col}
                {/foreach}
                {/row}
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
