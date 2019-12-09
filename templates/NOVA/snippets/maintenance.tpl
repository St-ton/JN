{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-maintenance'}
    {block name='snippets-maintenance-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='snippets-maintenance-content'}
        {modal id="maintenance" class="fade show" size="lg" data=['backdrop' => 'static']}
            {card header="<i class='fa fa-wrench'></i> {lang key='maintainance'}"
                tag="article"
                bg-variant="light"
                border-variant="warning"
                id="maintenance-notice"}
                <p class="card-text">
                    {lang key='maintenanceModeActive'}
                </p>
            {/card}
        {/modal}
        {inline_script}
            <script>
                $('#maintenance').modal('show');
            </script>
        {/inline_script}
    {/block}

    {block name='snippets-maintenance-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
