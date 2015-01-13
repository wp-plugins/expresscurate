var Utils = (function (jQuery) {
    /* detect  var isChromium = window.chrome,
     vendorName = window.navigator.vendor;
     if (isChromium !== null && isChromium !== undefined && vendorName === "Google Inc.") {
     detectChromeExtension('nldipdepdfjilejlpeeknodkpiajhfkf', myCallbackFunction);
     }*/
    var isSetup = false,
        interval;
    var detectChromeExtension = function (extensionId, callback) {
        if (typeof(chrome) !== 'undefined') {
            var testUrl = 'chrome-extension://' + extensionId + '/app/index.htm';
            jQuery.ajax({
                url: testUrl,
                timeout: 1000,
                type: 'HEAD',
                success: function () {
                    if (typeof(callback) == 'function')
                        callback.call(this, true);
                },
                error: function () {
                    if (typeof(callback) == 'function')
                        callback.call(this, false);
                }
            });
        } else {
            if (typeof(callback) == 'function')
                callback.call(this, false);
        }
    };
    var myCallbackFunction = function (extensionExists) {
        if (extensionExists) {
            console.log('Extension present');
        } else {
            console.log('Extension not present');
        }
    };
    return {
        /*curate from content feed and bookmarks*/
        addSources: function (list, dataElem) {
            var items = [];
            jQuery.each(list, function (index, el) {
                var item = {};
                items.push(jQuery(el).find(dataElem).text());
            });
            jQuery('#expresscurate_bookmarks_curate_data').val(JSON.stringify(items));
            jQuery('form#expresscurate_bookmarks_curate').submit();
        },
        /*message for empty lists*/
        notDefinedMessage: function (message, list) {
            var pageWithControls = (jQuery('.expresscurate_feed_list').length || jQuery('.expresscurate_bookmarks').length) ? true : false;
            if (list.length > 0) {
                message.addClass('expresscurate_displayNone');
                if (pageWithControls) {
                    jQuery('.expresscurate_controls').removeClass('expresscurate_displayNone');
                }
            } else {
                message.removeClass('expresscurate_displayNone');
                if (pageWithControls) {
                    jQuery('.expresscurate_controls li.check').removeClass('active');
                    jQuery('.expresscurate_controls').addClass('expresscurate_displayNone');
                }
            }
        },
        /*show/hide controls in content feed and bookmarks*/
        checkControls: function (controls) {
            var checkboxes=jQuery('.checkInput'),
                atLeastOneIsChecked = checkboxes.is(':checked'),
                allIsChecked = jQuery('.checkInput:checked').length == checkboxes.length,
                checkControl=jQuery('.expresscurate_controls .check');
            if (atLeastOneIsChecked) {
                controls.addClass('active');
            } else {
                controls.removeClass('active');
            }
            if (allIsChecked) {
                checkControl.addClass('active');
            } else {
                checkControl.removeClass('active');
            }
        },
        /*validation for support and FAQ*/
        expresscurateSupportSubmit: function () {
            jQuery('#expresscurate_support_form .expresscurate_errorMessage').remove();
            var valid_msg = true;
            var valid_email = true,
                supportMessage = jQuery("#expresscurate_support_message");
            var msg = supportMessage.val();
            if (msg == "" || msg == null) {
                valid_msg = false;
                supportMessage.after('<label class="expresscurate_errorMessage">Please enter the message</label>');
            } else if (msg.length < 3) {
                valid_msg = false;
                supportMessage.after('<label class="expresscurate_errorMessage">Message is too short</label>');
            } else
                supportMessage.next('.expresscurate_errorMessage').remove();

            var supportMail = jQuery("#expresscurate_support_email"),
                email = supportMail.val();
            var regularExpression = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            valid_email = regularExpression.test(email);
            if (email == "" || email == null) {
                valid_email = false;
                supportMail.after('<label class="expresscurate_errorMessage">Please enter the email</label>');
            } else if (!valid_email) {
                supportMail.after('<label class="expresscurate_errorMessage">Email is not valid</label>');
            } else
                supportMail.next('.expresscurate_errorMessage').remove();
            if (valid_email && valid_msg) {
                jQuery("#expresscurate_support_form").submit();
            }
            return false;
        },
        /*loading for bookmarks and feeds*/
        startLoading: function (input, elemToRotate) {
            input.prop('disabled', true);
            elemToRotate.addClass('expresscurate_startRotate');
        },
        endLoading: function (input, elemToRotate) {
            input.removeAttr('disabled');
            elemToRotate.removeClass('expresscurate_startRotate');
            input.focus();
        },

        countDown: function () {
            if (jQuery('.expresscurate_dashboard_smartPublishing .list > li').length > 0) {
                var target_date = jQuery('.expresscurate_dashboard_smartPublishing .topPart .target_date').html(),
                    current_date = jQuery('.expresscurate_dashboard_smartPublishing .topPart .current_date').html();
                if (target_date && current_date) {
                    var countDown = jQuery('.expresscurate_dashboard_smartPublishing .topPart .countdown'),
                        days, hours, minutes, seconds;
                    target_date = new Date(target_date).getTime();
                    current_date = new Date(current_date).getTime();
                    var seconds_left = (target_date - current_date) / 1000;
                    var intervalID = setInterval(function () {
                        if (seconds_left <= 0) {
                            jQuery.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_smart_publish_event', {url: 'link'}, function (res) {
                                var data = jQuery.parseJSON(res);
                                if (data.status == 'success') {
                                    jQuery("#dashboard_widget_smartPublishing .inside").load(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_show_smart_publish', function () {
                                        target_date = jQuery('.expresscurate_dashboard_smartPublishing .topPart .target_date').html();
                                        current_date = jQuery('.expresscurate_dashboard_smartPublishing .topPart .current_date').html();
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

                        countDown.html(hours + ' <b> : </b>' + minutes + ' <b> : </b>' + seconds);
                    }, 1000);
                }
            }
        },

        setup: function () {
            if (!isSetup) {
                jQuery(document).ready(function () {
                    if (jQuery('.expresscurate_settings').length) {
                        var tabs=jQuery('.tabs'),
                            tab_id = tabs.attr('data-currenttab');
                        if (tab_id.length < 1) {
                            tab_id = 'tab-1';
                        }
                        jQuery('ul.tabs li').removeClass('current');
                        jQuery('.tab-content').removeClass('current');
                        tabs.find('li[data-tab=' + tab_id + ']').addClass('current');
                        jQuery("#" + tab_id).addClass('current');
                    }

                  /*      jQuery('.expresscurate_blocksContainer').sortable({
                            distance: 12,
                            forcePlaceholderSize: true,
                            items: '.expresscurate_masonryItem',
                            tolerance: 'pointer',
                            containment: "parent",
                            start: function (event, ui) {
                                ui.item.parent().masonry('destroy');
                            },
                            change: function (event, ui) {
                            },
                            stop: function (event, ui) {
                                setTimeout(function(){
                                    ui.item.parent().masonry({
                                        itemSelector: '.expresscurate_masonryItem',
                                        isResizable: true,
                                        isAnimated: true,
                                        columnWidth: '.expresscurate_masonryItem',
                                        gutter: 10
                                    });
                                },100);
                            },
                            cursor: "move",
                            placeholder: "expresscurate_sortablePlaceholder",
                            grid: [ 20, 10 ]
                        });*/

                    if (jQuery('.expresscurate_dashboard_smartPublishing .topPart .target_date').length) {
                        Utils.countDown();
                    }
                    jQuery('.expresscurate_advancedSEO_widget ul.tabs li,.expresscurate ul.tabs li').click(function () {
                        var tab_id = jQuery(this).attr('data-tab');

                        jQuery('ul.tabs li').removeClass('current');
                        jQuery('.tab-content').removeClass('current');

                        jQuery(this).addClass('current');
                        jQuery("#" + tab_id).addClass('current');

                        //Save tab data

                        jQuery.post('admin-ajax.php?action=expresscurate_change_tab_event', {tab: tab_id}, function (res) {
                        });
                    });
                    jQuery('#expresscurate_sitemap_post_configure_manually').on('change', function () {
                        var options = jQuery('.expresscurate_sitemap_widget .hiddenOptions');
                        if (jQuery(this).is(':checked')) {
                            options.removeClass('expresscurate_displayNone').hide().slideDown('slow');
                        } else {
                            options.removeClass('expresscurate_displayNone').slideUp('slow');
                        }
                    });
                    jQuery('#expresscurate_sitemap_post_exclude_from_sitemap').on('change', function () {
                        var options = jQuery('.expresscurate_sitemap_widget .sitemapOption');
                        if (!jQuery(this).is(':checked')) {
                            options.removeClass('expresscurate_displayNone').hide().slideDown('slow');
                        } else {
                            options.removeClass('expresscurate_displayNone').slideUp('slow');
                        }
                    });
                    isSetup = true;
                });
            }
        }

    }
})(window.jQuery);
Utils.setup();