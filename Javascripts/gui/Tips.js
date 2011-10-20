/***********************************************************/
/*                    tinyTips Plugin                      */
/*                      Version: 1.1                       */
/*                      Mike Merritt                       */
/*              Modified by Laurent Dinclaux               */
/*                 Updated: Sept 23, 2010                  */
/***********************************************************/

(function($){  
	
	if($.fn.gui === undefined) $.fn.gui = function() {};
	
	$.fn.guiTips = function (tipColor, supCont, options) {
		
		if (tipColor === 'null') {
			tipColor = 'light';
		} 
		
		var tipName = tipColor + 'Tip';
		
		/* User settings
		**********************************/
		
		// Enter the markup for your tooltips here. The wrapping div must have a class of tinyTip and 
		// it must have a div with the class "content" somewhere inside of it.
		var tipFrame = '<div class="' + tipName + '"><div class="content"></div><div class="bottom">&nbsp;</div></div>';
		
		// Speed of the animations in milliseconds - 1000 = 1 second.
		var animSpeed = 300;
		
		/***************************************************************************************************/
		/* End of user settings - Do not edit below this line unless you are trying to edit functionality. */
		/***************************************************************************************************/
		
		// Global tinyTip variables;
		var tinyTip;
		var tText;
		
		// When we hover over the element that we want the tooltip applied to
		$(this).hover(function() {
			
			// Inject the markup for the tooltip into the page and
			// set the tooltip global to the current markup and then hide it.
			$('body').append(tipFrame);
			var divTip  = 'div.'+tipName;
			
			tinyTip = $(divTip);
			tinyTip.hide();
			
			// Grab the content for the tooltip from the title attribute (or the supplied content) and
			// inject it into the markup for the current tooltip. NOTE: title attribute is used unless
			// other content is supplied instead.
			if (supCont === 'title') {
				var tipCont = $(this).attr('title');
			} else if (supCont !== 'title') {
				
				if( id = $(this).attr('id') ) {
					var divCont = '#'+id+'_tip';
					if( $(divCont).length ) var tipCont = $(divCont).html();
					else var tipCont = supCont;
				}
				else var tipCont = supCont;
				
				
			}
			$(divTip + ' .content').html(tipCont);
			tText = $(this).attr('title');
			$(this).attr('title', '');
			
			// Offsets so that the tooltip is centered over the element it is being applied to but
			// raise it up above the element so it isn't covering it.
			var yOffset = tinyTip.height() + 2;
			var xOffset = (tinyTip.width() / 2) - ($(this).width() / 2);
			
			// Grab the coordinates for the element with the tooltip and make a new copy
			// so that we can keep the original un-touched.
			var pos = $(this).offset();
			var nPos = pos;
			
			// Add the offsets to the tooltip position
			nPos.top = pos.top - yOffset;
			nPos.left = pos.left - xOffset;
			
			// Make sure that the tooltip has absolute positioning and a high z-index, 
			// then place it at the correct spot and fade it in.
			tinyTip.css('position', 'absolute').css('z-index', '1000');
			tinyTip.css(nPos).fadeIn(animSpeed);
			
		}, function() {
			
			$(this).attr('title', tText);
		
			// Fade the tooltip out once the mouse moves away and then remove it from the DOM.
			tinyTip.fadeOut(animSpeed, function() {
				$(this).remove();
			});
			
		});
		
	};
	

})(jQuery);