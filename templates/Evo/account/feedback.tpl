{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}

<h1>{lang key='allRatings'}</h1>

{if empty($smarty.session.Kunde->kKunde)}
    <div class="alert alert-danger">{lang key='loginFirst' section='product rating'}</div>
{elseif empty($bewertungen)}
    <div class="alert alert-danger">{lang key='no feedback' section='product rating'}</div>
{else}
    {foreach $bewertungen as $Bewertung}
        <div class="review panel panel-default">
            <div class="panel-body">
                <strong>{$Bewertung->cTitel}</strong> - {$Bewertung->dDatum}
                <span class="pull-right">{$Bewertung->nSterne} {lang key='starPlural' section='product rating'}</span>
                <hr class="hr-sm">
                {$Bewertung->cText}
                <div class="pull-right">
                    <a href="{$ShopURL}/bewertung.php?a={$Bewertung->kArtikel}&bfa=1">
                        <button class="btn btn-default" title="{lang key='edit' section='product rating'}">
                            <span class="fa fa-pencil"></span>
                        </button>
                    </a>
                </div>
                {if !empty($Bewertung->cAntwort)}
                    <div class="review panel panel-default top15">
                        <div class="panel-body review-reply-inner">
                            <strong>{lang key='reply' section='product rating'} {$cShopName}:</strong>
                            <hr class="hr-sm">
                            <blockquote>
                                <p>{$Bewertung->cAntwort}</p>
                                <small>{$Bewertung->dAntwortDatum}</small>
                            </blockquote>
                        </div>
                    </div>
                {/if}
            </div>
            <div class="panel-footer">
                {if !empty($Bewertung->fGuthabenBonus)}
                    {lang key='balance bonus' section='product rating'}: {$Bewertung->fGuthabenBonus|gibPreisStringLocalized}
                {/if}
                {if $Bewertung->nAktiv == 1}
                    {lang key='feedback activated' section='product rating'}
                {else}
                    {lang key='feedback deactivated' section='product rating'}
                {/if}
            </div>
        </div>
    {/foreach}
{/if}
