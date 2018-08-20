{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div class="panel-wrap">
    {if isset($position) && $position === 'popup'}
        {if count($Artikelhinweise) > 0}
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {foreach $Artikelhinweise as $Artikelhinweis}
                    {$Artikelhinweis}
                {/foreach}
            </div>
        {/if}
    {/if}
    <form action="{if !empty($Artikel->cURLFull)}{$Artikel->cURLFull}{if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y'}#tab-productquestion{/if}{else}index.php{/if}" method="post" id="article_question" class="evo-validate">
        {$jtl_token}
        <fieldset>
            <legend>{lang key='contact'}</legend>
            {if $Einstellungen.artikeldetails.produktfrage_abfragen_anrede !== 'N'}
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group float-label-control">
                            <label for="salutation" class="control-label">{lang key='salutation' section='account data'}</label>
                            <select name="anrede" id="salutation" class="form-control" autocomplete="honorific-prefix">
                                <option value="" disabled selected>{lang key='salutation' section='account data'}</option>
                                <option value="w" {if isset($Anfrage->cAnrede) && $Anfrage->cAnrede === 'w'}selected="selected"{/if}>{lang key='salutationW'}</option>
                                <option value="m" {if isset($Anfrage->cAnrede) && $Anfrage->cAnrede === 'm'}selected="selected"{/if}>{lang key='salutationM'}</option>
                            </select>
                        </div>
                    </div>
                </div>
            {/if}

            {if $Einstellungen.artikeldetails.produktfrage_abfragen_vorname !== 'N' || $Einstellungen.artikeldetails.produktfrage_abfragen_nachname !== 'N'}
                <div class="row">

                    {if $Einstellungen.artikeldetails.produktfrage_abfragen_vorname !== 'N'}
                        <div class="col-xs-12 col-md-6">
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'text', 'firstName', 'vorname',
                                    {$Anfrage->cVorname|default:null}, {lang key='firstName' section='account data'},
                                    $Einstellungen.artikeldetails.produktfrage_abfragen_vorname, null, 'given-name'
                                ]
                            }
                        </div>
                    {/if}

                    {if $Einstellungen.artikeldetails.produktfrage_abfragen_nachname !== 'N'}
                        <div class="col-xs-12 col-md-6">
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'text', 'lastName', 'nachname',
                                    {$Anfrage->cNachname|default:null}, {lang key='lastName' section='account data'},
                                    $Einstellungen.artikeldetails.produktfrage_abfragen_nachname, null, 'family-name'
                                ]
                            }
                        </div>
                    {/if}
                </div>
            {/if}

            {if $Einstellungen.artikeldetails.produktfrage_abfragen_firma !== 'N'}
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'text', 'company', 'firma',
                                {$Anfrage->cFirma|default:null}, {lang key='firm' section='account data'},
                                $Einstellungen.artikeldetails.produktfrage_abfragen_firma, null, 'organization'
                            ]
                        }
                    </div>
                </div>
            {/if}

            <div class="row">
                <div class="col-xs-12 col-md-6">
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'email', 'email', 'question_email',
                            {$Anfrage->cMail|default:null}, {lang key='email' section='account data'},
                            true, $fehlendeAngaben_fragezumprodukt.email|default:null, 'email'
                        ]
                    }
                </div>
            </div>

            {if $Einstellungen.artikeldetails.produktfrage_abfragen_tel !== 'N' || $Einstellungen.artikeldetails.produktfrage_abfragen_mobil !== 'N'}
                <div class="row">
                    {if $Einstellungen.artikeldetails.produktfrage_abfragen_tel !== 'N'}
                        <div class="col-xs-12 col-md-6">
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'tel', 'tel', 'tel',
                                    {$Anfrage->cTel|default:null}, {lang key='tel' section='account data'},
                                    $Einstellungen.artikeldetails.produktfrage_abfragen_tel, $fehlendeAngaben_fragezumprodukt.tel|default:null, 'home tel'
                                ]
                            }
                        </div>
                    {/if}

                    {if $Einstellungen.artikeldetails.produktfrage_abfragen_mobil !== 'N'}
                        <div class="col-xs-12 col-md-6">
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'tel', 'mobile', 'mobil',
                                    {$Anfrage->cMobil|default:null}, {lang key='tel' section='account data'},
                                    $Einstellungen.artikeldetails.produktfrage_abfragen_mobil, $fehlendeAngaben_fragezumprodukt.mobil|default:null, 'mobile tel'
                                ]
                            }
                        </div>
                    {/if}
                </div>
            {/if}

            {if $Einstellungen.artikeldetails.produktfrage_abfragen_fax !== 'N'}
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'tel', 'fax', 'fax',
                                {$Anfrage->cMobil|default:null}, {lang key='fax' section='account data'},
                                $Einstellungen.artikeldetails.produktfrage_abfragen_fax, $fehlendeAngaben_fragezumprodukt.fax|default:null, 'fax tel'
                            ]
                        }
                    </div>
                </div>
            {/if}
        </fieldset>

        <fieldset>
            <legend>{lang key='productQuestion' section='productDetails'}</legend>
            <div class="form-group float-label-control {if isset($fehlendeAngaben_fragezumprodukt.nachricht) && $fehlendeAngaben_fragezumprodukt.nachricht > 0}has-error{/if}">
                <label class="control-label" for="question">{lang key='question' section='productDetails'}</label>
                <textarea class="form-control" name="nachricht" id="question" cols="80" rows="8" required>{if isset($Anfrage)}{$Anfrage->cNachricht}{/if}</textarea>
                {if isset($fehlendeAngaben_fragezumprodukt.nachricht) && $fehlendeAngaben_fragezumprodukt.nachricht > 0}
                    <div class="form-error-msg text-danger"><i class="fa fa-warning"></i> {if $fehlendeAngaben_fragezumprodukt.nachricht > 0}{lang key='fillOut'}{/if}</div>
                {/if}
            </div>

            {if isset($fehlendeAngaben_fragezumprodukt)}
                {include file='snippets/checkbox.tpl' nAnzeigeOrt=$smarty.const.CHECKBOX_ORT_FRAGE_ZUM_PRODUKT cPlausi_arr=$fehlendeAngaben_fragezumprodukt cPost_arr=null}
            {else}
                {include file='snippets/checkbox.tpl' nAnzeigeOrt=$smarty.const.CHECKBOX_ORT_FRAGE_ZUM_PRODUKT cPlausi_arr=null cPost_arr=null}
            {/if}

        </fieldset>
        {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
            isset($Einstellungen.artikeldetails.produktfrage_abfragen_captcha) && $Einstellungen.artikeldetails.produktfrage_abfragen_captcha !== 'N' && empty($smarty.session.Kunde->kKunde)}
            <hr>
            <div class="row">
                <div class="col-xs-12 col-md-12{if !empty($fehlendeAngaben_fragezumprodukt.captcha)} has-error{/if}">
                    {captchaMarkup getBody=true}
                    <hr>
                </div>
            </div>
        {/if}

        {if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'P' && !empty($oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getName())}
            <p class="privacy text-muted small">
                <a href="{$oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getURL()}" class="popup">{$oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getName()}</a>
            </p>
        {/if}
        <input type="hidden" name="a" value="{$Artikel->kArtikel}" />
        <input type="hidden" name="show" value="1" />
        <input type="hidden" name="fragezumprodukt" value="1" />
        <button type="submit" value="{lang key='sendQuestion' section='productDetails'}" class="btn btn-primary" >{lang key='sendQuestion' section='productDetails'}</button>
    </form>
</div>
