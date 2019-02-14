{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {include file='snippets/extension.tpl'}
    {if !isset($cPost_arr)}
        {assign var=cPost_arr value=array()}
    {/if}
    {if $cOption === 'eintragen'}
        {if empty($bBereitsAbonnent)}
            {block name='newsletter-subscribe'}
            {card id="newsletter-subscribe"}
                <div class="h3">{block name='newsletter-subscribe-title'}{lang key='newsletterSubscribe' section='newsletter'}{/block}</div>
                {block name='newsletter-subscribe-body'}
                <p>{lang key='newsletterSubscribeDesc' section='newsletter'}</p>

                {form method="post" action="{get_static_route id='newsletter.php'}" role="form" class="evo-validate"}
                    <fieldset>
                        {formgroup label="{lang key='newslettertitle' section='newsletter'}" label-for="newslettertitle"}
                            {select id="newslettertitle" name="cAnrede"}
                                <option value="w"{if (isset($oKunde->cAnrede) && $oKunde->cAnrede === 'w')} selected="selected"{/if}>{lang key='salutationW'}</option>
                                <option value="m"{if (isset($oKunde->cAnrede) && $oKunde->cAnrede === 'm')} selected="selected"{/if}>{lang key='salutationM'}</option>
                            {/select}
                        {/formgroup}
                        {if !empty($oPlausi->cPost_arr.cVorname)}
                            {assign var='inputVal_firstname' value=$oPlausi->cPost_arr.cVorname}
                        {elseif !empty($oKunde->cVorname)}
                            {assign var='inputVal_firstname' value=$oKunde->cVorname}
                        {/if}
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'text', 'newsletterfirstname', 'cVorname',
                                {$inputVal_firstname|default:null}, {lang key='newsletterfirstname' section='newsletter'},
                                false, null, 'given-name'
                            ]
                        }
                        {if !empty($oPlausi->cPost_arr.cNachname)}
                            {assign var='inputVal_lastName' value=$oPlausi->cPost_arr.cNachname}
                        {elseif !empty($oKunde->cNachname)}
                            {assign var='inputVal_lastName' value=$oKunde->cNachname}
                        {/if}
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'text', 'lastName', 'cNachname',
                                {$inputVal_lastName|default:null}, {lang key='newsletterlastname' section='newsletter'},
                                false, null, 'family-name'
                            ]
                        }
                        {if !empty($oPlausi->cPost_arr.cEmail)}
                            {assign var='inputVal_email' value=$oPlausi->cPost_arr.cEmail}
                        {elseif !empty($oKunde->cMail)}
                            {assign var='inputVal_email' value=$oKunde->cMail}
                        {/if}
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'email', 'email', 'cEmail',
                                {$inputVal_email|default:null}, {lang key='newsletteremail' section='newsletter'},
                                true, null, 'email'
                            ]
                        }
                        {if isset($oPlausi->nPlausi_arr)}
                            {assign var=plausiArr value=$oPlausi->nPlausi_arr}
                        {else}
                            {assign var=plausiArr value=array()}
                        {/if}
                        {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
                            isset($Einstellungen.newsletter.newsletter_sicherheitscode) && $Einstellungen.newsletter.newsletter_sicherheitscode !== 'N' && empty($smarty.session.Kunde->kKunde)}
                            <hr>
                            <div class="form-group{if !empty($plausiArr.captcha) && $plausiArr.captcha === true} has-error{/if}">
                            {captchaMarkup getBody=true}
                            </div>
                        {/if}
                        {hasCheckBoxForLocation nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr bReturn="bHasCheckbox"}
                        {if $bHasCheckbox}
                            <hr>
                            {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                            <hr>
                        {/if}

                        {formgroup}
                            {$jtl_token}
                            {input type="hidden" name="abonnieren" value="1"}
                            {button type="submit" variant="primary" class="submit"}
                                <span>{lang key='newsletterSendSubscribe' section='newsletter'}</span>
                            {/button}
                            <p class="info small">
                                {lang key='unsubscribeAnytime' section='newsletter'}
                            </p>
                        {/formgroup}
                    </fieldset>
                {/form}
                {/block}
            {/card}
            {/block}
        {/if}
        
        {block name='newsletter-unsubscribe'}
        {card id="newsletter-unsubscribe" class="mt-3"}
                <div class="h3">
                    {block name='newsletter-unsubscribe-title'}{lang key='newsletterUnsubscribe' section='newsletter'}{/block}
                </div>
                {block name='newsletter-unsubscribe-body'}
                <p>{lang key='newsletterUnsubscribeDesc' section='newsletter'}</p>

                {form method="post" action="{get_static_route id='newsletter.php'}" name="newsletterabmelden" class="evo-validate"}
                    <fieldset>
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'email', 'checkOut', 'cEmail',
                                {$oKunde->cMail|default:null}, {lang key='newsletteremail' section='newsletter'},
                                true, $oFehlendeAngaben->cUnsubscribeEmail|default:null, 'email'
                            ]
                        }
                        {$jtl_token}
                        {input type="hidden" name="abmelden" value="1"}
                        {button type="submit" class="submit"}
                            <span>{lang key='newsletterSendUnsubscribe' section='newsletter'}</span>
                        {/button}
                    </fieldset>
                {/form}
                {/block}
        {/card}
        {/block}
    {elseif $cOption === 'anzeigen'}
        {if isset($oNewsletterHistory) && $oNewsletterHistory->kNewsletterHistory > 0}
            {block name='newsletter-history'}
            <div class="h3">{lang key='newsletterhistory' section='global'}</div>
            <div id="newsletterContent">
                <div class="newsletter">
                    <p class="newsletterSubject">
                        <strong>{lang key='newsletterdraftsubject' section='newsletter'}:</strong> {$oNewsletterHistory->cBetreff}
                    </p>
                    <p class="newsletterReference">
                        {lang key='newsletterdraftdate' section='newsletter'}: {$oNewsletterHistory->Datum}
                    </p>
                </div>
    
                <fieldset id="newsletterHtml">
                    <legend>{lang key='newsletterHtml' section='newsletter'}</legend>
                    {$oNewsletterHistory->cHTMLStatic|replace:'src="http://':'src="//'}
                </fieldset>
            </div>
            {/block}
        {else}
            {alert variant="danger"}{lang key='noEntriesAvailable' section='global'}{/alert}
        {/if}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
