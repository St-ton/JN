{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-container'}
    {container class="box box-container" id="sidebox{$oBox->getID()}"}
        {$oBox->getHTML()}
    {/container}
{/block}
