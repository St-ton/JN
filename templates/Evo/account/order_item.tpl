{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if $Position->nPosTyp == 1}
    <p><a href="{$Position->Artikel->cURLFull}">{$Position->cName}</a></p>
    {* Seriennummer *}
    {if !empty($Position->cSeriennummer)}
        <p>{lang key='serialnumber'}: {$Position->cSeriennummer}</p>
    {/if}
    {* MHD *}
    {if !empty($Position->dMHD)}
        <p>{lang key='mdh'}: {$Position->dMHD_de}</p>
    {/if}
    {* Charge *}
    {if !empty($Position->cChargeNr)}
        <p>{lang key='charge'}: {$Position->cChargeNr}</p>
    {/if}
    {if !empty($Position->cUnique) && $Position->kKonfigitem == 0 && $bKonfig}
        <ul class="children_ex">
            {foreach $Bestellung->Positionen as $KonfigPos}
                {if $Position->cUnique == $KonfigPos->cUnique}
                    <li>{if !($KonfigPos->cUnique|strlen > 0 && $KonfigPos->kKonfigitem == 0)}{$KonfigPos->nAnzahlEinzel}x {/if}{$KonfigPos->cName} {if $bPreis}
                        <span class="price">{$KonfigPos->cEinzelpreisLocalized[$NettoPreise]}{/if}</span>
                    </li>
                {/if}
            {/foreach}
        </ul>
    {/if}

    {if $Position->Artikel->cLocalizedVPE}
        <small><b>{lang key='basePrice' section='global'}:</b> {$Position->Artikel->cLocalizedVPE[$NettoPreise]}</small>
        <br />
    {/if}

    {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}
        <br />
        <span>{$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
            {if $WKPosEigenschaft->fAufpreis && $bPreis}
                {$WKPosEigenschaft->cAufpreisLocalized[$NettoPreise]}
            {/if}
        </span>
    {/foreach}
{else}
    {$Position->cName}
    {if !empty($Position->cHinweis)}
        <p>
            <small>{$Position->cHinweis}</small>
        </p>
    {/if}
{/if}