{% if (!$uploaded) %}
<div class="info-line">
Not uploaded.
</div>
<!--<div class="button-row">-->
<!--    <a href="#" class="button button-primary button-small">Regenerate</a>-->
<!--</div>-->
{% else %}
<div class="info-line">
	<h3>Dimensions</h3>
	{{$width}} x {{$height}}
</div>
<div class="info-line">
    <h3>Storage Service</h3>
    {{$driverName}}
</div>
<div class="info-line">
	<h3>Bucket</h3>
    {% if ($bucketLink) %}
	<a href="{{$bucketLink}}" target="_blank">{{$bucket}}</a>
    {% else %}
    {{$bucket}}
    {% endif %}
</div>
<div class="info-line">
	<h3>Path</h3>
    {% if ($pathLink) %}
    <a href="{{$pathLink}}" target="_blank">{{$key}}</a>
    {% else %}
    {{$key}}
    {% endif %}
</div>
{% if (!$isSize) %}
<div class="info-line">
	<label for="s3-access-acl">Access</label>
	<select id="s3-access-acl" name="s3-access-acl">
		<option value="public-read" {{ ($privacy == 'public-read') ? 'selected' : '' }}>
		Public
		</option>
		<option value="authenticated-read" {{ ($privacy == 'authenticated-read') ? 'selected' : '' }}>
		Authenticated Users
		</option>
	</select>
</div>
<div class="info-line">
	<label for="s3-cache-control">Cache-Control</label>
	<input type="text" class="widefat" name="s3-cache-control" id="s3-cache-control" value="{{ $cacheControl }}">
</div>
<div class="info-line">
	<label for="s3-expires">Expires</label>
	<input type="text" class="widefat" name="s3-expires" id="s3-expires" value="{{$expires}}">
</div>
{% endif %}
<div class="button-row">
	<a href="{{$url}}" class="button button-secondary button-small" target="_blank">Storage URL</a>
	{% if (!empty($publicUrl) && ($publicUrl != $url)) %}
	<a href="{{$publicUrl}}" class="button button-secondary button-small"  target="_blank">Public URL</a>
	{% endif %}
</div>
{% endif %}