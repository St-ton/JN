{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {if $opcPageService->getCurPage()->isReplace()}
        {include file='snippets/opc_mount_point.tpl' id='opc_replace_all'}
    {else}
        {if !empty($Spezialcontent->titel)}
            <div class="title h2">
                {$Spezialcontent->titel}
            </div>
        {/if}

        {include file='snippets/extension.tpl'}
        {include file='snippets/opc_mount_point.tpl' id='opc_contact_prepend'}
        {if isset($step)}
            {if $step === 'formular'}
                {if !empty($Spezialcontent->oben)}
                    <div class="custom_content">
                        {$Spezialcontent->oben}
                    </div>
                {/if}

                {if !empty($fehlendeAngaben)}
                    {alert variant="danger" dismissible=true}
                        {lang key='fillOut'}
                    {/alert}
                {/if}
                {form name="contact" action="{get_static_route id='kontakt.php'}" method="post" class="evo-validate"}
                    <fieldset>
                        <legend>{lang key='contact'}</legend>
                        {row}
                            {if $Einstellungen.kontakt.kontakt_abfragen_anrede !== 'N'}
                                {col cols=12 md=6}
                                    {formgroup
                                        label="{lang key='salutation' section='account data'}{if $Einstellungen.kontakt.kontakt_abfragen_anrede === 'O'}<span class='optional'> - {lang key='optional'}</span>{/if}"
                                        label-for="salutation"
                                    }
                                        {select name="anrede" id="salutation" required=($Einstellungen.kontakt.kontakt_abfragen_anrede === 'Y')}
                                            <option value="" selected="selected" disabled>{lang key='salutation' section='account data'}</option>
                                            <option value="w"{if isset($Vorgaben->cAnrede) && $Vorgaben->cAnrede === 'w'} selected="selected"{/if}>{lang key='salutationW'}</option>
                                            <option value="m"{if isset($Vorgaben->cAnrede) && $Vorgaben->cAnrede === 'm'} selected="selected"{/if}>{lang key='salutationM'}</option>
                                        {/select}
                                    {/formgroup}
                                {/col}
                            {/if}
                        {/row}

                        {if $Einstellungen.kontakt.kontakt_abfragen_vorname !== 'N' || $Einstellungen.kontakt.kontakt_abfragen_nachname !== 'N'}
                            {row}
                                {if $Einstellungen.kontakt.kontakt_abfragen_vorname !== 'N'}
                                    {col cols=12 md=6}
                                        {include file='snippets/form_group_simple.tpl' options=["text", "firstName", "vorname", {$Vorgaben->cVorname}, {lang key='firstName' section='account data'}, {$Einstellungen.kontakt.kontakt_abfragen_vorname}]}
                                    {/col}
                                {/if}
                                {if $Einstellungen.kontakt.kontakt_abfragen_nachname !== 'N'}
                                    {col cols=12 md=6}
                                        {assign var=invalidReason value=null}
                                        {if isset($fehlendeAngaben.nachname)}
                                            {if $fehlendeAngaben.nachname == 1}
                                                {lang assign='invalidReason' key='fillOut'}
                                            {elseif $fehlendeAngaben.nachname == 2}
                                                {lang assign='invalidReason' key='lastNameNotNumeric' section='account data'}
                                            {/if}
                                        {/if}
                                        {include file='snippets/form_group_simple.tpl' options=['text' , 'lastName', 'nachname', {$Vorgaben->cNachname}, {lang key='lastName' section='account data'}, {$Einstellungen.kontakt.kontakt_abfragen_nachname}, {$invalidReason}]}
                                    {/col}
                                {/if}
                            {/row}
                        {/if}

                        {if $Einstellungen.kontakt.kontakt_abfragen_firma !== 'N'}
                            {row}
                                {col cols=12 md=6}
                                    {include file='snippets/form_group_simple.tpl' options=[ 'text' , 'firm', 'firma', {$Vorgaben->cFirma}, {lang key='firm' section='account data'}, {$Einstellungen.kontakt.kontakt_abfragen_firma}]}
                                {/col}
                            {/row}
                        {/if}
                        {row}
                            {col cols=12 md=6}
                                {assign var=invalidReason value=null}
                                {if isset($fehlendeAngaben.email)}
                                    {if $fehlendeAngaben.email == 1}{lang assign='invalidReason' key='fillOut'}
                                    {elseif $fehlendeAngaben.email == 2}{lang assign='invalidReason' key='invalidEmail'}
                                    {elseif $fehlendeAngaben.email == 3}{lang assign='invalidReason' key='blockedEmail'}
                                    {elseif $fehlendeAngaben.email == 4}{lang assign='invalidReason' key='noDnsEmail' section='account data'}
                                    {elseif $fehlendeAngaben.email == 5}{lang assign='invalidReason' key='emailNotAvailable' section='account data'}{/if}
                                {/if}
                                {include file='snippets/form_group_simple.tpl' options=['email' , 'email', 'email', {$Vorgaben->cMail}, {lang key='email' section='account data'}, true, {$invalidReason}]}
                            {/col}
                        {/row}
                        {if $Einstellungen.kontakt.kontakt_abfragen_tel !== 'N' || $Einstellungen.kontakt.kontakt_abfragen_mobil !== 'N'}
                            {row}
                                {if $Einstellungen.kontakt.kontakt_abfragen_tel !== 'N'}
                                    {col cols=12 md=6}
                                        {assign var=invalidReason value=null}
                                        {if isset($fehlendeAngaben.tel) && $fehlendeAngaben.tel === 1}{lang assign='invalidReason' key='fillOut'}{elseif isset($fehlendeAngaben.tel) && $fehlendeAngaben.tel === 2}{lang assign='invalidReason' key='invalidTel'}{/if}
                                        {include file='snippets/form_group_simple.tpl' options=['tel' , 'tel', 'tel', {$Vorgaben->cTel}, {lang key='tel' section='account data'}, {$Einstellungen.kontakt.kontakt_abfragen_tel}, {$invalidReason}]}
                                    {/col}
                                {/if}
                                {if $Einstellungen.kontakt.kontakt_abfragen_mobil !== 'N'}
                                    {col cols=12 md=6}
                                        {assign var=invalidReason value=null}
                                        {if isset($fehlendeAngaben.mobil) && $fehlendeAngaben.mobil === 1}{lang assign='invalidReason' key='fillOut'}{elseif isset($fehlendeAngaben.mobil) && $fehlendeAngaben.mobil === 2}{lang assign='invalidReason' key='invalidTel'}{/if}
                                        {include file='snippets/form_group_simple.tpl' options=['tel' , 'mobile', 'mobil', {$Vorgaben->cMobil}, {lang key='mobile' section='account data'}, {$Einstellungen.kontakt.kontakt_abfragen_mobil}, {$invalidReason}]}
                                    {/col}
                                {/if}
                            {/row}
                        {/if}

                        {if $Einstellungen.kontakt.kontakt_abfragen_fax !== 'N'}
                            {row}
                                {col cols=12 md=6}
                                    {assign var=invalidReason value=null}
                                    {if !empty($fehlendeAngaben.fax) && $fehlendeAngaben.fax === 1}{lang assign='invalidReason' key='fillOut'}{elseif isset($fehlendeAngaben.fax) && $fehlendeAngaben.fax === 2}{lang assign='invalidReason' key='invalidTel'}{/if}
                                    {include file='snippets/form_group_simple.tpl' options=['tel' , 'fax', 'fax', {$Vorgaben->cFax}, {lang key='fax' section='account data'}, {$Einstellungen.kontakt.kontakt_abfragen_fax}, {$invalidReason}]}
                                {/col}
                            {/row}
                        {/if}
                        {if !isset($cPost_arr)}
                            {assign var=cPost_arr value=array()}
                        {/if}
                        {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$fehlendeAngaben cPost_arr=$cPost_arr}

                    </fieldset>

                    <fieldset>
                        <legend>{lang key='message' section='contact'}</legend>
                        {if $betreffs}
                            {if isset($fehlendeAngaben.betreff)}
                                {alert variant="danger"}
                                    {lang key='fillOut'}
                                {/alert}
                            {/if}
                            {row}
                                {col cols=12 md=12}
                                    {formgroup
                                        class="{if isset($fehlendeAngaben.subject)} has-error{/if}"
                                        label="{lang key='subject' section='contact'}"
                                        label-for="subject"
                                    }
                                        {select name="subject" id="subject" required=true}
                                            <option value="" selected disabled>{lang key='subject' section='contact'}</option>
                                            {foreach $betreffs as $betreff}
                                                <option value="{$betreff->kKontaktBetreff}" {if $Vorgaben->kKontaktBetreff == $betreff->kKontaktBetreff}selected{/if}>{$betreff->AngezeigterName}</option>
                                            {/foreach}
                                        {/select}
                                        {if !empty($fehlendeAngaben.subject)}
                                            <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                                                {lang key='fillOut'}
                                            </div>
                                        {/if}
                                    {/formgroup}
                                {/col}
                            {/row}
                        {/if}
                        {row}
                            {col cols=12 md=12}
                                {formgroup
                                    class="{if isset($fehlendeAngaben.nachricht)} has-error{/if}"
                                    label="{lang key='message' section='contact'}"
                                    label-for="message"
                                }
                                    {textarea name="nachricht" rows="10" id="message" required=true}
                                        {if isset($Vorgaben->cNachricht)}{$Vorgaben->cNachricht}{/if}
                                    {/textarea}
                                    {if !empty($fehlendeAngaben.nachricht)}
                                        <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                                            {lang key='fillOut'}
                                        </div>
                                    {/if}
                                {/formgroup}
                            {/col}
                        {/row}
                    </fieldset>
                    {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
                        isset($Einstellungen.kontakt.kontakt_abfragen_captcha) && $Einstellungen.kontakt.kontakt_abfragen_captcha !== 'N' && empty($smarty.session.Kunde->kKunde)}
                        <hr>
                        {row}
                            {col cols=12 md=6 class="{if !empty($fehlendeAngaben.captcha)} has-error{/if}"}
                                {captchaMarkup getBody=true}
                                <hr>
                            {/col}
                        {/row}
                    {/if}
                    {input type="hidden" name="kontakt" value="1"}
                    {include file='snippets/opc_mount_point.tpl' id='opc_contact_form_submit_prepend'}
                    {button type="submit" variant="primary"}{lang key='sendMessage' section='contact'}{/button}
                {/form}
                <br>
                {if !empty($Spezialcontent->unten)}
                    <div class="custom_content">
                        {$Spezialcontent->unten}
                    </div>
                {/if}
            {/if}
        {/if}
        {include file='snippets/opc_mount_point.tpl' id='opc_contact_append'}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
