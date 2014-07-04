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
        jQuery('div#expresscurate_publish').on('click',function(){
            if (jQuery(this).hasClass('switchOn')) {
                jQuery('#smartPublishingWrap').removeClass('expresscurate_displayNone').slideUp('slow');
            } else {
                jQuery('#smartPublishingWrap').removeClass('expresscurate_displayNone').hide().slideDown('slow');
            }
        });
        jQuery('#expresscurate_seo').click(function () {
            if (jQuery('input[name=expresscurate_seo]:checked').val() == '0') {
                jQuery('#publisherWrap').removeClass('expresscurate_displayNone').hide().slideDown('slow');
            } else {
                jQuery('#publisherWrap').slideUp('slow');
            }
        });

        jQuery('input[name=expresscurate_publisher]').bind("change paste keyup", function () {
            var href = jQuery(this).next('span').children('a').attr('href');
            var rest = href.substring(0, href.lastIndexOf("user_profile") + 13);
            jQuery(this).next('span').children('a').attr('href', rest + jQuery(this).val());
        });
        jQuery('.switch').on("click", function (e) {
            var elem = jQuery(this),
                slider = jQuery(this).find('.slider'),
                input_id = elem.attr('id');
            if (slider.hasClass('sliderOffBack')) {
                slider.removeClass('sliderOffBack').addClass('sliderOnBack');
                elem.removeClass('switchOff').addClass('switchOn');
                jQuery('#' + input_id + '_yes').attr('checked', 'checked');
                jQuery('#' + input_id + '_no').attr("checked", false);

            } else {
                slider.removeClass('sliderOnBack').addClass('sliderOffBack');
                elem.removeClass('switchOn').addClass('switchOff');
                jQuery('#' + input_id + '_yes').attr('checked', false);
                jQuery('#' + input_id + '_no').attr('checked', 'checked');
            }
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