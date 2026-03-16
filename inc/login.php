<?php

function kreativ_login_head() {
    echo "
    <style>
    body.login #login h1 a {
        background: url('//kreativfont.com/wp-content/uploads/2019/08/Kreativ-Font-logo-128.png') no-repeat scroll center top transparent;
        height: 128px;
        width: 128px;
    }
    </style>
    ";
}
add_action( 'login_head', 'kreativ_login_head' );

function kreativ_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'kreativ_login_logo_url' );

function kreativ_login_logo_url_title() {
    return get_bloginfo( 'name' );
}
add_filter( 'login_headertitle', 'kreativ_login_logo_url_title' );
