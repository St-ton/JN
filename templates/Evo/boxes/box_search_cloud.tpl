{if $oBox->show()}
    <script>
        $('document').ready(function () {
            var searchItems = {$oBox->getItems()|json_encode};
            var searchcloudTags = [];
            $.each(searchItems, function(key, value) {
                searchcloudTags.push( { text: value.cSuche, weight: value.nAnzahlGesuche, link: 'index.php?qs=' + value.cSuche } );
            } );
            $(".searchcloud").jQCloud(searchcloudTags, {
                autoResize: true,
                steps: 7
            } );
        } );
    </script>
    <section class="panel panel-default box box-searchcloud" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='searchcloud'}</div>
        </div>
        <div class="box-body panel-body">
            <div class="tagbox">
                <div class="searchcloud"></div>
            </div>
        </div>
    </section>
{/if}