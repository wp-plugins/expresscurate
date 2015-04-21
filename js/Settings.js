var ExpressCurateSettings = (function ($) {
    var isSetup = false;

    function showHideOptions(slider, control) {
        if (control.is(':checked')) {
            slider.removeClass('expresscurate_displayNone').hide().slideDown('slow');
        } else {
            slider.removeClass('expresscurate_displayNone').slideUp('slow');
        }
    }

    function connectSelectOptions() {
        var $alertFrequencySelect = $('#expresscurate_content_alert_frequency'),
            selectedValue = parseInt($('#expresscurate_pull_hours_interval').val()),
            $selectedFrequency = $alertFrequencySelect.find("option:selected");
        $alertFrequencySelect.find('option').prop('disabled', false).filter(function () {
            return (this.value < selectedValue);
        }).prop('disabled', true);
        if (parseInt($selectedFrequency.val()) < selectedValue) {
            $selectedFrequency.prop("selected", false);
        }
    }

    function setupSettings() {
        var $submitSitemap = $('.expresscurate #submitSiteMap');
        if ($('input[name=expresscurate_post_status]:checked').val() === 'draft') {
            $('#expresscurate_publish_div').show();
        }
        connectSelectOptions();

        /*post default status*/
        $('input[name=expresscurate_post_status]').change(function () {
            var $publishDiv = $('#expresscurate_publish_div');
            if ($('input[name=expresscurate_post_status]:checked').val() === 'draft') {
                $publishDiv.stop(true, true).slideDown('slow');
            } else {
                $('#expresscurate_publish_no').attr('checked', true);
                $publishDiv.stop(true, true).slideUp('slow');
            }

        });
        /*settings page traching*/
        $('#tab-1').on('change', 'input', function () {
            ExpressCurateUtils.track('/settings/general');
        });
        $('#tab-2').on('change', 'input, select', function () {
            ExpressCurateUtils.track('/settings/smartpublishing');
        });
        $('#tab-3').on('change', 'input, select', function () {
            ExpressCurateUtils.track('/settings/sitemap');
        });
        $('#tab-4').on('change', 'input', function () {
            ExpressCurateUtils.track('/settings/extension');
        });
        $('#tab-5').on('change', 'input, select', function () {
            ExpressCurateUtils.track('/settings/feed');
        });
        /*smart publishing*/
        $('#expresscurate_publish').on('change', function () {
            showHideOptions($('#smartPublishingWrap'), $(this));
        });

        /*feed*/
        $('#expresscurate_enable_content_alert').on('change', function () {
            showHideOptions($('.emailAlertSlider'), $(this));
        });
        $('#expresscurate_pull_hours_interval').on('change', function () {
            connectSelectOptions();
        });

        /*sitemap*/
        $('#expresscurate_sitemap_submit').on('change', function () {
            showHideOptions($('.sitemapUpdateFrequency'), $(this));
            var status = '',
                $submitSitemap = $('.expresscurate #submitSiteMap'),
                $autorize = $('.getApiKey');
            status = ($(this).is(':checked')) ? 'on' : 'off';
            if ($(this).is(':checked') && $submitSitemap.hasClass('generated')) {
                $submitSitemap.removeClass('expresscurate_displayNone');
            } else {
                $submitSitemap.addClass('expresscurate_displayNone');
            }
            if ($submitSitemap.hasClass('generated')) {
                $autorize.addClass('expresscurate_displayNone');
            } else {
                $autorize.removeClass('expresscurate_displayNone');
            }
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_save_sitemap_google_status',
                data: {status: status}
            });
        });
        $('.expresscurate #generateSiteMap').on('click', function () {
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_sitemap_generate'
            }).done(function (res) {
                var data = $.parseJSON(res);
                if (data.status === 'success') {
                    $submitSitemap.removeClass('expresscurate_displayNone').addClass('generated');
                } else {
                    $submitSitemap.addClass('expresscurate_displayNone').removeClass('generated');
                }
            });
        });
        $submitSitemap.on('click', function () {
            $('.expresscurate_Error').remove();
            var message = '',
                className = '';
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_sitemap_submit'
            }).done(function (res) {
                var data = $.parseJSON(res);
                if (data.status === 0) {
                    $submitSitemap.addClass('generated');
                    message = data.message;
                    className='expresscurate_SettingsSuccess';
                } else if (data.status === 1) {
                    message = data.message;
                    className='expresscurate_SettingsError';
                } else if (data.status === 2) {
                    message = data.message;
                    className='expresscurate_SettingsError';
                } else if (data.status === 3) {
                    message = data.message;
                    className='expresscurate_SettingsError';
                }
            }).always(function () {
                $submitSitemap.after('<p class="expresscurate_Error '+className+'">' + message + '</p>');
            });
        });

        /*SEO settings*/
        $('#expresscurate_seo').click(function () {
            var $this = $(this),
                $slider = $('#publisherWrap'),
                $sitemapTab = $('#sitemapTab');
            showHideOptions($slider, $this);
            if ($this.is(':checked')) {
                $sitemapTab.removeClass('expresscurate_displayNone');
            } else {
                $sitemapTab.addClass('expresscurate_displayNone');
            }
        });
        $('input[name=expresscurate_publisher]').bind("change paste keyup", function () {
            var href = $(this).next('span').children('a').attr('href'),
                rest = href.substring(0, href.lastIndexOf("user_profile") + 13);
            $(this).next('span').children('a').attr('href', rest + $(this).val());
        });
    }

    return {
        setup: function () {
            if (!isSetup) {
                $(document).ready(function () {
                    setupSettings();
                    isSetup = true;
                });
            }
        }
    }
})(window.jQuery);

ExpressCurateSettings.setup();