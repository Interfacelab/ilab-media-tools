<?php
$hideBug = get_option('ilab_media_cloud_hide_upgrade_bug');
if (!empty($hideBug)) {
	if ($hideBug + (60 * 60 * 24 * 30) < time()) {
		delete_option('ilab_media_cloud_hide_upgrade_bug');
		$hideBug = false;
    }
}
?>

<div class="upgrade-promo @if(!empty($hideBug)) hide-on-mobile @endif">
    <div class="upgrade-interior">
        <h2>Upgrade to Media Cloud Premium</h2>
        <ul>
            <li>Easily manage your theme's image sizes</li>
            <li>Built-in dynamic image generation</li>
            <li>Image moderation with Google Vision</li>
            <li>Serve your CSS/JS assets from the cloud (Pro)</li>
            <li>Upload directly to {{\ILAB\MediaCloud\Storage\StorageManager::currentDriverName()}} (Pro)</li>
            <li>Built-in {{\ILAB\MediaCloud\Storage\StorageManager::currentDriverName()}} storage browser (Pro)</li>
            <li>Import media from {{\ILAB\MediaCloud\Storage\StorageManager::currentDriverName()}} (Pro)</li>
            <li>WPML, WooCommerce, Easy Digital Downloads, WP Job Manager integration (Pro)</li>
        </ul>
        <div class="button-container">
           <a href="{{admin_url('admin.php?page=media-cloud-pricing')}}">Upgrade Now!</a>
        </div>
        <a href="#" class="upgrade-close">Close</a>
    </div>
</div>