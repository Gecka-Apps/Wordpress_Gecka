(function($) {

	$.fn.idle = function(time)
	  {
	      var o = $(this);
	      o.queue(function()
	      {
	         setTimeout(function()
	         {
	            o.dequeue();
	         }, time);
	      });
	      return this; 
	  };

	
	$.guiForm = function(form, result, options) {
		var $form, $result, defaults, opts;
		
		$form = $(form);
		
		defaults = {
			errorClass: 'error',
			updatedClass: 'updated',
			dataType: 'json',
			onsuccess: function(){},
			onerror: function(){},
			redir_keep_history: false
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
					var field = error.data.form_field || field;
				
				var fields = field.split(',');
				
				$.each(fields, function(f, ff) {
					
					$(form).find('#'+ff).addClass('field_error');
				});
				
				
				errors_ar[i] = '' + error.message + '';
				i++;
			});
			
			return errors_ar.join('<br />');
			
		};
		
		function success (response) {  
			$result.removeClass('updated error');
			
			/* error */
			if(typeof response.status != 'undefined' && response.status == 0) {
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
				
				
				if( opts.redir_keep_history == false) {
				if(response.redirect_timeout)  setTimeout("location.replace('"+response.redirect_to+"')", response.redirect_timeout);
				else location.replace(response.redirect_to);
				}
				else {
					if(response.redirect_timeout)  setTimeout("document.location = '"+response.redirect_to+"'", response.redirect_timeout);
					else document.location = response.redirect_to;
				}
			}
			
			if(response.hide) {
				
				$result.idle(response.hide).hide('slow');
			}
	    };
	    
	    function before() {
	    	
	    	$form.find('.field_error').removeClass('field_error');
	    	
	    	$result.removeClass('error').addClass('updated').html('<p>En cours ...</p>').show('fast');
		};
		
		function clear() {
			
			$form.find(':input').each(function() {
		        switch(this.type) {
		            case 'password':
		            case 'select-multiple':
		            case 'select-one':
		            case 'text':
		            case 'textarea':
		                $(this).val('');
		                break;
		            case 'checkbox':
		            case 'radio':
		                this.checked = false;
		        }
		    });

			return $this;
			
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