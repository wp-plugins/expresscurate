var Settings = (function(jQuery){

    var isSetup = false;
    var showHideOptions = function(slider, control){
        if (control.is(':checked')) {
            slider.removeClass('expresscurate_displayNone').hide().slideDown('slow');
        } else {
            slider.removeClass('expresscurate_displayNone').slideUp('slow');
        }
    };
    var connectSelectOptions = function(){
        var alertFrequencySelect=jQuery('#expresscurate_content_alert_frequency');
        var selectedValue = parseInt(jQuery('#expresscurate_pull_hours_interval').val());
        alertFrequencySelect.find('option').prop('disabled', false).filter(function(){
            return (this.value < selectedValue);
        }).prop('disabled', true);
        if(parseInt(alertFrequencySelect.find("option:selected").val())<selectedValue) {
            alertFrequencySelect.find("option:selected").prop("selected", false);
        }
    };
    var setupSettings = function(){
        if (jQuery('input[name=expresscurate_post_status]:checked').val() == 'draft') {
            jQuery('#expresscurate_publish_div').show();
        }
        connectSelectOptions();
        jQuery('input[name=expresscurate_post_status]').change(function () {
            if (jQuery('input[name=expresscurate_post_status]:checked').val() == 'draft') {
                jQuery('#expresscurate_publish_div').slideDown('slow');
            } else {
                jQuery('#expresscurate_publish_no').attr('checked', true);
                jQuery('#expresscurate_publish_div').slideUp('slow');
            }

        });
        jQuery('#expresscurate_publish').on('change',function(){
            showHideOptions(jQuery('#smartPublishingWrap'),jQuery(this));
        });
        /*feed*/
        jQuery('#expresscurate_enable_content_alert').on('change',function(){
            showHideOptions(jQuery('.emailAlertSlider'),jQuery(this));
        });
        jQuery('#expresscurate_pull_hours_interval').on('change', function () {
            connectSelectOptions();
        });
        /*sitemap*/
        jQuery('#expresscurate_sitemap_submit_webmasters').on('change',function(){
            showHideOptions(jQuery('.sitemapUpdateFrequency'),jQuery(this));
        });
        jQuery('.expresscurate #generateSiteMap').on('click',function(){
            jQuery.post('admin-ajax.php?action=expresscurate_sitemap_generate', function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    jQuery('.expresscurate #submitSiteMap').removeClass('expresscurate_displayNone');
                }else{
                    jQuery('.expresscurate #submitSiteMap').addClass('expresscurate_displayNone');
                }
            });
        });
        jQuery('.expresscurate #submitSiteMap').on('click',function(){
            jQuery.post('admin-ajax.php?action=expresscurate_sitemap_submit', function (res) {
              /*var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                   // jQuery('.expresscurate #SubmitSiteMap').removeClass('expresscurate_displayNone');
                }*/
            });
        });
        /**/
        jQuery('#expresscurate_seo').click(function () {
            var slider=jQuery('#publisherWrap'),
                sitemapTab=jQuery('#sitemapTab');
            if (jQuery(this).is(':checked')) {
                slider.removeClass('expresscurate_displayNone').hide().slideDown('slow');
                sitemapTab.removeClass('expresscurate_displayNone');
            } else {
                slider.slideUp('slow');
                sitemapTab.addClass('expresscurate_displayNone');
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