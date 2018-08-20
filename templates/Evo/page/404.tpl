{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div id="page-not-found">
    {if $bSeiteNichtGefunden}
        <p class="alert alert-danger">
            {lang key='pageNotFound' section='global'}
        </p>
    {/if}
    {include file='page/sitemap.tpl'}
</div>
