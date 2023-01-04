window.publishButton.addEventListener('click', () => showModal(window.publishModal));

window.navScrollRight.addEventListener('click', () => {
    window.navtabs.scrollLeft += 64;
});

window.navScrollLeft.addEventListener('click', () => {
    window.navtabs.scrollLeft -= 64;
});

// tabs

document.querySelectorAll('[data-tab]').forEach(tab => {
    let tabPane = document.querySelector('#' + tab.dataset.tab);
    let allTabs = tab.closest('.tabs').querySelectorAll('[data-tab]');

    tab.addEventListener('click', () => {
        allTabs.forEach(tab => {
            let tabPane = document.querySelector('#' + tab.dataset.tab);
            tab.classList.remove('active');
            tabPane.classList.remove('active');
        });

        tab.classList.add('active')
        tab.scrollIntoView();
        tabPane.classList.add('active');
    });
});

// collapses

document.querySelectorAll('[data-collapse]').forEach(collapseBtn => {
    let collapse = document.querySelector('#' + collapseBtn.dataset.collapse);

    collapse.addEventListener('transitionend', () => {
        if (!collapseBtn.classList.contains('collapsed')) {
            collapse.style.removeProperty('height');
        }
    });

    collapseBtn.addEventListener('click', async() => {
        if (collapseBtn.classList.contains('collapsed')) {
            let curHeight = collapse.offsetHeight;
            collapse.style.removeProperty('height');
            let orgHeight = collapse.offsetHeight;
            collapse.style.height = curHeight + 'px';
            await sleep(0);
            collapse.style.height = orgHeight + 'px';
        } else {
            collapse.style.height = collapse.offsetHeight + 'px';
            await sleep(0);
            collapse.style.height = 0;
        }

        collapseBtn.classList.toggle('collapsed');
    });
});

// tooltips

document.querySelectorAll('[data-tooltip]').forEach(elm => {
    let title = elm.title;
    let tooltip = document.createElement('div');
    let arrow = document.createElement('div');

    arrow.classList.add('tooltip-arrow');
    arrow.setAttribute('data-popper-arrow', true);
    tooltip.classList.add('tooltip');
    tooltip.innerText = title;
    tooltip.append(arrow);
    document.body.append(tooltip);
    elm.title = '';

    let popper = Popper.createPopper(elm, tooltip, {modifiers: [{name: 'offset', options: {offset: [0, 8]}}]});

    elm.addEventListener('mouseenter', () => {
        tooltip.classList.add('shown');
        popper.update();
    });

    elm.addEventListener('mouseleave', () => {
        tooltip.classList.remove('shown');
    });
})

// resizer

window.resizer.addEventListener('mousedown', e => {
    let resizerStartWidth = window.sidebar.offsetWidth;
    let resizerStartX = e.clientX;

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
        window.sidebar.style.width = width + 'px';
        let portletsPerRow = 16;

        while (
            portletsPerRow > 1 &&
            window.sidebar.offsetWidth < portletsPerRow * 96 + (portletsPerRow - 1) * 22 + 24 * 2
        ) {
            portletsPerRow --;
        }

        window.portlets.style.setProperty('--portlets-per-row', portletsPerRow);
    }

    function stopResize(e)
    {
        e.stopPropagation();
        window.removeEventListener('mousemove', resize);
        window.removeEventListener('mouseup', stopResize);
        document.body.classList.remove('resizing');
    }
});

// close buttons

document.querySelectorAll('[data-close]').forEach(closeBtn => {
    let modal = closeBtn.closest('.modal');

    closeBtn.addEventListener('click', () => {
        modal.style.opacity = 0;

        modal.addEventListener('transitionend', () => {
            modal.classList.remove('shown');
        }, {once: true});
    });
});

async function showModal(modal)
{
    modal.classList.add('shown');
    modal.style.opacity = 0;
    await sleep(0);
    modal.style.opacity = 1;
}

// helpers

function sleep(ms)
{
    return new Promise(res => setTimeout(res, ms));
}