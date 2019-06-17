{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-tag-cloud'}
    {block name='boxes-box-tag-cloud-script'}
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
    {/block}
    {card class="box box-tagcloud mb-7" id="sidebox{$oBox->getID()}" title="{lang key='tagcloud'}"}
        {block name='boxes-box-tag-cloud-content'}
            <div class="tagcloud"></div>
        {/block}
    {/card}
{/block}
