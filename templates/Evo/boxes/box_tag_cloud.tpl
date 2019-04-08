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
<section class="panel panel-default box box-tagcloud" id="sidebox{$oBox->getID()}">
    <div class="panel-heading">
        <div class="panel-title">{lang key='tagcloud'}</div>
    </div>
    <div class="box-body panel-body">
        <div class="tagcloud"></div>
    </div>
</section>
