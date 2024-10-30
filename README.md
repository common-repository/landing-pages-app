### Development

- clone repository
- run `docker-compose up -d`
- run `composer install`
- go to URL `wordpress.landingi.it`
- go through default Wordpress installation process
- activate landingi plugin
- change the `landing_pages_api_url` config option in [landingi-plugin.php](landingi-plugin.php) to `http://api.landingi.it/`
- change the `landing_pages_export_url` config option in [landingi-plugin.php](landingi-plugin.php) to `http://lp.landingi.it/`
- modify the [ApiClientService](src/LandingPagesPlugin/Service/ApiClientService.php), adding the `'proxy' => 'http://application:80'` option to the Guzzle client
- modify the [LandendApiClientService](src/LandingPagesPlugin/Service/LandendApiClientService.php), adding the `'proxy' => 'http://application:80'` option to the Guzzle client
- profit

### Deployment:

- make your changes
- revert any local-specific changes (like the Guzzle proxy modifications and config options)
- update the plugin version in [landingi-plugin.php](landingi-plugin.php)
- run `composer update --no-dev`
- commit the changes to the Git repository
- once merged, tag the squashed commit with the semantic version number and push the tag to the Git repository
- copy the merged changes to your SVN trunk (without the development-specific files, like this one or docker-compose.yml) and commit them to the WordPress SVN repository
- check [the plugin page](https://wordpress.org/plugins/landing-pages-app/) in 10-15 minutes to see the plugin version updated
- profit
