<div class="uix-field-wrapper">
    <ul class="ui-tab-nav">
        <li><a href="#ui-general" class="active"><?php esc_html_e( 'General','tour-operator' ); ?></a></li>
		<li><a href="#ui-image-scaling"><?php esc_html_e( 'Image Settings','tour-operator' ); ?></a></li>
	</ul>

	<div id="ui-general" class="ui-tab active">
		<table class="form-table">
			<tbody>
				<tr class="form-field -wrap">
					<th scope="row">
						<label for="disable_tour_descriptions">Disable Tour Descriptions</label>
					</th>
					<td>
						<input type="checkbox" {{#if disable_tour_descriptions}} checked="checked" {{/if}} name="disable_tour_descriptions" />
						<small>If you are going to manage your tour descriptions on this site and not on WETU then enable this setting.</small>
					</td>
				</tr>

				<tr class="form-field -wrap">
					<th scope="row">
						<label for="disable_accommodation_descriptions">Disable Accommodation Descriptions</label>
					</th>
					<td>
						<input type="checkbox" {{#if disable_accommodation_descriptions}} checked="checked" {{/if}} name="disable_accommodation_descriptions" />
						<small>If you are going to edit the accommodation descriptions imported then enable this setting.</small>
					</td>
				</tr>

				<tr class="form-field -wrap">
					<th scope="row">
						<label for="disable_accommodation_excerpts">Disable Accommodation Excerpts</label>
					</th>
					<td>
						<input type="checkbox" {{#if disable_accommodation_excerpts}} checked="checked" {{/if}} name="disable_accommodation_excerpts" />
						<small>If you are going to edit the accommodation excerpts then enable this setting.</small>
					</td>
				</tr>

				<tr class="form-field -wrap">
					<th scope="row">
						<label for="disable_destination_descriptions">Disable Destinations Descriptions</label>
					</th>
					<td>
						<input type="checkbox" {{#if disable_destination_descriptions}} checked="checked" {{/if}} name="disable_destination_descriptions" />
						<small>If you are going to edit the destination descriptions on this site then enable this setting.</small>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div id="ui-image-scaling" class="ui-tab">
		<table class="form-table">
			<tbody>
				<tr class="form-field -wrap">
					<th scope="row">
						<label for="image_replacing">Replace Images</label>
					</th>
					<td>
						<input type="checkbox" {{#if image_replacing}} checked="checked" {{/if}} name="image_replacing" />
						<p>Do you want your images to be replaced on each import.</p>
					</td>
				</tr>
				<tr class="form-field -wrap">
					<th scope="row">
						<label for="image_limit"> Limit the amount of images imported to the gallery</label>
					</th>
					<td>
						<input placeholder="" type="text" {{#if image_limit}} value="{{image_limit}}" {{/if}} name="image_limit" />
					</td>
				</tr>

				<tr class="form-field -wrap">
					<th scope="row">
						<label for="image_scaling">Enable Image Scaling</label>
					</th>
					<td>
						<input type="checkbox" {{#if image_scaling}} checked="checked" {{/if}} name="image_scaling" />
					</td>
				</tr>
				<tr class="form-field -wrap">
					<th scope="row">
						<label for="password"> Width (px)</label>
					</th>
					<td>
						<input placeholder="1024" type="text"  {{#if width}} value="{{width}}" {{/if}} name="width" />
					</td>
				</tr>
				<tr class="form-field -wrap">
					<th scope="row">
						<label for="password"> Height (px)</label>
					</th>
					<td>
						<input placeholder="768" type="text"  {{#if height}} value="{{height}}" {{/if}} name="height" />
					</td>
				</tr>

				<tr class="form-field -wrap">
					<th scope="row">
						<label for="password"> Scaling</label>
					</th>
					<td>
						<input type="radio" {{#is scaling value="raw"}} checked="checked"{{/is}} name="scaling" value="raw" /> Get the Full size image, no cropping takes place <br />
						<input type="radio" {{#is scaling value="c"}} checked="checked"{{/is}} name="scaling"  value="c" /> Crop image to fit fully into the frame, Crop is taken from middle, preserving as much of the image as possible.<br />
						<input type="radio" {{#is scaling value="h"}} checked="checked"{{/is}} name="scaling"  value="h" /> Crop image to fit fully into the frame, but resize to height first, then crop on width if needed<br />
						<input type="radio" {{#is scaling value="w"}} checked="checked"{{/is}} name="scaling"  value="w" /> Crop image to fit fully into the frame, but resize to width first, then crop on height if needed<br />
						<input type="radio" {{#is scaling value="nf"}} checked="checked"{{/is}} name="scaling"  value="nf" /> Resize the image to fit within the frame. but pad the image with white to ensure the resolution matches the frame<br />
						<input type="radio" {{#is scaling value="n"}} checked="checked"{{/is}} name="scaling"  value="n" /> Resize the image to fit within the frame. but do not upscale the image.<br />
						<input type="radio" {{#is scaling value="W"}} checked="checked"{{/is}} name="scaling"  value="W" /> Resize the image to fit within the frame. Image will not exceed specified dimensions
					</td>
				</tr>

			</tbody>
		</table>
	</div>
</div>
