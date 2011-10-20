<?php

$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.dev' : '';

wp_register_script( 'jquery-ui-datepicker', GK_URL . '/Javascripts/jquery/ui/ui.datepicker.min.js', array('jquery', 'jquery-ui-core'));

wp_register_script( 'jquery-ui-datepicker-i18n', GK_URL . '/Javascripts/jquery/ui/i18n/jquery-ui-i18n.js', array('jquery-ui-datepicker'));
wp_register_style( 'jquery-ui-datepicker', GK_URL . '/Javascripts/jquery/ui/css/smoothness/jquery-ui-1.7.3.custom.css');

wp_register_script( 'jquery-ui-accordion', GK_URL . '/Javascripts/jquery/ui/ui.accordion.min.js', array('jquery', 'jquery-ui-core'));

wp_register_script( 'jquery-easing', GK_URL . "/Javascripts/jquery/jquery.easing$suffix.js", array('jquery') );

wp_register_script( 'jquery-cycle', GK_URL . "/Javascripts/jquery/jquery.cycle$suffix.js", array('jquery') );

wp_register_script( 'jquery-cycle-lite', GK_URL . "/Javascripts/jquery/jquery.cycle.lite$suffix.js", array('jquery') );

//wp_register_script( 'gui-httpr-upload', GK_URL . '/Javascripts/gui/httpr/upload.js', array('jquery', 'jquery-ui-core'));

wp_register_script( 'gui-form', GK_URL . "/Javascripts/gui/Form$suffix.js", array('jquery-form'));

wp_register_script( 'gui-tips', GK_URL . "/Javascripts/gui/Tips$suffix.js", array('jquery'));

wp_register_script( 'gui-select-item', GK_URL . "/Javascripts/gui/SelectItem$suffix.js", array('jquery-ui-dialog', 'json2'), GK_VERSION);

wp_localize_script( 'gui-select-item', 'guiSelectItemL10n', array(
					'update' => __('Update'),
					'save' => __('Add Link'),
					'noTitle' => __('(no title)'),
					'noMatchesFound' => __('No matches found.'),
					'l10n_print_after' => 'try{convertEntities(gkSelectItemL10n);}catch(e){};',
					'newTab' => __('Opens in a new window', 'gecka'),
					'sameTab' => __('Opens in same window', 'gecka')) );

wp_register_style('gui-select-item', GK_URL . '/Css/SelectItem.css');

/*wp_register_script( 'fancybox', GK_URL . '/Javascripts/fancybox/jquery.fancybox-1.3.1.pack.js', array('jquery'));
wp_register_style( 'fancybox', GK_URL . '/Javascripts/fancybox/jquery.fancybox-1.3.1.css');

wp_register_script( 'fancybox-easing', GK_URL . '/Javascripts/fancybox/jquery.easing-1.3.pack.js', array('jquery'));
wp_register_script( 'fancybox-mousewheel', GK_URL . '/Javascripts/fancybox/jquery.mousewheel-3.0.2.pack.js', array('jquery'));
*/
