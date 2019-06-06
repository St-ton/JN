{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($oAuswahlAssistent->kAuswahlAssistentGruppe)}
    {include file='snippets/opc_mount_point.tpl' id='opc_before_selection_wizard'}

    <div id="selection_wizard">
        {include file='productwizard/form.tpl'}
    </div>
{elseif isset($AWA)}
    {include file='snippets/opc_mount_point.tpl' id='opc_before_selection_wizard'}

    {include file='selectionwizard/index.tpl'}
{/if}
