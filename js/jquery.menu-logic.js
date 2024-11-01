jQuery(document).ready(function($) {
    console.log(ml);
    // Add tab
    $('.nav-tab-wrapper').append(ml.nav_tab);
    
    // Move Menu Logic
    $('.menu-logic-wrap').insertAfter('#nav-menus-frame');
    
    // Menu Logic is Active
    if ( 'menu-logic' == ml.action ) {
        // Change Active Tab to Menu Logic
        $(".nav-tab-wrapper a:contains('Edit Menus')").removeClass('nav-tab-active').addClass('edit-menu').attr('onclick','javascript: mlEditMenu(); return false;');
        
        // Hide Navigation Menu
        $('#nav-menus-frame').hide();
        
        $('.edit-menu').on('click', function(e){
            e.preventDefault();
            mlEditMenu();
        });
    }
});

function mlEditMenu(action) {
    if ( 'hide' == action ) {
        jQuery('.edit-menu').removeClass('nav-tab-active');
        jQuery('#nav-menus-frame').hide();
        mlMenuLogic('show');
    } else {
        jQuery('.edit-menu').addClass('nav-tab-active');
        jQuery('#nav-menus-frame').fadeIn();
        mlMenuLogic('hide');
    }
}

function mlMenuLogic(action) {
    if ( 'hide' == action ) {
        jQuery('.menu-logic-tab').removeClass('nav-tab-active');
        jQuery('.menu-logic-wrap').hide();
        mlEditMenu('show')
    } else {
        jQuery('.edit-menu').addClass('nav-tab-active');
        jQuery('.menu-logic-wrap').fadeIn();
        mlEditMenu('hide')
    }
}

function mlToggle(hide,show) {
    
}