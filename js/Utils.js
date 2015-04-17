var ExpressCurateUtils = (function ($) {
    var isSetup = false;
    /*curate from content feed and bookmarks*/
    function addSources(list, dataElem) {
        var items = [];
        $.each(list, function (index, el) {
            items.push($(el).find(dataElem).text());
        });
        $('#expresscurate_bookmarks_curate_data').val(JSON.stringify(items));
        $('form#expresscurate_bookmarks_curate').submit();
        ga('expresscurate.send', 'event', 'button', 'click', 'curate');
    }

    /*message for empty lists*/
    function notDefinedMessage(message, list) {
        var pageWithControls = ($('.expresscurate_feed_list').length || $('.expresscurate_bookmarks').length) ? true : false,
            $controls = $('.expresscurate_controls');
        if (list.length > 0) {
            message.addClass('expresscurate_displayNone');
            if (pageWithControls) {
                $('.expresscurate_controls li.check').removeClass('disabled');
                //$('.expresscurate_controls li.layout').removeClass('expresscurate_displayNone');
                ExpressCurateBookmarks.fixedMenu();
            }
        } else {
            message.removeClass('expresscurate_displayNone');
            if (pageWithControls) {
                $controls.removeClass('active');
                //$('.expresscurate_controls li.layout').addClass('expresscurate_displayNone');
                $('.expresscurate_controls li.check').addClass('disabled');
                $('.expresscurate_controls li.pull').add($('.expresscurate_controls li.pullTime')).addClass('active');
            }
        }
    }

    /*error messages*/
    function validationMessages(messageText, message, input) {
        $(message).text(messageText).addClass('errorActive');
        $(input).prop('disabled', true).blur().addClass('expresscurate_errorMessageInput');
        setTimeout(function () {
            $(message).removeClass('errorActive');
            setTimeout(function () {
                $(message).text('');
                $(input).removeClass('expresscurate_errorMessageInput').removeAttr('disabled').focus();
            }, 300);
        }, 3000);
    }

    /*show/hide controls in content feed and bookmarks*/
    function checkControls(controls) {
        var $checkboxes = $('.checkInput'),
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
        var $form = $('#expresscurate_support_form'),
            validMsg = true,
            $supportMessage = $("#expresscurate_support_message"),
            msg = $supportMessage.val(),
            $supportMail = $("#expresscurate_support_email"),
            email = $supportMail.val(),
            regularExpression = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
            validEmail = regularExpression.test(email),
            $messageError = $('#expresscurate_support_message_validation'),
            $mailError = $('#expresscurate_support_email_validation');

        $form.find('.expresscurate_errorMessage').text('');

        if (msg === "" || !msg) {
            validMsg = false;
            validationMessages('Please enter the message', $messageError, $supportMessage);
        } else if (msg.length < 3) {
            validMsg = false;
            validationMessages('Message is too short', $messageError, $supportMessage);
        }

        if (email === "" || !email) {
            validEmail = false;
            validationMessages('Please enter the email', $mailError, $supportMail);
        } else if (!validEmail) {
            validationMessages('Email is not valid', $mailError, $supportMail);
        }
        if (validEmail && validMsg) {
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
        input.removeAttr('disabled');
        elemToRotate.removeClass('expresscurate_startRotate');
    }

    /*add template*/
    function getTemplate(templateName, data) {
        var template = wp.template(templateName);
        return template(data);
    }

    function countDown() {
        var $smartPublishingWrap = $('.expresscurate_dashboard_smartPublishing'),
            targetDate = $smartPublishingWrap.find('.target_date').html(),
            currentDate = $smartPublishingWrap.find('.current_date').html(),
            $countDown = $smartPublishingWrap.find('.countdown'),
            days, hours, minutes, seconds, secondsLeftTemp, secondsLeft;

        if ($smartPublishingWrap.find('.list > li').length > 0) {
            if (targetDate && currentDate) {
                targetDate = new Date(targetDate).getTime();
                currentDate = new Date(currentDate).getTime();
                secondsLeft = (targetDate - currentDate) / 1000;

                (function loop() {
                    var intervalID = setTimeout(function () {
                        if (secondsLeft <= 0) {
                            $.ajax({
                                type: 'POST',
                                url: 'admin-ajax.php?action=expresscurate_smart_publish_event',
                                data: {url: 'link'}
                            }).done(function (res) {
                                var data = $.parseJSON(res);
                                if (data.status === 'success') {
                                    $('#dashboard_widget_smartPublishing').find('.inside').load('admin-ajax.php?action=expresscurate_show_smart_publish', function () {
                                        targetDate = $smartPublishingWrap.find('.target_date').html();
                                        currentDate = $smartPublishingWrap.find('.current_date').html();
                                        targetDate = new Date(targetDate).getTime();
                                        currentDate = new Date(currentDate).getTime();
                                        secondsLeft = (targetDate - currentDate) / 1000;
                                        clearInterval(intervalID);
                                        countDown();
                                    });
                                }
                            });
                        }
                        secondsLeft--;
                        days = parseInt(secondsLeft / 86400);
                        secondsLeftTemp = secondsLeft % 86400;
                        hours = parseInt(secondsLeftTemp / 3600);
                        secondsLeftTemp = secondsLeftTemp % 3600;
                        minutes = parseInt(secondsLeftTemp / 60);
                        seconds = parseInt(secondsLeftTemp % 60);

                        $countDown.html(hours + ' <b> : </b>' + minutes + ' <b> : </b>' + seconds);
                    }, 1000);
                })();
            }
        }
    }

    function track(action, curate) {
        if (!siteSendAnalytics) {
            ga('expresscurate.send', 'pageview', {
                'page': '/site/' + expresscurate_track_hash
            });
        }
        if (!siteWpSendAnalytics) {
            ga('expresscurate.send', 'pageview', {
                'page': '/site/wp/' + expresscurate_track_hash
            });
        }
        if (curate) {
            ga('expresscurate.send', 'pageview', {
                'page': '/site/curate/' + expresscurate_track_hash
            });
        }
        ga('expresscurate.send', 'pageview', {
            'page': '/action/wp' + action
        });
    }

    function setupUtils() {
        var pathname = window.location.pathname,
            postOldStatus,
            postNewStatus;
        if (pathname.match(/\/edit.php$/, 'gmi')) {
            $(document).on('focus', 'select[name="_status"]', function () {
                postOldStatus = this.value;
            });
            $(document).on('change', 'select[name="_status"]', function () {
                postNewStatus = this.value;
            });
            $(document).on('mouseup', '.inline-edit-save .save', function () {
                var $this = $(this),
                    postId = $this.parents('tr').attr('id'),
                    $post = $('#post-' + postId.split('-')[1]),
                    curated = $post.find('.column-curated em').text();
                if (postOldStatus != postNewStatus && postNewStatus == 'publish' && curated == 'Yes') {
                    track('/post-edit/publish',true);
                    postOldStatus = postOldStatus = null;
                }
            });
        }
        /*support submit*/
        $('#expresscurate_support_form').on('click', '.feedbackButton, .askButton', function () {
            expresscurateSupportSubmit();
        });
        /*settings page tabs*/
        if ($('.expresscurate_settings').length) {
            var $tabs = $('.tabs'),
                tabID = $tabs.attr('data-currenttab'),
                currentTab;
            if (tabID.length < 1) {
                tabID = 'tab-1';
            }
            currentTab = $tabs.find('li[data-tab=' + tabID + ']');
            if (tabID && !currentTab.hasClass('disabled')) {
                $('ul.tabs li').add($('.tab-content')).removeClass('current');
                currentTab.add($("#" + tabID)).addClass('current');
            }
        }
        /*expressCurate dashboard*/
        $('.expresscurate_blocksContainer').sortable({
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
                var order = [];
                $('.expresscurate_blocksContainer > div').each(function (index, value) {
                    order.push($(this).attr('id'));
                });
                $.ajax({
                    type: 'POST',
                    url: 'admin-ajax.php?action=dashboard_items_order',
                    data: {item_order: order}
                });
            },
            cursor: "move",
            placeholder: "expresscurate_sortablePlaceholder"
        });

        if ($('.expresscurate_dashboard_smartPublishing .topPart .target_date').length) {
            countDown();
        }
        /*tabs*/
        $('.expresscurate_advancedSEO_widget ul.tabs li,.expresscurate ul.tabs li').click(function () {
            var $this = $(this),
                tabID = $this.attr('data-tab');
            if (!$this.hasClass('disabled')) {
                $('ul.tabs li').add($('.tab-content')).removeClass('current');
                $this.add($("#" + tabID)).addClass('current');

                $.ajax({
                    type: "POST",
                    url: 'admin-ajax.php?action=expresscurate_change_tab_event',
                    data: {tab: tabID}
                });
            }
        });
        /*Advanced SEO tracking*/
        $('.expresscurate_sitemap_widget').on('change', 'input , select', function () {
            track('/seo-advanced/sitemap');
        });
        $('#expresscurate_social_widget input').on('change', function () {
            track('/seo-advanced/social');
        });
        $('#expresscurate_advancedSEO_widget input').on('change', function () {
            track('/seo-advanced/general');
        });
        /*dashboard tracking*/
        $('.expresscurate_keywordsBlock, #keyWordsIntOverTime, #keyWordsRelTopics').on('click', '.settingsLink', function () {
            track('/dashboard/linkkeywordsettings');
        });
        $('.expresscurate_bookmarksBlock').on('click', '.settingsLink', function () {
            track('/dashboard/linkbookmarks');
        });
        $('.expresscurate_feedBlock').on('click', '.settingsLink', function () {
            track('/dashboard/linkcontentfeed');
        });
        $('.expresscurate_dashboard_smartPublishing').on('click', '.settingsLink', function () {
            if ($(this).attr('href').contains('page=expresscurate_settings')) {
                track('/dashboard/linksettings');
            } else {
                track('/dashboard/linkdraftedposts');
            }

        });
        /*sitemap*/
        $('#expresscurate_sitemap_post_configure_manually').on('change', function () {
            var $options = $('.expresscurate_sitemap_widget .hiddenOptions');
            if ($(this).is(':checked')) {
                $options.removeClass('expresscurate_displayNone').hide().stop(true, true).slideDown('slow');
            } else {
                $options.removeClass('expresscurate_displayNone').stop(true, true).slideUp('slow');
            }
        });
        $('#expresscurate_sitemap_post_exclude_from_sitemap').on('change', function () {
            var $options = $('.expresscurate_sitemap_widget .sitemapOption');
            if (!$(this).is(':checked')) {
                $options.removeClass('expresscurate_displayNone').hide().stop(true, true).slideDown('slow');
            } else {
                $options.removeClass('expresscurate_displayNone').stop(true, true).slideUp('slow');
            }
        });
        /*cron messages*/
        $('#exec_function_perm_seen,#cron_setup_manually').on('click', function () {
            var $elem = $(this),
                status = ($elem.is('#cron_setup_manually')) ? 'set' : 'seen';
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_set_cron_permission_status',
                data: {status: status}
            }).done(function (res) {
                var data = $.parseJSON(res);
                if (data.status === 'success') {
                    $elem.parents('div.notice-warning').fadeOut(600);
                }
            });
        });
        $('#expresscurate_sitemap_update_permission').on('click', function () {
            var $elem = $(this),
                status = 'seen';
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_set_sitemap_permission_status',
                data: {status: status}
            }).done(function (res) {
                var data = $.parseJSON(res);
                if (data.status === 'success') {
                    $elem.parents('div.notice-warning').fadeOut(600);
                }
            });
        });

        /*layout*/
        $('.expresscurate_controls .layout').on('click', function () {
            var $wrap = $('.expresscurate_Styles.wrap'),
                page = ($wrap.hasClass('expresscurate_feed_list')) ? 'expresscurate_feed_layout' : 'expresscurate_bookmark_layout',
                layout = '',
                $layoutTooltip = $('.expresscurate_controls li.layout .tooltip'),
                $masonryWrap = $('.expresscurate_masonryWrap');
            if ($wrap.hasClass('expresscurate_singleColumn')) {
                layout = 'grid';
                $wrap.removeClass('expresscurate_singleColumn');
                $layoutTooltip.text('view as list');
            } else {
                layout = 'single';
                $wrap.addClass('expresscurate_singleColumn');
                $layoutTooltip.text('view as grid');
            }
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_change_layout_event',
                data: {
                    page: page,
                    layout: layout
                }
            });
            $('.expresscurate_controls').width($masonryWrap.width());
            $masonryWrap.masonry();
        });
        isSetup = true;
    }

    return {
        setup: function () {
            if (!isSetup) {
                $(document).ready(function () {
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
        countDown: countDown,
        getTemplate: getTemplate,
        track: track,
        validationMessages: validationMessages
    }
})(window.jQuery);
ExpressCurateUtils.setup();