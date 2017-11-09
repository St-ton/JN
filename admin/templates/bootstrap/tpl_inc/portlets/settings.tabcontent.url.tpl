<div id="url-link" class="tab-pane fade in" role="tabpanel" aria-labelledby="url-link-tab">
    <div class="form-group">
        <label for="link-flag">URL (link)</label>
        <div class="radio" id="link-flag">
            <label class="radio-inline">
                <input type="radio" name="link-flag" id="link-flag-0" value="no"{if $properties['link-flag'] === 'no'} checked="checked"{/if}> No
            </label>
            <label class="radio-inline">
                <input type="radio" name="link-flag" id="link-flag-1" value="yes"{if $properties['link-flag'] === 'yes'} checked="checked"{/if}> Yes
            </label>
        </div>
    </div>
    <div id="url-link-container" class="well" {if $properties['link-flag'] === 'no'}style="display:none;"{/if}>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="link-url">Choose a link</label>
                    <input type="text" class="form-control" id="link-url" name="link-url" placeholder="URL: http://www.example.com" value="{$properties['link-url']}">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="link-title">Link Title</label>
                    <input type="text" class="form-control" id="link-title" name="link-title" value="{$properties['link-title']}">
                </div>
            </div>
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" id="link-new-tab-flag" name="link-new-tab-flag" value="yes" {if $properties['link-new-tab-flag'] === 'yes'} checked="checked"{/if}> Open link in a new tab
            </label>
        </div>
    </div>
    <script>
        $(function(){
            $('input[name="link-flag"]').click(function(){
                if ($(this).val() == 'yes'){
                    $('#url-link-container').show();
                }else{
                    $('#url-link-container').hide();
                }
            });
        });
    </script>
</div>