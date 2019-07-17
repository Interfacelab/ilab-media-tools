<?php $description = isset($description) ? $description : false; ?>
<p>This tool will rebuild all of the thumbnails for all of your images.</p>
@if(!$description)
<p>Depending on the number of items you have, this could take anywhere from a minute to several hours.  This process runs in the background until it's finished.  Once you've started the process, please check this page for progress.</p>
<p>If you don't have any of the source files on your WordPress server, this will download what it can from your storage service.  Obviously this can be very slow going if you are processsing a lot of images.  If you only want to regenerate thubmnails for a select group of images, use the bulk action in the media library's list view.</p>
@endif