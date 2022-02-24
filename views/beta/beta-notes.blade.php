<h3>Media Cloud {{MEDIA_CLOUD_VERSION}} Beta Notes</h3>
<p>This beta includes the following fixes:</p>
<ol>
    <li>Fix for Imgix when an image size has been defined with a width or height of zero.</li>
    <li>Ability to override the upload path for cloud uploads via the `media-cloud/storage/custom-prefix` filter.</li>
    <li>When migrating from another plugin like Offload Media or WP-Stateless, you can now choose to manually migrate any media uploaded with the other plugin.  This is a very fast process.  If you do not do the manual migration, media will be migrated the first time a URL for an attachment is generated.  You should choose to do the manual migration though.
    <li>New option in Cloud Storage settings to turn off the automatic migration of media uploaded with another plugin.</li>
    <li>Fix for Regenerate Thumbnails not using the original image with 5.3's big image feature</li>
    <li>Fix for uploads not being deleted from WordPress in certain circumstances</li>
    <li>Fix for duplicates being uploaded to Cloud Storage when the upload path prefix `@{versioning}` is being used</li>
    <li>Fix for some uploads not being deleted when deleted from the WordPress media library</li>
    <li>Support for direct uploading on the front end when using plugins like WC Frontend Manager for WooCommerce.</li>
    <li>BuddyPress integration for uploading profile and cover images on profiles and groups.</li>
</ol>
<p>If you have previously used <strong>WP Offload Media</strong> or <strong>WP-Stateless</strong> and have had a support session with Charles or Jon that fixed an issue related to that, be sure to turn <strong>ON</strong> the <strong>Skip Importing From Other Plugins</strong> setting in the <strong>Upload Handling</strong> section in <a href="{{admin_url('admin.php?page=media-cloud-settings-storage#mcloud-storage-big-size-original-privacy')}}">Cloud Storage settings</a>.</p>
