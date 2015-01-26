var Settings = (function (jQuery) {
    var isSetup = false;

    function showHideOptions(slider, control) {
        if (control.is(':checked')) {
            slider.removeClass('expresscurate_displayNone').hide().slideDown('slow');
        } else {
            slider.removeClass('expresscurate_displayNone').slideUp('slow');
        }
    }

    function connectSelectOptions() {
        var $alertFrequencySelect = jQuery('#expresscurate_content_alert_frequency'),
            selectedValue = parseInt(jQuery('#expresscurate_pull_hours_interval').val()),
            $selectedFrequency = $alertFrequencySelect.find("option:selected");
        $alertFrequencySelect.find('option').prop('disabled', false).filter(function () {
            return (this.value < selectedValue);
        }).prop('disabled', true);
        if (parseInt($selectedFrequency.val()) < selectedValue) {
            $selectedFrequency.prop("selected", false);
        }
    }

    function setupSettings() {
        var $submitSitemap = jQuery('.expresscurate #submitSiteMap');
        if (jQuery('input[name=expresscurate_post_status]:checked').val() === 'draft') {
            jQuery('#expresscurate_publish_div').show();
        }
        connectSelectOptions();
        jQuery('input[name=expresscurate_post_status]').change(function () {
            var $publishDiv = jQuery('#expresscurate_publish_div');
            if (jQuery('input[name=expresscurate_post_status]:checked').val() === 'draft') {
                $publishDiv.stop(true, true).slideDown('slow');
            } else {
                jQuery('#expresscurate_publish_no').attr('checked', true);
                $publishDiv.stop(true, true).slideUp('slow');
            }

        });
        jQuery('#expresscurate_publish').on('change', function () {
            showHideOptions(jQuery('#smartPublishingWrap'), jQuery(this));
        });
        /*feed*/
        jQuery('#expresscurate_enable_content_alert').on('change', function () {
            showHideOptions(jQuery('.emailAlertSlider'), jQuery(this));
        });
        jQuery('#expresscurate_pull_hours_interval').on('change', function () {
            connectSelectOptions();
        });
        /*sitemap*/
        jQuery('#expresscurate_sitemap_submit').on('change', function () {
            showHideOptions(jQuery('.sitemapUpdateFrequency'), jQuery(this));
            var status = '',
                $submitSitemap = jQuery('.expresscurate #submitSiteMap');
            if (jQuery(this).is(':checked') && $submitSitemap.hasClass('generated')) {
                status = 'on';
                $submitSitemap.removeClass('expresscurate_displayNone');
            } else {
                status = 'off';
                $submitSitemap.addClass('expresscurate_displayNone');
            }
            jQuery.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_save_sitemap_google_status',
                data: {status: status}
            });
        });
        jQuery('.expresscurate #generateSiteMap').on('click', function () {
            jQuery.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_sitemap_generate'
            }).done(function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    $submitSitemap.removeClass('expresscurate_displayNone').addClass('generated');
                } else {
                    $submitSitemap.addClass('expresscurate_displayNone').removeClass('generated');
                }
            });
        });
        $submitSitemap.on('click', function () {
            jQuery.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_sitemap_submit'
            }).done(function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    $submitSitemap.removeClass('expresscurate_displayNone').addClass('generated');
                }
            });
        });
        /**/
        jQuery('#expresscurate_seo').click(function () {
            var $slider = jQuery('#publisherWrap'),
                $sitemapTab = jQuery('#sitemapTab');
            if (jQuery(this).is(':checked')) {
                $slider.removeClass('expresscurate_displayNone').hide().slideDown('slow');
                $sitemapTab.removeClass('expresscurate_displayNone');
            } else {
                $slider.stop(true, true).slideUp('slow');
                $sitemapTab.addClass('expresscurate_displayNone');
            }
        });
        jQuery('input[name=expresscurate_publisher]').bind("change paste keyup", function () {
            var href = jQuery(this).next('span').children('a').attr('href'),
                rest = href.substring(0, href.lastIndexOf("user_profile") + 13);
            jQuery(this).next('span').children('a').attr('href', rest + jQuery(this).val());
        });
    }

    return {
        setup: function () {
            if (!isSetup) {
                jQuery(document).ready(function () {
                    setupSettings();
                    isSetup = true;
                });
            }
        }
    }
})(window.jQuery);

Settings.setup();