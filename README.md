# s3podcasts

Wordpress Plugin to store podcasts in Amazon S3

## Depedencies

### Amazon AWS PHP SDK

This plugin requires AWS PHP SDK version v3 and it wasn't tested in another version of the SDK.

Each release of the AWS SDK for PHP ships with a zip file containing all of the classes and dependencies you need to run the SDK. Additionally, the zip file includes a class autoloader for the AWS SDK for PHP and all of its dependencies.

To get started, you must download the zip file, unzip it into plugin folder (do not change the aws folder name), and include the autoloader: require_once('/aws/aws-autoloader.php');

Download link: http://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/installation.html#installing-via-zip

### AWS access keys

Edit the file "s3podcasts.php", lines 44 and 45, and inform your credentials:

define(ACCESS_KEY, 'YOUR AWS ACCESS KEY HERE');
define(SECRET_KEY, 'YOUR SECRET KEY HERE');

If you do not have credentials yet, access your AWS account and create them following creditials best practices.

### Test under localhost (CURL certificate)

To test this plugin in your localhost you need to follow the steps bellow:

1 - Access this link: http://curl.haxx.se/ca/cacert.pem 
2 - Copy the entire page and save it in a: "cacert.pem"
3 - Then in your php.ini file insert or edit the following line: curl.cainfo = "[pathtothisfile]\cacert.pem".

There are other ways to fix this issue. Follow the link bellow to see the options:

http://stackoverflow.com/questions/29822686/curl-error-60-ssl-certificate-unable-to-get-local-issuer-certificate