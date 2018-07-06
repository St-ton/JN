{foreach $prop as $i => $tabTitle}
    <div class="input-group">
        <div class="input-group-addon">
            <button type="button" class="btn btn-xs btn-danger"
                    onclick="removeLine(this);">
                <i class="fa fa-remove fa-fw"></i>
            </button>
        </div>
        <input type="text" class="form-control" name="tabs[]" value="{$tabTitle}">
    </div>
{/foreach}
<div class="input-group" id="new-input-group">
    <div class="input-group-addon">
        <button type="button" class="btn btn-xs btn-primary"
                onclick="addNewLine()">
            <i class="fa fa-asterisk fa-fw"></i>
        </button>
    </div>
    <input type="text" class="form-control" disabled>
</div>
<script>
    function removeLine(elm)
    {
        $(elm).closest('.input-group').remove();
    }

    function addNewLine()
    {
        var newInputGroup      = $('#new-input-group');
        var newInputGroupClone = newInputGroup.clone();

        newInputGroupClone.attr('id', '');
        newInputGroupClone.find('button').removeClass('btn-primary').addClass('btn-danger')
            .attr('onclick', 'removeLine(this);');
        newInputGroupClone.find('i.fa').removeClass('fa-asterisk').addClass('fa-remove');
        newInputGroupClone.find('input').prop('disabled', false).attr('name', 'tabs[]');
        newInputGroupClone.insertBefore(newInputGroup);
    }
</script>