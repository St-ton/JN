{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-wishlist'}
    {block name='snippets-wishlist-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='snippets-wishlist-content'}
        {block name='snippets-wishlist-include-extension'}
            {include file='snippets/extension.tpl'}
        {/block}

        {if $step === 'wunschliste versenden' && $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
            {block name='snippets-wishlist-content-heading-email'}
                <h1>{lang key='wishlistViaEmail' section='login'}</h1>
            {/block}
            {block name='snippets-wishlist-content-form-outer'}
                {row}
                    {col cols=12}
                        {block name='snippets-wishlist-form-subheading'}
                            <div class="h3">{$CWunschliste->cName}</div>
                        {/block}
                        {block name='snippets-wishlist-form'}
                            {form method="post" action="{get_static_route id='wunschliste.php'}" name="Wunschliste"}
                                {block name='snippets-wishlist-form-inner'}
                                    {block name='snippets-wishlist-form-inputs-hidden'}
                                        {input type="hidden" name="wlvm" value="1"}
                                        {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                        {input type="hidden" name="send" value="1"}
                                    {/block}
                                    {block name='snippets-wishlist-form-textarea'}
                                        {formgroup
                                            label-for="wishlist-email"
                                            label="{lang key='wishlistEmails' section='login'}{if $Einstellungen.global.global_wunschliste_max_email > 0} | {lang key='wishlistEmailCount' section='login'}: {$Einstellungen.global.global_wunschliste_max_email}{/if}"
                                        }
                                            {textarea id="wishlist-email" name="email" rows="5" style="width:100%"}{/textarea}
                                        {/formgroup}
                                    {/block}
                                    {block name='snippets-wishlist-form-submit'}
                                        {row}
                                            {col cols=12}
                                                {button name="action" type="submit" value="sendViaMail" variant="primary"}
                                                    {lang key='wishlistSend' section='login'}
                                                {/button}
                                            {/col}
                                        {/row}
                                    {/block}
                                {/block}
                            {/form}
                        {/block}
                    {/col}
                {/row}
            {/block}
        {else}
            {block name='snippets-wishlist-content-heading'}
                <div class="h1">
                    {if $isCurrenctCustomer === false && isset($CWunschliste->oKunde->cVorname)}
                        {$CWunschliste->cName} {lang key='from' section='product rating' alt_section='login,productDetails,productOverview,global,'} {$CWunschliste->oKunde->cVorname}
                    {else}
                        {lang key='myWishlists'}
                    {/if}
                </div>
            {/block}

            {if $isCurrenctCustomer === true}
                {row}
                    {block name='snippets-wishlist-actions'}
                        {col cols=2 md=1 class="mb-2"}
                            {dropdown variant="link" class="no-chevron wishlist-options" text="<i class='fas fa-ellipsis-v'></i>"}
                                {dropdownitem class="text-center"}
                                    {block name='snippets-wishlist-actions-rename'}
                                        {button type="submit" variant="link" data=["toggle" => "collapse", "target"=>"#edit-wishlist-name"]}
                                            {lang key='rename'}
                                        {/button}
                                    {/block}
                                {/dropdownitem}
                                {dropdownitem class="text-center"}
                                    {block name='snippets-wishlist-actions-remove-products'}
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
                                    {/block}
                                {/dropdownitem}
                                {dropdownitem class="text-center"}
                                    {block name='snippets-wishlist-actions-add-all-cart'}
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
                                    {/block}
                                {/dropdownitem}
                                {dropdownitem class="text-center"}
                                    {block name='snippets-wishlist-actions-delete-wl'}
                                        {form method="post" action="{get_static_route id='wunschliste.php'}"}
                                            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                            {input type="hidden" name="kWunschlisteTarget" value=$CWunschliste->kWunschliste}
                                            {input type="hidden" name="action" value="delete"}
                                            {button type="submit" variant="link"}
                                                {lang key='wlDelete' section='wishlist'}
                                            {/button}
                                        {/form}
                                    {/block}
                                {/dropdownitem}
                                {if $CWunschliste->nStandard != 1}
                                    {dropdownitem class="text-center"}
                                        {block name='snippets-wishlist-actions-set-active'}
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
                                        {/block}
                                    {/dropdownitem}
                                {/if}
                                {dropdownitem class="text-center"}
                                    {block name='snippets-wishlist-actions-add-new'}
                                        {button type="submit"
                                            variant="link"
                                            data=["toggle" => "collapse", "target"=>"#create-new-wishlist"]
                                        }
                                            {lang key='wishlistAddNew' section='login'}
                                        {/button}
                                    {/block}
                                {/dropdownitem}
                            {/dropdown}
                        {/col}
                    {/block}
                    {block name='snippets-wishlist-wishlists'}
                        {col cols=10 md=5 class="mb-2"}
                            {dropdown id='wlName' variant='light' text=$CWunschliste->cName}
                                {foreach $oWunschliste_arr as $wishlist}
                                    {dropdownitem href="{get_static_route id='wunschliste.php'}{if $wishlist->nStandard != 1}?wl={$wishlist->kWunschliste}{/if}" rel="nofollow" }
                                        {$wishlist->cName}
                                    {/dropdownitem}
                                {/foreach}
                            {/dropdown}
                        {/col}
                    {/block}
                    {block name='snippets-wishlist-search'}
                        {col cols=12 md=6 class="mb-2"}
                            {if $hasItems === true || !empty($wlsearch)}
                                <div id="wishlist-search">
                                    {form
                                    method="post"
                                    action="{get_static_route id='wunschliste.php'}"
                                    name="WunschlisteSuche"
                                    class="form-inline"
                                    }
                                    {block name='snippets-wishlist-search-form-inputs-hidden'}
                                        {if $CWunschliste->nOeffentlich == 1 && !empty($cURLID)}
                                            {input type="hidden" name="wlid" value=cURLID}
                                        {else}
                                            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                        {/if}
                                    {/block}
                                    {block name='snippets-wishlist-search-form-inputs'}
                                        {inputgroup}
                                            {input name="cSuche" size="35" type="text" value=$wlsearch placeholder="{lang key='wishlistSearch' section='login'}"}
                                            {inputgroupaddon append=true}
                                            {block name='snippets-wishlist-search-form-submit'}
                                                {button name="action" value="search" type="submit"}
                                                    <i class="fa fa-search"></i> {lang key='wishlistSearchBTN' section='login'}
                                                {/button}
                                            {/block}
                                            {/inputgroupaddon}
                                            {if !empty($wlsearch)}
                                                {block name='snippets-wishlist-search-form-remove-search'}
                                                {inputgroupaddon append=true}
                                                    {link href="{get_static_route id='wunschliste.php'}?wl={$CWunschliste->kWunschliste}" class="btn btn-secondary"}
                                                        {lang key='wishlistRemoveSearch' section='login'}
                                                    {/link}
                                                {/inputgroupaddon}
                                                {/block}
                                            {/if}
                                        {/inputgroup}
                                    {/block}
                                    {/form}
                                </div>
                            {/if}
                        {/col}
                    {/block}
                {/row}
                {block name='snippets-wishlist-visibility'}
                    {block name='snippets-wishlist-visibility-hr-top'}
                        <hr class="mt-2 mb-2">
                    {/block}
                    {row}
                        {block name='snippets-wishlist-visibility-form'}
                            {col cols=8 md=10}
                                {form method="post" action="{get_static_route id='wunschliste.php'}"}
                                    {block name='snippets-wishlist-visibility-inputs-hidden'}
                                        {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                        {input type="hidden" name="kWunschlisteTarget" value=$CWunschliste->kWunschliste}
                                    {/block}
                                    {if $CWunschliste->nOeffentlich == 1}
                                        {block name='snippets-wishlist-visibility-public'}
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
                                        {/block}
                                    {elseif $CWunschliste->nOeffentlich == 0}
                                        {block name='snippets-wishlist-visibility-private'}
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
                                        {/block}
                                    {/if}
                                {/form}
                            {/col}
                        {/block}
                        {block name='snippets-wishlist-visibility-count'}
                            {col cols=4 md=2 class='align-bottom text-right'}
                                {count($CWunschliste->CWunschlistePos_arr)} {lang key='products'}
                            {/col}
                        {/block}
                    {/row}
                {/block}
                {if $CWunschliste->nOeffentlich == 1}
                    {block name='snippets-wishlist-link'}
                        {row class='mt-3'}
                            {col cols=12}
                                {form method="post" action="{get_static_route id='wunschliste.php'}"}
                                    {block name='snippets-wishlist-link-inputs-hidden'}
                                        {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                    {/block}
                                    {block name='snippets-wishlist-link-inputs'}
                                        {inputgroup}
                                            {block name='snippets-wishlist-link-name'}
                                                {input
                                                    type="text"
                                                    id="wishlist-url"
                                                    name="wishlist-url"
                                                    readonly=true
                                                    value="{get_static_route id='wunschliste.php'}?wlid={$CWunschliste->cURLID}"
                                                }
                                            {/block}
                                            {if $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
                                                {block name='snippets-wishlist-link-copy'}
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
                                                {/block}
                                                {block name='snippets-wishlist-link-envelop'}
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
                                                {/block}
                                            {/if}
                                        {/inputgroup}
                                    {/block}
                                {/form}
                            {/col}
                        {/row}
                    {/block}
                {/if}

                {block name='snippets-wishlist-form-rename'}
                    {block name='snippets-wishlist-form-rename-hr-top'}
                        <hr class="mt-2 mb-2">
                    {/block}
                    {row}
                        {col cols=12}
                            {collapse id="edit-wishlist-name" visible=false class='mb-3'}
                                {form
                                    method="post"
                                    action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}"
                                    name="Wunschliste"
                                }
                                    {block name='snippets-wishlist-form-content-rename'}
                                        {block name='snippets-wishlist-form-content-rename-inputs-hidden'}
                                            {input type="hidden" name="wla" value="1"}
                                            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                            {input type="hidden" name="action" value="update"}
                                        {/block}
                                        {block name='snippets-wishlist-form-content-rename-submit'}
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
                                        {/block}
                                    {/block}
                                {/form}
                            {/collapse}
                        {/col}
                    {/row}
                {/block}
                {block name='snippets-wishlist-form-new'}
                    {row}
                        {col cols=12}
                            {collapse id="create-new-wishlist" visible=($newWL === 1) class='mb-3'}
                                {form method="post" action="{get_static_route id='wunschliste.php'}"}
                                    {block name='snippets-wishlist-form-content-new'}
                                        {block name='snippets-wishlist-form-content-new-inputs-hidden'}
                                            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                                        {/block}
                                        {block name='snippets-wishlist-form-content-new-submit'}
                                            {inputgroup}
                                                {input name="cWunschlisteName" type="text" class="input-sm" placeholder="{lang key='wishlistAddNew' section='login'}" size="35"}
                                                {inputgroupaddon append=true}
                                                    {button type="submit" size="sm" name="action" value="createNew"}
                                                        <i class="fa fa-save"></i> {lang key='wishlistSaveNew' section='login'}
                                                    {/button}
                                                {/inputgroupaddon}
                                            {/inputgroup}
                                        {/block}
                                    {/block}
                                {/form}
                            {/collapse}
                        {/col}
                    {/row}
                {/block}
            {/if}

            {block name='snippets-wishlist-form-basket'}
                {form
                    method="post"
                    action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}"
                    name="Wunschliste"
                    class="basket_wrapper{if $hasItems === true} mt-3{/if}"
                }
                    {block name='snippets-wishlist-form-basket-content'}
                        {block name='snippets-wishlist-form-basket-inputs-hidden'}
                            {input type="hidden" name="wla" value="1"}
                            {input type="hidden" name="kWunschliste" value=$CWunschliste->kWunschliste}
                            {if !empty($wlsearch)}
                                {input type="hidden" name="wlsearch" value="1"}
                                {input type="hidden" name="cSuche" value=$wlsearch}
                            {/if}
                        {/block}
                        {if !empty($CWunschliste->CWunschlistePos_arr)}
                            {block name='snippets-wishlist-form-basket-products'}
                                {row class="gallery"}
                                    {foreach $CWunschliste->CWunschlistePos_arr as $wlPosition}
                                        {col cols=12 md=4 class="product-wrapper mb-5"}
                                            <div id="result-wrapper_buy_form_{$wlPosition->kWunschlistePos}" class="product-cell text-center hover-enabled">
                                                {if $isCurrenctCustomer === true}
                                                    {block name='snippets-wishlist-form-basket-remove'}
                                                        {button
                                                            type="submit"
                                                            variant="link"
                                                            name="remove" value=$wlPosition->kWunschlistePos
                                                            title="{lang key='wishlistremoveItem' section='login'}"
                                                            class="wishlist-pos-delete float-right text-decoration-none mb-2 fs-large"
                                                            data=["toggle"=>"tooltip"]
                                                        }
                                                            <i class="fas fa-times"></i>
                                                        {/button}
                                                    {/block}
                                                {/if}
                                                {block name='snippets-wishlist-form-basket-image'}
                                                    {link class="image-box mx-auto clearer d-block" href=$wlPosition->Artikel->cURLFull}
                                                        {image alt=$wlPosition->Artikel->cName src=$wlPosition->Artikel->Bilder[0]->cURLNormal fluid=true}
                                                    {/link}
                                                {/block}
                                                {block name='snippets-wishlist-form-basket-name'}
                                                    {link href=$wlPosition->Artikel->cURL class="caption my-2"}
                                                        {$wlPosition->cArtikelName}
                                                    {/link}
                                                {/block}
                                                {block name='snippets-wishlist-form-basket-price'}
                                                    {if $wlPosition->Artikel->getOption('nShowOnlyOnSEORequest', 0) === 1}
                                                        <p class="caption text-decoration-none">{lang key='productOutOfStock' section='productDetails'}</p>
                                                    {elseif $wlPosition->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                                        <p class="caption text-decoration-none">{lang key='priceOnApplication' section='global'}</p>
                                                    {else}
                                                        {block name='snippets-wishlist-form-basket-include-price'}
                                                            {include file='productdetails/price.tpl' Artikel=$wlPosition->Artikel tplscope='wishlist'}
                                                        {/block}
                                                    {/if}
                                                {/block}
                                                {block name='snippets-wishlist-form-basket-characteristics'}
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
                                                {/block}
                                                {block name='snippets-wishlist-form-basket-main'}
                                                    <div class="expandable pb-3">
                                                        {block name='snippets-wishlist-form-basket-textarea'}
                                                            {textarea
                                                                readonly=($isCurrenctCustomer !== true)
                                                                rows="5"
                                                                name="Kommentar_{$wlPosition->kWunschlistePos}"
                                                                class="my-3"
                                                            }{$wlPosition->cKommentar}{/textarea}
                                                        {/block}
                                                        {if !($wlPosition->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N')}
                                                            {block name='snippets-wishlist-form-basket-input-group-details'}
                                                                {inputgroup}
                                                                    {block name='snippets-wishlist-form-basket-quantity'}
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
                                                                    {/block}
                                                                    {if $wlPosition->Artikel->cEinheit}
                                                                        {block name='snippets-wishlist-form-basket-unit'}
                                                                            {inputgroupappend}
                                                                                {inputgrouptext class="unit form-control"}
                                                                                    {$wlPosition->Artikel->cEinheit}
                                                                                {/inputgrouptext}
                                                                            {/inputgroupappend}
                                                                        {/block}
                                                                    {/if}
                                                                    {inputgroupaddon append=true}
                                                                        {if $wlPosition->Artikel->bHasKonfig}
                                                                            {block name='snippets-wishlist-form-basket-has-config'}
                                                                                {link href=$wlPosition->Artikel->cURLFull
                                                                                    class="btn btn-primary ml-3"
                                                                                    title="{lang key='product' section='global'} {lang key='configure' section='global'}"
                                                                                }
                                                                                    <span class="fa fa-cogs"></span>
                                                                                {/link}
                                                                            {/block}
                                                                        {else}
                                                                            {block name='snippets-wishlist-form-basket-add-to-cart'}
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
                                                                            {/block}
                                                                        {/if}
                                                                    {/inputgroupaddon}
                                                                {/inputgroup}
                                                            {/block}
                                                        {/if}
                                                    </div>
                                                {/block}
                                            </div>
                                        {/col}
                                    {/foreach}
                                {/row}
                            {/block}
                            {block name='snippets-wishlist-form-basket-submit'}
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
                            {/block}
                        {else}
                            {block name='snippets-wishlist-alert'}
                                {alert variant="info"}{lang key='noEntriesAvailable' section='global'}{/alert}
                            {/block}
                        {/if}
                    {/block}
                {/form}
            {/block}
        {/if}
    {/block}

    {block name='snippets-wishlist-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
