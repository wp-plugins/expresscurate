var DashboardWidget = (function (jQuery){

    var isSetup = false;

    var setupDashboardWidget = function(){
        if (jQuery('.expresscurate_dashboard .expresscurate_background_wrap').length < 1) {
            jQuery('.expresscurate_dashboard .dashboardMessage').removeClass('expresscurate_displayNone');
        }
    };

    return{
        setup: function(){
           if(!isSetup){
               jQuery(document).ready(function(){
                   setupDashboardWidget();
                   isSetup = true;
               });
           }
        }
    }
})(window.jQuery);

DashboardWidget.setup();