{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($position) && $position === 'popup'}
    {if count($Artikelhinweise) > 0}
        {alert dismissable=true variant="danger"}
            {foreach $Artikelhinweise as $Artikelhinweis}
                {$Artikelhinweis}
            {/foreach}
        {/alert}
    {/if}
{/if}
{form action="{if !empty($Artikel->cURLFull)}{$Artikel->cURLFull}{if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y'}#tab-productquestion{/if}{else}{$ShopURL}/{/if}" method="post" id="article_question" class="evo-validate"}
    <fieldset>
        <legend>{lang key='contact'}</legend>
        {if $Einstellungen.artikeldetails.produktfrage_abfragen_anrede !== 'N'}
            {row}
                {col md=6}
                    {formgroup
                        label-for="salutation"
                        label="{lang key='salutation' section='account data'}{if $Einstellungen.artikeldetails.produktfrage_abfragen_anrede === 'O'}<span class='optional'> - {lang key='optional'}</span>{/if}"
                    }
                        {select name="anrede" id="salutation" placeholder="{lang key='emailadress'}" autocomplete="honorific-prefix" required=($Einstellungen.artikeldetails.produktfrage_abfragen_anrede === 'Y')}
                            <option value="" disabled selected>{lang key='salutation' section='account data'}</option>
                            <option value="w" {if isset($Anfrage->cAnrede) && $Anfrage->cAnrede === 'w'}selected="selected"{/if}>{lang key='salutationW'}</option>
                            <option value="m" {if isset($Anfrage->cAnrede) && $Anfrage->cAnrede === 'm'}selected="selected"{/if}>{lang key='salutationM'}</option>
                        {/select}
                    {/formgroup}
                {/col}
            {/row}
        {/if}

        {if $Einstellungen.artikeldetails.produktfrage_abfragen_vorname !== 'N' || $Einstellungen.artikeldetails.produktfrage_abfragen_nachname !== 'N'}
            {row}
                {if $Einstellungen.artikeldetails.produktfrage_abfragen_vorname !== 'N'}
                    {col md=6}
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'text', 'firstName', 'vorname',
                                {$Anfrage->cVorname|default:null}, {lang key='firstName' section='account data'},
                                $Einstellungen.artikeldetails.produktfrage_abfragen_vorname, null, 'given-name'
                            ]
                        }
                    {/col}
                {/if}

                {if $Einstellungen.artikeldetails.produktfrage_abfragen_nachname !== 'N'}
                    {col md=6}
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'text', 'lastName', 'nachname',
                                {$Anfrage->cNachname|default:null}, {lang key='lastName' section='account data'},
                                $Einstellungen.artikeldetails.produktfrage_abfragen_nachname, null, 'family-name'
                            ]
                        }
                    {/col}
                {/if}
            {/row}
        {/if}

        {if $Einstellungen.artikeldetails.produktfrage_abfragen_firma !== 'N'}
            {row}
                {col md=6}
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'text', 'company', 'firma',
                            {$Anfrage->cFirma|default:null}, {lang key='firm' section='account data'},
                            $Einstellungen.artikeldetails.produktfrage_abfragen_firma, null, 'organization'
                        ]
                    }
                {/col}
            {/row}
        {/if}

        {row}
            {col md=6}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'email', 'question_email', 'email',
                        {$Anfrage->cMail|default:null}, {lang key='email' section='account data'},
                        true, $fehlendeAngaben_fragezumprodukt.email|default:null, 'email'
                    ]
                }
            {/col}
        {/row}

        {if $Einstellungen.artikeldetails.produktfrage_abfragen_tel !== 'N' || $Einstellungen.artikeldetails.produktfrage_abfragen_mobil !== 'N'}
            {row}
                {if $Einstellungen.artikeldetails.produktfrage_abfragen_tel !== 'N'}
                    {col md=6}
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'tel', 'tel', 'tel',
                                {$Anfrage->cTel|default:null}, {lang key='tel' section='account data'},
                                $Einstellungen.artikeldetails.produktfrage_abfragen_tel, $fehlendeAngaben_fragezumprodukt.tel|default:null, 'home tel'
                            ]
                        }
                    {/col}
                {/if}

                {if $Einstellungen.artikeldetails.produktfrage_abfragen_mobil !== 'N'}
                    {col md=6}
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'tel', 'mobile', 'mobil',
                                {$Anfrage->cMobil|default:null}, {lang key='mobile' section='account data'},
                                $Einstellungen.artikeldetails.produktfrage_abfragen_mobil, $fehlendeAngaben_fragezumprodukt.mobil|default:null, 'mobile tel'
                            ]
                        }
                    {/col}
                {/if}
            {/row}
        {/if}

        {if $Einstellungen.artikeldetails.produktfrage_abfragen_fax !== 'N'}
            {row}
                {col md=6}
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'tel', 'fax', 'fax',
                            {$Anfrage->cMobil|default:null}, {lang key='fax' section='account data'},
                            $Einstellungen.artikeldetails.produktfrage_abfragen_fax, $fehlendeAngaben_fragezumprodukt.fax|default:null, 'fax tel'
                        ]
                    }
                {/col}
            {/row}
        {/if}
    </fieldset>

    <fieldset>
        <legend>{lang key='productQuestion' section='productDetails'}</legend>

        {formgroup label-for="question" label="{lang key='question' section='productDetails'}"}
            {textarea name="nachricht" id="question" rows="8" required=true class="{if isset($fehlendeAngaben_fragezumprodukt.nachricht) && $fehlendeAngaben_fragezumprodukt.nachricht > 0}has-error{/if}"}
                {if isset($Anfrage)}{$Anfrage->cNachricht}{/if}
            {/textarea}
            {if isset($fehlendeAngaben_fragezumprodukt.nachricht) && $fehlendeAngaben_fragezumprodukt.nachricht > 0}
                <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i> {if $fehlendeAngaben_fragezumprodukt.nachricht > 0}{lang key='fillOut'}{/if}</div>
            {/if}
        {/formgroup}

        {if isset($fehlendeAngaben_fragezumprodukt)}
            {include file='snippets/checkbox.tpl' nAnzeigeOrt=$smarty.const.CHECKBOX_ORT_FRAGE_ZUM_PRODUKT cPlausi_arr=$fehlendeAngaben_fragezumprodukt cPost_arr=null}
        {else}
            {include file='snippets/checkbox.tpl' nAnzeigeOrt=$smarty.const.CHECKBOX_ORT_FRAGE_ZUM_PRODUKT cPlausi_arr=null cPost_arr=null}
        {/if}

    </fieldset>
    {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
        isset($Einstellungen.artikeldetails.produktfrage_abfragen_captcha) && $Einstellungen.artikeldetails.produktfrage_abfragen_captcha !== 'N' && empty($smarty.session.Kunde->kKunde)}
        {row}
            {col class="{if !empty($fehlendeAngaben_fragezumprodukt.captcha)}has-error{/if}"}
                {captchaMarkup getBody=true}
            {/col}
        {/row}
    {/if}

    {if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'P' && !empty($oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getName())}
        <p class="privacy text-muted small">
            {link href=$oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getURL() class="popup"}
                {$oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getName()}
            {/link}
        </p>
    {/if}

    {input type="hidden" name="a" value=$Artikel->kArtikel}
    {input type="hidden" name="show" value="1"}
    {button type="submit" value="1" variant="primary" class="w-auto"}
        {lang key='sendQuestion' section='productDetails'}
    {/button}
{/form}
