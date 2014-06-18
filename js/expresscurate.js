var keywords;
var plugin_folder = 'expresscurate';
// Curation Plugin for WordPress JS

function send_wp_editor(html) {
    var editor = tinyMCE.get('content');
    if (editor) {
        editor.execCommand("mceInsertContent", true, html);
    } else {
        editor = jQuery('#content');
        if (editor.length == 0) {
            if (tinyMCE.editors.length > 0) {
                editor = tinyMCE.editors[0];
                editor.execCommand("mceInsertContent", false, html);
            }
        } else {
            var oldValue = editor.val();
            var selectionStart = editor[0].selectionStart;
            var selectionEnd = editor[0].selectionEnd;

            var newValue = oldValue.substring(0, selectionStart) + html + oldValue.substring(selectionEnd);
            editor.val(newValue);
        }
    }
    //editor.setContent(editor.getContent() + html);
//  var win = window.dialogArguments || opener || parent || top;
//  if (win.send_to_editor) {
//	  win.send_to_editor(html);
//  }

    // alternativ
    // tinyMCE.execCommand("mceInsertContent", false, html);
}

function display_curated_images(images) {
    var img_count = false;
    jQuery.each(images, function (index, value) {
        var img = new Image();
        img.onload = function () {
            var height = this.height,
                width = this.width;
            if (width > 150 && height > 100) {
                //images_html += '<li id="tcurated_image_' + index + '" class="tcurated_image" style="background-image: url(' + value + ')" onclick="insert_delete_image(' + index + ')"></li>';
                jQuery('<li id="tcurated_image_' + index + '" class="tcurated_image" style="background-image: url(' + value + ')" onclick="insert_delete_image(' + index + ')"></li>').appendTo("#curated_images");
            }
        };
        img.src = value;
    });
    setTimeout(function () {
        if (jQuery('ul#curated_images li').length > 0) {
            jQuery('.content .img').removeClass("noimage");
            jQuery('.content .img').css('background-image', jQuery('ul#curated_images li').first().css('background-image'))
        } else {
            error_html = '<div class="error">No image found for specified size</div>';
            jQuery('#expresscurate_post_form').before(error_html);
        }
    }, 300);
}

var paragraphWidth = 93;

function display_curated_paragraphs(paragraphs, count) {
    jQuery('.paragraphs_preview').width(paragraphs.length * paragraphWidth);
    var text_html = '';
    //var text_div_html = '';
    jQuery.each(paragraphs, function (index, value) {
        text_html += '<li id="tcurated_text_' + index + '" title="' + value + '" onclick="insert_text(\'tcurated_text_' + index + '\', \'p\')">' + value + '</li>';
        if (index < count) {
            generate_tags(value);
            tinyMCE.get('expresscurate_content_editor').execCommand('mceInsertContent', false, "<p>" + value + "<p>");
        }
        //text_div_html += '<div style="display:none;" id="tcurated_text_' + index + '">'+ value +'</div>';
    });
    //jQuery("#tcurated_paragraphs").before(text_div_html);
    //jQuery("#curated_special").html(text_html);
    jQuery(text_html).appendTo('#curated_paragraphs');
    //jQuery('#expresscurate_paragraphs').jcarousel();
}

//paragraphs slider
jQuery(document).ready( function(){

    moveMenuArrow();

    function buttonsStatus(){
        var l = parseInt(jQuery('#curated_paragraphs').css('left'));
        var listEnd = jQuery('#curated_paragraphs').width() + l;
        if(l >= 0){
            jQuery('.prevSlide').addClass('inactiveButton');
        } else {
            jQuery('.prevSlide').removeClass('inactiveButton');
        }
        if(listEnd <= jQuery('.slider').width()){
            jQuery('.nextSlide').addClass('inactiveButton');
        } else {
            jQuery('.nextSlide').removeClass('inactiveButton');
        }
    }
    buttonsStatus();

    jQuery('.nextSlide').click(function (e){
        if(jQuery(this).hasClass('inactiveButton')){
            return;
        } else {
            var l = Math.floor((parseInt(jQuery('#curated_paragraphs').css('left')) - 3 * paragraphWidth) / paragraphWidth) * paragraphWidth;
            if(jQuery('#curated_paragraphs').width() + l <= jQuery('.slider').width()){
                l = jQuery('.slider').width() - jQuery('#curated_paragraphs').width();
                jQuery(this).addClass('inactiveButton');
            }
            jQuery('#curated_paragraphs').stop().animate({
                'left': l + 'px'
            }, {
                duration: 300,
                always: function(){
                    buttonsStatus();
                }
            });
            //jQuery('#curated_paragraphs').css('left', l + 'px');
            //console.log("next - " + parseInt(jQuery('#curated_paragraphs').css('left')));
        }
    });
    jQuery('.prevSlide').click(function (e){
        if(jQuery(this).hasClass('inactiveButton')){
            return;
        } else {
            var l = Math.floor((parseInt(jQuery('#curated_paragraphs').css('left')) + 3 * paragraphWidth) / paragraphWidth) * paragraphWidth;
            if(l >= 0){
                l = 0;
                jQuery(this).addClass('inactiveButton');
            }
            jQuery('#curated_paragraphs').stop().animate({
                'left': l + 'px'
            }, {
                duration: 300,
                always: function(){
                    buttonsStatus();
                }
            });
        }
    });

    jQuery('.expresscurate_tabMenu a').hover(function(){
        var menuItemWidth=jQuery(this).width(),
            index=jQuery(this).index();
        jQuery('.expresscurate_tabMenu .arrow').css({'left':(index*menuItemWidth)-menuItemWidth/2+30+'px'});
    });
    jQuery('.expresscurate_tabMenu').mouseleave(function(){
        moveMenuArrow();
    });
});
function moveMenuArrow(){
    var menuItemWidth= jQuery('.expresscurate_tabMenu a').width(),
        index=jQuery('.expresscurate_tabMenu a.active').index();
    jQuery('.expresscurate_tabMenu .arrow').css({'left':(index*menuItemWidth)-menuItemWidth/2+30+'px'});
}
//

function display_curated_tags(keywords) {
    var keywords_html = '';
    jQuery.each(keywords, function (index, value) {
        keywords_html += '<li  id="curated_post_tag_' + index + '"><span>' + value + '</span><a href="#" class="remove" onclick="del_curated_tag(' + index + '); return false;">&times;</a></li>';
    });
    jQuery("#curated_tags").html(keywords_html);
}

function generate_tags(text) {
    var keywords_html = '';
    if (keywords !== null && keywords > 0) {
        jQuery.each(keywords, function (index, value) {
            if (text.indexOf(value) !== -1) {
                keywords_html += '<li id="curated_post_tag_' + index + '"><a href="#" onclick="del_curated_tag(' + index + '); return false;">X</a><span>' + value + '</span></li>';
                keywords.splice(index, 1);
            }
        });
    }
    jQuery(keywords_html).appendTo("#curated_tags");
    //jQuery("#curated_tags").html(keywords_html);
}
function display_specials(data) {
    var specials_html = '';
    specials_html += display_curated_headings(data.headings);
    specials_html += display_curated_description(data.metas.description);
    if (specials_html.length === 0) {
        specials_html += '<li>No specal data</li>';
    }
    jQuery(specials_html).appendTo('#expresscurate_special');
}
function display_curated_headings(headings) {
    var headings_html = '';
    if (headings.h1 && headings.h1.length > 0) {

        headings_html += '<li id="curated_heading_h1" onclick="insert_text(\'curated_heading_h1\', \'p\');" data-tag="h1" title="' + headings.h1 + '">H1</li>';
    }
    if (headings.h2 && headings.h2.length > 0) {
        headings_html += '<li id="curated_heading_h2" onclick="insert_text(\'curated_heading_h2\', \'li\');" data-tag="h2" title="' + headings.h2 + '">H2</li>';
    }
    if (headings.h3 && headings.h3.length > 0) {
        headings_html += '<li id="curated_heading_h3" onclick="insert_text(\'curated_heading_h3\', \'li\');" data-tag="h3" title="' + headings.h3 + '">H3</li>';
    }
    return headings_html;
}

function display_curated_description(description) {
    var description_html = '';
    if (description !== null && description.length > 0) {
        description_html += '<li id="curated_description" onclick="insert_text(\'curated_description\', \'p\')"; title="' + description + '">Description</li>';
    }
    return description_html;
}

function insert_text_old(id, tag) {
    var currentVal = jQuery('#expresscurate_content_editor').val();
    var paragraph = "<" + tag + ">" + jQuery("#" + id).attr('title').replace(/\r\n/g, "<br />").replace(/\n/g, "<br />");
    +"</" + tag + ">";
    jQuery('#expresscurate_content_editor').val(currentVal + paragraph);
}

function insert_text(id, tag) {
    var paragraph = '';
    if (tag == 'li') {
        paragraph += "<ul>";
        var lis = jQuery("#" + id).attr('title');
        lis = lis.split(/\r?\n/);
        jQuery.each(lis, function (index, value) {
            if (value) {
                paragraph += "<li>" + value + "</li>";
            }
        });
        paragraph += "</ul>";
    } else {
        paragraph += "<" + tag + ">" + jQuery("#" + id).attr('title').replace(/\r\n/g, "<br />").replace(/\n/g, "<br />");
        +"</" + tag + "> &nbsp;";
    }
    generate_tags(paragraph);
    tinyMCE.get('expresscurate_content_editor').execCommand('mceInsertContent', false, paragraph);
}

function del_curated_tag(index) {
    jQuery("#curated_post_tag_" + index).fadeOut(7000).remove();
    return false;
}

function insert_delete_image(index) {
    if (jQuery("#tcurated_image_" + index).parent().attr('id') == 'curated_images') {
        if (jQuery("#curated_content_selected_img").find("img").length == 0) {
            jQuery("#tcurated_image_" + index).appendTo('#curated_content_selected_img');
            jQuery("#curated_images #tcurated_image_" + index).remove();
        } else {
            jQuery("#" + jQuery('#curated_content_selected_img').find("img").parent().attr('id')).appendTo('#curated_images');
            jQuery("#curated_content_selected_img #" + jQuery('#curated_content_selected_img').find("img").parent().attr('id')).remove();
            jQuery("#tcurated_image_" + index).appendTo('#curated_content_selected_img');
            jQuery("#curated_images #tcurated_image_" + index).remove();
        }
    } else if (jQuery("#tcurated_image_" + index).parent().attr('id') == 'curated_content_selected_img') {
        jQuery("#tcurated_image_" + index).appendTo('#curated_images');
        jQuery("#curated_content_selected_img #tcurated_image_" + index).remove();
    } else {
        //alert(jQuery("#tcurated_image_" + index).parent().attr('id'));
    }

}

function clear_expresscurate_form() {
    jQuery('#expresscurate_dialog div.error').remove();
    jQuery('#expresscurate_dialog div.updated').remove();
    jQuery("#expresscurate_dialog").find('ul').html('');
    //jQuery("#expresscurate_dialog").find('input[type=text]').val('');
    jQuery("#expresscurate_content_editor").val('');
    jQuery('.content .img').attr('style', '');
    jQuery('.content .img').addClass("noimage");
    jQuery('.controls').hide();
    //jQuery("#expresscurate_slider").html('').html('<ul class="preview left jcarousel-skin-tango" id="expresscurate_paragraphs"></ul>');
    jQuery("#curated_paragraphs").empty();
    if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function") {
        tinyMCE.get('expresscurate_content_editor').setContent('');
    }
    jQuery('#expresscurate_source').focus();
}

// setup everything when document is ready
jQuery(document).ready(function ($) {
    jQuery('textarea[name=expresscurate_add_tags]').val('');
    if (jQuery.ui) {
        if (jQuery("#expresscurate_dialog").length) {
            var $dialog = jQuery("#expresscurate_dialog");
            $dialog.dialog({
                'dialogClass': 'wp-dialog',
                'modal': true,
                'autoOpen': false,
                'closeOnEscape': true,
                'width': '829px',
                'height': 'auto',
                'resizable': false,
                'close': clear_expresscurate_form
            });
        } else {
            var $dialog = jQuery("#expresscurate_dialog_theme");
            $dialog.dialog({
                'dialogClass': 'wp-dialog',
                'modal': true,
                'autoOpen': false,
                'closeOnEscape': true,
                'width': '829px',
                'resizable': false,
                'close': clear_expresscurate_form
            });
        }

        jQuery("#expresscurate_content_editor").addClass("mceEditor");

        var currentImage = 0;
        var numberOfImages = 0;

        /*modified, new added*/
        jQuery('.prevImg, .nextImg').click(function (e) {
            numberOfImages = jQuery('ul#curated_images li').length;
            if (jQuery(this).hasClass('next')) {
                currentImage = (++currentImage > numberOfImages) ? numberOfImages : currentImage;
            } else if (jQuery(this).hasClass('prev')) {
                currentImage = (--currentImage < 0) ? 0 : currentImage;
            }
            var img = jQuery('ul#curated_images li:eq(' + currentImage + ')').css('background-image');
            if (img) {
                jQuery('.content .img').css('background-image', img);
            }
        });
        /*
         jQuery('.content .img').on('click', '.prev, .next', function() {
         numberOfImages = jQuery('ul#curated_images li').length;
         if (jQuery(this).hasClass('next')) {
         currentImage = (++currentImage > numberOfImages) ? numberOfImages : currentImage;
         } else if (jQuery(this).hasClass('prev')) {
         currentImage = (--currentImage < 0) ? 0 : currentImage;
         }
         var img = jQuery('ul#curated_images li:eq(' + currentImage + ')').css('background-image');
         if (img) {
         jQuery('.content .img').css('background-image', img);
         }
         }).on('mouseenter', function() {
         numberOfImages = jQuery('ul#curated_images li').length;
         if (numberOfImages > 1) {
         jQuery('.nav a').stop().animate({
         width: 17
         }, 200);
         }
         }).on('mouseleave', function() {
         jQuery('.nav a').stop().animate({
         width: 0
         }, 200);
         });
         */

        var alignImg = 'alignnone';
        jQuery('.alignL').click(function (e) {
            alignImg = 'alignleft';
        });
        jQuery('.alignNone').click(function (e) {
            alignImg = 'alignnone';
        });
        jQuery('.alignR').click(function (e) {
            alignImg = 'alignright';
        });

        var imgSize = 'sizeX';
        jQuery('.sizeX').click(function (e) {
            imgSize = 'sizeX';
        });
        jQuery('.sizeM').click(function (e) {
            imgSize = 'sizeM';
        });
        jQuery('.sizeS').click(function (e) {
            imgSize = 'sizeS';
        });

        jQuery('.sizeS, .sizeM, .sizeX').each(function (i, el) {
            $(this).click(function (e) {
                jQuery('.sizeS, .sizeM, .sizeX').css({
                    'background-color': '#777777'
                });
                $(this).css({
                    'background-color': '#27cfae'
                });
            });
        });
        jQuery('.alignL').click(function (e) {
            jQuery(this).css('background-position', 'right bottom');
            jQuery('.alignNone').css('background-position', 'center top');
            jQuery('.alignR').css('background-position', 'left top');
        });
        jQuery('.alignR').click(function (e) {
            jQuery('.alignL').css('background-position', 'right top');
            jQuery('.alignNone').css('background-position', 'center top');
            jQuery(this).css('background-position', 'left bottom');
        });
        jQuery('.alignNone').click(function (e) {
            jQuery('.alignL').css('background-position', 'right top');
            jQuery(this).css('background-position', 'center bottom');
            jQuery('.alignR').css('background-position', 'left top');
        });
        /**/

        jQuery("#expresscurate_open-modal").click(function (event) {
            event.preventDefault();
            //tinyMCE.activeEditor.focus(false);
            var editor = tinyMCE.get('content');
            if (!editor) {
                editor = jQuery('#content');
                if (editor.length == 0) {
                    if (tinyMCE.editors.length > 0) {
                        editor = tinyMCE.editors[0];
                    }
                }
            }

            var body = tinyMCE.activeEditor.dom.select('body.mce-content-body');
            jQuery(body).append('<span class="cursourHolder"> </span>');
            var sp = tinyMCE.activeEditor.dom.select('span.cursourHolder');
            tinyMCE.activeEditor.selection.select(sp[sp.length - 1]);

            if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function") {
                if (!tinyMCE.execCommand("mceAddControl", true, "expresscurate_content_editor")) {
                    tinyMCE.execCommand("mceAddEditor", true, "expresscurate_content_editor");
                }
            }

            //jQuery("#expresscurate_content_editor_resize").trigger('click');
            //clear_expresscurate_form();
            $dialog.dialog('open');
        });

        jQuery("#expresscurate_insert").click(function () {
            var tags_html = '';
            var inserted_tags = jQuery("#post_tag .tagchecklist span").length;
            var inserted_tags_textarea = "";
            inserted_tags_textarea = jQuery("#tax-input-post_tag").val();
            jQuery('#curated_tags li').each(function (i) {
                inserted_tags_textarea += "," + jQuery(this).find('span').text();
            });
            jQuery("#tax-input-post_tag").val(inserted_tags_textarea);
            jQuery(".tagadd").trigger('click');
            var html = "";
            var insite_html = '';
            var bg = jQuery('.img').css('background-image');

            bg = bg.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
            if (bg.indexOf('images/noimage.png') === -1 && bg.length > 5) {
                ///html += jQuery("#curated_content_selected_img li").html();
                html += '<img class="' + alignImg + ' ' + imgSize + '" src="' + bg + '">';
            }
            if (tinyMCE.get('expresscurate_content_editor').getContent().length > 0) {
                html += "<blockquote>" + tinyMCE.get('expresscurate_content_editor').getContent() + "</blockquote><br />";
            }
            html += insite_html;
            if (html.length > 0) {
                if (jQuery("#expresscurate_source").val().length > 0) {
                    var matches = jQuery("#expresscurate_source").val().match(/^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i);
                    var domain = matches && matches[1];
                    if (domain) {
                        html += '<div class="expresscurate_source"><p>' + jQuery("#expresscurate_from").val() + ' <a class="expresscurated" data-curated-url="' + jQuery("#expresscurate_source").val() + '"  href = "' + jQuery("#expresscurate_source").val() + '">' + domain + '</a></p></div><br/>';
                    }
                }
                if (jQuery("#titlewrap #title").val().length == 0) {
                    jQuery("#titlewrap #title").trigger('focus');
                    jQuery("#titlewrap #title").val(jQuery("#curated_title").val());
                }
                send_wp_editor(html);
                $dialog.dialog('close');

            } else {
                return false;
            }

            tinyMCE.activeEditor.execCommand('annotation', undefined, true);
        });
    }

    function submit_expresscurate_form() {
        var blog_domain = document.domain;
        //remove error divs
        jQuery('#expresscurate_dialog div.error').remove();
        jQuery('#expresscurate_dialog div.updated').remove();
        jQuery("#expresscurate_dialog").fadeIn();
        var error_html = '';
        var notif_html = '';
        $.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_get_article&check=1', jQuery('#expresscurate_post_form input').serialize(), function (res) {
            var data = $.parseJSON(res);
            if (data.status == 'notification') {
                notif_html = '<div class="updated">' + data.msg + '</div>';
                jQuery('#expresscurate_post_form').before(notif_html);
            }
        });
        $.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_get_article', jQuery('#expresscurate_post_form input').serialize(), function (res) {
            var data = $.parseJSON(res);
            if (data) {
                if (data.status == 'error') {
                    error_html = '<div class="error">' + data.error + '</div>';
                    jQuery('#expresscurate_post_form').before(error_html);
                    jQuery("#expresscurate_loading").fadeOut('fast');
                } else if (data.status == 'success') {
                    clear_expresscurate_form();
                    jQuery(".controls").show();
                    if (data.result.title !== null && data.result.title.length > 0) {
                        jQuery("#curated_title").val(data.result.title);
                    }
                    if (data.result.images.length > 0) {
                        $.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_export_api_check_images', {img_url: data.result.images[data.result.images.length - 1], img_url2: data.result.images[data.result.images.length - 2]}, function (res) {
                            var data_check = $.parseJSON(res);
                            if (data_check.status === 'success' && data_check.statusCode === 200) {
                                display_curated_images(data.result.images);
                                jQuery("#expresscurate_loading").fadeOut('fast');
                            } else if (data_check.status === 'fail' && data_check.statusCode === 200) {
                                jQuery('.content .img').css('background-image', jQuery("#expresscurate_loading img").attr('src'));
                                $.post(jQuery('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_export_api_download_images', {images: data.result.images, post_id: jQuery('#post_ID').val()}, function (res) {
                                    var data_images = $.parseJSON(res);
                                    if (data_images.status == 'error') {
                                        error_html = '<div class="error">' + data_images.error + '</div>';
                                        jQuery('#expresscurate_post_form').before(error_html);
                                    } else if (data_images.status == 'success') {
                                        display_curated_images(data_images.images);
                                    }
                                    jQuery("#expresscurate_loading").fadeOut('fast');
                                });
                            }
                            else if (data_check.status === 'error') {
                                error_html = '<div class="error">' + data_check.msg + '</div>';
                                jQuery('#expresscurate_post_form').before(error_html);
                                jQuery("#expresscurate_loading").fadeOut('fast');
                            }
                            else {
                                display_curated_images(data.result.images);
                                jQuery("#expresscurate_loading").fadeOut('fast');
                            }
                        });
                    } else {
                        jQuery("#expresscurate_loading").fadeOut('fast');
                    }
                    if (data.result.metas.keywords !== null && data.result.metas.keywords.length > 0) {
                        display_curated_tags(data.result.metas.keywords);
                    }
                    keywords = data.result.metas.keywords;
                    display_specials(data.result);

                    if (data.result.paragraphs.length > 0) {
                        display_curated_paragraphs(data.result.paragraphs, jQuery("#expresscurate_autosummary").val());
                    }
                    jQuery('#expresscurate_source').focus();
                }
            } else {
                error_html = '<div class="error">Can\'t curate from this page</div>';
                jQuery('#expresscurate_post_form').before(error_html);
                jQuery("#expresscurate_loading").fadeOut('fast');
            }

        });

    }

    // onClick action
    jQuery('#expresscurate_submit').click(function () {
        jQuery("#expresscurate_loading").show();
        submit_expresscurate_form();
        jQuery(document).ajaxComplete(function () {

        });

    });

    // check for ENTER or ArrowDown keys
    jQuery('#expresscurate_source').keypress(function (e) {
        if (e.keyCode == 13 || e.keyCode == 40) {
            submit_expresscurate_form();
            return false;
        }

    });

    if (jQuery('input[name=expresscurate_post_status]:checked').val() == 'draft') {
        jQuery('#expresscurate_publish_div').show();
    }
    jQuery('input[name=expresscurate_post_status]').change(function () {
        if (jQuery('input[name=expresscurate_post_status]:checked').val() == 'draft') {
            jQuery('#expresscurate_publish_div').slideDown('slow');
        } else {
            jQuery('#expresscurate_publish_no').attr('checked', true);
            jQuery('#expresscurate_publish_div').slideUp('slow');
        }

    });

    jQuery('#expresscurate_seo').click(function () {
        if (jQuery('input[name=expresscurate_seo]:checked').val() == '0') {
            jQuery('#publisherWrap').slideDown('slow');
        } else {
            jQuery('#publisherWrap').slideUp('slow');
        }
    });

    jQuery('input[name=expresscurate_publisher]').bind("change paste keyup", function () {
        var href = jQuery(this).next('span').children('a').attr('href');
        var rest = href.substring(0, href.lastIndexOf("user_profile") + 13);
        jQuery(this).next('span').children('a').attr('href', rest + jQuery(this).val());
    });
    jQuery('.switch').on("click", function (e) {
        var elem=jQuery(this),
            slider=jQuery(this).find('.slider'),
            input_id=elem.attr('id');
        if(slider.hasClass('sliderOffBack')){
            slider.removeClass('sliderOffBack').addClass('sliderOnBack');
            elem.removeClass('switchOff').addClass('switchOn');
            jQuery('#'+input_id+'_yes').attr('checked', 'checked');
            jQuery('#'+input_id+'_no').attr("checked", false);

        }else{
            slider.removeClass('sliderOnBack').addClass('sliderOffBack');
            elem.removeClass('switchOn').addClass('switchOff');
            jQuery('#'+input_id+'_yes').attr('checked', false);
            jQuery('#'+input_id+'_no').attr('checked', 'checked');
        }
    });

});


function expresscurate_support_submit() {
    jQuery('#expresscurate_support_form .errorMessage').remove();
    var valid_msg = true;
    var valid_email = true;
    var msg = jQuery("#expresscurate_support_message").val();
    var regularExpression = /^[a-zA-Z0-9]+$/;
    valid_msg = regularExpression.test(msg);
    if (msg == "" || msg == null) {
        valid_msg = false;
        jQuery("#expresscurate_support_message").after('<label class="errorMessage">Please enter the message</label>');
    } else if (!valid_msg) {
        jQuery("#expresscurate_support_message").after('<label class="errorMessage">Input is not alphanumeric</label>');
    }else jQuery("#expresscurate_support_message").next('.errorMessage').remove();

    var email = jQuery("#expresscurate_support_email").val();
    var regularExpression = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    valid_email = regularExpression.test(email);
    if (email == "" || email == null) {
        valid_email = false;
        jQuery("#expresscurate_support_email").after('<label class="errorMessage">Please enter the email</label>');
    } else if (!valid_email) {
        jQuery("#expresscurate_support_email").after('<label class="errorMessage">Email is not valid</label>');
    } else jQuery("#expresscurate_support_email").next('.errorMessage').remove();
    if (valid_email && valid_msg) {
        jQuery("#expresscurate_support_form").submit();
    }
    return false;
}