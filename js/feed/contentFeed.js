var FeedWall = (function (jQuery) {
    var $notDefFeed, $feedControls, $masonryWrap, $feedBoxes;

    function bookmark_add(els) {
        var items = [];
        jQuery.each(els, function (index, el) {
            items.push(JSON.parse(jQuery(el).find('.expresscurate_feedData').text()));
        });
        jQuery.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_bookmarks_add',
            data: {items: JSON.stringify(items)}
        });
    }

    function addFeed(el, url) {
        jQuery.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_feed_add',
            data: {url: url}
        }).done(function (res) {
            var data = jQuery.parseJSON(res),
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
        var items = [];
        jQuery.each(els, function (index, el) {
            var item = jQuery(el).find('textarea').val();
            items.push(item);
        });
        jQuery.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_delete_feed_content_items',
            data: {items: JSON.stringify(items)}
        }).done(function (res) {
            var data = jQuery.parseJSON(res);
            if (data.status === 'success') {
                jQuery(els).addClass('expresscurate_transparent');
                setTimeout(function () {
                    jQuery(els).remove();
                    $masonryWrap.masonry();
                    Utils.checkControls($feedControls);
                    Utils.notDefinedMessage($notDefFeed, $feedBoxes.find(' > li'));
                }, 700);
            }
        });
    }

    function setupFeed() {
        $notDefFeed = jQuery('.expresscurate_feed_list .expresscurate_notDefined');
        $feedControls = jQuery('.feedListControls li');
        $masonryWrap = jQuery('.expresscurate_masonryWrap');
        $feedBoxes = jQuery('.expresscurate_feedBoxes');
        $masonryWrap.masonry({
            itemSelector: '.expresscurate_masonryItem',
            isResizable: true,
            isAnimated: true,
            columnWidth: '.expresscurate_masonryItem',
            gutter: 10
        });

        if (jQuery('.expresscurate_feed_list').length) {
            Utils.notDefinedMessage($notDefFeed, $feedBoxes.find(' > li'));
        }
        $feedBoxes.find('li input:checkbox').prop('checked', false);

        /*checkboxes*/
        $feedBoxes.on('click', '> li', function (e) {
            if (e.target !== this) {
                return;
            }
            var $checkbox = jQuery(this).find('.checkInput');
            if ($checkbox.is(':checked'))
                $checkbox.attr('checked', false);
            else
                $checkbox.attr('checked', true);
            Utils.checkControls($feedControls);
        });
        $feedBoxes.on('change', '.checkInput', function () {
            Utils.checkControls($feedControls);
        });
        jQuery('.expresscurate_feed_list .check').on('click', function () {
            var $checked = $feedBoxes.find('li input:checkbox:checked').length,
                liCount = $feedBoxes.find(' > li').length,
                $allCheckboxes = $feedBoxes.find('li input:checkbox');
            if ($checked === liCount) {
                $allCheckboxes.prop('checked', false);
            } else {
                $allCheckboxes.prop('checked', true);
            }
            Utils.checkControls($feedControls);
        });
        /*delete*/
        jQuery('.expresscurate_feed_list .remove').on('click', function () {
            var $checked = $feedBoxes.find('li input:checkbox:checked');
            deleteFeedItems($checked.parents('.expresscurate_feedBoxes > li'));
            Utils.notDefinedMessage($notDefFeed, $feedBoxes.find(' > li'));

        });
        $feedBoxes.on('click', '.controls .hide', function () {
            var $elem = jQuery(this).parents('.expresscurate_feedBoxes > li');
            deleteFeedItems($elem);
            Utils.notDefinedMessage($notDefFeed, $feedBoxes.find(' > li'));
        });
        /*add from top sources*/
        jQuery('.expresscurate_URL').on('click', '.rssStatusAdd', function () {
            addFeed(jQuery(this), jQuery(this).parent().find('.expresscurate_topCuratedURL').text());
        });
        /*bookmark*/
        $feedBoxes.on('click', '.controls .bookmark', function () {
            var $elem = jQuery(this).parents('.expresscurate_feedBoxes > li');
            bookmark_add($elem);
            deleteFeedItems($elem);
        });
        jQuery('.feedListControls').on('click', '.bookmark', function () {
            var $checked = $feedBoxes.find('li input:checkbox:checked'),
                $elems = $checked.parents('.expresscurate_feedBoxes > li');
            bookmark_add($elems);
            deleteFeedItems($elems);
        });
        /*curate*/
        jQuery('.expresscurate_feed_list .quotes').on('click', function () {
            var $checked = $feedBoxes.find('li input:checkbox:checked');
            if ($checked.length === 1) {
                var $elem = jQuery($checked[0]).parent().find('a'),
                    title = $elem.html(),
                    url = $elem.attr('href');
                window.location.href = '/wp-admin/post-new.php?expresscurate_load_source=' + url + '&expresscurate_load_title=' + title;
                Utils.addSources($checked.parents('.expresscurate_feedBoxes > li'), '.expresscurate_feedData');
                return false;
            }
        });
        jQuery(window).on('load', function () {
            $masonryWrap.masonry();
        });
    }

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
