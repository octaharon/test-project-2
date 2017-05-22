# test-project-2
Full-stack application as a test assignment

Technologies used: Apache, MySQL, PHP (Silex), ZURB Foundation, AngularJS 1.x, Gulp

To run just checkout the whole DIR into some apache folder (should be under document root), and then do   
*npm install*   
and then   
*composer update*   
*bower update*   
*gulp build*   
or just   
*gulp install*   

Don't forget to import a db.sql into your server and set up connection settings in **config/config.yml**
See **config/config_common.yml** for an example, but better not change it directly, as **config.yml** overrides it
