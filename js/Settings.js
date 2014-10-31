var Settings = (function(jQuery){

    var isSetup = false;

    var setupSettings = function(){
        if (jQuery('input[name=expresscurate_post_status]:checked').val() == 'draft') {
            jQuery('#expresscurate_publish_div').show();
        }
        jQuery('input[name=expresscurate_post_status]').change(function () {
            if (jQuery('input[name=expresscurate_post_status]:checked').val() == 'draft') {
                jQuery('#expresscurate_publish_div').slideDown('slow');
            } else {
                jQuery('#expresscurate_publish_no').attr('checked', true);
                jQuery('#expresscurate_publish_div').slideUp('slow');
            }

        });
        jQuery('#expresscurate_publish').on('change',function(){
            var slider=jQuery('#smartPublishingWrap');
            if (jQuery(this).is(':checked')) {
                slider.removeClass('expresscurate_displayNone').hide().slideDown('slow');
            } else {
                slider.removeClass('expresscurate_displayNone').slideUp('slow');
            }
        });
        jQuery('#expresscurate_seo').click(function () {
            var slider=jQuery('#publisherWrap');
            if (jQuery(this).is(':checked')) {
                slider.removeClass('expresscurate_displayNone').hide().slideDown('slow');
            } else {
                slider.slideUp('slow');
            }
        });
        jQuery('input[name=expresscurate_publisher]').bind("change paste keyup", function () {
            var href = jQuery(this).next('span').children('a').attr('href');
            var rest = href.substring(0, href.lastIndexOf("user_profile") + 13);
            jQuery(this).next('span').children('a').attr('href', rest + jQuery(this).val());
        });
    };

    return {
        setup: function(){
            if(!isSetup){
                jQuery(document).ready(function(){
                    setupSettings();
                    isSetup = true;
                });
            }
        }
    }
})(window.jQuery);

Settings.setup();