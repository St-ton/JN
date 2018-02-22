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
                            <button class="btn btn-default" title="{lang key="edit" section="global"}">
                                <span class="fa fa-pencil"></span>
                            </button>
                        </a>
                        <form method="post" id="del_bew" action="{get_static_route id='jtl.php'}?bewertungen=1">
                            <input type="hidden" name="del_bew" value="{$Bewertung->kBewertung}" />
                            <button type="submit" class="btn btn-danger" name="bwl" title="{lang key="wishlisteDelete" section="login"}">
                                <span class="fa fa-trash-o"></span>
                            </button>
                        </form>
                    </div>
                    <br>
                    <br>

                    {if !empty($Bewertung->cAntwort)}
                        <div class="review panel panel-default">
                            <div class="panel-body">
                                <div class="review-reply">
                                    <strong>Antwort von {$cShopName}:</strong>
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
                        <div class="alert alert-info"> {lang key="balance bonus" section="feedback"}:{$Bewertung->fGuthabenBonus} &euro;</div>
                    {/if}
                    {if $Bewertung->nAktiv == 1}
                        <div class="alert alert-success">{lang key="feedback activated" section="feedback"}</div>
                    {else}
                        <div class="alert alert-warning">{lang key="feedback deactivated" section="feedback"}</div>
                    {/if}

                </div>
            </div>
        {/foreach}
    {/if}
<form method="post" id="del_all_bew" action="{get_static_route id='jtl.php'}?bewertungen=1">
    <input type="hidden" name="del_all_bew" value="1" />
    <input type="submit" class="submit btn btn-danger" value="{lang key="wishlistDelAll" section="login"}" />
</form>