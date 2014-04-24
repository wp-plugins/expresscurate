var keywords;
var plugin_folder = 'expresscurate';
// Curation Plugin for WordPress JS

function send_wp_editor(html) {
	var editor = tinyMCE.get('content');
	editor.execCommand("mceInsertContent", false, html);
}

function display_curated_images(images) {
  var img_count = false;
  jQuery.each(images, function(index, value) {
    var img = new Image();
    img.onload = function() {
      var height = this.height,
              width = this.width;
      if (width > 150 && height > 100) {
        //images_html += '<li id="tcurated_image_' + index + '" class="tcurated_image" style="background-image: url(' + value + ')" onclick="insert_delete_image(' + index + ')"></li>';
        jQuery('<li id="tcurated_image_' + index + '" class="tcurated_image" style="background-image: url(' + value + ')" onclick="insert_delete_image(' + index + ')"></li>').appendTo("#curated_images");
      }
    };
    img.src = value;
  });
  setTimeout(function() {
    if (jQuery('ul#curated_images li').length > 0) {
      jQuery('.content .img').removeClass("noimage");
      jQuery('.content .img').css('background-image', jQuery('ul#curated_images li').first().css('background-image'))
    } else {
      error_html = '<div class="error">No image found for specified size</div>';
      jQuery('#curate_post_form').before(error_html);
    }
  }, 300);
}

function display_curated_paragraphs(paragraphs, count) {
  var text_html = '';
  //var text_div_html = '';
  jQuery.each(paragraphs, function(index, value) {
    text_html += '<li id="tcurated_text_' + index + '" title="' + value + '" onclick="insert_text(\'tcurated_text_' + index + '\', \'p\')">' + value + '</li>';
    if (index < count) {
      generate_tags(value);
      tinyMCE.get('expresscurate_content_editor').execCommand('mceInsertContent', false, "<p>" + value + "<p>");
    }
    //text_div_html += '<div style="display:none;" id="tcurated_text_' + index + '">'+ value +'</div>';
  });
  //jQuery("#tcurated_paragraphs").before(text_div_html);
  //jQuery("#curated_special").html(text_html);
  jQuery(text_html).appendTo('#expresscurate_paragraphs');
  jQuery('#expresscurate_paragraphs').jcarousel();
}

function display_curated_tags(keywords) {
  var keywords_html = '';
  jQuery.each(keywords, function(index, value) {
    keywords_html += '<li  id="curated_post_tag_' + index + '"><span>' + value + '</span><a href="#" class="remove" onclick="del_curated_tag(' + index + '); return false;">&times;</a></li>';
  });
  jQuery("#curated_tags").html(keywords_html);
}

function generate_tags(text) {
  var keywords_html = '';
  if (keywords !== null && keywords > 0) {
    jQuery.each(keywords, function(index, value) {
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
  if (headings.h1.length > 0) {

    headings_html += '<li id="curated_heading_h1" onclick="insert_text(\'curated_heading_h1\', \'p\');" data-tag="h1" title="' + headings.h1 + '">H1</li>';
  }
  if (headings.h2.length > 0) {
    headings_html += '<li id="curated_heading_h2" onclick="insert_text(\'curated_heading_h2\', \'li\');" data-tag="h2" title="' + headings.h2 + '">H2</li>';
  }
  if (headings.h3.length > 0) {
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
    jQuery.each(lis, function(index, value) {
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

function clear_expresscurate_form(insight) {
  jQuery('#expresscurate_dialog div.error').remove();
  jQuery('#expresscurate_dialog div.updated').remove();
  jQuery("#expresscurate_dialog").find('ul').html('');
  //jQuery("#expresscurate_dialog").find('input[type=text]').val('');
  jQuery("#expresscurate_content_editor").val('');
  if (insight !== true) {
    //jQuery("#wp_curation_dialog").find('input[type=text]').val('');
    jQuery("#expresscurate_insight_editor").val('');
  }
  jQuery('.content .img').attr('style', '');
  jQuery('.content .img').addClass("noimage");
  jQuery('.controls').hide();
  jQuery("#expresscurate_slider").html('').html('<ul class="preview left jcarousel-skin-tango" id="expresscurate_paragraphs"></ul>');
  if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function") {
    tinyMCE.get('expresscurate_content_editor').setContent('');
    if (insight !== true) {
      tinyMCE.get('expresscurate_insight_editor').setContent('');
      if(!tinyMCE.execCommand("mceRemoveControl", true, "expresscurate_content_editor")) {
    	  tinyMCE.execCommand("mceRemoveEditor", true, "expresscurate_content_editor");
      }
      if(!tinyMCE.execCommand("mceRemoveControl", true, "expresscurate_insight_editor")) {
    	  tinyMCE.execCommand("mceRemoveEditor", true, "expresscurate_insight_editor");
      }
    }
  }
}

// setup everything when document is ready
jQuery(document).ready(function($) {
  $('textarea[name=expresscurate_add_tags]').val('');
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

    jQuery("#expresscurate_content_editor, #expresscurate_insight_editor").addClass("mceEditor");

    var currentImage = 0;
    var numberOfImages = 0;

    $('.content .img').on('click', '.prev, .next', function() {
      numberOfImages = $('ul#curated_images li').length;
      if ($(this).hasClass('next')) {
        currentImage = (++currentImage > numberOfImages) ? numberOfImages : currentImage;
      } else if ($(this).hasClass('prev')) {
        currentImage = (--currentImage < 0) ? 0 : currentImage;
      }
      var img = $('ul#curated_images li:eq(' + currentImage + ')').css('background-image');
      if (img) {
        $('.content .img').css('background-image', img);
      }
    }).on('mouseenter', function() {
      numberOfImages = $('ul#curated_images li').length;
      if (numberOfImages > 1) {
        $('.nav a').stop().animate({
          width: 17
        }, 200);
      }
    }).on('mouseleave', function() {
      $('.nav a').stop().animate({
        width: 0
      }, 200);
    });

    $("#expresscurate_open-modal").click(function(event) {
      event.preventDefault();

      if (typeof(tinyMCE) === "object" && typeof(tinyMCE.execCommand) === "function") {
        if(!tinyMCE.execCommand("mceAddControl", true, "expresscurate_content_editor")) {
        	tinyMCE.execCommand("mceAddEditor", true, "expresscurate_content_editor");
        }
        if(!tinyMCE.execCommand("mceAddControl", true, "expresscurate_insight_editor")) {
        	tinyMCE.execCommand("mceAddEditor", true, "expresscurate_insight_editor");
        }
      }
      //$("#expresscurate_content_editor_resize").trigger('click');
      //$("#expresscurate_insight_editor_resize").trigger('click');
      //clear_expresscurate_form();
      $dialog.dialog('open');
    });

    $("#expresscurate_insert").click(function() {
      var tags_html = '';
      var inserted_tags = $("#post_tag .tagchecklist span").length;
      var inserted_tags_textarea = "";
      inserted_tags_textarea = $("#tax-input-post_tag").val();
      $('#curated_tags li').each(function(i) {
        inserted_tags_textarea += "," + $(this).find('span').text();
      });
      $("#tax-input-post_tag").val(inserted_tags_textarea);
      $(".tagadd").trigger('click');
      var html = "";
      var insite_html = '';

      if (tinyMCE.get('expresscurate_insight_editor').getContent().length > 0) {
        insite_html += '<div class="expresscurate_annotate">' + tinyMCE.get('expresscurate_insight_editor').getContent() + '</div>';
      }

      var bg = $('.img').css('background-image');

      bg = bg.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
      if (bg.indexOf('images/noimage.png') === -1 && bg.length > 5) {
        ///html += $("#curated_content_selected_img li").html();
        html += '<img src="' + bg + '">';
      }
      if (tinyMCE.get('expresscurate_content_editor').getContent().length > 0) {
        html += "<blockquote>" + tinyMCE.get('expresscurate_content_editor').getContent() + "</blockquote>";
      }
      html += insite_html;
      if (html.length > 0) {
        if ($("#expresscurate_source").val().length > 0) {
          var matches = $("#expresscurate_source").val().match(/^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i);
          var domain = matches && matches[1];
          html += '<div class="expresscurate_source"><p>' + $("#expresscurate_from").val() + ' <a class="expresscurated" data-curated-url="' + $("#expresscurate_source").val() + '"  href = "' + $("#expresscurate_source").val() + '">' + domain + '</a></p></div>';
        }
        if ($("#titlewrap #title").val().length == 0) {
          $("#titlewrap #title").trigger('focus');
          $("#titlewrap #title").val($("#curated_title").val());
        }
        send_wp_editor(html);
        $dialog.dialog('close');

      } else {
        return false;
      }
    });
  }

  function submit_expresscurate_form() {
    var blog_domain = document.domain;
    //remove error divs
    $('#expresscurate_dialog div.error').remove();
    $('#expresscurate_dialog div.updated').remove();
    $("#expresscurate_dialog").fadeIn();
    var error_html = '';
    var notif_html = '';
    $.post($('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_get_article&check=1', $('#curate_post_form input').serialize(), function(res) {
      var data = $.parseJSON(res);
      if (data.status == 'notification') {
        notif_html = '<div class="updated">' + data.msg + '</div>';
        $('#curate_post_form').before(notif_html);
      }
    });
    $.post($('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_get_article', $('#curate_post_form input').serialize(), function(res) {
      var data = $.parseJSON(res);
      if (data) {
        if (data.status == 'error') {
          error_html = '<div class="error">' + data.error + '</div>';
          $('#curate_post_form').before(error_html);
          $("#expresscurate_loading").fadeOut('fast');
        } else if (data.status == 'success') {
          clear_expresscurate_form(true);
          $(".controls").show();
          if (data.result.title !== null && data.result.title.length > 0) {
            $("#curated_title").val(data.result.title);
          }
          if (data.result.images.length > 0) {
            $.post($('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_export_api_check_images', {img_url: data.result.images[data.result.images.length - 1], img_url2: data.result.images[data.result.images.length - 2]}, function(res) {
              var data_check = $.parseJSON(res);
              if (data_check.status === 'success' && data_check.statusCode === 200) {
                display_curated_images(data.result.images);
                $("#expresscurate_loading").fadeOut('fast');
              } else if (data_check.status === 'fail' && data_check.statusCode === 403) {
                $('.content .img').css('background-image', $("#expresscurate_loading img").attr('src'));
                $.post($('#expresscurate_admin_url').val() + 'admin-ajax.php?action=expresscurate_export_api_download_images', {images: data.result.images, post_id: $('#post_ID').val()}, function(res) {
                  var data_images = $.parseJSON(res);
                  if (data_images.status == 'error') {
                    error_html = '<div class="error">' + data_images.error + '</div>';
                    $('#curate_post_form').before(error_html);
                  } else if (data_images.status == 'success') {
                    display_curated_images(data_images.images);
                  }
                  $("#expresscurate_loading").fadeOut('fast');
                });
              }
              else if (data_check.status === 'error') {
                error_html = '<div class="error">' + data_check.msg + '</div>';
                $('#curate_post_form').before(error_html);
                $("#expresscurate_loading").fadeOut('fast');
              }
              else {
                display_curated_images(data.result.images);
                $("#expresscurate_loading").fadeOut('fast');
              }
            });
          } else {
            $("#expresscurate_loading").fadeOut('fast');
          }
          if (data.result.metas.keywords !== null && data.result.metas.keywords.length > 0) {
            display_curated_tags(data.result.metas.keywords);
          }
          keywords = data.result.metas.keywords;
          display_specials(data.result);

          if (data.result.paragraphs.length > 0) {
            display_curated_paragraphs(data.result.paragraphs, $("#expresscurate_autosummary").val());
          }
        }
      } else {
        error_html = '<div class="error">Can\'t curate from this page</div>';
        $('#curate_post_form').before(error_html);
        $("#expresscurate_loading").fadeOut('fast');
      }

    });


    return;
  }

  // onClick action
  $('#expresscurate_submit').click(function() {
    $("#expresscurate_loading").show();
    submit_expresscurate_form();
    $(document).ajaxComplete(function() {

    });

  });

  // check for ENTER or ArrowDown keys
  $('#expresscurate_source').keypress(function(e) {
    if (e.keyCode == 13 || e.keyCode == 40) {
      submit_expresscurate_form();
      return false;
    }

  });

  if ($('input[name=expresscurate_post_status]:checked').val() == 'draft') {
    $('#expresscurate_publish_div').show();
  }
  $('input[name=expresscurate_post_status]').change(function() {
    if ($('input[name=expresscurate_post_status]:checked').val() == 'draft') {
      $('#expresscurate_publish_div').slideDown('slow');
    } else {
      $('#expresscurate_publish_no').attr('checked', true);
      $('#expresscurate_publish_div').slideUp('slow');
    }

  });

  $('input[name=expresscurate_seo]').change(function() {
    if ($('input[name=expresscurate_seo]:checked').val() == '1') {
      $('#tryyy').slideDown('slow');
    } else {
      $('#tryyy').slideUp('slow');
    }
  });

  $('input[name=expresscurate_publisher]').bind("change paste keyup", function() {
    var href = $(this).next('span').children('a').attr('href');
    var rest = href.substring(0, href.lastIndexOf("user_profile") + 13);
    $(this).next('span').children('a').attr('href', rest + $(this).val());
  });

  $('textarea[name=expresscurate_add_tags]').on("keyup", function(e) {
    if (e.keyCode == 188 || e.keyCode == 13) {
      addKeyWord($(this));
    }
  });

  $('.expresscurate_addKeyword').on("click", function(e) {
    var textarea = $('textarea[name=expresscurate_add_tags]');
    addKeyWord(textarea);
    textarea.focus();
  });

  function addKeyWord(textarea) {
    text = textarea.val().replace(',', '');
    text = text.replace(/[,.;:?!\s]+/g, '');
    $.each($('.expresscurate_keywords'), function(key, value) {
      if (justtext($(this)) == text) {
        text = '';
      }
    });
    if (!/^\s+$/.test(text) && text.length > 1) {
      var keyword = '<div class="expresscurate_keywords">' + text + '<span>×</span></div>';
      textarea.parent('div').before(keyword);
      var defTags = $('textarea[name=expresscurate_defined_tags]');
      var defVal = defTags.val();
      var s ;
	  if(defVal=='') s=text;
		else  s=defVal + ', ' + text;
      defTags.val(s);
    }
    textarea.val('');
  }

  function justtext(elem) {
    return elem.clone().children().remove().end().text();
  }

  $('.expresscurate_keywords span').live('click', function() {
    var defTags = $('textarea[name=expresscurate_defined_tags]');
    defTags.val(defTags.val().replace(justtext($(this).parent('div')), ''));
	defTags.val(defTags.val().replace(', ,', ','));
	if(defTags.val().match(/^, /)) defTags.val(defTags.val().slice(2));
    $(this).parent('div').remove();
  });

});
