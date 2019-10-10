{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='newsletter-index'}
    {block name='newsletter-index-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='newsletter-index-content'}
        {block name='newsletter-index-include-extension'}
            {include file='snippets/extension.tpl'}
        {/block}
        {if !isset($cPost_arr)}
            {assign var=cPost_arr value=array()}
        {/if}
        {if $cOption === 'eintragen'}
            {if empty($bBereitsAbonnent)}
                {block name='newsletter-index-newsletter-subscribe-form'}
                    {opcMountPoint id='opc_before_newsletter_subscribe'}
                    {row}
                        {col cols=12 lg=8}
                            <div id="newsletter-subscribe" class="mb-8">
                                {block name='newsletter-index-newsletter-subscribe-subheading'}
                                    <div class="h3">{lang key='newsletterSubscribe' section='newsletter'}</div>
                                {/block}
                                    <p>{lang key='newsletterSubscribeDesc' section='newsletter'}</p>
                                {form method="post" action="{get_static_route id='newsletter.php'}" role="form" class="evo-validate label-slide"}
                                {block name='newsletter-index-newsletter-subscribe-form-content'}
                                    <fieldset>
                                        {if !empty($oPlausi->cPost_arr.cEmail)}
                                            {assign var=inputVal_email value=$oPlausi->cPost_arr.cEmail}
                                        {elseif !empty($oKunde->cMail)}
                                            {assign var=inputVal_email value=$oKunde->cMail}
                                        {/if}
                                        {block name='newsletter-index-form-email'}
                                            {include file='snippets/form_group_simple.tpl'
                                            options=[
                                            'email', 'email', 'cEmail',
                                            {$inputVal_email|default:null}, {lang key='newsletteremail' section='newsletter'},
                                            true, null, 'email'
                                            ]
                                            }
                                        {/block}
                                        {assign var=plausiArr value=$oPlausi->nPlausi_arr|default:[]}
                                        {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
                                        isset($Einstellungen.newsletter.newsletter_sicherheitscode) && $Einstellungen.newsletter.newsletter_sicherheitscode !== 'N' && empty($smarty.session.Kunde->kKunde)}
                                            {block name='newsletter-index-form-captcha'}
                                                <div class="form-group{if !empty($plausiArr.captcha) && $plausiArr.captcha === true} has-error{/if}">
                                                    {captchaMarkup getBody=true}
                                                </div>
                                            {/block}
                                        {/if}
                                        {hasCheckBoxForLocation nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr bReturn="bHasCheckbox"}
                                        {if $bHasCheckbox}
                                            {block name='newsletter-index-form-include-checkbox'}
                                                {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                                            {/block}
                                        {/if}

                                        {block name='newsletter-index-newsletter-subscribe-form-content-submit'}
                                            {row}
                                                {col md=4 class='ml-md-auto'}
                                                    {input type="hidden" name="abonnieren" value="1"}
                                                    {button type="submit" variant="primary" block=true}
                                                        <span>{lang key='newsletterSendSubscribe' section='newsletter'}</span>
                                                    {/button}
                                                    <p class="info small">
                                                        {lang key='unsubscribeAnytime' section='newsletter'}
                                                    </p>
                                                {/col}
                                            {/row}
                                        {/block}
                                    </fieldset>
                                {/block}
                                {/form}
                            </div>
                        {/col}
                    {/row}
                {/block}
            {/if}

            {block name='newsletter-index-newsletter-unsubscribe-form'}
                {opcMountPoint id='opc_before_newsletter_unsubscribe'}
                {row}
                    {col cols=12 lg=8}
                        <div id="newsletter-unsubscribe" class="mt-3">
                            {block name='newsletter-index-newsletter-unsubscribe-subheading'}
                                <div class="h3">{lang key='newsletterUnsubscribe' section='newsletter'}</div>
                            {/block}
                                <p>{lang key='newsletterUnsubscribeDesc' section='newsletter'}</p>
                            {form method="post" action="{get_static_route id='newsletter.php'}" name="newsletterabmelden" class="evo-validate label-slide"}
                            {block name='newsletter-index-newsletter-unsubscribe-form-content'}
                                <fieldset>
                                    {include file='snippets/form_group_simple.tpl'
                                    options=[
                                    'email', 'checkOut', 'cEmail',
                                    {$oKunde->cMail|default:null}, {lang key='newsletteremail' section='newsletter'},
                                    true, $oFehlendeAngaben->cUnsubscribeEmail|default:null, 'email'
                                    ]
                                    }
                                    {input type="hidden" name="abmelden" value="1"}
                                    {row}
                                        {col md=4 class='ml-md-auto'}
                                            {button type="submit" block=true}
                                                <span>{lang key='newsletterSendUnsubscribe' section='newsletter'}</span>
                                            {/button}
                                        {/col}
                                    {/row}
                                </fieldset>
                            {/block}
                            {/form}
                        </div>
                    {/col}
                {/row}
            {/block}
        {elseif $cOption === 'anzeigen'}
            {if isset($oNewsletterHistory) && $oNewsletterHistory->kNewsletterHistory > 0}
                {block name='newsletter-index-newsletter-history'}
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
                {block name='newsletter-index-alert'}
                    {alert variant="danger"}{lang key='noEntriesAvailable' section='global'}{/alert}
                {/block}
            {/if}
        {/if}
    {/block}

    {block name='newsletter-index-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
