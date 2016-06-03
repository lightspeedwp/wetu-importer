<div class="uix-field-wrapper">
	<table class="form-table">
		<tbody>
			<tr class="form-field banner-wrap">
				<th scope="row" colspan="2"><label><h3>API Settings</h3></label></th>
			</tr> 
			<tr class="form-field -wrap">
				<th scope="row">
					<label for="token"> Token</label>
				</th>
				<td>
					<input type="text"  {{#if token}} value="{{token}}" {{/if}} name="token" />
				</td>
			</tr>				
			<tr class="form-field -wrap">
				<th scope="row">
					<label for="username"> Username</label>
				</th>
				<td>
					<input type="text"  {{#if username}} value="{{username}}" {{/if}} name="username" />
				</td>
			</tr>	
			<tr class="form-field -wrap">
				<th scope="row">
					<label for="password"> Password</label>
				</th>
				<td>
					<input type="text"  {{#if password}} value="{{password}}" {{/if}} name="password" />
				</td>
			</tr>			

			<tr class="form-field banner-wrap">
				<th scope="row" colspan="2"><label><h3>Image Settings</h3></label></th>
			</tr> 
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
		
		</tbody>
	</table>
</div>	