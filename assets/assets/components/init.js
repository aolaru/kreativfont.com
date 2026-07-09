(function ($) {
    $(function () {
        $('[data-toggle="offcanvas"]').on('click', function () {
            $('.offcanvas-collapse').toggleClass('open');
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
