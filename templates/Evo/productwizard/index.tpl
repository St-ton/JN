{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($oAuswahlAssistent->kAuswahlAssistentGruppe)}
    {opcMountPoint id='opc_before_selection_wizard'}

    <div id="selection_wizard">
        {include file='productwizard/form.tpl'}
    </div>
{elseif isset($AWA)}
    {opcMountPoint id='opc_before_selection_wizard'}

    {include file='selectionwizard/index.tpl'}
{/if}
