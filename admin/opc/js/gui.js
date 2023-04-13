import {sleep} from "./utils.js";

$('#publishButton').on('click', () => {
    showModal(window.publishModal);
});

$('#navScrollRight').on('click', () => {
    window.navtabs.scrollLeft += 64;
});

$('#navScrollLeft').on('click', () => {
    window.navtabs.scrollLeft -= 64;
});

// tabs

for(const tab of $('[data-tab]')) {
    let $tab = $(tab);
    let tabPane = $('#' + tab.dataset.tab)[0];
    let allTabs = $tab.closest('.tabs').find('[data-tab]');

    $tab.on('click', () => {
        allTabs.each((i, tab) => {
            let tabPane = $('#' + tab.dataset.tab);
            tab.classList.remove('active');
            tabPane[0].classList.remove('active');
        });

        tab.classList.add('active')
        tab.scrollIntoView();
        tabPane.classList.add('active');
    });
}

// collapses

for(const collapseBtn of $('[data-collapse]')) {
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
}

// tooltips

for(const elm of $('[data-tooltip]')) {
    enableTooltip(elm);
}

// dropdowns

document.querySelectorAll('[data-dropdown]').forEach(btn => {
    let dropdown = document.querySelector('#' + btn.dataset.dropdown);

    let popper = Popper.createPopper(btn, dropdown, {
        modifiers: [
            {
                name: 'offset', options: {
                    offset: ({popper, reference}) => [popper.width / 2 - reference.width / 2, 0]
                }
            }
        ]
    });

    btn.addEventListener('click', e => {
        if (!dropdown.classList.contains('shown')) {
            dropdown.classList.add('shown');
            popper.update();
            e.stopPropagation()
        }
    });

    window.addEventListener('click', () => {
        if (dropdown.classList.contains('shown')) {
            dropdown.classList.remove('shown');
        }
    });
})

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

// functions

export async function showModal(modal)
{
    modal = $(modal);
    modal.addClass('shown')
    modal.css('opacity', 0);
    await sleep(0);
    modal.css('opacity', 1);
}

export async function showError(msg, title)
{
    if (title) {
        $(errorTitle).html(title);
    }

    $(errorAlert).html(msg);
    await showModal(window.errorModal);
}

export function enableTooltip(elm)
{
    let title   = elm.title;
    let tooltip = document.createElement('div');
    let arrow   = document.createElement('div');

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
}