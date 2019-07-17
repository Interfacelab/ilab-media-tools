@extends('../templates/sub-page', ['title' => 'Help and Support'])

@section('top')
<style>
    .help-docs > h2 {
        margin-top: 40px;
    }
    .help-docs > h2:first-of-type {
        margin-top: 20px;
    }

    td > ul {
        margin-top: 0px;
        margin-bottom: 0px;
    }
</style>
@endsection

@section('main')
<div class="help-docs">
	<h2>Report a Bug</h2>
    <p>Please click the button below to report a bug directly to our issue reporting system.  While you can open a topic on the WordPress.org site, we can respond quicker through our system as we don't check WordPress.org very often.</p>
	<a href="#" id="media-cloud-report-bug" class="button">Report Bug</a>

    <h2>Donate</h2>
    <p>This plugin is free and will be free forever.  However, it's taken a lot of work to get it this far.  If this plugin is useful to you, please consider donating to my son's Juvenile Diabetes Research Foundation fundraiser.  He was diagnosed with type 1 diabetes last year at one year of age.  The JDRF is a research charity trying to find a cure for T1D.  They can use all the help they can get.</p>
    <a href="http://www2.jdrf.org/site/TR?fr_id=6912&pg=personal&px=11429802" target="_blank" class="button">Donate to Diabetes Research</a>

    <h2>Using S3 Compatible Services</h2>
    <p>ILAB Media Cloud is compatible with any S3 compatible cloud storage server like Minio, Ceph RGW or Digital Ocean Spaces.</p>
    <p>If you are using Minio, it's important that you specify the region (for example <code>us-east-1</code>) in your <code>/etc/minio/config.json</code> file on your Minio server. <strong>IMPORTANT:</strong> If you are using Minio, your bucket must have a public read policy set for the entire bucket.  See <a href='https://github.com/minio/minio/issues/3774' target=_blank>here</a> for more details.</p>


    <h2>Environment Variables</h2>
	<p>You can configure this plugin using the various pages, but it's highly recommended that you configure it through
		environment variables.  This is particularly true if you are using Bedrock for Wordpress development.</p>
	<p>Below is a list of all the environment variables you can use:</p>
	<table class="wp-list-table widefat striped">
		<thead>
			<tr>
				<th>Environment Variable</th>
				<th>Type</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
		<tr>
			<td>ILAB_MEDIA_CROP_ENABLED</td>
			<td>Boolean</td>
			<td>Enable or disable the crop tool.</td>
		</tr>
		<tr>
			<td>ILAB_MEDIA_IMGIX_ENABLED</td>
			<td>Boolean</td>
			<td>Enable or disable imgix support.</td>
		</tr>
		<tr>
			<td>ILAB_MEDIA_S3_ENABLED</td>
			<td>Boolean</td>
			<td>Enable or disable s3 uploads.</td>
		</tr>
        <tr>
            <td>ILAB_MEDIA_CROP_QUALITY</td>
            <td>Number (1-100)</td>
            <td>Compression quality for images cropped with the crop tool.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_DOMAINS</td>
            <td>String</td>
            <td>Comma separatred list of your source domains. For more information, please read the imgix documentation.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_USE_HTTPS</td>
            <td>Boolean</td>
            <td>Use HTTPS for image URLs.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_SIGNING_KEY</td>
            <td>String</td>
            <td>Optional signing key to create secure URLs. Recommended. For information on setting it up, refer to the imgix documentation.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_DEFAULT_QUALITY</td>
            <td>Number (0-100)</td>
            <td>Default quality for images when served from Imgix in a lossy format like jpeg or webp.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_AUTO_FORMAT</td>
            <td>Boolean</td>
            <td>Allows imgix to choose the most appropriate file format to deliver your image based on the requesting web browser.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_AUTO_COMPRESS</td>
            <td>Boolean</td>
            <td>Allows imgix to automatically compress your images.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_ENABLE_GIFS</td>
            <td>Boolean</td>
            <td>Enables support for animated GIFs. If this is not enabled, any uploaded GIFs will be converted.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_NO_GIF_SIZES</td>
            <td>String</td>
            <td>Comma separatred list of the image sizes that aren't allowed to have animated GIFs. These sizes will display jpegs instead.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_ENABLE_ALT_FORMATS</td>
            <td>Boolean</td>
            <td>Allow uploads of Photoshop PSDs, TIFF images and Adobe Illustrator documents.  Note that if you enable this, you'll only be able to view them as images on your site while Imgix is enabled.  Basically, once you head down this path, you cannot go back.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_IMGIX_RENDER_PDF_FILES</td>
            <td>Boolean</td>
            <td>Render PDF files as images.  Like the <em>ILAB_MEDIA_S3_IMGIX_ENABLE_ALT_FORMATS</em>, once you enable this option, you'll only be able to see the PDFs as images while Imgix is enabled.</td>
        </tr>
        <tr>
            <td>ILAB_AWS_S3_BUCKET</td>
            <td>String</td>
            <td>The name of the bucket to use on S3.</td>
        </tr>
        <tr>
            <td>ILAB_AWS_S3_ACCESS_KEY</td>
            <td>String</td>
            <td>Access key for Amazon S3.</td>
        </tr>
        <tr>
            <td>ILAB_AWS_S3_ACCESS_SECRET</td>
            <td>String</td>
            <td>Secret key for Amazon S3.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_S3_DELETE_UPLOADS</td>
            <td>Boolean</td>
            <td>Delete uploads from the WordPress server after a successful upload to Amazon S3.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_S3_DELETE_FROM_S3</td>
            <td>Boolean</td>
            <td>Delete uploads from Amazon S3 when deleted from WordPress's media library.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_S3_PREFIX</td>
            <td>String</td>
            <td>This will prepend a prefix to any file uploaded to S3. For dynamically created prefixes, you can use the following variables: <code>@{date:format}</code>, <code>@{site-name}</code>, <code>@{site-host}</code>, <code>@{site-id}</code>, <code>@{versioning}</code>, <code>@{user-name}</code>, <code>@{unique-id}</code>, <code>@{unique-path}</code>. For the date token, format is any format string that you can use with php's <code>date()</code> function. Note that specifying a prefix here will remove WordPress's default date prefix. WordPress's default prefix would look like: <code>@{date:Y/m}</code>.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_S3_PRIVACY</td>
            <td>String</td>
            <td>This will set the privacy for each upload.  You should leave it as <code>public-read</code> unless you are using Imgix.  Valid values are <code>public-read</code> and <code>authenticated-read</code>.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_S3_UPLOAD_DOCUMENTS</td>
            <td>Boolean</td>
            <td>Enables uploading non-image files such as Word documents, PDF files, zip files, etc. to Amazon S3.</td>
        </tr>
        <tr>
            <td>ILAB_MEDIA_S3_IGNORED_MIME_TYPES</td>
            <td>String</td>
            <td>Comma separated list of mime types that should NOT be uploaded to S3.</td>
        </tr>
        <tr>
            <td>ILAB_AWS_S3_CDN_BASE</td>
            <td>String</td>
            <td>This is the base URL for your CDN for serving images, including the scheme (meaning the http/https part).</td>
        </tr>
        <tr>
            <td>ILAB_AWS_S3_DOC_CDN_BASE</td>
            <td>String</td>
            <td>This is the base URL for your CDN for serving non-image files, including the scheme (meaning the http/https part). This is separated for your convenience. If you don't specify a document CDN, it'll use the media/image CDN.</td>
        </tr>
        <tr>
            <td>ILAB_AWS_S3_CACHE_CONTROL</td>
            <td>String</td>
            <td>Sets the Cache-Control metadata for an object in S3, e.g. public,max-age=2592000.</td>
        </tr>
        <tr>
            <td>ILAB_AWS_S3_EXPIRES</td>
            <td>String</td>
            <td>Sets the Expire metadata for an object in S3. This is the number of minutes from the date of upload.</td>
        </tr>
		</tbody>
	</table>

	<h2>Filters</h2>
	<p>This plugin exposes a few filter hooks that you can use to change the behavior of the plugin for whatever purposes.</p>
    <p>For example, you may want to append parameters to an Imgix URL or override the default ones.  The following gist is an example of that:</p>
    <script src="https://gist.github.com/jawngee/4e941a0f28149eaaa06499447e326698.js"></script>
    <p>Below is a complete list of available filters:</p>
    <table class="wp-list-table widefat striped">
        <thead>
        <tr>
            <th>Filter Name</th>
            <th>Description</th>
            <th>Arguments</th>
            <th>Returns</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><strong>ilab_imgix_filter_parameters</strong></td>
            <td>Modifiy or append parameters to the Imgix URL.</td>
            <td>
                <ul>
                    <li><strong>$params</strong> - The current list of params.</li>
                    <li><strong>$size</strong> - The current name of the image size that the URL is being generated for.</li>
                    <li><strong>$id</strong> - The ID of the attachment.</li>
                    <li><strong>$meta</strong> - The complete metadata for the attachment.</li>
                </ul>
            </td>
            <td>Returns the <strong>$params</strong> array that may or may not have been modified.</td>
        </tr>
        <tr>
            <td><strong>ilab_imgix_enabled</strong></td>
            <td>Determines if imgix is enabled.</td>
            <td>
                <ul>
                    <li><strong>$isEnabled</strong> - Boolean that determines if imgix is enabled.</li>
                </ul>
            </td>
            <td>Returns true if enabled.</td>
        </tr>
        <tr>
            <td><strong>ilab_s3_can_calculate_srcset</strong></td>
            <td>Determines if the S3 tool can calculate the srcset of an image.  This would allow you to override how S3 generates the image's srcset when WordPress requests it.</td>
            <td>
                <ul>
                    <li><strong>$canCalculate</strong> - Boolean that determines if S3 can calculate it or not.</li>
                </ul>
            </td>
            <td>Returns a boolean that determines if S3 can calculate it or not.</td>
        </tr>
        <tr>
            <td><strong>ilab_s3_upload_options</strong></td>
            <td>Modify the options for the S3 upload.</td>
            <td>
                <ul>
                    <li><strong>$options</strong> - The current array of options for the upload.</li>
                    <li><strong>$id</strong> - The ID of the attachment.</li>
                    <li><strong>$data</strong> - The complete metadata for the attachment.</li>
                </ul>
            </td>
            <td>Returns the <strong>$options</strong> array that may or may not have been modified.</td>
        </tr>
        </tbody>
    </table>

	<h2>Actions</h2>
	<p>This plugin exposes a single action hooks that you can use.</p>
    <table class="wp-list-table widefat striped">
        <thead>
        <tr>
            <th>Action Name</th>
            <th>Description</th>
            <th>Arguments</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><strong>ilab_imgix_setup</strong></td>
            <td>Called after the Imgix tool has been setup.</td>
            <td>None.</td>
        </tr>
        </tbody>
    </table>

    <h2>Sample S3 Policy For WordPress</h2>
    <p>Below is the minimum AWS IAM policy you can have for the plugin to function:</p>
    <script  src="https://gist.github.com/jawngee/9cc2031f5ad154558b14e1fb395414cf.js"></script>
    <p>Replace `YOURBUCKET` with the name of the bucket you want to enable access to.</p>

    <h2>Direct Uploads</h2>
    <p>To allow direct uploads to S3 via the "Cloud Upload" feature, you must also configure CORS on your bucket.  This is the
        recommended CORS configuration:</p>
    <script  src="https://gist.github.com/jawngee/6fc89497e10d0915ab2dfac807aa01e1.js"></script>


    <script  type="text/javascript" src="https://interfacelab.atlassian.net/s/d41d8cd98f00b204e9800998ecf8427e-T/5f3v0s/b/22/a44af77267a987a660377e5c46e0fb64/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?locale=en-US&collectorId=ae2b6a04"></script>

    <script type="text/javascript">window.ATL_JQ_PAGE_PROPS =  {
            "triggerFunction": function(showCollectorDialog) {
                //Requires that jQuery is available!
                jQuery("#media-cloud-report-bug").click(function(e) {
                    e.preventDefault();
                    showCollectorDialog();
                });
            }};</script>
</div>
@endsection