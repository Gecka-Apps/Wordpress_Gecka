(function($){

	var lasthash='', hashListener=null, defaulthash='';
		
	styleSelect = {
			init: function () {
				$( '.controls .select_wrapper').each(function () {
					$(this).prepend( '<span>' + $(this).find('select option:selected').text() + '</span>' );
				});
			
				$( '.select_wrapper select').live( 'change', function () {
					$(this).prev('span').html( $(this).find( 'option:selected').text() );
				});
		
				$( '.select_wrapper select').bind($.browser.msie ? 'click' : 'change', function(event) {
					$(this).prev('span').html( $(this).find( 'option:selected').text() );
				});
			}
	};
	
	function menu_click (event) {
	
		
		changeHashValue( $(this).attr( 'href' ).replace(/^#/, '') );
	
		event.preventDefault();
		
	}
	
	function getHashQuery() {
	  return location.hash.replace(/^#\!/, '');
	}
	
	function watchHash () {
		var hash = getHashQuery();
		
		if (hash == lasthash)
		    return;
		
		lasthash = hash;
		
		locationHashChanged(hash);

	}

	function startHashListener() {
	  hashListener = setInterval(watchHash, 200);
	}

	function stopHashListener() {
	  if (hashListener != null) clearInterval(hashListener);
	  hashListener = null;
	}
	
	function changeHashValue(hash) {
	  stopHashListener();
	  if( hash == '' ) hash = defaulthash;
	  //lasthash     	= hash;
	  location.hash = '!'+hash;
	  startHashListener();
	}
	
	function locationHashChanged (q) {	
		
		var id  = "div#" + q;
		var id2 = "li."+ q;
		
		$('#gk-container .menu li.current').removeClass('current'); 
		$('#gk-container .content > div').hide(); 
		
		$('#gk-container .content > ' + id).fadeIn('fast');
		$('#gk-container .menu ' + id2).addClass( 'current' );
		$('#gk-container .menu ' + id2).parents('li').addClass( 'current' );
		
	}
	
	$(document).ready( function() {
	
		styleSelect.init();
		
		// Move .updated and .error alert boxes. Don't move boxes designed to be inline.
		$('div.updated, div.error').not('.inline').insertBefore( $('div.wrap') );
		$('div.updated, div.error').addClass('below-h2');
		
		$('#gk-container .menu ul li a').click ( menu_click );
		$('#gk-container .content > div').hide();
		
		defaulthash = $('#gk-container .content > div:first').attr('id');
		changeHashValue(getHashQuery() ? getHashQuery() : defaulthash);
	
	});

})(jQuery);