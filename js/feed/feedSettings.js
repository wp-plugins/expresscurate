var FeedSettings = (function (jQuery) {
    var $input, $elemToRotate, $notDefMessage, $feedList;

    function addFeed() {
        var message = '',
            myRegExp = new RegExp(/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/|www\.|^)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/),
            link = $input.val(),
            li_html = '',
            $existedLi = $feedList.find(' > li'),
            existed = false,
            existedURL, $lastLi, $addedLi;
        Utils.startLoading($input, $elemToRotate);
        jQuery('.errorMessage').remove();

        if (link.match(myRegExp)) {

            if ($existedLi.length > 0) {
                $existedLi.each(function (index, value) {
                    existedURL = jQuery(value).find('a').text();
                    if (link === existedURL) {
                        existed = true;
                        li_html = '';
                        $addedLi = jQuery(value);
                        $addedLi.addClass('expresscurate_highlight');
                        setTimeout(function () {
                            $addedLi.removeClass('expresscurate_highlight');
                        }, 1000);
                    }
                });
            }
            if (!existed) {
                jQuery.ajax({
                    type: 'POST',
                    url: 'admin-ajax.php?action=expresscurate_feed_add',
                    data: {url: link}
                }).done(function (res) {
                    var data = jQuery.parseJSON(res);
                    if (data.status === 'success') {
                        li_html = '<li>\
                        <a target="_newtab" href="' + data.feed_url + '">' + data.feed_url + '</a>\
                        <span class="postsCount expresscurate_floatRight">' + data.post_count + '\
                        <input type="hidden" name="expresscurate_feed_url" value="' + data.feed_url + '" />\
                        </span>\
                        <span class="close">&#215</span>\
                    </li>';
                        jQuery('.expresscurate_feedSettingsList').append(li_html);
                        Utils.notDefinedMessage($notDefMessage, $feedList.find(' > li'));
                        $lastLi = $feedList.find(' > li').last();
                        $lastLi.addClass('expresscurate_highlight');
                        jQuery('.addFeed input').val('');
                        setTimeout(function () {
                            $lastLi.removeClass('expresscurate_highlight');
                        }, 1000);

                    } else if (data.status === 'nofeed') {
                        message = 'No RSS feed found at this URL';
                    } else if (data.status === 'invalid_rss_url') {
                        message = 'Invalid RSS URL.';
                    } else if (data.status === 'warning') {
                        message = 'URL already exists.';
                    } else if (data.status === 'error') {
                        message = 'Something went wrong. Please check the URL. If the problem persists, please contact us.';
                    }
                    if (message !== '') {
                        jQuery(".addFeed").after('<span class="errorMessage">' + message + '</span>');
                    }
                }).always(function () {
                    Utils.endLoading($input, $elemToRotate);
                });
            } else {
                message = 'URL already exists.';
            }
        } else {
            message = 'Invalid RSS URL.';
        }
        if (message !== '') {
            jQuery(".addFeed").after('<span class="errorMessage">' + message + '</span>');
            Utils.endLoading($input, $elemToRotate);
        }
    }

    function deleteFeed(el) {
        var link = el.parents('li').find('input').val(),
            $element;

        jQuery.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_feed_delete', data: {url: link}
        }).done(function (res) {
            var data = jQuery.parseJSON(res);
            if (data.status === 'success') {
                $element = el.parents('li');
                $element.addClass('expresscurate_highlight');
                setTimeout(function () {
                    $element.remove();
                }, 1000);
                Utils.notDefinedMessage($notDefMessage, $feedList.find(' > li'));
            }
        });
    }

    function setupFeedSettings() {
        $input = jQuery('.addFeed input');
        $elemToRotate = jQuery('.addFeed span span');
        $notDefMessage = jQuery('.expresscurate_feed_dashboard .expresscurate_notDefined');
        $feedList = jQuery('.expresscurate_feedSettingsList');
        if (jQuery('.expresscurate_feed_dashboard').length) {
            Utils.notDefinedMessage($notDefMessage, $feedList.find(' > li'));
        }
        /*add*/
        $input.on("keyup", function (e) {
            if (e.keyCode === 13) {
                addFeed();
            }
        });
        $elemToRotate.on('click', function () {
            addFeed();
        });
        /*remove*/
        $feedList.on('click', 'li span.close', function () {
            deleteFeed(jQuery(this));
        });

    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                jQuery(document).ready(function () {
                    setupFeedSettings();
                    isSetup = true;
                });
            }
        }
    }
})(window.jQuery);

FeedSettings.setup();
