<?php $description = isset($description) ? $description : false; ?>
<p>This tool will run any images that have already been uploaded to S3 through Amazon Rekognizer.</p>
@if(!$description)
<p>Depending on the number of items you have, this could take anywhere from a minute to several hours.  This process runs in the background until it's finished.  Once you've started the process, please check this page for progress.</p>
<p><strong>Note:</strong></p>
<ol>
    <li>You <strong>MUST HAVE</strong> Rekognizer enabled and working in <a href="admin.php?page=media-cloud">Features</a> before running this task.</li>
    <li>You <strong>MUST</strong> be using Amazon S3 for cloud storage.</li>
    <li>Your S3 bucket must be in a region that Rekognition can be used in.</li>
</ol>
@endif