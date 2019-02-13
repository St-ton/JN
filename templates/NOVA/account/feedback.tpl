{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}

<h1>{lang key='allRatings'}</h1>
{if empty($smarty.session.Kunde->kKunde)}
    {alert variant="danger"}{lang key='loginFirst' section='product rating'}{/alert}
{elseif empty($bewertungen)}
    {alert variant="danger"}{lang key='no feedback' section='product rating'}{/alert}
{else}
    {foreach $bewertungen as $Bewertung}
        {card no-body=true}
            {cardheader}
                <strong>{$Bewertung->cTitel}</strong> - {$Bewertung->dDatum}
                {include file='productdetails/rating.tpl' stars=$Bewertung->nSterne}
            {/cardheader}
            {cardbody}
                {$Bewertung->cText}
                <span class="float-right">
                    {link class="btn btn-sm btn-secondary" title="{lang key='edit' section='product rating'}" href="{$ShopURL}/bewertung.php?a={$Bewertung->kArtikel}&bfa=1"}
                        <span class="fa fa-pencil-alt"></span>
                    {/link}
                </span>
                {if !empty($Bewertung->cAntwort)}
                    {card}
                        <strong>{lang key='reply' section='product rating'} {$cShopName}:</strong>
                        <hr>
                        <blockquote>
                            <p>{$Bewertung->cAntwort}</p>
                            <small>{$Bewertung->dAntwortDatum}</small>
                        </blockquote>
                    {/card}
                {/if}
            {/cardbody}
            {cardfooter}
                {if !empty($Bewertung->fGuthabenBonus)}
                    {lang key='balance bonus' section='product rating'}: {$Bewertung->fGuthabenBonusLocalized}
                {/if}
                {if $Bewertung->nAktiv == 1}
                    {lang key='feedback activated' section='product rating'}
                {else}
                    {lang key='feedback deactivated' section='product rating'}
                {/if}
            {/cardfooter}
        {/card}
    {/foreach}
{/if}
