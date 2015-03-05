var ExpressCurateFeedSettings = (function ($) {
    var $input, $elemToRotate, $notDefMessage, $feedList, $addFeed;

    function addFeed() {
        ExpressCurateUtils.track('/rss-feeds/add');

        var message = '',
            link = $input.val(),
            liHTML = '',
            $lastLi,
            $errorMessage=$(".addNewFeed .errorMessage");

        ExpressCurateUtils.startLoading($input, $elemToRotate);
        $errorMessage.text();

        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_feed_add',
            data: {url: link}
        }).done(function (res) {
            var data = $.parseJSON(res);
            if (data.status === 'success') {
                liHTML = ExpressCurateUtils.getTemplate('rssfeedItem', data);
                $('.expresscurate_feedSettingsList').append(liHTML);

                ExpressCurateUtils.notDefinedMessage($notDefMessage, $feedList.find(' > li'));

                $lastLi = $feedList.find(' > li').last();
                $lastLi.addClass('expresscurate_highlight');
                $('.addFeed input').val('');
                setTimeout(function () {
                    $lastLi.removeClass('expresscurate_highlight');
                }, 1000);
            } else {
                message = data.status;
                $errorMessage.text(message);
            }
        }).always(function () {
            ExpressCurateUtils.endLoading($input, $elemToRotate);
        });
    }

    function deleteFeed(el) {
        ExpressCurateUtils.track('/rss-feeds/delete');

        var link = el.parents('li').find('input').val(),
            $element;

        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_feed_delete', data: {url: link}
        }).done(function (res) {
            var data = $.parseJSON(res);

            if (data.status === 'success') {
                $element = el.parents('li');
                $element.addClass('expresscurate_highlight');
                setTimeout(function () {
                    $element.remove();
                }, 1000);
                ExpressCurateUtils.notDefinedMessage($notDefMessage, $feedList.find(' > li'));
            }
        });
    }

    function setupFeedSettings() {
        $addFeed=$('.addFeed');
        $input = $addFeed.find('input');
        $elemToRotate = $addFeed.find('span span');
        $notDefMessage = $('.expresscurate_feed_dashboard .expresscurate_notDefined');
        $feedList = $('.expresscurate_feedSettingsList');

        if ($('.expresscurate_feed_dashboard').length) {
            ExpressCurateUtils.notDefinedMessage($notDefMessage, $feedList.find(' > li'));
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
            deleteFeed($(this));
        });

    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                $(document).ready(function () {
                    setupFeedSettings();
                    isSetup = true;
                });
            }
        }
    }
})(window.jQuery);

ExpressCurateFeedSettings.setup();
