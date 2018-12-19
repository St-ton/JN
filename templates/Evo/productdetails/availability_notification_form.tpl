{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div class="panel-wrap">
    {if isset($position) && $position === 'popup'}
        {if isset($Artikelhinweise) && count($Artikelhinweise) > 0}
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
    <form action="{if !empty($Artikel->cURLFull)}{$Artikel->cURLFull}{else}{$ShopURL}/{/if}" method="post" id="article_availability{$Artikel->kArtikel}" class="evo-validate">
        {$jtl_token}
        <fieldset>
            <legend>{lang key='contact' section='global'}</legend>
            {if $Einstellungen.$tplscope.benachrichtigung_abfragen_vorname !== 'N' || $Einstellungen.$tplscope.benachrichtigung_abfragen_nachname !== 'N'}
                <div class="row">
                    {if $Einstellungen.$tplscope.benachrichtigung_abfragen_vorname !== 'N'}
                    <div class="col-xs-12 col-md-6">
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                "text", "article_availability{$Artikel->kArtikel}_firstName", "vorname",
                                {$Benachrichtigung->cVorname|default:null}, {lang key='firstName' section='account data'},
                                $Einstellungen.$tplscope.benachrichtigung_abfragen_vorname|default:null, null, "given-name"
                            ]
                        }
                    </div>
                    {/if}
    
                    {if $Einstellungen.$tplscope.benachrichtigung_abfragen_nachname !== 'N'}
                    <div class="col-xs-12 col-md-6">
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                "text", "article_availability{$Artikel->kArtikel}_lastName", "nachname",
                                {$Benachrichtigung->cNachname|default:null}, {lang key='lastName' section='account data'},
                                $Einstellungen.$tplscope.benachrichtigung_abfragen_nachname|default:null, null, "family-name"
                            ]
                        }
                    </div>
                    {/if}
                </div>
            {/if}
    
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="form-group float-label-control{if !empty($fehlendeAngaben_benachrichtigung.email)} has-error{/if}">
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                "email", "article_availability{$Artikel->kArtikel}_email", "email",
                                {$Benachrichtigung->cNachname|default:null}, {lang key='email' section='account data'},
                                true, $fehlendeAngaben_benachrichtigung.email|default:null, "email"
                            ]
                        }
                    </div>
                </div>
            </div>
    
            {if isset($fehlendeAngaben_benachrichtigung)}
                {include file='snippets/checkbox.tpl' nAnzeigeOrt=$smarty.const.CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT cPlausi_arr=$fehlendeAngaben_benachrichtigung cPost_arr=null}
            {else}
                {include file='snippets/checkbox.tpl' nAnzeigeOrt=$smarty.const.CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT cPlausi_arr=null cPost_arr=null cIDPrefix="article_availability{$Artikel->kArtikel}"}
            {/if}
    
        </fieldset>
        {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
            isset($Einstellungen.$tplscope.benachrichtigung_abfragen_captcha) && $Einstellungen.$tplscope.benachrichtigung_abfragen_captcha !== 'N' && empty($smarty.session.Kunde->kKunde)}
            <hr>
            <div class="row">
                <div class="col-xs-12 col-md-12{if !empty($fehlendeAngaben_benachrichtigung.captcha)} has-error{/if}">
                    {captchaMarkup getBody=true}
                    <hr>
                </div>
            </div>
        {/if}
    
        <input type="hidden" name="a" value="{if $Artikel->kVariKindArtikel}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}" />
        <input type="hidden" name="show" value="1" />
        <input type="hidden" name="benachrichtigung_verfuegbarkeit" value="1" />
        <button type="submit" value="{lang key='requestNotification' section='global'}" class="btn btn-primary" >{lang key='requestNotification' section='global'}</button>
    </form>
</div>
