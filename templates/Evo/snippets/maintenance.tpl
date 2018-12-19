{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}

    {include file='snippets/extension.tpl'}

    <div id="maintenance-notice" class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-wrench"></i> {lang key='maintainance' section='global'}</h3>
        </div>
        <div class="panel-body">
            {lang key='maintenanceModeActive' section='global'}
        </div>
    </div>
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
