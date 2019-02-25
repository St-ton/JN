{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<script>
    $(window).on('load', function () {
        var tagItems     = {$oBox->getItems()|json_encode},
            tagcloudTags = [];

        $.each(tagItems, function(key, value) {
            tagcloudTags.push( { text: value.cName, weight: value.Anzahl, link: value.cURLFull } );
        } );

        $('#sidebox{$oBox->getID()} .tagcloud').jQCloud(tagcloudTags, {
            autoResize: true,
            steps: 7
        } );
    } );
</script>
{card class="box box-tagcloud mb-7" id="sidebox{$oBox->getID()}" title="{lang key='tagcloud'}"}
    <hr class="mt-0 mb-4">
    <div class="tagcloud"></div>
{/card}
