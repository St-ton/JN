{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {if !empty($hinweis)}
        <div class="alert alert-success">
            {$hinweis}
        </div>
    {/if}
    {if !empty($fehler)}
        <div class="alert alert-danger">
            {$fehler}
        </div>
    {/if}
    {include file='snippets/extension.tpl'}
    {if !isset($cPost_arr)}
        {assign var=cPost_arr value=array()}
    {/if}
    {if $cOption === 'eintragen'}
        {if empty($bBereitsAbonnent)}
            {block name='newsletter-subscribe'}
            <div id="newsletter-subscribe" class="panel-wrap">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{block name='newsletter-subscribe-title'}{lang key='newsletterSubscribe' section='newsletter'}{/block}</h3>
                    </div>
                    <div class="panel-body">
                        {block name='newsletter-subscribe-body'}
                        <p>{lang key='newsletterSubscribeDesc' section='newsletter'}</p>
    
                        <form method="post" action="{get_static_route id='newsletter.php'}" role="form" class="evo-validate">
                            <fieldset>
                                <div class="form-group float-label-control">
                                    <label for="newslettertitle" class="control-label">{lang key='newslettertitle' section='newsletter'}</label>
                                    <select id="newslettertitle" name="cAnrede" class="form-control">
                                        <option value="w"{if (isset($oKunde->cAnrede) && $oKunde->cAnrede === 'w')} selected="selected"{/if}>{lang key='salutationW'}</option>
                                        <option value="m"{if (isset($oKunde->cAnrede) && $oKunde->cAnrede === 'm')} selected="selected"{/if}>{lang key='salutationM'}</option>
                                    </select>
                                </div>
                                {if !empty($oPlausi->cPost_arr.cVorname)}
                                    {assign var='inputVal_firstname' value=$oPlausi->cPost_arr.cVorname}
                                {elseif !empty($oKunde->cVorname)}
                                    {assign var='inputVal_firstname' value=$Kunde->cVorname}
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
                                    {assign var='inputVal_lastName' value=$Kunde->cNachname}
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
                                    {assign var='inputVal_email' value=$Kunde->cMail}
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
                                    <div class="form-group float-label-control{if !empty($plausiArr.captcha) && $plausiArr.captcha === true}} has-error{/if}">
                                    {captchaMarkup getBody=true}
                                    </div>
                                {/if}
                                {hasCheckBoxForLocation nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr bReturn="bHasCheckbox"}
                                {if $bHasCheckbox}
                                    <hr>
                                    {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                                    <hr>
                                {/if}
    
                                <div class="form-group">
                                    {$jtl_token}
                                        <input type="hidden" name="abonnieren" value="1" />
                                        <button type="submit" class="btn btn-primary submit">
                                            <span>{lang key='newsletterSendSubscribe' section='newsletter'}</span>
                                        </button>
                                        <p class="info small">
                                            {lang key='unsubscribeAnytime' section='newsletter'}
                                        </p>
                                </div>
                            </fieldset>
                        </form>
                        {/block}
                    </div>
                </div>
            </div>
            {/block}
        {/if}
        
        {block name='newsletter-unsubscribe'}
        <div id="newsletter-unsubscribe" class="panel-wrap top15">
            <div class="panel panel-default">
                <div class="panel-heading">
                <h3 class="panel-title">{block name='newsletter-unsubscribe-title'}{lang key='newsletterUnsubscribe' section='newsletter'}{/block}</h3></div>
                <div class="panel-body">
                    {block name='newsletter-unsubscribe-body'}
                    <p>{lang key='newsletterUnsubscribeDesc' section='newsletter'}</p>
    
                    <form method="post" action="{get_static_route id='newsletter.php'}" name="newsletterabmelden" class="evo-validate">
                        <fieldset>
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'email', 'checkOut', 'cEmail',
                                    {$oKunde->cMail|default:null}, {lang key='newsletteremail' section='newsletter'},
                                    true, $oFehlendeAngaben->cUnsubscribeEmail|default:null, 'email'
                                ]
                            }
                            {$jtl_token}
                            <input type="hidden" name="abmelden" value="1" />
                            <button type="submit" class="submit btn btn-default">
                                <span>{lang key='newsletterSendUnsubscribe' section='newsletter'}</span>
                            </button>
                        </fieldset>
                    </form>
                    {/block}
                </div>
            </div>
        </div>
        {/block}
    {elseif $cOption === 'anzeigen'}
        {if isset($oNewsletterHistory) && $oNewsletterHistory->kNewsletterHistory > 0}
            {block name='newsletter-history'}
            <h2>{lang key='newsletterhistory' section='global'}</h2>
            <div id="newsletterContent">
                <div class="newsletter">
                    <p class="newsletterSubject">
                        <strong>{lang key='newsletterdraftsubject' section='newsletter'}:</strong> {$oNewsletterHistory->cBetreff}
                    </p>
                    <p class="newsletterReference smallfont">
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
            <div class="alert alert-danger">{lang key='noEntriesAvailable' section='global'}</div>
        {/if}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
