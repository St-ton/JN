window.publishButton.onclick = () => showModal(publishModal);

window.navScrollRight.onclick = () => {
    window.navtabs.scrollLeft += 64;
};

window.navScrollLeft.onclick = () => {
    window.navtabs.scrollLeft -= 64;
};

window.resizer.onmousedown = e => {
    let resizerStartX     = 0;
    let resizerStartWidth = 0;

    let sidebar       = window.sidebar;
    resizerStartWidth = sidebar.offsetWidth;
    resizerStartX     = e.clientX;

    window.addEventListener('mousemove', resize);
    window.addEventListener('mouseup', stopResize);
    document.body.classList.add('resizing');

    function resize(e)
    {
        e.stopPropagation();
        setSiderbarSize(resizerStartWidth + e.clientX - resizerStartX);

        if (window.navtabs.scrollWidth > window.navtabs.offsetWidth) {
            window.navScrollRight.classList.add('shown');
            window.navScrollLeft.classList.add('shown');
        } else {
            window.navScrollRight.classList.remove('shown');
            window.navScrollLeft.classList.remove('shown');
        }
    }

    function setSiderbarSize(width)
    {
        let sidebar          = window.sidebar;
        let portlets         = window.portlets;
        let portlets_per_row = 16;

        while (portlets_per_row > 1 && width < portlets_per_row * 96 + (portlets_per_row - 1) * 22 + 24 * 2) {
            portlets_per_row --;
        }

        sidebar.style.width = width + 'px';
        portlets.style.setProperty('--portlets-per-row', portlets_per_row);
    }

    function stopResize(e)
    {
        e.stopPropagation();
        window.removeEventListener('mousemove', resize);
        window.removeEventListener('mouseup', stopResize);
        document.body.classList.remove('resizing');
    }
};

document.querySelectorAll('.tabs').forEach(tabGroup => {
    let tabs = tabGroup.querySelectorAll('[data-tab]');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(tab => {
                tab.classList.remove('active')
                document.querySelector('#' + tab.dataset.tab).classList.remove('active');
            });

            tab.classList.add('active');
            document.querySelector('#' + tab.dataset.tab).classList.add('active');
            tab.scrollIntoView();
        });
    });
});

document.querySelectorAll('[data-collapse]').forEach(collapse => {
    let target = document.querySelector('#' + collapse.dataset.collapse);

    target.addEventListener('transitionend', () => {
        if (target.offsetHeight) {
            target.style.removeProperty('height');
        }
    });

    collapse.addEventListener('click', async() => {
        let height = target.offsetHeight;

        if (height) {
            target.style.height = height + 'px';
            await sleep(0);
            target.style.height = 0;
        } else {
            target.style.removeProperty('height');
            height              = target.offsetHeight;
            target.style.height = 0;
            await sleep(0);
            target.style.height = height + 'px';
        }

        collapse.classList.toggle('collapsed');
    });
})

document.querySelectorAll('.modal').forEach(modal => {
    modal.querySelectorAll('[data-close]').forEach(closer => {
        closer.addEventListener('click', () => {
            modal.style.opacity = 0;

            modal.addEventListener('transitionend', () => {
                modal.classList.remove('shown');
            }, {once: true});
        })
    })
});

async function showModal(modal)
{
    modal.classList.add('shown');
    modal.style.opacity = 0;
    await sleep(0);
    modal.style.opacity = 1;
}

function sleep(ms)
{
    return new Promise(res => setTimeout(res, ms));
}