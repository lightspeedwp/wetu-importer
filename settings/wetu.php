<div class="uix-field-wrapper">
	<table class="form-table">
		<tbody>
			<tr class="form-field -wrap">
				<th scope="row">
					<label for="image_scaling">Enable Image Scaling</label>
				</th>
				<td>
					<input type="checkbox"  {{#if image_scaling}} checked="checked" {{/if}} name="image_scaling" />
				</td>
			</tr>
			<tr class="form-field -wrap">
				<th scope="row">
					<label for="password"> Width (px)</label>
				</th>
				<td>
					<input placeholder="800" type="text"  {{#if width}} value="{{width}}" {{/if}} name="width" />
				</td>
			</tr>	
			<tr class="form-field -wrap">
				<th scope="row">
					<label for="password"> Height (px)</label>
				</th>
				<td>
					<input placeholder="600" type="text"  {{#if height}} value="{{height}}" {{/if}} name="height" />
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