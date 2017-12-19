{if isset($Artikel->oSuchspecialBild)}
    <img class="overlay-img hidden-xs"
         srcset="{$Artikel->oSuchspecialBild->cPfadKlein},
                         {$Artikel->oSuchspecialBild->cPfadNormal} 2x,
                         {$Artikel->oSuchspecialBild->cPfadGross} 3x,
                         {$Artikel->oSuchspecialBild->cPfadRetina} 4x"
         src="{$Artikel->oSuchspecialBild->cPfadKlein}"
         alt="{if isset($Artikel->oSuchspecialBild->cSuchspecial)}{$Artikel->oSuchspecialBild->cSuchspecial}{else}{$Artikel->cName}{/if}"/>
{/if}