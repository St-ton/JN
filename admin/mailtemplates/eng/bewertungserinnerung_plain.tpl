{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname}  {$Kunde->cNachname},

We would love it if you could write a rating and share your experience with your recently products.

Please click on the product to rate it:

{foreach $Bestellung->Positionen as $Position}
    {if $Position->nPosTyp == 1}
        {$Position->cName} ({$Position->cArtNr})
        {$ShopURL}/index.php?a={$Position->kArtikel}&bewertung_anzeigen=1#tab-votes

        {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}

            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
        {/foreach}
    {/if}
{/foreach}

Thank you for sharing!


Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}
