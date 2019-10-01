function header () {
    let search = '#search'
    let dropdowns = 'header .navbar-nav > .dropdown'
    let dropdownsToggle = 'header .navbar-nav .dropdown > .dropdown-toggle'
    let mobileBackLink = '[data-nav-back]'

    let $window = $(window)
    let $document = $(document)
    let $navbar = $('header .nav-scrollbar')
    let $navbarnav = $('header .navbar-nav')

    let mobileCurrentLevel = 0
    let dropdownInTo = null
    let dropdownOutTo = null
    let $activeDropdown = null

    const delayDropdownFadeIn = 400
    const delayDropdownFadeOut = 200


    const hasNavScrollbar = () => $navbar.data('jtl.navscrollbar') !== undefined

    const onResize = () => {
        if (isMobile()) {
            showMobileLevel(mobileCurrentLevel)

            if (hasNavScrollbar())
                $navbar.navscrollbar('destroy')
        } else {
            $navbarnav.removeAttr('style')

            if (!hasNavScrollbar())
                $navbar.navscrollbar()
        }
    }

    const showDropdown = (dropdown) => {
        $activeDropdown = $(dropdown)
        $activeDropdown.parent().addClass('show')
        $activeDropdown.next().addClass('show')
        $activeDropdown.attr('aria-expanded', true)
    }

    const hideDropdown = () => {
        if ($activeDropdown === null)
            return

        $activeDropdown.parent().removeClass('show')
        $activeDropdown.next().removeClass('show')
        $activeDropdown.attr('aria-expanded', false)
        $activeDropdown = null
    }

    const showMobileLevel = (level) => {
        mobileCurrentLevel = level < 0 ? 0 : mobileCurrentLevel
        $navbarnav.css('transform', `translateX(${mobileCurrentLevel * -100}%)`)
        $navbar.scrollTop(0)
    }

    onResize()

    /* events */

    $window.on('resize', debounce(() => onResize()))
    $document.on('focus blur', search, () => setTimeout(() => {
        if (hasNavScrollbar()) $navbar.navscrollbar('update')
    }, 250))

// desktop
    if (hasTouch()) {
        $document.on('click', dropdownsToggle, onDesktop((e) => {
            e.preventDefault()

            if ($activeDropdown !== null && $activeDropdown.get(0) === e.currentTarget) {
                hideDropdown()
                return
            }

            if ($activeDropdown !== null)
                hideDropdown()

            showDropdown(e.currentTarget)
        }))
    }

    $document.on('mouseenter', dropdowns, onDesktop((e) => {
        if (hasTouch())
            return

        if (dropdownOutTo != undefined)
            clearTimeout(dropdownOutTo)

        let delay = delayDropdownFadeIn

        if ($activeDropdown !== null) {
            hideDropdown()
            delay = 0
        }

        dropdownInTo = setTimeout(() => {
            showDropdown($(e.currentTarget).find('> .dropdown-toggle'))
        }, delay)
    })).on('mouseleave', dropdowns, onDesktop((e) => {
        if (hasTouch())
            return

        if (dropdownInTo != undefined)
            clearTimeout(dropdownInTo)

        dropdownOutTo = setTimeout(() => {
            hideDropdown()
        }, delayDropdownFadeOut)
    }))

// mobile
    $document.on('click', mobileBackLink, onMobile((e) => {
        e.preventDefault()

        $activeDropdown = $(e.currentTarget).closest('.show').prev()

        showMobileLevel(--mobileCurrentLevel)
        hideDropdown()
    }))

    $document.on('click', dropdownsToggle, onMobile((e) => {
        e.preventDefault()

        showDropdown(e.currentTarget)
        showMobileLevel(++mobileCurrentLevel)
    }))
}

header();