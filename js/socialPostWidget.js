var ExpressCurateSocialPostWidget = (function ($) {
    var widget,
        maxLength = 110,
        posts = [];

    function uniqueId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function parseContent() {
        var $contentWrap = $('#content'),
            content = (($contentWrap.css("display") === "block") ? $contentWrap.val() : tinyMCE.get("content").getContent()),
            myRegExp = new RegExp('(<([^>]+)>)', 'ig'),
            li = $(content).find('li'),
            posts = [];

        if (li.length) {
            $.each(li, function (index, value) {
                var text = $(value).text();
                posts.push(text);
                content = content.replace(text, '');
            });
        }

        content = content.split('<p>');
        for (var i = 0; i < content.length; i++) {
            content[i] = content[i].replace(myRegExp, ' ').trim();
        }

        var result = $.merge(posts, content);
        result = result.filter(function (el) {
            return el.length !== 0;
        });

        return result;
    }

    function getHeader(header) {
        var $contentWrap = $('#content'),
            content = (($contentWrap.css("display") === "block") ? $contentWrap.val() : tinyMCE.get("content").getContent()),
            data = {
                message: $(content).find(header).text()
            };
        ExpressCurateUtils.track('/post/social-post-widget/get' + header);
        if (data.message.length > 1) {
            createSocialPost(data);
        } else {
            return false;
        }
    }

    function createSocialPost(data) {

        var blockId = (data && data.id) ? data.id : uniqueId(),
            message = data ? data.message : '',
            messageCounter = maxLength - message.length,
            post = {
                id: blockId,
                message: message,
                approved: false,
                postLength: messageCounter,
                errorColor: (messageCounter < 0) ? 'error' : ''
            };
        posts.push(post);
        updatePosts(posts);
        $('.mainControls').after(ExpressCurateUtils.getTemplate('socialPostWidget', post));

    }

    function postLengthValidation($block) {
        var $textarea = $block.find('textarea'),
            $countWrap = $block.find('.expresscurate_socialPostLength'),
            textLength = $textarea.val().length,
            count = maxLength - textLength;

        if (count < 0) {
            $countWrap.addClass('error');
        } else {
            $countWrap.removeClass('error');
        }
        return count;
    }

    function updatePosts(posts) {
        $.ajax({
            type: 'POST',
            url: 'admin-ajax.php?action=expresscurate_save_post_messages',
            data: {
                post_id: $('#expresscurate_postId').val(),
                messages: JSON.stringify(posts)
            }
        });
    }

    function setupSocial() {
        var $metaTag = $('#expresscurate_social_post_messages');

        if ($metaTag.length) {
            var savedPosts = $metaTag.val();
            var data = (savedPosts.length > 1) ? $.parseJSON(savedPosts) : [];
            $.each(data, function (index, value) {
                posts.push(value);
            });
        }
        widget = $('.expresscurate_social_post_widget');

        /*delete*/
        widget.on('click', '.expresscurate_socialPostBlock .close', function () {
            var $block = $(this).parents('.expresscurate_socialPostBlock'),
                id = $block.attr('id');
            $block.remove();
            posts = posts.filter(function (el) {
                return el.id !== id;
            });
            updatePosts(posts);
        });
        /*post edit*/
        widget.on('blur', '.expresscurate_socialPostBlock textarea', function () {
            var $this = $(this),
                $block = $this.parents('.expresscurate_socialPostBlock'),
                blockId = $block.attr('id'),
                text = $this.val();
            $.each(posts, function (index, value) {
                if (value.id == blockId) {
                    value.message = text;
                    value.postLength = maxLength - text.length;
                    updatePosts(posts);
                }
            });
        });
        widget.on('change', '.expresscurate_socialPostBlock select', function () {
            var $this = $(this),
                $block = $this.parents('.expresscurate_socialPostBlock'),
                blockId = $block.attr('id'),
                selectedAccount = $this.val(),
                username = $this.text();

            $.each(posts, function (index, value) {
                if (value.id == blockId) {
                    value.profile_ids = selectedAccount;
                    value.formatted_username = username;
                    updatePosts(posts);
                }
            });
        });
        /*length validation*/
        widget.on('keyup', '.expresscurate_socialPostBlock textarea', function () {
            var $this = $(this),
                $postLengthWrap = $this.parents('.expresscurate_socialPostBlock').find('.expresscurate_socialPostLength');

            $postLengthWrap.text(postLengthValidation($(this).parents('.expresscurate_socialPostBlock')));
        });
        /*get content*/
        widget.on('click', '.expresscurate_social_get_content', function () {
            var $this = $(this),
                content = parseContent();
            $.each(content, function (index, value) {
                var data = {
                    message: value
                };
                createSocialPost(data);
            });
            ExpressCurateUtils.track('/post/social-post-widget/getcontent');
        });
        /*add new*/
        $('#expresscurate_addTweet').on('click', function () {
            createSocialPost(null);
            ExpressCurateUtils.track('/post/social-post-widget/createnew');
        });
        /*post from headers*/
        $('.expresscurate_headerTweet').on('click', function () {
            getHeader($(this).data('header'));
        });
        /*get social title*/
        widget.on('click', '#expresscurate_socialTitlePost', function () {
            var $this = $(this);
            if ($('#expresscurate_advanced_seo')) {
                var data = {
                    message: $('#expresscurate_advanced_seo_social_title').val()
                };
                ExpressCurateUtils.track('/post/social-post-widget/socialtitle');
                createSocialPost(data);
            }
        });
        /*get social description*/
        widget.on('click', '#expresscurate_socialDescriptionPost', function () {
            var $this = $(this);
            if ($('#expresscurate_advanced_seo')) {
                var data = {
                    message: $('#expresscurate_advanced_seo_social_desc').val()
                };
                ExpressCurateUtils.track('/post/social-post-widget/socialdescription');
                createSocialPost(data);
            }
        });
        /*get social short description*/
        widget.on('click', '#expresscurate_socialShortDescriptionPost', function () {
            var $this = $(this);
            if ($('#expresscurate_advanced_seo')) {
                var data = {
                    message: $('#expresscurate_advanced_seo_social_shortdesc').val()
                };
                ExpressCurateUtils.track('/post/social-post-widget/socialshortdescription');
                createSocialPost(data);
            }
        });
        /*approve*/
        widget.on('click', '.expresscurate_socialPostBlock .approve', function () {
            var $block = $(this).parents('.expresscurate_socialPostBlock'),
                blockId = $block.attr('id'),
                $edit = $block.find('.edit'),
                option = $block.find('#profile option:selected'),
                profileId = option.val(),
                username = option.text();

            $.each(posts, function (index, value) {
                if (value.id == blockId) {
                    value.approved = true;
                    value.profile_ids = profileId;
                    value.formatted_username = username;
                    value.message = value.message.slice(0, maxLength);
                }
            });
            ExpressCurateUtils.track('/post/social-post-widget/approve');
            updatePosts(posts);

            $block.find('li').addClass('expresscurate_displayNone');
            $block.find('textarea').attr('readonly', 'true');
            $block.find('select').attr('disabled', 'true');
            $edit.removeClass('expresscurate_displayNone');
        });
        /*edit*/
        widget.on('click', '.expresscurate_socialPostBlock .edit', function () {

            var $block = $(this).parents('.expresscurate_socialPostBlock'),
                blockId = $block.attr('id'),
                $edit = $block.find('.edit');

            $.each(posts, function (index, value) {
                if (value.id == blockId) {
                    value.approved = false;
                }
            });

            updatePosts(posts);

            $block.find('li').removeClass('expresscurate_displayNone');
            $block.find('textarea').removeAttr('readonly');
            $block.find('select').removeAttr('disabled');
            $edit.addClass('expresscurate_displayNone');
        });
        /*clone*/
        widget.on('click', '.expresscurate_socialPostBlock .clone', function () {
            var $block = $(this).parents('.expresscurate_socialPostBlock'),
                post = null,
                blockId = $block.attr('id');

            $.each(posts, function (index, value) {
                if (value.id == blockId) {
                    post = value;
                }
            });
            var clone = $.extend({}, post);
            clone.id = uniqueId();
            clone.postLength = maxLength - clone.message.length;
            posts.push(clone);
            ExpressCurateUtils.track('/post/social-post-widget/copy');
            updatePosts(posts);
            $block.before(ExpressCurateUtils.getTemplate('socialPostWidget', post));
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
        },
        createSocialPost: createSocialPost
    }
})
(window.jQuery);

ExpressCurateSocialPostWidget.setup();
