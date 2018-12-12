{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<img class="overlay-img hidden-xs"
     srcset="{$Artikel->oSuchspecialBild->getURL('klein')},
             {$Artikel->oSuchspecialBild->getURL('normal')} 2x,
             {$Artikel->oSuchspecialBild->getURL('gross')} 3x,
             {$Artikel->oSuchspecialBild->getURL('retina')} 4x"
     src="{$src}"
     alt="{if isset($Artikel->oSuchspecialBild->getName())}{$Artikel->oSuchspecialBild->getName()}{else}{$alt}{/if}"/>
