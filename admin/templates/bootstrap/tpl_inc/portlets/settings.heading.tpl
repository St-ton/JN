<dl>
    <dt>
        <label for="config-heading-level">Level:</label>
    </dt>
    <dd>
        <select name="level" class="form-control" id="config-heading-level">
            <option value="1"{if $properties.level === '1'} selected{/if}>h1</option>
            <option value="2"{if $properties.level === '2'} selected{/if}>h2</option>
            <option value="3"{if $properties.level === '3'} selected{/if}>h3</option>
            <option value="4"{if $properties.level === '4'} selected{/if}>h4</option>
            <option value="5"{if $properties.level === '5'} selected{/if}>h5</option>
            <option value="6"{if $properties.level === '6'} selected{/if}>h6</option>
        </select>
    </dd>
</dl>
<dl>
    <dt>
        <label for="config-heading-text">Text:</label>
    </dt>
    <dd>
        <input name="text" value="{$properties.text}" class="form-control" id="config-heading-text">
    </dd>
</dl>
