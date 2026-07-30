(function ($) {
    $(function () {
        var $toggle = $('[data-toggle="offcanvas"]');
        var $panel = $('#primary-navigation-panel');
        var $body = $('body');

        function isMobileNavigation() {
            return window.matchMedia('(max-width: 991.98px)').matches;
        }

        function setMenuState(isOpen, returnFocus) {
            if (!isMobileNavigation()) {
                $panel.removeClass('open').attr('aria-hidden', 'false');
                $toggle.attr({
                    'aria-expanded': 'false',
                    'aria-label': 'Open navigation'
                }).text('Menu');
                $body.removeClass('kreativ-nav-open');
                return;
            }

            $panel.toggleClass('open', isOpen).attr('aria-hidden', isOpen ? 'false' : 'true');
            $toggle.attr({
                'aria-expanded': isOpen ? 'true' : 'false',
                'aria-label': isOpen ? 'Close navigation' : 'Open navigation'
            }).text(isOpen ? 'Close' : 'Menu');
            $body.toggleClass('kreativ-nav-open', isOpen);

            if (isOpen) {
                window.setTimeout(function () {
                    $panel.find('.kreativ-search-input').first().trigger('focus');
                }, 50);
            } else if (returnFocus) {
                $toggle.trigger('focus');
            }
        }

        setMenuState(false, false);

        $toggle.on('click', function () {
            setMenuState(!$panel.hasClass('open'), false);
        });

        $panel.find('a').on('click', function () {
            setMenuState(false, false);
        });

        $(document).on('keydown.kreativNavigation', function (event) {
            if (event.key === 'Escape' && $panel.hasClass('open')) {
                setMenuState(false, true);
            }
        });

        $(window).on('resize.kreativNavigation', function () {
            setMenuState(false, false);
        });

        $('.kft-output').each(function (index) {
            var $output = $(this);

            if ($output.attr('aria-label') || $output.attr('aria-labelledby')) {
                return;
            }

            $output.attr(
                'aria-label',
                $output.attr('placeholder') || 'Fancy text result ' + (index + 1)
            );
        });
    });

    $('.dropdown .dropdown-toggle').on('click', function () {
        var myDropDown = $(this).next('.dropdown-menu');

        if (myDropDown.is(':visible')) {
            $(this).parent().removeClass('open');
            myDropDown.hide();
        } else {
            myDropDown.fadeIn();
            $(this).parent().addClass('open');
        }

        return false;
    });

    $('html').on('click', function () {
        $('.dropdown-menu').hide();
    });

    $('.dropdown-menu').on('click', function (event) {
        event.stopPropagation();
    });

    $(window).on('scroll', function () {
        var scroll = $(window).scrollTop();

        if (scroll >= 57) {
            $('.kreativ-header').addClass('header-shadow');
        } else {
            $('.kreativ-header').removeClass('header-shadow');
        }

        if (scroll >= 125) {
            $('.sticky-bar').addClass('active');
        } else {
            $('.sticky-bar').removeClass('active');
        }
    });
})(jQuery);
