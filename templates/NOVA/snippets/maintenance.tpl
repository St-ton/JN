{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-maintenance'}
    {block name='snippets-maintenance-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='snippets-maintenance-include-extension'}
        {include file='snippets/extension.tpl'}
    {/block}

    {block name='snippets-maintenance-content'}
        {card header="<i class='fa fa-wrench'></i> {lang key='maintainance'}"
            tag="article"
            bg-variant="light"
            border-variant="warning"
            id="maintenance-notice"}
            <p class="card-text">
                {lang key='maintenanceModeActive'}
            </p>
        {/card}
    {/block}

    {block name='snippets-maintenance-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
