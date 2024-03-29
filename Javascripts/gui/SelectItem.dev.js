;jQuery.gui = jQuery.gui || {};

(function($){
	
	var inputs = {}, rivers = {}, ed, w, River, Query, lastSelected, dialog = $('<div>');

	$.gui.SelectItem = {
			
		timeToTriggerRiver: 150,
		minRiverAJAXDuration: 200,
		riverBottomThreshold: 5,
		keySensitivity: 100,
		lastSearch: '',
		
		defaults : {
			id: '',
			field_id: '',
			multiple: false,
			internal: true,
			link: true,
			title: '',
			onSelect: false,
			onCancel: false
		},
		
		init : function(o) {
			
			o = o || {};
			
			opts = $.extend(this.defaults, o);
			
			if( !opts.onSelect ) opts.onSelect = $.gui.SelectItem.selectIt;
			
			w = $('#gk-link');
			
			// sublit button
			inputs.submit = $('#gk-link-submit');
			
			// URL
			inputs.url = $('#gk-url-field');
			
			// Secondary options
			inputs.title = $('#gk-link-title-field');
			
			// Advanced Options
			inputs.openInNewTab = $('#gk-link-target-checkbox');
			
			// Advanced Options
			inputs.search = $('#gk-search-field');
			inputs.type = $('input[name="search-type"]:checked');
			
			// Build Rivers
			rivers.search = new River( $('#gk-search-results') );
			rivers.recent = new River( $('#gk-most-recent-results') );
			rivers.elements = $('.query-results', w);
			
			// Bind event handlers
			w.keydown( this.keydown );
			w.keyup( this.keyup );
			
			inputs.submit.click( function(e){
				$.gui.SelectItem.update();
				e.preventDefault();
			});
			
			$('#gk-link-cancel').click( this.cancel );
			$('#gk-internal-toggle').click( this.toggleInternalLinking );
			
			rivers.elements.bind('river-select',this.updateFields );

			inputs.search.keyup( this.searchInternalLinks );
			
			$('input[name="search-type"]', w).click( this.searchInternalLinks );
			
			this.refresh();
		},
		
		select : function (id, field_id, o) {
			
			var opts = $.extend(this.defaults, o || {} );
			
			opts.id = id;
			opts.field_id = field_id;
			
			var args = {
					id : id,
					action : 'gui-select-item-' + id
			};
						
			dialog.dialog( {
				autoOpen: false,
				title : opts.title,		
				width: 480,
				height: 'auto',
				modal: true,
				dialogClass: 'wp-dialog gk-dialog',
				zIndex: 300000
		    } );
			
			dialog.load(ajaxurl, args, function () {
				dialog.dialog('open');
				$.gui.SelectItem.init(opts);
			});

            //prevent the browser to follow the link
            return false;

		},

		refresh : function() {
			
			// Refresh rivers (clear links, check visibility)
			rivers.search.refresh();
			rivers.recent.refresh();
			
			$.gui.SelectItem.setDefaultValues();
			
			// Focus the URL field and highlight its contents.
			//     If this is moved above the selection changes,
			//     IE will show a flashing cursor over the dialog.
			inputs.search.focus();
			
			// Load the most recent results if this is the first time opening the panel.
			if ( ! rivers.recent.ul.children().length )
				rivers.recent.ajax();
			
		},

		cancel : function() {
			dialog.dialog('close');
		},

		update : function() {
			if( typeof opts.onSelect != 'function') opts.onSelect =  window[opts.onSelect];
			
			retval = opts.onSelect(lastSelected, opts);
			
			if(retval==true) dialog.dialog('close');
		},

		updateFields : function( e, li, originalEvent ) {
			
			if(opts.link) {
				inputs.url.val( li.children('.item-permalink').val() );
				inputs.title.val( li.hasClass('no-title') ? '' : li.children('.item-title').text() );
			}
			
			lastSelected = { url: li.children('.item-permalink').val(),
							 title : li.hasClass('no-title') ? '' : li.children('.item-title').text(),
							 id: li.children('.item-id').val(),
							 type: li.children('.item-type').val(),
							 info: li.children('.item-info').val(),
							 post_type: li.children('.item-post-type').val(),
							 taxonomy: li.children('.item-taxonomy').val()
						  };
			
			if ( originalEvent && originalEvent.type == "click" )
				inputs.search.focus();
			
			$.gui.SelectItem.toggleSubmit();
			
		},
		
		setDefaultValues : function() {
			// Set URL and description to defaults.
			// Leave the new tab setting as-is.
			inputs.url.val('http://');
			inputs.title.val('');
			
			lastSelected = '';
			this.toggleSubmit();
		},

		searchInternalLinks : function() {
			
			var t = inputs.search, waiting,
				search = t.val(),
				type = $('input[name="search-type"]:checked', w).val();

			if ( search.length > 2 ) {
				
				rivers.recent.hide();
				rivers.search.show();

				// Don't search if the keypress didn't change the title.
				if ( $.gui.SelectItem.lastSearch == search+type)
					return;

				$.gui.SelectItem.lastSearch = search+type;
				waiting = t.siblings('img.waiting').show();

				rivers.search.change( search, type );
				rivers.search.ajax( function(){ waiting.hide(); });
			} else {
				rivers.search.hide();
				rivers.recent.show();
			}
		},

		next : function() {
			rivers.search.next();
			rivers.recent.next();
		},
		prev : function() {
			rivers.search.prev();
			rivers.recent.prev();
		},
		
		toggleSubmit: function () {
			
			if( (opts.link && inputs.url.val() != '' && inputs.url.val() != 'http://' && inputs.title.val() != '') || lastSelected )  inputs.submit.removeAttr('disabled');
			
			else inputs.submit.attr("disabled", "true");
			
		},

		keydown : function( event ) {
			var fn, key = $.ui.keyCode;

			switch( event.which ) {
				case key.UP:
					fn = 'prev';
				case key.DOWN:
					fn = fn || 'next';
					clearInterval( $.gui.SelectItem.keyInterval );
					$.gui.SelectItem[ fn ]();
					$.gui.SelectItem.keyInterval = setInterval( $.gui.SelectItem[ fn ], $.gui.SelectItem.keySensitivity );
					break;
				default:
					return;
			}
			event.preventDefault();
		},
		keyup: function( event ) {
			var key = $.ui.keyCode;

			$.gui.SelectItem.toggleSubmit();
			
			switch( event.which ) {
				case key.ESCAPE:
					$.gui.SelectItem.cancel();
					break;
				case key.UP:
				case key.DOWN:
					clearInterval( $.gui.SelectItem.keyInterval );
					break;
				default:
					return;
			}
			event.preventDefault();
		},

		delayedCallback : function( func, delay ) {
			var timeoutTriggered, funcTriggered, funcArgs, funcContext;

			if ( ! delay )
				return func;

			setTimeout( function() {
				if ( funcTriggered )
					return func.apply( funcContext, funcArgs );
				// Otherwise, wait.
				timeoutTriggered = true;
			}, delay);

			return function() {
				if ( timeoutTriggered )
					return func.apply( this, arguments );
				// Otherwise, wait.
				funcArgs = arguments;
				funcContext = this;
				funcTriggered = true;
			};
		},
		
		toggleInternalLinking : function( event ) {
						
			var panel = $('#gk-search-panel'),
				// We're about to toggle visibility; it's currently the opposite
				visible = !panel.is(':visible'),
				win = $(window),
				d = dialog.dialog('widget');

			$(this).toggleClass('toggle-arrow-active', visible);

			if(visible) {
				inputs.url.attr('disabled', 'disabled');
				inputs.title.attr('disabled', 'disabled');
				inputs.openInNewTab.attr('disabled', 'disabled');
			}
			else {
				inputs.url.removeAttr('disabled');
				inputs.title.removeAttr('disabled');
				inputs.openInNewTab.removeAttr('disabled');
				
				//lastSelected = null;
				//rivers.deselect();
			}
			
			d.height('auto');
			
			panel.slideToggle( 300, function() {
				//setUserSetting('wplink', visible ? '1' : '0');
				inputs[ visible ? 'search' : 'url' ].focus();

				// Move the box if the box is now expanded, was opened in a collapsed state,
				// and if it needs to be moved. (Judged by bottom not being positive or
				// bottom being smaller than top.)
				var h =  d.outerHeight(),
					wh = win.height(),
					scroll = win.scrollTop(),
					top = d.offset().top,
					bottom = top + h,
					diff = bottom - wh;
				
				if ( diff > scroll ) {
					d.animate({'top': diff < top ?  wh - h + scroll : scroll }, 200);
				}
			});
			event.preventDefault();
		},
		
		selectIt : function (item, opts) {
			var i = $('#'+opts.field_id+'-items');
			var o = $('#'+opts.field_id+'-toclone');
			
			if( !opts.multiple ) {
				i.html( $.gui.SelectItem.prepare_element(o.clone(), item).show());
			}
			
			else if( $.inArray(item.type+'|'+item.id, $.gui.SelectItem.selected(opts.id) ) === -1 ) {
					$.gui.SelectItem.prepare_element(o.clone(), item).appendTo(i).show();
			}
			
			lastSelected = null;
			
			return true;
		},
		
		prepare_element : function (e, item) {
			
			var htmlInfos = '', infos = {}, type, info, title, nw;
			
			if( item.type == 'post' ||  item.type == 'taxonomy' ) {
				info = item.info +'('+item.type+')';
				title = item.title;
				type = item.type;
			} 
			
			else {
				nw = inputs.openInNewTab.is(':checked') ? 1 : 0;
				item = {
					type: 'url',
					id: inputs.url.val(),
					url: inputs.url.val(),
					title: inputs.title.val(),
					new_window : nw
				};
				
				type = 'url';
				title = '<a href="'+item.id+'" target="_blank">' + item.title + '</a>';
				info = nw ? guiSelectItemL10n.newTab : guiSelectItemL10n.sameTab;
			}			
			
			$('.the_item', e).val(JSON.stringify(item)).removeAttr('disabled');
			$('.the_type', e).val(type).removeAttr('disabled');
			
			$('.title', e).html(title);
			$('.infos', e).html(info);
			
			e.removeAttr('id');	
			
			return e;
		},
		
		selected : function(id) {
			var items = [];
			
			$('.items div', $('#gecka_items_selector-'+id)).each( function() { items.push( $('.the_id', this).val() ); });
			
			return items;
		},
		
		remove : function (id, e) {	}
	};

	River = function( element, search ) {
		
		var self = this;
		this.element = element;
		this.ul = element.children('ul');
		this.waiting = element.find('#gk-link .river-waiting');

		this.change( search, 'post' );
		this.refresh();

		element.scroll( function(){ self.maybeLoad(); });
		element.delegate('li', 'click', function(e){ self.select( $(this), e ); });
	};

	$.extend( River.prototype, {
		refresh: function() {
			this.deselect();
			this.visible = this.element.is(':visible');
		},
		show: function() {
			if ( ! this.visible ) {
				this.deselect();
				this.element.show();
				this.visible = true;
			}
		},
		hide: function() {
			this.element.hide();
			this.visible = false;
		},
		// Selects a list item and triggers the river-select event.
		select: function( li, event ) {
			var liHeight, elHeight, liTop, elTop;

			if ( li.hasClass('unselectable') || li == this.selected )
				return;

			this.deselect();
			this.selected = li.addClass('selected');
			// Make sure the element is visible
			liHeight = li.outerHeight();
			elHeight = this.element.height();
			liTop = li.position().top;
			elTop = this.element.scrollTop();

			if ( liTop < 0 ) // Make first visible element
				this.element.scrollTop( elTop + liTop );
			else if ( liTop + liHeight > elHeight ) // Make last visible element
				this.element.scrollTop( elTop + liTop - elHeight + liHeight );

			// Trigger the river-select event
			this.element.trigger('river-select', [ li, event, this ]);
		},
		deselect: function() {
			if ( this.selected )
				this.selected.removeClass('selected');
			this.selected = false;
		},
		prev: function() {
			if ( ! this.visible )
				return;

			var to;
			if ( this.selected ) {
				to = this.selected.prev('li');
				if ( to.length )
					this.select( to );
			}
		},
		next: function() {
			if ( ! this.visible )
				return;

			var to = this.selected ? this.selected.next('li') : $('li:not(.unselectable):first', this.element);
			if ( to.length )
				this.select( to );
		},
		ajax: function( callback ) {
			var self = this,
				delay = this.query.page == 1 ? 0 : $.gui.SelectItem.minRiverAJAXDuration,
				response = $.gui.SelectItem.delayedCallback( function( results, params ) {
					self.process( results, params );
					if ( callback )
						callback( results, params );
				}, delay );

			this.query.ajax( response );
		},
		change: function( search, type ) {
			
			if ( this.query && this._search == search && this._type == type )
				return;
			
			this._search = search;
			this._type = type;
			this.query = new Query( search, type );
			this.element.scrollTop(0);
		},
		process: function( results, params ) {
			var list = '', alt = true, classes = '',
				firstPage = params.page == 1;

			if ( !results ) {
				if ( firstPage ) {
					list += '<li class="unselectable"><span class="item-title"><em>'
					+ guiSelectItemL10n.noMatchesFound
					+ '</em></span></li>';
				}
			} else {
				$.each( results, function() {
					classes = alt ? 'alternate' : '';
					classes += this['title'] ? '' : ' no-title';
					list += classes ? '<li class="' + classes + '">' : '<li>';
					list += '<input type="hidden" class="item-id" value="' + this['id'] + '" />';
					list += '<input type="hidden" class="item-permalink" value="' + this['permalink'] + '" />';
					list += '<input type="hidden" class="item-type" value="' + this['type'] + '" />';
					
					if( this['type'] == 'post')
						list += '<input type="hidden" class="item-post-type" value="' + this['post_type'] + '" />';
					
					if( this['type'] == 'taxonomy')
						list += '<input type="hidden" class="item-taxonomy" value="' + this['taxonomy'] + '" />';
					
					list += '<input type="hidden" class="item-info" value="' + this['info'] + '" />';
					list += '<span class="item-title">';
					list += this['title'] ? this['title'] : guiSelectItemL10n.noTitle;
					list += '</span><span class="item-info">' + this['info'];
					
					if( this['type']=='post' && this['info'] && this['info'].toUpperCase() != this['post_type'].toUpperCase()) list += ' <small>('+this['post_type']+')</small>';
					
					list += '</span></li>';
					alt = ! alt;
				});
			}

			this.ul[ firstPage ? 'html' : 'append' ]( list );
		},
		maybeLoad: function() {
			var self = this,
				el = this.element,
				bottom = el.scrollTop() + el.height();

			if ( ! this.query.ready() || bottom < this.ul.height() - $.gui.SelectItem.riverBottomThreshold )
				return;

			setTimeout(function() {
				var newTop = el.scrollTop(),
					newBottom = newTop + el.height();

				if ( ! self.query.ready() || newBottom < self.ul.height() - $.gui.SelectItem.riverBottomThreshold )
					return;

				self.waiting.show();
				el.scrollTop( newTop + self.waiting.outerHeight() );

				self.ajax( function() { self.waiting.hide(); });
			}, $.gui.SelectItem.timeToTriggerRiver );
		}
	});

	Query = function( search, type ) {
		this.page = 1;
		this.allLoaded = false;
		this.querying = false;
		this.search = search;
		this.type = type;
	};

	$.extend( Query.prototype, {
		
		ready: function() {
			return !( this.querying || this.allLoaded );
		},
		
		ajax: function( callback ) {
			
			var self = this,
				query = {
					action : 'gui-select-item-'+opts.id,
					page : this.page,
					'_ajax_select_items_nonce' : $('#_ajax_select_items_nonce').val(),
					id: opts.id
				};

			if ( this.search )
				query.search = this.search;
			
			if ( this.type )
				query.type = this.type;
			
			this.querying = true;

			$.post( ajaxurl, query, function(r) {
				self.page++;
				self.querying = false;
				self.allLoaded = !r;
				callback( r, query );
			}, "json" );
		}
	});

	
})(jQuery);
