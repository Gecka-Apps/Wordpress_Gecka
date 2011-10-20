(function($) {

	$.guiForm = function(form, result, options) {
		var $form, $result, defaults, opts;
		
		$form = $(form);
		
		defaults = {
			errorClass: 'error',
			updatedClass: 'updated',
			dataType: 'json',
			onsuccess: function(){},
			onerror: function(){}
		};
		
		opts = $.extend(defaults, options);
		
		$result = $(result);
		
		opts.beforeSubmit = before;
		opts.success = success;
		
		$form.ajaxForm(opts);
		
		function parse_errors (errors) {
				
			var errors_ar = [];
			
			var i=0;
			$.each(errors, function(field, error) { 
				
				if($(error.data).length) 
					field = error.data.form-field || field;
				
				$(form).find('#'+field).addClass('field_error');
				errors_ar[i] = '' + error.message + '';
				i++;
			});
			
			return errors_ar.join('<br />');
			
		};
		
		function success (response) {  
			$result.removeClass('updated error');
			
			/* error */
			if(response.status == 0) {
				var r = opts.onerror(response);
				if(r==false)  {
					$result.hide('fast'); return;
				}
				
				var errors = '';
				if( $(response.errors).length ) {
					errors = parse_errors(response.errors);
					$result.addClass('error').html('<p>'+errors+'</p>').show('fast');
				}
				else { $result.hide('fast'); }
				
			}
			else{
				var r = opts.onsuccess(response);
				if(r==false) {
					$result.hide('fast'); return;
				}
				if(response.msg !== '') $result.addClass('updated').html('<p>'+response.msg+'</p>').show('fast');
				else $result.hide('fast');

			}
			if(response.redirect_to) {
				
				if(response.redirect_timeout)  setTimeout("location.replace('"+response.redirect_to+"')", response.redirect_timeout);
				else location.replace(response.redirect_to);
			}
	    };
	    
	    function before() {
	    	$result.removeClass('error').addClass('updated').html('<p>En cours ...</p>').show('fast');
		};
		
	};
	
	$.fn.guiForm = function(result, options) {

		options = options || {};
		
		this.each(function() {
			new $.guiForm(this, result, options);
		});

		return this;

	};

})(jQuery);