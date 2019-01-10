{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($oAuswahlAssistent->kAuswahlAssistentGruppe)}
    <div id="selection_wizard">
        {include file='productwizard/form.tpl'}
    </div>
{elseif isset($AWA)}
    {include file='selectionwizard/index.tpl'}
{/if}
