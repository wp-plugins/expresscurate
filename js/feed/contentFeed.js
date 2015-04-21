var ExpressCurateFeedWall = (function ($) {
    var $notDefFeed, $feedControls, $masonryWrap, $feedBoxes;

    function bookmarkAdd(els) {
        ExpressCurateUtils.track('/content-feed/bookmark');

        var items = [];

        $.each(els, function (index, el) {
            items.push(JSON.parse($(el).find('.expresscurate_feedData').text()));
        });
        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_bookmarks_add',
            data: {items: JSON.stringify(items)}
        });
    }

    /*add feed from top Sources*/
    function addFeed(el, url) {
        ExpressCurateUtils.track('/top-sources/subscribe-rss');

        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_feed_add',
            data: {url: url}
        }).done(function (res) {
            var data = $.parseJSON(res),
                $statusButton = el.parent().find('.rssStatus'),
                $tooltip = $statusButton.find('.tooltip');

            if (data.status === 'success') {
                $statusButton.removeClass('rssStatusAdd').addClass('rssStatusYes');
                $tooltip.html('Subscribed');
            } else {
                if (data.status === 'nofeed') {
                    $statusButton.removeClass('rssStatusAdd').addClass('rssStatusNo');
                    $tooltip.html('N/A');
                }
            }
        });
    }

    function deleteFeedItems(els) {
        ExpressCurateUtils.track('/content-feed/delete');

        var items = [];

        $.each(els, function (index, el) {
            var item = $(el).find('a.postTitle').data('link');
            items.push(item);
        });
        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_delete_feed_content_items',
            data: {items: items}
        }).done(function (res) {
            var data = $.parseJSON(res);

            if (data.status === 'success') {
                $(els).addClass('expresscurate_transparent');
                setTimeout(function () {
                    $(els).remove();
                    $masonryWrap.masonry();
                    ExpressCurateUtils.checkControls($feedControls);
                    ExpressCurateUtils.notDefinedMessage($notDefFeed, $feedBoxes.find(' > li'));
                }, 700);
            }
        });
    }

    function pullFeedManualy() {
        var $loading = $feedControls.find('.loading'),
            $control = $('.feedListControls li.pull');
        $loading.addClass('expresscurate_startRotate');
        $control.addClass('disabled');
        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_manual_pull_feed'
        }).done(function (res) {
            var data = $.parseJSON(res);
            if (data) {
                $.each(data.content, function (index, value) {
                    $("#expresscurate_feedBoxes").load("admin-ajax.php?action=expresscurate_show_content_feed_items #expresscurate_feedBoxes > li", function () {
                        $('.pullTime p').text('in ' + data.minutes_to_next_pull);
                        $masonryWrap.masonry('destroy').masonry({
                            itemSelector: '.expresscurate_masonryItem',
                            isResizable: true,
                            isAnimated: true,
                            columnWidth: '.expresscurate_masonryItem',
                            gutter: 10
                        });
                        ExpressCurateUtils.notDefinedMessage($notDefFeed, $feedBoxes.find(' > li'));
                    });
                });
            }
        }).always(function () {
            $loading.removeClass('expresscurate_startRotate');
            $control.removeClass('disabled');
        });
    }

    function setupFeed() {
        $notDefFeed = $('.expresscurate_feed_list .expresscurate_notDefined');
        $feedControls = $('.feedListControls li');
        $masonryWrap = $('.expresscurate_masonryWrap');
        $feedBoxes = $('.expresscurate_feedBoxes');
        $masonryWrap.masonry({
            itemSelector: '.expresscurate_masonryItem',
            isResizable: true,
            isAnimated: true,
            columnWidth: '.expresscurate_masonryItem',
            gutter: 10
        });

        if ($('.expresscurate_feed_list').length) {
            ExpressCurateUtils.notDefinedMessage($notDefFeed, $feedBoxes.find(' > li'));
        }
        $feedBoxes.find('li input:checkbox').prop('checked', false);

        /*checkboxes*/
        $feedBoxes.on('change', '.checkInput', function () {
            ExpressCurateUtils.checkControls($feedControls);
        });
        $('.expresscurate_feed_list .check').on('click', function () {
            var $checked = $feedBoxes.find('li input:checkbox:checked').length,
                liCount = $feedBoxes.find(' > li').length,
                $allCheckboxes = $feedBoxes.find('li input:checkbox');

            if ($checked === liCount) {
                $allCheckboxes.prop('checked', false);
            } else {
                $allCheckboxes.prop('checked', true);
            }
            ExpressCurateUtils.checkControls($feedControls);
        });
        /*pull*/
        $('.expresscurate_feed_list .pull').on('click', function () {
            pullFeedManualy();
        });
        /*delete*/
        $('.expresscurate_feed_list .remove').on('click', function () {
            var $checked = $feedBoxes.find('li input:checkbox:checked');
            deleteFeedItems($checked.parents('.expresscurate_feedBoxes > li'));
            ExpressCurateUtils.notDefinedMessage($notDefFeed, $feedBoxes.find(' > li'));

        });
        $feedBoxes.on('click', '.controls .hide', function () {
            var $elem = $(this).parents('.expresscurate_feedBoxes > li');
            deleteFeedItems($elem);
            ExpressCurateUtils.notDefinedMessage($notDefFeed, $feedBoxes.find(' > li'));
        });

        /*add from top sources*/
        $('.expresscurate_URL').on('click', '.rssStatusAdd', function () {
            addFeed($(this), $(this).parent().find('.expresscurate_topCuratedURL').text());
        });

        /*bookmark*/
        $feedBoxes.on('click', '.controls .bookmark', function () {
            var $elem = $(this).parents('.expresscurate_feedBoxes > li');
            bookmarkAdd($elem);
            deleteFeedItems($elem);
        });
        $('.feedListControls').on('click', '.bookmark', function () {
            var $checked = $feedBoxes.find('li input:checkbox:checked'),
                $elems = $checked.parents('.expresscurate_feedBoxes > li');
            bookmarkAdd($elems);
            deleteFeedItems($elems);
        });

        /*curate*/
        $('.expresscurate_feed_list .quotes').on('click', function () {
            ExpressCurateUtils.track('/content-feed/curate');

            var $checked = $feedBoxes.find('li input:checkbox:checked');

            if ($checked.length === 1) {
                var $elem = $($checked[0]).parent().find('a'),
                    title = $elem.html(),
                    url = window.btoa(encodeURIComponent($elem.attr('href')));
                window.location.href = $('#adminUrl').val() + 'post-new.php?expresscurate_load_source=' + url + '&expresscurate_load_title=' + title;
            } else if ($checked.length > 1) {
                ExpressCurateUtils.addSources($checked.parents('.expresscurate_feedBoxes > li'), '.expresscurate_feedData');
                return false;
            }
        });

        $(window).on('load', function () {
            $masonryWrap.masonry();
        });
    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                $(document).ready(function () {
                    setupFeed();
                    isSetup = true;
                });
            }
        }
    }
})(window.jQuery);

ExpressCurateFeedWall.setup();
