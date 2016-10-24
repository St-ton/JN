<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {if $Suchergebnisse->Suchspecialauswahl[1]->nAnzahl > 0}
        <li>
            <a href="{$Suchergebnisse->Suchspecialauswahl[1]->cURL}" rel="nofollow">
                <i class="fa fa-square-o text-muted"></i> {lang key="bestsellers" section="global"}
                <span class="badge">{if !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Suchergebnisse->Suchspecialauswahl[1]->nAnzahl}{/if}</span>
            </a>
        </li>
    {/if}
    {if $Suchergebnisse->Suchspecialauswahl[2]->nAnzahl > 0}
        <li>
            <a href="{$Suchergebnisse->Suchspecialauswahl[2]->cURL}" rel="nofollow">
                <i class="fa fa-square-o text-muted"></i> {lang key="specialOffer" section="global"}
                <span class="badge">{if  !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Suchergebnisse->Suchspecialauswahl[2]->nAnzahl}{/if}</span>
            </a>
        </li>
    {/if}
    {if $Suchergebnisse->Suchspecialauswahl[3]->nAnzahl > 0}
        <li>
            <a href="{$Suchergebnisse->Suchspecialauswahl[3]->cURL}" rel="nofollow">
                <i class="fa fa-square-o text-muted"></i> {lang key="newProducts" section="global"}
                <span class="badge">{if  !isset($nMaxAnzahlArtikel) ||! $nMaxAnzahlArtikel}{$Suchergebnisse->Suchspecialauswahl[3]->nAnzahl}{/if}</span>
            </a>
        </li>
    {/if}
    {if $Suchergebnisse->Suchspecialauswahl[4]->nAnzahl > 0}
        <li>
            <a href="{$Suchergebnisse->Suchspecialauswahl[4]->cURL}" rel="nofollow">
                <i class="fa fa-square-o text-muted"></i> {lang key="topOffer" section="global"}
                <span class="badge">{if  !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Suchergebnisse->Suchspecialauswahl[4]->nAnzahl}{/if}</span>
            </a>
        </li>
    {/if}
    {if $Suchergebnisse->Suchspecialauswahl[5]->nAnzahl > 0}
        <li>
            <a href="{$Suchergebnisse->Suchspecialauswahl[5]->cURL}" rel="nofollow">
                <i class="fa fa-square-o text-muted"></i> {lang key="upcomingProducts" section="global"}
                <span class="badge">{if  !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Suchergebnisse->Suchspecialauswahl[5]->nAnzahl}{/if}</span>
            </a>
        </li>
    {/if}
    {if $Suchergebnisse->Suchspecialauswahl[6]->nAnzahl > 0}
        <li>
            <a href="{$Suchergebnisse->Suchspecialauswahl[6]->cURL}" rel="nofollow">
                <i class="fa fa-square-o text-muted"></i> {lang key="topReviews" section="global"}
                <span class="badge">{if  !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Suchergebnisse->Suchspecialauswahl[6]->nAnzahl}{/if}</span>
            </a>
        </li>
    {/if}
</ul>
