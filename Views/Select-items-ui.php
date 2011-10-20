<form id="gk-link" tabindex="-1">

<?php wp_nonce_field( 'select-items', '_ajax_select_items_nonce', false ); ?>

<div id="gk-link-selector">
	
	<?php 
	
	// custom links
	
	if( $link ) : ?>
	<div id="gk-link-options">
	   <p class="howto"><?php _e( 'Enter the destination URL' ); ?></p>
		<div>
			<label><span><?php _e( 'URL' ); ?></span><input id="gk-url-field" type="text" tabindex="10" name="href" /></label>
		</div>
		<div>
			<label><span><?php _e( 'Title' ); ?></span><input id="gk-link-title-field" type="text" tabindex="20" name="linktitle" /></label>
		</div>
		<div class="link-target">
			<label><input type="checkbox" id="gk-link-target-checkbox" tabindex="30" /> <?php _e( 'Open link in a new window/tab' ); ?></label>
		</div>
	</div>
	<?php endif; ?>
	
	<?php 
	
	// internal linking
	
	if( $internal ) : ?>
	
		<?php if( $link ) : ?>
			<p class="howto toggle-arrow" id="gk-internal-toggle"><?php _e( 'Or link to existing content' ); ?></p>
		<?php else: ?>
			<p style="height: 1px; line-height: 1px; margin:0"></p>
		<?php endif; ?>
		
	<div id="gk-search-panel"<?php if ( $link ) echo ' style="display:none"'; ?>>
		
		<div class="link-search-wrapper">
			<label for="search-field">
				<span><?php _e( 'Search' ); ?></span>
				<input type="text" id="gk-search-field" class="link-search-field" tabindex="60" autocomplete="off" />
				<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
			</label>
			<br clear="all" />
			<?php 
			if( empty($post_types) ):?>
				<input type="hidden" name="search-type" value="taxonomy" />
			<?php elseif( empty($taxonomies) ): ?>
				<input type="hidden" name="search-type" value="post" />
			<?php else: ?>
			<input type="radio" value="post" name="search-type" checked="checked" /> Posts
			<input type="radio" value="taxonomy" name="search-type" /> Taxonomies
			<?php endif; ?>
			<input type="hidden" name="id" value="<?php echo esc_attr($_POST['id']) ?>" >
		</div>
		
		<div id="gk-search-results" class="query-results">
			<ul></ul>
			<div class="river-waiting">
				<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
			</div>
		</div>
		
		<div id="gk-most-recent-results" class="query-results">
			<div class="query-notice"><em><?php _e( 'No search term specified. Showing recent items.' ); ?></em></div>
			<ul></ul>
			<div class="river-waiting">
				<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
			</div>
		</div>
		
	</div>
	
	<?php endif; ?>
</div>

<div class="submitbox">
	<div id="gk-link-cancel">
		<a class="submitdelete deletion" href="#"><?php _e( 'Cancel' ); ?></a>
	</div>
	<div id="wp-link-update">
		<?php submit_button( __('Select'), 'primary', 'gk-link-submit', false, array('tabindex' => 100)); ?>
	</div>
</div>
</form>