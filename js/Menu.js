var Menu = (function(jQuery){
    var moveMenuArrow = function () {
        var menuItemWidth = jQuery('.expresscurate_tabMenu a').width(),
            index = jQuery('.expresscurate_tabMenu a.active').index();
        jQuery('.expresscurate_tabMenu .arrow').css({'left': (index * menuItemWidth) - menuItemWidth / 2 + 30 + 'px'});
    };

    var setupMenu = function(){
        moveMenuArrow();
        jQuery('.expresscurate_tabMenu a').hover(function () {
            var menuItemWidth = jQuery(this).width(),
                index = jQuery(this).index();
            jQuery('.expresscurate_tabMenu .arrow').css({'left': (index * menuItemWidth) - menuItemWidth / 2 + 30 + 'px'});
        });
        jQuery('.expresscurate_tabMenu').mouseleave(function () {
            moveMenuArrow();
        });
    };

    var isSetup = false;

    return {
        setup: function(){
            if(!isSetup){
                jQuery(document).ready(function(){
                    setupMenu();
                    isSetup = true;
                });
            }
        },

        moveMenuArrow: moveMenuArrow
    }
})(window.jQuery);

Menu.setup();