{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<img class="overlay-img hidden-xs"
     srcset="{$Artikel->oSuchspecialBild->cURLKlein},
                     {$Artikel->oSuchspecialBild->cURLNormal} 2x,
                     {$Artikel->oSuchspecialBild->cURLGross} 3x,
                     {$Artikel->oSuchspecialBild->cURLRetina} 4x"
     src="{$src}"
     alt="{if isset($Artikel->oSuchspecialBild->cSuchspecial)}{$Artikel->oSuchspecialBild->cSuchspecial}{else}{$alt}{/if}"/>
