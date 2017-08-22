## Cloud.gov PHP Example Application:  WordPress

This is an example application which can be run on Cloud.gov using the CloudFoundry [PHP Build Pack].

This is an out-of-the-box implementation of WordPress.  It's an example of how common PHP applications can easily be run on Cloud.gov

### Usage

1. Clone the app (i.e. this repo).

  ```bash
  git clone https://github.com/18F/cf-ex-wordpress.git cf-ex-wordpress
  cd cf-ex-wordpress
  ```
2. Create a service instance of a MySQL Database
 * View Services
    * `cf marketplace`
 * View Specific Service Plans
    * Template: `cf marketplace -s SERVICE`
    * Example: `cf marketplace -s aws-rds`
 * Create Service Instance
    * Template: `cf create-service SERVICE PLAN SERVICE_INSTANCE`
    * Example: `cf create-service aws-rds shared-mysql mysql-service`

3. Create a service instance of S3 storage. NB: A demo account will not be permitted to use the S3 service, so you can skip this for a basic demo.
  * View Services
     * `cf marketplace`
  * View Specific Service Plans
     * Template: `cf marketplace -s SERVICE`
     * Example: `cf marketplace -s s3`
  * Create Service Instance
     * Template: `cf create-service SERVICE PLAN SERVICE_INSTANCE`
     * Example: `cf create-service s3 basic s3-service`

4. Copy the example `manifest.yml.example` to `manifest.yml`. Edit the `manifest.yml` file.
  * Change the 'name' and 'host' attributes to something unique for your site.
  * Under "services:" change
    * "mysql-service" to the name of your MySQL service you created in Step 2.
    * "s3-storage" to the name of your S3 service you created in Step 3. Or delete this line if you're not using S3.

5. Copy the example `setup.json.example` to `setup.json`. Edit the `setup.json` file for your specific WordPress site information, plugins you want installed, and themes.
  * **NOTE** The example includes a set of plugins that will be used to attach to your previously created S3 storage so you can store media uploads, like pictures, for your WordPress site. If you do not use these plugins, every time you deploy, it will destroy your uploaded files.
  * See: [Setup JSON](#setup-json) for more information about the format of this file

6. Deploy the app with a no start command
`cf push --no-start`

7. Set environment variables for secret keys using [WordPress Secret Key Generator](https://api.wordpress.org/secret-key/1.1/salt/).

Make sure to include the leading and closing `'` characters to avoid errors escaping special characters.

  ```bash
  cf set-env mywordpress AUTH_KEY YOUR_KEY
  cf set-env mywordpress SECURE_AUTH_KEY YOUR_KEY
  cf set-env mywordpress LOGGED_IN_KEY YOUR_KEY
  cf set-env mywordpress NONCE_KEY YOUR_KEY
  cf set-env mywordpress AUTH_SALT YOUR_KEY
  cf set-env mywordpress SECURE_AUTH_SALT YOUR_KEY
  cf set-env mywordpress LOGGED_IN_SALT YOUR_KEY
  cf set-env mywordpress NONCE_SALT YOUR_KEY
  ```

8. Push it to CloudFoundry.

  ```bash
  cf push
  ```

9. Configure S3 WordPress Plugin
 * Login to https://mysite.app.cloud.gov/wp-admin
 * Click on AWS in the left navigation menu
 * Click on S3 and CloudFront in navigation menu
 * Turn on `Copy Files to S3` and `Rewrite File URLs`
 * Scroll to the bottom, and click Save Changes

### Setup JSON
The only required section is `site_info`
```json
{
  "site_info": {
    "url": "https://mysite.app.cloud.gov",
    "title": "My new site",
    "admin_user": "admin",
    "admin_password": "CHANGEME",
    "admin_email": "my.email@example.com"
  }
}
```
This section is used to setup your WordPress site for the first time. You can change most of these settings with the WordPress admin once you have logged in after setup.

You can define a specific version of WordPress that you would like to use by adding `"wordpress_version": VERSION_NUMBER` on line above the "site_info" section. If you don't add this line, cloud.gov will use the latest release of WordPress.

Plugins and themes are configured in the same way. You can provide either:
 * `name` and specific `version`
 * `name` only and latest version will be used
 * `url` only (if you want to add a plugin from GitHub that's not in the Plugin repository)

#### Full Example
```json
{
  "wordpress_version": "4.4",
  "site_info": {
    "url": "https://mysite.app.cloud.gov",
    "title": "My new site",
    "admin_user": "admin",
    "admin_password": "CHANGEME",
    "admin_email": "my.email@example.com"
  },
  "plugins": [
    {
      "name": "amazon-web-services",
      "version": "0.3.4"
    },
    {
      "name": "amazon-s3-and-cloudfront"
    },
    {
      "url": "https://example.org/my-great-plugin-0.1.zip"
    }
  ],
  "themes": [
    {
      "name": "create",
      "version": "1.3"
    }
  ]
}
```

### How It Works

When you push the application here's what happens.

1. The local bits are pushed to your target.  This is small, five files around 25k. It includes the changes we made and a build pack extension for WordPress.
1. The server downloads the [PHP Build Pack] and runs it.  This installs HTTPD and PHP.
1. The build pack sees the extension that we pushed and runs it.  The extension installs [wpcli](http://wp-cli.org/), and then reads your `setup.json` to install WordPress, and any plugins or themes you have defined. It then copies the rest of the files that we pushed and replaces the default WordPress files with them.  In this case, it's just the `wp-config.php` file.
1. At this point, the build pack is done and CF runs our droplet.

### Changes

These changes were made to prepare WordPress to run on CloudFoundry.

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
+'DB_NAME', $service['credentials']['dbname']);

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

Please read the following before using WordPress in production on CloudFoundry.

1. WordPress is designed to write to the local file system.  This does not work well with CloudFoundry, as an application's [local storage on CloudFoundry] is ephemeral.  In other words, WordPress will write things to the local disk and they will eventually disappear. Using S3 for media upload storage is the recommended way to rectify this at this time. Also, keep in mind, that if you install themes or plugins via the WordPress admin and you have not included them in your `setup.json`, the next time you push, these plugins or themes will be removed.

1. This is not an issue with WordPress specifically, but PHP stores session information to the local disk.  As mentioned previously, the local disk for an application on CloudFoundry is ephemeral, so it is possible for you to lose session and session data.  If you need reliable session storage, look at storing session data in an SQL database or with a NoSQL service.

### License

See [LICENSE](LICENSE.md) for license details.


[PHP Build Pack]:https://github.com/cloudfoundry/php-buildpack
[secret keys]:https://github.com/cloudfoundry-samples/cf-ex-wordpress/blob/master/wp-config.php#L49
[WordPress.org secret-key service]:https://api.wordpress.org/secret-key/1.1/salt
[ClearDb]:https://www.cleardb.com/
[local storage on CloudFoundry]:https://docs.cloudfoundry.org/devguide/deploy-apps/prepare-to-deploy.html#filesystem
[wp-content directory]:http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
[ephemeral file system]:http://docs.cloudfoundry.org/devguide/deploy-apps/prepare-to-deploy.html#filesystem
