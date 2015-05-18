var ExpressCurateSocialPostWidget = (function ($) {
    var widget;

    function parseContent(block) {
        var $contentWrap = $('#content'),
            content = (($contentWrap.css("display") === "block") ? $contentWrap.val() : tinyMCE.get("content").getContent()),
            myRegExp = new RegExp('(<([^>]+)>)', 'ig'),
        //$input = block.find('.expresscurate_social_post_content'),
            $cloneContent = $(content).clone().wrapAll('<span></span>');
        //console.log($cloneContent/*.find('h'+i).html()*/);

        content = content.replace(myRegExp, "\n\r");
        content = content.replace(/ {2,}/g, ' ');
        content = $("<div/>").html(content).text();
        return content;
    }

    function getHeader(header) {
        var $contentWrap = $('#content'),
            content = (($contentWrap.css("display") === "block") ? $contentWrap.val() : tinyMCE.get("content").getContent()),
            text = $(content).find(header).text();
        if (text.length > 1) {
            return ExpressCurateUtils.getTemplate('socialPostWidget', text);
        } else {
            return false;
        }
    }

    function setupSocial() {
        widget = $('.expresscurate_social_post_widget');
        widget.on('click', '.expresscurate_social_get_content', function (e) {
            e.preventDefault();
            var $this = $(this),
                data = parseContent($this.parents('.expresscurate_tweetBlock')),
                tweetHTML = ExpressCurateUtils.getTemplate('socialPostWidget', data);
            $this.parent('ul').after(tweetHTML);
        });
        $('#expresscurate_addTweet').on('click', function (e) {
            e.preventDefault();
            var tweetHTML = ExpressCurateUtils.getTemplate('socialPostWidget', '');
            $(this).parent('ul').after(tweetHTML);
        });
        $('.expresscurate_headerTweet').on('click', function (e) {
            e.preventDefault();
            var tweetHTML = getHeader($(this).data('header'));
            if (tweetHTML) {
                $(this).parent('ul').after(tweetHTML);
            }
        });
        widget.on('click', '.expresscurate_tweetBlock .approve', function () {
            var $block = $(this).parent('.expresscurate_tweetBlock'),
                messages=[],
                message = {
                    id: '5540b4e3dc302f2a02a9c09d',
                    message: 'last test',
                    approved: true
                };
            messages.push(message);
           /* console.log($('#expresscurate_postId').val());
            console.log(JSON.stringify(messages));*/
            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_save_post_messages',
                data: {
                    post_id: $('#expresscurate_postId').val(),
                    messages: JSON.stringify(messages)
                }
            });


            ///
          /*  var items = [],
                item = {};
            $.each(els, function (index, el) {
                item['link'] = $(el).find('.url').attr('href');
                items.push(item);
                item = {};
            });

            $.ajax({
                type: 'POST',
                url: 'admin-ajax.php?action=expresscurate_bookmarks_delete',
                data: {items: JSON.stringify(items)}
            });*/
        });
    }

    var isSetup = false;

    return {
        setup: function () {
            if (!isSetup) {
                $(document).ready(function () {
                    setupSocial();
                    isSetup = true;
                });
            }
        }
    }
})(window.jQuery);

ExpressCurateSocialPostWidget.setup();
