<?php $description = isset($description) ? $description : false; ?>
<p>This tool will clear the local file cache for any previously dynamically generated images.</p>
@if (!$description)
@if ($background)
<p>Depending on the number of items you have, this could take anywhere from a minute to several hours.  This process runs in the background until it's finished.  Once you've started the process, please check this page for progress.</p>
@else
<p>Depending on the number of items you have, this could take anywhere from a minute to several hours.</p>
<p><strong>IMPORTANT:</strong> You are running the clear cache process in the web browser.  <strong>Do not navigate away from this page or the import may not finish.</strong></p>
@endif
@endif
