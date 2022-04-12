<div id="{$propname}-searchpicker">
    <input type="hidden" id="config-{$propname}" name="{$propname}" value="{$propval}">
    <p id="{$propname}-searchpicker-status">foundEntries</p>
    <div id="{$propname}-searchpicker-results" class="list-group"></div>
    <script type="module">
        let keyName = "{$propdesc.keyName}";
        let propname = "{$propname}";
        let searcher = document.querySelector("#config-{$propdesc.searcher}");
        let resultsList = document.querySelector("#{$propname}-searchpicker-results");
        let statusLabel = document.querySelector("#{$propname}-searchpicker-status");
        let selectedInput = document.querySelector("#config-{$propname}");
        let selected = selectedInput.value.split(";").filter(Boolean).map(Number);
        let lastResults = [];

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
                        ${ item.cName } (${ item.cArtNr })
                    </a>
                `;
                itemElement = itemElement.firstElementChild;
                if(selected.includes(key)) {
                    itemElement.classList.add("active");
                }
                itemElement.addEventListener('click', () => {
                    selectItem(item, itemElement);
                });
                resultsList.append(itemElement);
            });
        }

        function selectItem(item, element)
        {
            let key = Number(item[keyName]);

            console.log(key, selected.includes(key));

            if(selected.includes(key)) {
                element.classList.remove('active');
                selected.splice(selected.indexOf(key), 1);
            } else {
                element.classList.add('active');
                selected.push(key);
            }

            selectedInput.value = selected.join(";");
            updateStatusLabel();
            console.log(item, element);
        }

        async function ioCall(name, args = [])
        {
            let formData = new FormData();
            formData.append("jtl_token", JTL_TOKEN);
            formData.append("io", JSON.stringify({ name: name, params : args }));
            let result = await fetch("./io.php", { method: "POST", body: formData });
            return await result.json();
        }
    </script>
</div>