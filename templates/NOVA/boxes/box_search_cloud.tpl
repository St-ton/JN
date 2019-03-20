{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<script>
    $(window).on('load', function () {
        var searchItems     = {$oBox->getItems()|json_encode},
            searchcloudTags = [];

        $.each(searchItems, function(key, value) {
            searchcloudTags.push( { text: value.cSuche, weight: value.nAnzahlGesuche, link: 'index.php?qs=' + value.cSuche } );
        } );

        $('#sidebox{$oBox->getID()} .searchcloud').jQCloud(searchcloudTags, {
            autoResize: true,
            steps: 7
        } );
    } );
</script>
{card class="box box-searchcloud mb-7" id="sidebox{$oBox->getID()}" title="{lang key='searchcloud'}"}
    <hr class="mt-0 mb-4">
    <div class="searchcloud"></div>
{/card}
