
(function() { 
    var getId = function(prefix) {
        var uniqueId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0,
                v = c == 'x' ? r : r & 0x3 | 0x8;
            return v.toString(16);
        });

        return (prefix || '') + '-' + uniqueId;
    };


	
	 function getElem(node,cssClass) {
        if (node && node.className && !! ~node.className.indexOf(cssClass)) {
            return node;
        }

        if (node.parentNode) {
            return getElem(node.parentNode,cssClass);
        }

        return false;
    };


	function textboxCommand(ed,elem,cssClass){
		var node=jQuery(ed.selection.getNode()).parents('div'),
				selectedClass=jQuery(node).attr('class');
		if(selectedClass==cssClass){
			var texbox = getElem(ed.selection.getNode(),cssClass);
			if (texbox) {
				if(jQuery(texbox).html()=='<p>&nbsp;</p>'){
					jQuery(texbox).html('');
					jQuery(texbox).remove();
				}else{
					var unwrapElem=jQuery(ed.selection.getNode());
					unWrap(unwrapElem);
					
				}
				ed.controlManager.setActive(elem,false);
				ed.selection.setCursorLocation(ed.selection.getNode(), 0);
			}
		} else {
			var content='';
			if (!node.is('div')) {
				content=ed.selection.getContent()
			}else {
				content=jQuery(ed.selection.getNode()).parents('div').html();
				jQuery(node).remove();
			}
			
			if(content=='') {
				content='<p>&nbsp;</p>';
			}else if (content.indexOf('<p>') < 0) {
				content ='<p>'+content+'</p>';
			}
			if(selectedClass && node.is('div')){
				ed.execCommand('mceRemoveNode', 0, node.id);
			}
			
			var id = elem + getId(),
				texboxElem = ed.getDoc().createElement('DIV');
			
			texboxElem.id = id;
			texboxElem.className =cssClass;
			texboxElem.innerHTML = content;
			ed.execCommand('mceInsertContent', true, texboxElem.outerHTML);
			
			var activeElem=jQuery(ed.selection.getNode()).parents('div').attr('id');
			activeElem=activeElem.substring(0,activeElem.indexOf('-'));
			ed.controlManager.setActive(activeElem,true);
		}
		
	};
	
	function unWrap(elem){
		if(elem.is('span')) elem=elem.parent('p');
		if(elem.parents().is('blockquote')) {elem=elem.parents('blockquote');}
		if(elem.index()==0){
				elem.parents('div').before(elem);
			}else if(elem.parents('div').children().length==elem.index()+1){
					elem.parents('div').after(elem);
				}else{
					var divhtml= jQuery(elem).parents('div').html();
					var myps = divhtml.split(elem.html());
					elem.parents('div').after(elem.parent('div').clone().html(myps[1])).html(myps[0]).after(elem);
					if(elem.prev().children('blockquote').html()=='') elem.prev().children('blockquote').remove();
				}
		if(elem.next().text()=='') elem.next().remove();
		if(elem.prev().text()=='') elem.prev().remove();
		
	}
	
    tinymce.create('tinymce.plugins.expresscurate', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
		
        init: function(ed, url) {
		
            ed.addButton('annotation', {
                title: 'Add Annotation (Ctrl + Up)',
                cmd: 'annotation',
                image: url + '/../images/annotate.png'
            });
			ed.addButton('righttextbox', {
                title: 'Add Right-Box (Ctrl + R | Ctrl + Right)',
                cmd: 'righttextbox',
                image: url + '/../images/rightBox.png',
            });
			ed.addButton('justifytextbox', {
                title: 'Add Center-Box (Ctrl + J | Ctrl + Down)',
                cmd: 'justifytextbox',
                image: url + '/../images/justifyBox.png'
            });
			ed.addButton('lefttextbox', {
                title: 'Add Left-Box (Ctrl + L | Ctrl + Left)',
                cmd: 'lefttextbox',
                image: url + '/../images/leftBox.png'
            });
           
            ed.onKeyDown.add(function(ed, e) {
				if (e.ctrlKey && (e.keyCode == 76 || e.keyCode == 37)) {     // ctrl+l|ctrl+left
					 e.returnValue = false;
					 textboxCommand(ed,'lefttextbox','expresscurate_fl_text_box');
					 e.preventDefault();
					 return false;
					}else if (e.ctrlKey && (e.keyCode == 82 || e.keyCode == 39)) {     // ctrl+r|ctrl+right
						 e.returnValue = false;
						 textboxCommand(ed,'righttextbox','expresscurate_fr_text_box');
						 e.preventDefault();
						 return false;
						}else if (e.ctrlKey && (e.keyCode == 74 || e.keyCode == 40)) {     // ctrl+j|ctrl+down
							 e.returnValue = false;
							 textboxCommand(ed,'justifytextbox','expresscurate_justify_text_box');
							 e.preventDefault();
							 return false;
							}else if (e.ctrlKey && e.keyCode == 38) {     // ctrl+up
								 e.returnValue = false;
								 textboxCommand(ed,'annotation','expresscurate_annotate');
								 e.preventDefault();
								 return false;
							}
				
               
            });

            ed.onLoadContent.add(function(ed, o) {
				var dom = tinymce.activeEditor.dom;
				var divElements = dom.select('div[class*=expresscurate]');
				dom.setStyle(divElements, 'height', 'auto');
		    });
			
			ed.onNodeChange.add(function(ed) {	
				var node,elem=jQuery(ed.selection.getNode());
				if(elem.is('div')){
					node= elem;
				  }else{
					node=elem.parents('div');
				  }
				
				var	cssClass=jQuery(node).attr('class'),
					activeButton=' ';
					  if (cssClass == 'expresscurate_fl_text_box')
							activeButton='lefttextbox';
						else if(cssClass == 'expresscurate_fr_text_box')
								activeButton='righttextbox';
							else if(cssClass == 'expresscurate_justify_text_box')
									activeButton='justifytextbox';
								else if(cssClass == 'expresscurate_annotate')
										activeButton='annotation';
				ed.controlManager.setActive('lefttextbox',false);
				ed.controlManager.setActive('righttextbox',false);
				ed.controlManager.setActive('justifytextbox',false);
				ed.controlManager.setActive('annotation',false);
				if(activeButton!=' ')
					ed.controlManager.setActive(activeButton,true);
			});
			
			//lefttextbox
			 ed.addCommand('lefttextbox',  function() {
				textboxCommand(ed,'lefttextbox','expresscurate_fl_text_box');
			});
			//righttextbox
			 ed.addCommand('righttextbox', function() {
				textboxCommand(ed,'righttextbox','expresscurate_fr_text_box');
            });
			//justifytextbox
			 ed.addCommand('justifytextbox', function() {
				textboxCommand(ed,'justifytextbox','expresscurate_justify_text_box');
            });
			//annotation
			 ed.addCommand('annotation', function() {
				textboxCommand(ed,'annotation','expresscurate_annotate');
            });
        },
		
        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl: function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo: function() {
            return {
                longname: 'Express curate Buttons',
                author: 'ExpressCurate',
                version: "0.1"
            };
        }
    });
    // Register plugin
    tinymce.PluginManager.add('expresscurate', tinymce.plugins.expresscurate);
})();(function() { 
    var getId = function(prefix) {
        var uniqueId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0,
                v = c == 'x' ? r : r & 0x3 | 0x8;
            return v.toString(16);
        });

        return (prefix || '') + '-' + uniqueId;
    };


	
	 function getElem(node,cssClass) {
        if (node && node.className && !! ~node.className.indexOf(cssClass)) {
            return node;
        }

        if (node.parentNode) {
            return getElem(node.parentNode,cssClass);
        }

        return false;
    };


	function textboxCommand(ed,elem,cssClass){
		var isActiveTextbox = ed.controlManager.get(elem).active;
		if (isActiveTextbox ) {
			var texbox = getElem(ed.selection.getNode(),cssClass);
			if (texbox) {
				if(jQuery(texbox).html()=='<p>&nbsp;</p>'){
					jQuery(texbox).html('');
					jQuery(texbox).remove();
				}else{
					var unwrapElem=jQuery(ed.selection.getNode());
					unWrap(unwrapElem);
					ed.selection.setCursorLocation(ed.selection.getNode(), 0);
				}
				ed.controlManager.get(elem).setActive(false);
				
			}
		} else {
			var node=jQuery(ed.selection.getNode()).parents('div'),
				selectedClass=jQuery(node).attr('class');
			if(selectedClass!=cssClass){
			
				var content='';
				if (!node.is('div')) {
					content=ed.selection.getContent()
				}else {
					content=jQuery(ed.selection.getNode()).parents('div').html();
					jQuery(node).remove();
				}
				
				if(content=='') {
					content='<p>&nbsp;</p>';
				}else if (content.indexOf('<p>') < 0) {
					content ='<p>'+content+'</p>';
				}
				if(selectedClass && node.is('div')){
					ed.execCommand('mceRemoveNode', 0, node.id);
				}
				
				var id = elem + getId(),
				    texboxElem = ed.getDoc().createElement('DIV');
				
				texboxElem.id = id;
				texboxElem.className =cssClass;
				texboxElem.innerHTML = content;
				ed.execCommand('mceInsertContent', true, texboxElem.outerHTML);
				
				var activeElem=jQuery(ed.selection.getNode()).parents('div').attr('id');
				activeElem=activeElem.substring(0,activeElem.indexOf('-'));
				ed.controlManager.get(activeElem).setActive(true);
			}
		}
	};
	
	function unWrap(elem){
		if(!elem.is('p')){
			if(elem.is('span')) elem=elem.parent('p');
			if(elem.parent().is('blockquote')) {elem=elem.parent('blockquote');}
		}
		if(elem.index()==0){
				elem.parents('div').before(elem);
			}else if(elem.parents('div').children().length==elem.index()+1){
					elem.parents('div').after(elem);
				}else{
					var divhtml= jQuery(elem).parents('div').html();
					var myps = divhtml.split(elem.html());
					elem.parents('div').after(elem.parent('div').clone().html(myps[1])).html(myps[0]).after(elem);
				}
		if(elem.next().children().text()=='') elem.next().remove();
		if(elem.prev().children().text()=='') elem.prev().remove();
		
	}
	
    tinymce.create('tinymce.plugins.expresscurate', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
		
        init: function(ed, url) {
		
            ed.addButton('annotation', {
                title: 'Add Annotation (Ctrl + Up)',
                cmd: 'annotation',
                image: url + '/../images/annotate.png'
            });
			ed.addButton('righttextbox', {
                title: 'Add Right-Box (Ctrl + R | Ctrl + Right)',
                cmd: 'righttextbox',
                image: url + '/../images/rightBox.png',
            });
			ed.addButton('justifytextbox', {
                title: 'Add Center-Box (Ctrl + J | Ctrl + Down)',
                cmd: 'justifytextbox',
                image: url + '/../images/justifyBox.png'
            });
			ed.addButton('lefttextbox', {
                title: 'Add Left-Box (Ctrl + L | Ctrl + Left)',
                cmd: 'lefttextbox',
                image: url + '/../images/leftBox.png'
            });
           
            ed.onKeyDown.add(function(ed, e) {
				if (e.ctrlKey && (e.keyCode == 76 || e.keyCode == 37)) {     // ctrl+l|ctrl+left
					 e.returnValue = false;
					 textboxCommand(ed,'lefttextbox','expresscurate_fl_text_box');
					 e.preventDefault();
					 return false;
					}else if (e.ctrlKey && (e.keyCode == 82 || e.keyCode == 39)) {     // ctrl+r|ctrl+right
						 e.returnValue = false;
						 textboxCommand(ed,'righttextbox','expresscurate_fr_text_box');
						 e.preventDefault();
						 return false;
						}else if (e.ctrlKey && (e.keyCode == 74 || e.keyCode == 40)) {     // ctrl+j|ctrl+down
							 e.returnValue = false;
							 textboxCommand(ed,'justifytextbox','expresscurate_justify_text_box');
							 e.preventDefault();
							 return false;
							}else if (e.ctrlKey && e.keyCode == 38) {     // ctrl+up
								 e.returnValue = false;
								 textboxCommand(ed,'annotation','expresscurate_annotate');
								 e.preventDefault();
								 return false;
							}
				
               
            });

           
			ed.onNodeChange.add(function(ed) {	
				var node,elem=jQuery(ed.selection.getNode());
				if(elem.is('div')){
					node= elem;
				  }else{
					node=elem.parents('div');
				  }
				
				var	cssClass=jQuery(node).attr('class'),
					activeButton=' ';
					  if (cssClass == 'expresscurate_fl_text_box')
							activeButton='lefttextbox';
						else if(cssClass == 'expresscurate_fr_text_box')
								activeButton='righttextbox';
							else if(cssClass == 'expresscurate_justify_text_box')
									activeButton='justifytextbox';
								else if(cssClass == 'expresscurate_annotate')
										activeButton='annotation';
				ed.controlManager.get('lefttextbox').setActive(false);
				ed.controlManager.get('righttextbox').setActive(false);
				ed.controlManager.get('justifytextbox').setActive(false);
				ed.controlManager.get('annotation').setActive(false);
				if(activeButton!=' ')
					ed.controlManager.get(activeButton).setActive(true);
			});
			
			//lefttextbox
			 ed.addCommand('lefttextbox',  function() {
				textboxCommand(ed,'lefttextbox','expresscurate_fl_text_box');
			});
			//righttextbox
			 ed.addCommand('righttextbox', function() {
				textboxCommand(ed,'righttextbox','expresscurate_fr_text_box');
            });
			//justifytextbox
			 ed.addCommand('justifytextbox', function() {
				textboxCommand(ed,'justifytextbox','expresscurate_justify_text_box');
            });
			//annotation
			 ed.addCommand('annotation', function() {
				textboxCommand(ed,'annotation','expresscurate_annotate');
            });
        },
		
        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl: function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo: function() {
            return {
                longname: 'Express curate Buttons',
                author: 'ExpressCurate',
                version: "0.1"
            };
        }
    });
    // Register plugin
    tinymce.PluginManager.add('expresscurate', tinymce.plugins.expresscurate);
})();