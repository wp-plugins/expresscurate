var FeedWall = (function (jQuery) {
    var notDefFeed=jQuery('.expresscurate_feed_list .expresscurate_notDefined'),
        feedItems=jQuery('.expresscurate_feedBoxes > li');
    var bookmark_add = function (els) {
        var items = [];
        jQuery.each(els, function (index, el) {
            items.push(JSON.parse(jQuery(el).find('.expresscurate_feedData').text()));
        });
        jQuery.post('admin-ajax.php?action=expresscurate_bookmarks_add', {items: JSON.stringify(items)});
    };

    var addFeed = function (el, url) {
        jQuery.post('admin-ajax.php?action=expresscurate_feed_add', {url: url}, function (res) {
            var data = jQuery.parseJSON(res);
            var statusButton = el.parent().find('.rssStatus'),
                tooltip = statusButton.find('.tooltip');
            if (data.status == 'success') {
                statusButton.removeClass('rssStatusAdd').addClass('rssStatusYes');
                tooltip.html('Subscribed');
            } else {
                if (data.status == 'nofeed') {
                    statusButton.removeClass('rssStatusAdd').addClass('rssStatusNo');
                    tooltip.html('N/A');
                }
            }
        });
    };

    var deleteFeedItems = function (els) {
        var items = [];
        jQuery.each(els, function (index, el) {
            var item = {};
            item = jQuery(el).find('textarea').val();
            items.push(item);
        });
        jQuery.post('admin-ajax.php?action=expresscurate_delete_feed_content_items', {items: JSON.stringify(items)}, function (res) {
            var data = jQuery.parseJSON(res);
            if (data.status == 'success') {
                jQuery(els).addClass('expresscurate_transparent');
                setTimeout(function () {
                    jQuery(els).remove();
                    jQuery('.expresscurate_feedBoxes').masonry();
                    Utils.checkControls(jQuery('.feedListControls li'));
                    Utils.notDefinedMessage(notDefFeed,jQuery('.expresscurate_feedBoxes > li'));
                }, 700);
            } else {
            }
        });
    };

    var setupFeed = function () {
        var feedBoxes=jQuery('.expresscurate_feedBoxes');
        jQuery('.expresscurate_masonryWrap').masonry({
            itemSelector: '.expresscurate_masonryItem',
            isResizable: true,
            isAnimated: true,
            columnWidth: '.expresscurate_masonryItem',
            gutter: 10
        });

        if (jQuery('.expresscurate_feed_list').length) {
            Utils.notDefinedMessage(notDefFeed,jQuery('.expresscurate_feedBoxes > li'));
        }
        jQuery('.expresscurate_feedBoxes li input:checkbox').prop('checked', false);

        /*checkboxes*/
        feedBoxes.on('click', '> li', function (e) {
            if (e.target !== this)
                return;
            var checkbox = jQuery(this).find('.checkInput');
            if (checkbox.is(':checked'))
                checkbox.attr('checked', false);
            else
                checkbox.attr('checked', true);
            Utils.checkControls(jQuery('.feedListControls .quotes,.feedListControls .remove,.feedListControls .bookmark'));
        });
        feedBoxes.on('change', '.checkInput', function () {
            Utils.checkControls(jQuery('.feedListControls .quotes,.feedListControls .remove,.feedListControls .bookmark'));
        });
        jQuery('.expresscurate_feed_list .check').on('click', function () {
            var checked = jQuery(".expresscurate_feedBoxes li input:checkbox:checked").length,
                liCount = jQuery(".expresscurate_feedBoxes > li").length,
                allCheckboxes = jQuery('.expresscurate_feedBoxes li input:checkbox');
            if (checked === liCount) {
                allCheckboxes.prop('checked', false);
            } else {
                allCheckboxes.prop('checked', true);
            }
            Utils.checkControls(jQuery('.feedListControls .quotes,.feedListControls .remove,.feedListControls .bookmark'));
        });
        /*delete*/
        jQuery('.expresscurate_feed_list .remove').on('click', function () {
            var checked = jQuery(".expresscurate_feedBoxes li input:checkbox:checked");
            deleteFeedItems(checked.parents('.expresscurate_feedBoxes > li'));
            Utils.notDefinedMessage(notDefFeed,jQuery('.expresscurate_feedBoxes > li'));

        });
        feedBoxes.on('click', '.controls .hide', function () {
            var elem = jQuery(this).parents('.expresscurate_feedBoxes > li');
            deleteFeedItems(elem);
            Utils.notDefinedMessage(notDefFeed,jQuery('.expresscurate_feedBoxes > li'));
        });
        /*add from top sources*/
        jQuery('.expresscurate_URL').on('click', '.rssStatusAdd', function () {
            addFeed(jQuery(this), jQuery(this).parent().find('.expresscurate_topCuratedURL').text());
        });
        /*bookmark*/
        feedBoxes.on('click', '.controls .bookmark', function () {
            var elem = jQuery(this).parents('.expresscurate_feedBoxes > li');
            bookmark_add(elem);
            deleteFeedItems(elem);
        });
        jQuery('.feedListControls').on('click', '.bookmark', function () {
            var checked = jQuery(".expresscurate_feedBoxes li input:checkbox:checked"),
                elems = checked.parents('.expresscurate_feedBoxes > li');
            bookmark_add(elems);
            deleteFeedItems(elems);
        });
        /*curate*/
        jQuery('.expresscurate_feed_list .quotes').on('click', function () {
            var checked = jQuery(".expresscurate_feedBoxes li input:checkbox:checked");
            if (checked.length == 1) {
                var title = jQuery(checked[0]).parent().find('a').html();
                var url = jQuery(checked[0]).parent().find('a').attr('href');
                window.location.href = '/wp-admin/post-new.php?expresscurate_load_source=' + url + '&expresscurate_load_title=' + title;
                Utils.addSources(checked.parents('.expresscurate_feedBoxes > li'), '.expresscurate_feedData');
                return false;
            }
        });
        jQuery(window).on('load', function () {
            jQuery('.expresscurate_masonryWrap').masonry();
        })
    };

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                jQuery(document).ready(function () {
                    setupFeed();
                    isSetup = true;
                });
            }
        }
    }
})(window.jQuery);

FeedWall.setup();
