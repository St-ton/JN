<div id="{$propname}-searchpicker">
    <input type="hidden" id="config-{$propname}" name="{$propname}" value="{$propval}">
    <p id="{$propname}-searchpicker-status">foundEntries</p>
    <p id="{$propname}-searchpicker-results" class="list-group"></p>
    <p>
        <button type="button" class="btn btn-sm btn-link" id="{$propname}-searchpicker-select-all">
            <i class="fas fa-check-square"></i>
            {__('selectAllShown')}
        </button>
        <button type="button" class="btn btn-sm btn-link" id="{$propname}-searchpicker-unselect-all">
            <i class="fas fa-square"></i>
            {__('unselectAllShown')}
        </button>
    </p>
    <script type="module">
        let keyName = "{$propdesc.keyName}";
        let propname = "{$propname}";
        let searcher = document.querySelector("#config-{$propdesc.searcher}");
        let resultsList = document.querySelector("#{$propname}-searchpicker-results");
        let statusLabel = document.querySelector("#{$propname}-searchpicker-status");
        let selectedInput = document.querySelector("#config-{$propname}");
        let selected = selectedInput.value.split(";").filter(Boolean).map(Number);
        let lastResults = [];

        document.querySelector('#{$propname}-searchpicker-select-all')
            .addEventListener('click', () => {
                lastResults.forEach(item => {
                    let key = Number(item[keyName]);
                    if(!selected.includes(key)) {
                        clickItem(item, document.querySelector(`#${ propname }-${ key }`));
                    }
                });
            });

        document.querySelector('#{$propname}-searchpicker-unselect-all')
            .addEventListener('click', () => {
                lastResults.forEach(item => {
                    let key = Number(item[keyName]);
                    if(selected.includes(key)) {
                        clickItem(item, document.querySelector(`#${ propname }-${ key }`));
                    }
                });
            });

        searcher.addEventListener("input", updateList);
        updateList();

        async function updateList()
        {
            let search = searcher.value;

            if(search === '') {
                search = selected;
            }

            updateStatusLabel(true);
            let results = await ioCall("{$propdesc.dataIoFuncName}", [search, 100, keyName]);
            renderList(results);
            updateStatusLabel();
        }

        function updateStatusLabel(searchPending)
        {
            let search = searcher.value;

            if(search === '') {
                if(selected.length === 0) {
                    statusLabel.innerText = `{__('noEntriesSelected')}`;
                } else {
                    statusLabel.innerText = `{__('allSelectedEntries')} ${ selected.length }`;
                }
            } else if(searchPending) {
                statusLabel.innerText = `{__('searchPending')}`;
            } else {
                statusLabel.innerText = `{__('foundEntries')} ${ lastResults.length }`;
            }
        }

        function renderList(results)
        {
            statusLabel.innerText
            resultsList.innerHTML = "";
            lastResults = results;
            results.forEach(item => {
                let key         = Number(item[keyName]);
                let itemElement = document.createElement("div");
                itemElement.innerHTML = `
                    <a class="list-group-item" style="cursor: pointer" id="${ propname }-${ key }">
                        ${ item.cName } <em>(${ item.cArtNr })</em>
                    </a>
                `;
                itemElement = itemElement.firstElementChild;
                if(selected.includes(key)) {
                    itemElement.classList.add("active");
                }
                itemElement.addEventListener('click', () => {
                    clickItem(item, itemElement);
                });
                resultsList.append(itemElement);
            });
        }

        function clickItem(item, element)
        {
            let key = Number(item[keyName]);

            if(selected.includes(key)) {
                element.classList.remove('active');
                selected.splice(selected.indexOf(key), 1);
            } else {
                element.classList.add('active');
                selected.push(key);
            }

            selectedInput.value = selected.join(";");
            updateStatusLabel();
        }

        async function ioCall(name, args = [])
        {
            let formData = new FormData();
            formData.append("jtl_token", JTL_TOKEN);
            formData.append("io", JSON.stringify({ name: name, params : args }));
            let result = await fetch("./io", { method: "POST", body: formData });
            return await result.json();
        }
    </script>
</div>
