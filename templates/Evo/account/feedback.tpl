{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<h1>{lang key="allRatings" section="global"}</h1>

    {if empty($smarty.session.Kunde->kKunde)}
        <div class="alert alert-danger">{lang key="loginFirst" section="product rating"}</div>
    {else}
        {foreach $bewertungen as $Bewertung}
            <div class="review panel panel-default">
                <div class="panel-body">
                    <strong>{$Bewertung->cTitel}</strong> - {$Bewertung->dDatum}
                    <span style="float:right">{$Bewertung->nSterne} {lang key="starPlural" section="product rating"}</span>
                    <hr class="hr-sm">
                        {$Bewertung->cText}

                    <div style="display:flex; float:right">
                        <a href="bewertung.php?a={$Bewertung->kArtikel}&bfa=1">
                            <button class="btn btn-default" title="{lang key="edit" section="product rating"}">
                                <span class="fa fa-pencil"></span>
                            </button>
                        </a>
                    </div>
                    <br>
                    <br>

                    {if !empty($Bewertung->cAntwort)}
                        <div class="review panel panel-default">
                            <div class="panel-body">
                                <div class="review-reply">
                                    <strong>{lang key="reply" section="product rating"} {$cShopName}:</strong>
                                    <hr class="hr-sm">
                                    <blockquote>
                                        <p>{$Bewertung->cAntwort}</p>
                                        <small>{$Bewertung->dAntwortDatum}</small>
                                    </blockquote>
                                </div>
                            </div>
                        </div>
                    {/if}

                    {if !empty($Bewertung->fGuthabenBonus)}
                        <div class="alert alert-info"> {lang key="balance bonus" section="product rating"}:{$Bewertung->fGuthabenBonus} &euro;</div>
                    {/if}

                    {if $Bewertung->nAktiv == 1}
                        <div class="alert alert-success">{lang key="feedback activated" section="product rating"}</div>
                    {else}
                        <div class="alert alert-warning">{lang key="feedback deactivated" section="product rating"}</div>
                    {/if}

                </div>
            </div>
        {/foreach}
    {/if}