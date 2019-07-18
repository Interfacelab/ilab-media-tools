# Vision
The Vision feature of Media Cloud enables automatic image tagging and classification using machine learning image classification services like Amazon Rekognition and Google Cloud Vision.   In addition to image tagging, these services can be used to detect explicit images for moderation purposes, detect faces and detect celebrities (Amazon Rekognition only).

&nbsp;

## Setup
Getting setup is easy and straight forward.

### Configuring Amazon Rekognition
You must be using Amazon S3 as your cloud storage provider to use Rekognition for Vision processing.  Rekognition will use all of the same connection info as cloud storage, so to get Rekognition working requires only a few added steps.

If you haven't already setup Amazon S3 cloud storage, you can get started by reading [this documentation](cloud-storage/setup/amazon-s3.md).

One important thing to note is that Rekognition is only available in a subset of regions.  Consult [this chart](https://aws.amazon.com/about-aws/global-infrastructure/regional-product-services/#Region_Table) to determine if your bucket is in one of those regions.  If not, you'll need to create a new bucket in a region that Rekognition is in.

If you followed the Amazon S3 cloud storage setup, you'll have created a policy specifically for use with Media Cloud.  Navigate to your Amazon Console and then navigate to the [IAM service](https://console.aws.amazon.com/iam/home#/home).

In the IAM service, select **Policies** on the left and then find the policy you created when setting up cloud storage.  Click on it and you will be in a **Summary** screen for the policy.  

Click on the **Edit policy** button.

Make the following adjustments to the policy so that it looks like this:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:DeleteObjectTagging",
                "s3:ListBucketMultipartUploads",
                "s3:DeleteObjectVersion",
                "s3:ListBucket",
                "s3:DeleteObjectVersionTagging",
                "s3:GetBucketAcl",
                "s3:ListMultipartUploadParts",
                "s3:PutObject",
                "s3:GetObjectAcl",
                "s3:GetObject",
                "s3:AbortMultipartUpload",
                "s3:DeleteObject",
                "s3:GetBucketLocation",
                "s3:PutObjectAcl"
            ],
            "Resource": [
                "arn:aws:s3:::YOUR-BUCKET-NAME",
                "arn:aws:s3:::YOUR-BUCKET-NAME/*",
            ]
        },
        {
            "Effect": "Allow",
            "Action": [
                "rekognition:DetectLabels",
                "rekognition:GetCelebrityRecognition",
                "rekognition:GetContentModeration",
                "rekognition:DetectFaces",
                "rekognition:DetectModerationLabels",
                "rekognition:RecognizeCelebrities",
                "rekognition:CompareFaces",
                "rekognition:DetectText",
                "rekognition:GetCelebrityInfo",
                "s3:HeadBucket",
                "rekognition:GetLabelDetection"
            ],
            "Resource": "*"
        }
    ]
}
```

Replace **YOUR-BUCKET-NAME** with the actual name of your bucket.

Once done, click on **Review Policy** and then click on **Save Changes**.

Rekognition is now configured and ready to use.

&nbsp;

### Configuring Google Cloud Vision
Google Cloud Vision, unlike Rekognition, can be used with any cloud storage provider.

If you've configured Google as your cloud storage provider, the only thing you need to do is [enable the Cloud Vision API](https://console.cloud.google.com/flows/enableapi?apiid=vision-json.googleapis.com) in Google Console.

For other cloud storage providers, you'll need to follow the first four steps in [their quickstart guide](https://cloud.google.com/vision/docs/quickstart-client-libraries).  You do not need to do step 5.  Once you've downloaded the credentials JSON file, you will copy and paste the contents of that file into the **Credentials** text box in **Vision Provider Settings**.  You are now done.

Note that Google Vision is only available in the paid version of Media Cloud.

&nbsp;

## Vision Options
### Detect Labels
Detects instances of real-world labels within an image (JPEG or PNG) provided as input. This includes objects like flower, tree, and table; events like wedding, graduation, and birthday party; and concepts like landscape, evening, and nature.

### Detect Labels Taxonomy
The taxonomy to apply the detected labels to.

### Detect Labels Confidence
The minimum confidence (0-100) required to apply the returned label as tags. Default is 70.

### Detect Moderation Labels
Detects explicit or suggestive adult content in a specified JPEG or PNG format image. Use this to moderate images depending on your requirements. For example, you might want to filter images that contain nudity, but not images containing suggestive content.

### Detect Moderation Labels Taxonomy
The taxonomy to apply the detected moderation labels to.

### Detect Moderation Labels Confidence
The minimum confidence (0-100) required to apply the returned label as tags. Default is 70.

### Detect Celebrity Faces (Rekognition only)
Detects celebrity faces in the image. This will also detect non-celebrity faces. If you use this option, you should not use the **Detect Faces** option as either will overwrite the other. Detected faces will be stored as additional metadata for the image. If you are using Imgix, you can use this for cropping images centered on a face.

### Detect Celebrity Faces Taxonomy (Rekognition only)
The taxonomy to apply the detected moderation labels to.

### Detect Faces
Detects faces in the image. If you use this option, you should not use the **Detect Celebrity Faces** option as either will overwrite the other. Detected faces will be stored as additional metadata for the image. If you are using Imgix, you can use this for cropping images centered on a face.

### Always Process in Background
Controls if Vision tasks are processed during an upload or queued to a background task to be processed at a later time (usually within a few minutes).

### Ignored Tags
A comma separated list of tags to ignore.
