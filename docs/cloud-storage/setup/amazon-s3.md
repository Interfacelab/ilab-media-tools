# Amazon S3
Before we can use Media Cloud, you'll first need to go through some basic steps to create a bucket on S3 and a user account we can use to access that bucket.

Using the Amazon console can be a little intimidating at first, but if you stick to these steps you should be able to breeze right through it.

## Step 1. Create an S3 Bucket
The first thing we'll need to do is create the bucket we're going to use for storing our media and files.  If you haven't already, log into your Amazon AWS account: [Amazon AWS Console](https://console.aws.amazon.com/).  

Once you've logged in, select the S3 service.  When the S3 Console has loaded, select **Create Bucket** to get started:

![image.png](https://i.imgur.com/21AuEHf.png){.responsive}

### Step 1.1 - Bucket Name/Region
When you click on **Create Bucket**, you'll be presented with a multi-step wizard dialog.  On the first step of this wizard, enter in the following information:

- Bucket Name
- Region

![image.png](https://i.imgur.com/wMsuAkZ.png){.responsive}
    
You should select a region that is closest geographically to either your server or to yourself (if using Direct Uploads).

Once you've specified the name and region, click on **Next** to continue to the next screen.

### Step 1.2 - Bucket Properties
Generally speaking, you can skip this page by clicking **Next**.

### Step 1.3 - Bucket Permissions
For this screen, it's important that you uncheck the following options:

- Uncheck **Block new public ACLs and uploading public objects**
- Uncheck **Remove public acess granted through public ACLs**
- Uncheck **Block public and cross-account access if bucket has public policies**
- Uncheck **Block new public bucket policies**

![image.png](https://i.imgur.com/thfCIhv.png){.responsive}

Click on **Create Bucket** to create your bucket.

### Step 1.4 - Transfer Acceleration (Optional)
It's highly recommended that you enable transfer acceleration on your bucket to improve upload and download speeds.  There will be an extra charge incurred for having it enabled, however.

To enable Transfer Acceleration, select your bucket in the S3 console and select the **Properties** tab.  Scroll down until you find a panel named **Transfer acceleration**.  Click on it to expand it and select the **Enabled** option.

![image.png](https://i.imgur.com/gfYcp2H.png){.responsive}

Click on **Save** to save the setting.

## Step 2 - Create Policy
For our next step, we'll be creating a policy that controls how the AWS user account we'll be creating in Step 3 accesses our bucket.  We want to define the narrowest set of permissions possible to keep things secure.

Select **IAM** from the services menu in the AWS Console.  Once the IAM console has loaded, click on **Policies** on the left hand side of the page and then click on **Create Policy** to get started.

![image.png](https://i.imgur.com/sV4BLCZ.png){.responsive}

### Step 2.1 - Define the Policy
When you click on **Create Policy** a wizard dialog will appear.  

![image.png](https://i.imgur.com/kDC5D7G.png){.responsive}

Click on the **JSON** tab and paste the following JSON into it:

https://gist.github.com/jawngee/9cc2031f5ad154558b14e1fb395414cf

**IMPORTANT!** Make sure you replace *YOURBUCKET* with the name of the bucket you created in Step 1. 

Click on **Review Policy** to continue.

### Step 2.2 - Name the Policy
On the next screen, give the policy a name and description that is meaningful to you.

![image.png](https://i.imgur.com/cQsurjz.png){.responsive}

Click on **Create Policy** to create the policy.

## Step 3 - Create IAM User
While still in the IAM console, select **Users** on the left hand side of the page and then click on **Add user**.

![image.png](https://i.imgur.com/X63N89o.png){.responsive}

### Step 3.1 - User Properties

In the create user wizard:

- Give the user a name
- Check the **Programmatic access** checkbox and make sure that **AWS Management Console access** is unchecked

![image.png](https://i.imgur.com/muZpBtl.png){.responsive}

Click on **Next: Permissions**

### Step 3.2 - Permissions
On the Permissions step, select **Attach existing policies directly**.  In the list of policies directly below, find the policy we created in the previous step and check the checkbox next to it.

![image.png](https://i.imgur.com/yd8cDre.png){.responsive}

Click on **Next: Tags** and then click on **Next: Review** on the screen that follows.  Make sure everything is correct and click on **Create user**.

### Step 3.3 - Download Credentials

Once you've clicked on **Create user** you'll see a screen that will allow you to download your credentials as a CSV file.  

![image.png](https://i.imgur.com/wjqrCGV.png){.responsive}

Download them and keep them in a safe place.  We will need them for configuring Media Cloud.

# Step 4 - CORS Configuration (Optional)
If you intend to use Direct Upload functionality, you'll need to set the CORS policy on your bucket.

## Step 4.1 - Bucket Properties
Log into the Amazon S3 Console and select your bucket.  Click on the **Permissions** tab and then select **CORS Configuration**

![image.png](https://i.imgur.com/UwXWGCU.png){.responsive}

## Step 4.2 - Set the CORS Configuration
Copy and paste the example CORS configration found below into the CORS configration editor in the S3 console.

https://gist.github.com/jawngee/6fc89497e10d0915ab2dfac807aa01e1

![image.png](https://i.imgur.com/9q4P1RW.png){.responsive}

Click **Save**.

Your CORS configuration has now been set and you should be able to perform direct uploads when you've enabled that feature.
  
# Step 5 - Configure Media Cloud
Now that everything has been set up with Amazon, it's time to setup Media Cloud.

Navigate to **Settings** in the Media Cloud admin menu and select **Cloud Storage**.

## Step 5.1 - Provider Settings
In the **Provider** panel, set the **Storage Provider** to Amazon S3.

In the **Provider Settings** section, supply the **Access Key** and **Secret** from the CSV file we downloaded in Step 3.3.

For the **Region** you can set this **Automatic** or select the region you created the S3 bucket in, if you remember it.

If you turned on **Transfer Acceleration** for your bucket, toggle this to on.

At this point, these are the basic settings you need to get S3 working with Media Cloud.  Save the settings and run the Troubleshooter to verify that everything is working correctly.