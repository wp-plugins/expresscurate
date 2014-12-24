var FeedSettings = (function (jQuery) {
    var addFeed = function () {
        Utils.startLoading(jQuery('.addFeed input'), jQuery('.addFeed span span'));
        jQuery('.errorMessage').remove();
        var message = '';
        var myRegExp = new RegExp(/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/|www\.|^)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/);

        var link = jQuery('.addFeed input').val(),
            li_html = '';
        if (link.match(myRegExp)) {
            var existedLi = jQuery('.expresscurate_feedSettingsList li'),
                existed = false;
            if (existedLi.length > 0) {
                existedLi.each(function (index, value) {
                    var existedURL = jQuery(value).find('h3').text();
                    if (link == existedURL) {
                        existed = true;
                        li_html = '';
                        var addedLi = jQuery(value);
                        addedLi.css('background-color', '#FCFCFC');
                        setTimeout(function () {
                            addedLi.css('background-color', 'transparent');
                        }, 1000);
                        return;
                    }
                });
            }
            if (!existed) {

                jQuery.post('admin-ajax.php?action=expresscurate_feed_add', {url: link}, function (res) {
                    data = jQuery.parseJSON(res);
                    if (data.status == 'success') {
                        li_html = '<li>\
                        <a target="_newtab" href="'+data.feed_url+'">' + data.feed_url + '</a>\
                        <span class="postsCount expresscurate_floatRight">' + data.post_count + '\
                        <input type="hidden" name="expresscurate_feed_url" value="' + data.feed_url + '" />\
                        </span>\
                        <span class="close">&#215</span>\
                    </li>';
                        jQuery('.expresscurate_feedSettingsList').append(li_html);
                        Utils.notDefinedMessage(jQuery('.expresscurate_feed_dashboard .expresscurate_notDefined'),jQuery('.expresscurate_feedSettingsList > li'));
                        var lastLi = jQuery('.expresscurate_feedSettingsList li').last();
                        lastLi.css('background-color', '#FCFCFC');
                        jQuery('.addFeed input').val('');
                        setTimeout(function () {
                            lastLi.css('background-color', 'transparent');
                        }, 1000);

                    } else if (data.status == 'nofeed') {
                        message = 'No RSS feed found at this URL';
                    } else if (data.status == 'invalid_rss_url') {
                        message = 'Invalid RSS URL.';
                    } else if (data.status == 'warning') {
                        message = 'URL already exists.';
                    } else if (data.status == 'error') {
                        message = 'Something went wrong. Please check the URL. If the problem persists, please contact us.';
                    }
                    if (message != '') {
                        jQuery(".addFeed").after('<span class="errorMessage">' + message + '</span>');
                    }
                }).always(function() {
                    Utils.endLoading(jQuery('.addFeed input'), jQuery('.addFeed span span'));
                });
            } else {
                message = 'URL already exists.';
            }
        } else {
            message = 'Invalid RSS URL.';
        }
        if (message != '') {
            jQuery(".addFeed").after('<span class="errorMessage">' + message + '</span>');
            Utils.endLoading(jQuery('.addFeed input'), jQuery('.addFeed span span'));
        }
    }
    var deleteFeed = function (el) {
        var link = el.parents('li').find('input').val();

        jQuery.post('admin-ajax.php?action=expresscurate_feed_delete', {url: link}, function (res) {
            data = jQuery.parseJSON(res);
            if (data.status == 'success') {
                el.parents('li').remove();
                Utils.notDefinedMessage(jQuery('.expresscurate_feed_dashboard .expresscurate_notDefined'),jQuery('.expresscurate_feedSettingsList > li'));
            }
            /*else {
             }*/
        });
    }

    var setupFeedSettings = function () {
        if(jQuery('.expresscurate_feed_dashboard').length){
            Utils.notDefinedMessage(jQuery('.expresscurate_feed_dashboard .expresscurate_notDefined'),jQuery('.expresscurate_feedSettingsList > li'));
        }
/*add*/
        jQuery('.addFeed input').on("keyup", function (e) {
            if (e.keyCode == 13) {
                addFeed();
            }
        });
        jQuery('.addFeed span span').on('click', function () {
            addFeed();
        });
/*remove*/
        jQuery('.expresscurate_feedSettingsList').on('click', 'li span.close', function () {
            deleteFeed(jQuery(this));
        });

    };

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
