## Cloud.gov PHP Example Application:  Wordpress

This is an example application which can be run on Cloud.gov using the CloudFoundry [PHP Build Pack].

This is an out-of-the-box implementation of Wordpress.  It's an example of how common PHP applications can easily be run on Cloud.gov

### Usage

1. Clone the app (i.e. this repo).

  ```bash
  git clone https://github.com/18F/cf-ex-wordpress.git cf-ex-wordpress
  cd cf-ex-wordpress
  ```

2. Create a service instance of a MySQL Database
View Services
`cf marketplace`
View Specific Service Plans
Template: `cf marketplace -s SERVICE`
Example: `cf marketplace -s mysql56`
Create Service Instance
Template: `cf create-service SERVICE PLAN SERVICE_INSTANCE`
Example: `cf create-service mysql56 free mysql-service`

3. Create a service instance of S3 storage
View Services
`cf marketplace`
View Specific Service Plans
Template: `cf marketplace -s SERVICE`
Example: `cf marketplace -s s3`
Create Service Instance
Template: `cf create-service SERVICE PLAN SERVICE_INSTANCE`
Example: `cf create-service s3 basic s3-service`

4. Copy the example `manifest.yml.example` to `manifest.yml`. Edit the `manifest.yml` file.  Change the 'host' attribute to something unique.  Then under "services:" change "mysql-service" to the name of your MySQL service.  This is the name of the service that will be bound to your application and thus used by Wordpress.

5. Copy the example `setup.json.example` to `setup.json`. Edit the `setup.json` file for your specific WordPress site information, plugins you want installed, and themes.
**NOTE** The example includes a set of plugins that will be used to attach to your previously created S3 storage so you can store media uploads, like pictures, for your WordPress site. If you do not use these plugins, every time you deploy, it will destroy your uploaded files.

See: [Setup JSON](#setup-json) for more information about the format of this file

6. Deploy the app with a no start command
`cf push --no-start`

7. Set environment variables for secret keys using [Wordpress Secret Key Generator](https://api.wordpress.org/secret-key/1.1/salt/)
```bash
cf set-env mywordpress-new AUTH_KEY YOUR_KEY
cf set-env mywordpress-new SECURE_AUTH_KEY YOUR_KEY
cf set-env mywordpress-new LOGGED_IN_KEY YOUR_KEY
cf set-env mywordpress-new NONCE_KEY YOUR_KEY
cf set-env mywordpress-new AUTH_SALT YOUR_KEY
cf set-env mywordpress-new SECURE_AUTH_SALT YOUR_KEY
cf set-env mywordpress-new LOGGED_IN_SALT YOUR_KEY
cf set-env mywordpress-new NONCE_SALT YOUR_KEY
```

8. Push it to CloudFoundry.
```bash
cf push
```

9. Configure S3 WordPress Plugin
 1. Login to https://your-site.apps.cloud.gov/wp-admin
 2. Click on AWS in the left navigation menu
 3. Click on S3 and CloudFront in navigation menu
 4. Turn on `Copy Files to S3` and `Rewrite File URLs`
 5. Scroll to the bottom, and click Save Changes

### Setup JSON

### How It Works

When you push the application here's what happens.

1. The local bits are pushed to your target.  This is small, five files around 25k. It includes the changes we made and a build pack extension for Wordpress.
1. The server downloads the [PHP Build Pack] and runs it.  This installs HTTPD and PHP.
1. The build pack sees the extension that we pushed and runs it.  The extension downloads the stock Wordpress file from their server, unzips it and installs it into the `htdocs` directory.  It then copies the rest of the files that we pushed and replaces the default Wordpress files with them.  In this case, it's just the `wp-config.php` file.
1. At this point, the build pack is done and CF runs our droplet.

### Changes

These changes were made to prepare Wordpress to run on CloudFoundry.

1. Edit `wp-config.php`, configure to use CloudFoundry database.

```diff
--- wp-config-sample.php	2013-10-24 18:58:23.000000000 -0400
+++ wp-config.php	2014-03-05 15:44:23.000000000 -0500
@@ -14,18 +14,22 @@
  * @package WordPress
  */

+// ** Read MySQL service properties from _ENV['VCAP_SERVICES']
+$services = json_decode($_ENV['VCAP_SERVICES'], true);
+$service = $services['cleardb'][0];  // pick the first MySQL service
+
 // ** MySQL settings - You can get this info from your web host ** //
 /** The name of the database for WordPress */
-'DB_NAME', 'database_name_here');
+'DB_NAME', $service['credentials']['name']);

 /** MySQL database username */
-'DB_USER', 'username_here');
+'DB_USER', $service['credentials']['username']);

 /** MySQL database password */
-'DB_PASSWORD', 'password_here');
+'DB_PASSWORD', $service['credentials']['password']);

 /** MySQL hostname */
-'DB_HOST', 'localhost');
+'DB_HOST', $service['credentials']['hostname'] . ':' . $service['credentials']['port']);

 /** Database Charset to use in creating database tables. */
 'DB_CHARSET', 'utf8');
```

### Caution

Please read the following before using Wordpress in production on CloudFoundry.

1. Wordpress is designed to write to the local file system.  This does not work well with CloudFoundry, as an application's [local storage on CloudFoundry] is ephemeral.  In other words, Wordpress will write things to the local disk and they will eventually disappear.  See the [Persistent Storage](#persistent-storage) above for ways to work around this.

1. This is not an issue with Wordpress specifically, but PHP stores session information to the local disk.  As mentioned previously, the local disk for an application on CloudFoundry is ephemeral, so it is possible for you to lose session and session data.  If you need reliable session storage, look at storing session data in an SQL database or with a NoSQL service.


[PHP Build Pack]:https://github.com/dmikusa-pivotal/cf-php-build-pack
[secret keys]:https://github.com/dmikusa-pivotal/cf-ex-worpress/blob/master/wp-config.php#L49
[WordPress.org secret-key service]:https://api.wordpress.org/secret-key/1.1/salt
[ClearDb]:https://www.cleardb.com/
[local storage on CloudFoundry]:http://docs.cloudfoundry.org/devguide/deploy-apps/prepare-to-deploy.html#filesystem
[wp-content directory]:http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
[ephemeral file system]:http://docs.cloudfoundry.org/devguide/deploy-apps/prepare-to-deploy.html#filesystem
