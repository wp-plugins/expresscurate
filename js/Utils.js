var Utils = (function (jQuery) {
    var isSetup = false;
    /*curate from content feed and bookmarks*/
    function addSources(list, dataElem) {
        var items = [];
        jQuery.each(list, function (index, el) {
            items.push(jQuery(el).find(dataElem).text());
        });
        jQuery('#expresscurate_bookmarks_curate_data').val(JSON.stringify(items));
        jQuery('form#expresscurate_bookmarks_curate').submit();
    }

    /*message for empty lists*/
    function notDefinedMessage(message, list) {
        var pageWithControls = (jQuery('.expresscurate_feed_list').length || jQuery('.expresscurate_bookmarks').length) ? true : false,
            $controls = jQuery('.expresscurate_controls');
        if (list.length > 0) {
            message.addClass('expresscurate_displayNone');
            if (pageWithControls) {
                $controls.removeClass('expresscurate_displayNone');
            }
        } else {
            message.removeClass('expresscurate_displayNone');
            if (pageWithControls) {
                jQuery('.expresscurate_controls li.check').removeClass('active');
                $controls.addClass('expresscurate_displayNone');
            }
        }
    }

    /*show/hide controls in content feed and bookmarks*/
    function checkControls(controls) {
        var $checkboxes = jQuery('.checkInput'),
            atLeastOneIsChecked = $checkboxes.is(':checked'),
            allIsChecked = $checkboxes.find(':checked').length === $checkboxes.length,
            $checkControl = controls.find('.check');
        if (atLeastOneIsChecked) {
            controls.addClass('active');
        } else {
            controls.removeClass('active');
        }
        if (allIsChecked) {
            $checkControl.addClass('active');
        } else {
            $checkControl.removeClass('active');
        }
    }

    /*validation for support and FAQ*/
    function expresscurateSupportSubmit() {
        var $form = jQuery('#expresscurate_support_form'),
            valid_msg = true,
            $supportMessage = jQuery("#expresscurate_support_message"),
            msg = $supportMessage.val(),
            $supportMail = jQuery("#expresscurate_support_email"),
            email = $supportMail.val(),
            regularExpression = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
            valid_email = regularExpression.test(email);

        $form.find('.expresscurate_errorMessage').remove();

        if (msg === "" || !msg) {
            valid_msg = false;
            $supportMessage.after('<label class="expresscurate_errorMessage">Please enter the message</label>');
        } else if (msg.length < 3) {
            valid_msg = false;
            $supportMessage.after('<label class="expresscurate_errorMessage">Message is too short</label>');
        } else {
            $supportMessage.next('.expresscurate_errorMessage').remove();
        }

        if (email === "" || !email) {
            valid_email = false;
            $supportMail.after('<label class="expresscurate_errorMessage">Please enter the email</label>');
        } else if (!valid_email) {
            $supportMail.after('<label class="expresscurate_errorMessage">Email is not valid</label>');
        } else {
            $supportMail.next('.expresscurate_errorMessage').remove();
        }
        if (valid_email && valid_msg) {
            $form.submit();
        }
        return false;
    }

    /*loading for bookmarks and feeds*/
    function startLoading(input, elemToRotate) {
        input.prop('disabled', true);
        elemToRotate.addClass('expresscurate_startRotate');
    }

    function endLoading(input, elemToRotate) {
        input.removeAttr('disabled').focus();
        elemToRotate.removeClass('expresscurate_startRotate');
    }

    function countDown() {
        var $smartPublishingWrap = jQuery('.expresscurate_dashboard_smartPublishing'),
            target_date = $smartPublishingWrap.find('.target_date').html(),
            current_date = $smartPublishingWrap.find('.current_date').html(),
            $countDown = $smartPublishingWrap.find('.countdown'),
            days, hours, minutes, seconds, seconds_left_temp, seconds_left;

        if ($smartPublishingWrap.find('.list > li').length > 0) {
            if (target_date && current_date) {
                target_date = new Date(target_date).getTime();
                current_date = new Date(current_date).getTime();
                seconds_left = (target_date - current_date) / 1000;

                (function loop() {
                    var intervalID = setTimeout(function () {
                        if (seconds_left <= 0) {
                            jQuery.ajax({
                                type: 'POST',
                                url: 'admin-ajax.php?action=expresscurate_smart_publish_event',
                                data: {url: 'link'}
                            }).done(function (res) {
                                var data = jQuery.parseJSON(res);
                                if (data.status === 'success') {
                                    jQuery('#dashboard_widget_smartPublishing').find('.inside').load('admin-ajax.php?action=expresscurate_show_smart_publish', function () {
                                        target_date = $smartPublishingWrap.find('.target_date').html();
                                        current_date = $smartPublishingWrap.find('.current_date').html();
                                        target_date = new Date(target_date).getTime();
                                        current_date = new Date(current_date).getTime();
                                        seconds_left = (target_date - current_date) / 1000;
                                        clearInterval(intervalID);
                                        Utils.countDown();
                                    });
                                }
                            });
                        }
                        seconds_left--;
                        days = parseInt(seconds_left / 86400);
                        seconds_left_temp = seconds_left % 86400;
                        hours = parseInt(seconds_left_temp / 3600);
                        seconds_left_temp = seconds_left_temp % 3600;
                        minutes = parseInt(seconds_left_temp / 60);
                        seconds = parseInt(seconds_left_temp % 60);

                        $countDown.html(hours + ' <b> : </b>' + minutes + ' <b> : </b>' + seconds);
                    }, 1000);
                })();
            }
        }
    }

    function setupUtils() {
        if (jQuery('.expresscurate_settings').length) {
            var $tabs = jQuery('.tabs'),
                tab_id = $tabs.attr('data-currenttab');
            if (tab_id.length < 1) {
                tab_id = 'tab-1';
            }
            jQuery('ul.tabs li').add(jQuery('.tab-content')).removeClass('current');
            $tabs.find('li[data-tab=' + tab_id + ']').add(jQuery("#" + tab_id)).addClass('current');
        }

        jQuery('.expresscurate_blocksContainer').sortable({
            distance: 12,
            forcePlaceholderSize: true,
            items: '.expresscurate_masonryItem',
            tolerance: 'pointer',
            start: function (event, ui) {
                ui.item.parent().masonry('destroy');
            },
            stop: function (event, ui) {
                setTimeout(function () {
                    ui.item.parent().masonry({
                        itemSelector: '.expresscurate_masonryItem',
                        isResizable: true,
                        isAnimated: true,
                        columnWidth: '.expresscurate_masonryItem',
                        gutter: 10
                    });
                }, 100);
            },
            cursor: "move",
            placeholder: "expresscurate_sortablePlaceholder"
        });

        if (jQuery('.expresscurate_dashboard_smartPublishing .topPart .target_date').length) {
            Utils.countDown();
        }
        jQuery('.expresscurate_advancedSEO_widget ul.tabs li,.expresscurate ul.tabs li').click(function () {
            var tab_id = jQuery(this).attr('data-tab');

            jQuery('ul.tabs li').add(jQuery('.tab-content')).removeClass('current');
            jQuery(this).add(jQuery("#" + tab_id)).addClass('current');

            jQuery.ajax({
                type: "POST",
                url: 'admin-ajax.php?action=expresscurate_change_tab_event',
                data: {tab: tab_id}
            });
        });
        jQuery('#expresscurate_sitemap_post_configure_manually').on('change', function () {
            var $options = jQuery('.expresscurate_sitemap_widget .hiddenOptions');
            if (jQuery(this).is(':checked')) {
                $options.removeClass('expresscurate_displayNone').hide().stop(true, true).slideDown('slow');
            } else {
                $options.removeClass('expresscurate_displayNone').stop(true, true).slideUp('slow');
            }
        });
        jQuery('#expresscurate_sitemap_post_exclude_from_sitemap').on('change', function () {
            var $options = jQuery('.expresscurate_sitemap_widget .sitemapOption');
            if (!jQuery(this).is(':checked')) {
                $options.removeClass('expresscurate_displayNone').hide().stop(true, true).slideDown('slow');
            } else {
                $options.removeClass('expresscurate_displayNone').stop(true, true).slideUp('slow');
            }
        });
        jQuery('#exec_function_perm_seen,#cron_setup_manually').on('click', function () {
            var $elem = jQuery(this),
                status = ($elem.is('#cron_setup_manually')) ? 'set' : 'seen';
            jQuery.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_set_cron_permission_status',
                data: {status: status}
            }).done(function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    $elem.parents('div.notice-warning').fadeOut(600);
                }
            });
        });
        jQuery('#expresscurate_sitemap_update_permission').on('click', function () {
            var $elem = jQuery(this),
                status = 'seen';
            jQuery.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_set_sitemap_permission_status',
                data: {status: status}
            }).done(function (res) {
                var data = jQuery.parseJSON(res);
                if (data.status === 'success') {
                    $elem.parents('div.notice-warning').fadeOut(600);
                }
            });
        });
        /*layout*/
        jQuery('.expresscurate_controls .layout').on('click', function () {
            var $wrap = jQuery('.expresscurate_Styles.wrap'),
                page = ($wrap.hasClass('expresscurate_feed_list')) ? 'expresscurate_feed_layout' : 'expresscurate_bookmark_layout',
                layout = '';
            if ($wrap.hasClass('expresscurate_singleColumn')) {
                layout = 'grid';
                $wrap.removeClass('expresscurate_singleColumn');
            } else {
                layout = 'single';
                $wrap.addClass('expresscurate_singleColumn');
            }
            jQuery.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_change_layout_event',
                data: {
                    page: page,
                    layout: layout
                }
            });
            jQuery('.expresscurate_masonryWrap').masonry();
        });
        isSetup = true;
    }

    return {
        setup: function () {
            if (!isSetup) {
                jQuery(document).ready(function () {
                    setupUtils();
                });
            }
        },
        addSources: addSources,
        notDefinedMessage: notDefinedMessage,
        checkControls: checkControls,
        expresscurateSupportSubmit: expresscurateSupportSubmit,
        startLoading: startLoading,
        endLoading: endLoading,
        countDown: countDown
    }
})(window.jQuery);
Utils.setup();