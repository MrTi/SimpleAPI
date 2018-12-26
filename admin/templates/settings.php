<div class="__sapi wrap">

	<h1><?php print $this->title ?></h1>

	<div class="panel">
		<div class="panel-body">
			<form action="<?php print admin_url('admin-post.php'); ?>" method="post">
				<input type="hidden" name="action" value="<?php print $this->_name  . '_post' ?>"/>
				<?php wp_nonce_field('sapi_' . $this->_name . '_post', 'sapi_' . $this->_name . '_nonce'); ?>


				<div class="row" style="padding: 10px 0; ">
					<div class="col-md-12">
						<label for="sapi-settings-ips"><?php _e('Global white list of IPs: ', SAPI_TEXT_DOMAIN); ?></label>
						<textarea name="sapi_settings_fields[ips]" id="sapi-settings-ips" cols="30" rows="10" class="form-control"><?php print implode(",\n", $this->ips) ?></textarea>
					</div>
				</div>
				<div class="row">
					<input type="submit" class="button button-primary col-md-1" value="<?php _e('Save',SAPI_TEXT_DOMAIN); ?>"/>
				</div>

			</form>
		</div>
	</div>

</div>