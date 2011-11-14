<div id="gk-container" class="wrap">
	<form id="gkform" enctype="multipart/form-data" action="<?php echo admin_url('admin-ajax.php') ?>" method="post">
	
		<div class="header">
			<div id="icon-options-general" class="icon32"></div>
			<h2><?php esc_html_e( $this->page_name ) ?></h2>
		</div>
		
		<div class="top-menu">
			<ul>
				<li class="right">
					<input class="button submit-button" type="submit" value="<?php _e( 'Save All Changes', 'gk') ?>">
				</li>
			</ul>
		</div>
		
		<div clasS="main">
			
			<div class="menu">
			<?php $this->menu() ?>
			</div>
			
			<div class="content">
			<?php $this->content() ?>
			</div>
		
		</div>
		
		<input type="hidden" name="action" value="gk_settings_save_<?php echo $this->page_id ?>" />
		<?php  wp_nonce_field( '_nonce_gk_settings_' . $this->page_id); ?>
		
	</form>
</div>