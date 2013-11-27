*Author: Nobuyori Takahashi 
> since 2010 to present

# Get started with GraceNote
- What you have to do first
> Create an index file in your document root directory that points to GraceNote/core/main.php file
<pre>
Example: the inside of index.php file would look like this:
include('/var/www/GraceNote/core/main.php');
</pre>

- Create a configuration file
> The configuration file <b>MUST</b> be name as GraceNote/cofigs/config.json
>> Please refer to GraceNote/configs/example.json
<pre>
Configuration file example:
GraceNote will be installed as following.
/var/www/yourApp/GraceNote/
Create your configuration file.
/var/www/yourApp/configs/config.json
</pre>

- Create bootstrapping index.php for your project
> In this file, you will be choosing what files to import for your project
<pre>
Bootstrapping example: 
GraceNote will be installed as following.
/var/www/yourApp/GraceNote/
Create your index.php file.
/var/www/yourApp/index.php
Import library classes for your application.
Inside index.php file: 
Loader::imort('lib', 'Encrypt.class.php');
// register custom import path
Loader::setPath('myLib', '../myLib/');
Loader::import('myLib', 'MyAwesomeClass.class.php');
// override controller path
Loader::setPath('controller', '../myController/');
</pre>

- Create directory for logging
> make sure the directory's permission is set correctly for GraceNote to read and write
<pre>
mkdir /var/www/GraceNote/logs/
</pre>

# Set up CMS
> Once you have created a configuration file (config.json), GraceNote offers a built-in CMS
>> Make sure to separate the CMS and your application
>>> For the built-in CMS to correctly function, you <b>MUST<b> use the default bootstrap index.php

1. Create a directory for your CMS. Example: mkdir /var/www/cms/
2. Install GraceNote into your cms directory. Example: git clone https://github.com/voltrue2/GraceNote GraceNote
3. Point your document root index.php to your CMS GraceNote. Example: include('/var/www/cms/GraceNote/core/main.php');
4. Create configuration file. Example" /var/www/cmns/configs/config.json
5. Configure Asset correctly
<pre>
"Asset": {
	"embedPaths": {
		"js": "/var/www/GraceNote/js/",
		"css": "/var/www/GraceNote/css/",
		"media": "/var/www/htdocs/img/"
	},
	"httpUrls": {
		"normal": {
			"protocol": "http",
			"host": "yourCMSDomain.com",
			"path": "/media/img",
			"sourcePath": "/var/www/GraceNote/assets/"
		},
		"ssl": {
			"protocol": "https",
			"host": "yourCMSDomain.com",
			"path": "/media/img",
			"sourcePath": "/var/www/GraceNote/assets/"
		}
	}
}
</pre>

- NOTE: If you host the CMS and your application on different web servers, plase make sure to have a way to distribute the media files from CMS to your application server.
> You may use shell script such as rsync etc


# How to create a web page with GraceNote

### The following example will create a page for the URL: http://yourdomain.com/helloworld/

- Create a directory with the same name as http://yourdomain.com/<strong>helloworld</strong>/
<pre>
mkdir GraceNote/controller/helloworld/
</pre>

- Create a file called index.class.php NOTE: the file extension <b>MUST</b> be .class.php
<pre>
touch GraceNote/controller/helloworld/index.class.php
</pre>

- How to write your controller class in index.class.php
> Your controller class name has to match the directory name (case insensitive). In this example it would be helloworld
>> Your controller class <b>MUST</b> inehrit Controller core class with "extends Controller"
<pre>
class HelloWorld extends Controller {
	// constructor
	public function HelloWorld($view) {
		// The controller class constructor will have 1 argument. $view
	}
}
</pre>

- Writting a public method for your HelloWorld controller class
> if the URL does <b>NOT</b> contain the name of your public method, GraceNote will try to call a method called "index"
<pre>
class HelloWorld extends Controller {
	private $view; // we want to be able to use this class outside of the constructor
	public function HelloWorld($view) {
		$this->view = $view;
	}
	// public method
	// $param1 and $param2 are optional
	// if URL http://yourdomain/helloworld/index/myParam1/myParam2/ is given: We will have $param1 = 'myParam1' and $param2 = 'myParam2'
	public function index($param1 = null, $param2 = null) {
		// this will output "Hello World" to the browser
		echo "Hello World";
	}
}
</pre>

- URL Rules for GraceNote
> http://yourdomain.com/<b>ControllerClassName</b>/<b>ControllerPublicMethodName</b>/AsManyParamtetersAsYouWant/...
> If no public method name is given in the URL, it will default to a public method called <b>index</b>
>> If public method is not found, GraceNote will respond with 404 error.

- Output page content with a template file
> The template file is a PHP file
> The directory name under template does <b>NOT</b> have to match the controller name
> The template file name does <b>NOT</b> have to match the controller method
<pre>
mkdir GraceNote/template/helloworld/
touch GraceNote/template/helloworld/index.html.php
</pre>

- Template file content
<code>`
<html>
	<head></head>
	<bod>Hello World!</body>
</html>
`</code>

- How to use a template file from controller class
<pre>
// public method of controller class
public function index() {
	// use $view class' respondTemplate method
	// this will output "Hellow World!" to the browser
	$this->view->respondTemplate('helloworld/index.html.php');
}
</pre>

- How to use variables in your template
> <?= $phpvariable; ?> is a short tag. If you have not enabled PHP short tags, the syntax will be <?php echo $phpVariable; ?>
`
<html>
	<head></head>
	<body>Hello <?= $param1; ?></body>
</html>
`

<pre>
public function index($param1) {
	// this is how to pass a variable to your template
	$this->view->assign('param1', $param1); // the first argument (string) becomes the name of the variable
	$this->view->respondTemplate('helloworld/index.html.php');
}
</pre>

# Public methods of View class
> an instance of View class is passed to your controller's constructor as the first parameter.

<pre>
/****
* Assigns a variable to template
* @param variableName (String) name of a variable used in template
* @param variableValue (Mix) variable used in template
* @return void
***/
View::assign($variableName, $variableValue);
</pre>
<pre>
/****
* Responds to client request with template
* @param templatePath (String) path to template file
* @return void
***/
View::respondTemplate($templatePath);
</pre>
<pre>
/***
* Redirect the user to specified URL
* @param path (String) URL path to redirect the user to
* @return void
*/
View::redirect($path);
</pre>
<pre>
/****
* Responds to client request with JSON. Variables assigned with View::assign will be sent as JSON
* @param gzip (Boolean) gzip the JSON response or not. Default is true
* @param headerCode (integer) HTTP header code such as 200, 404 etc. This is potional
* @return void
***/
View::respondJson($gzip, $headerCode);
</pre>
<pre>
/****
* Responds to client with error
* @param errorCode (Integer) error code. Example: 404, 500 etc
* @return void
***/
View::respondError($errorCode);
</pre>

# Public method of core Controller class
> Your controller class extends this class
>> all public methods of core Controller class can be called in your controller class. Example: $this->getQuery

<pre>
/***
* Get the value of query from the request URL (Post and Get)
* @param queryName (String) Example: http://yourdomain.com/yourController/yourPublicMethod/?example=helloWorld would be queried as $controller->getQuery('example');
* @return mix the method can return string, number, object, array etc
*/
Controller::getQuery($queryName);
</pre>

<pre>
/***
* Get the uploaded values from client
* @param fileName (String) file name that is passed from the client
* @return array
*/
Controller::getFile($fileName);
</pre>

<pre>
/***
* Return session object
* @return array
*/
Contoller::getSession();
</pre>

<pre>
/***
* Store session value array
* @param sessionValue (Array)
* @return void
*/
Controller::setSession($sessionValue);
</pre>

<pre>
/***
* Add session value to the existing session array
* @param key (String)
* @param value (Mixed)
* @return void
*/
Controller::addSession($key, $value);
</pre>

# Globally Available Classes
> GraceNote framework includes utility classes to be used in the application globally
## Static Classes

### Config
> Public methods
<pre>
/***
* Get configuration JSON object as an associative array
* @param configName (String) a string name of configurations that cooresponds in the config.json file. Example: "helloWorld": { "myConfig": "hello", "list": [1, 2, 3] } would be queried as Config::get('helloWorld')
* @return mix the method can return string, number, or array
*/
Config::get($configName);
</pre>
<pre>
/***
* Get all configuration JSON object as an associative array
* @return mix the method can return string, number, or array
*/
Config::getAll();
</pre>
> Required configurations

- None

### Loader
> Public methods
<pre>
/***
* Include a PHP file from predefined categories. Categories: root, core, datasources, controller, model, template, lib
* The categories that the developer should be using are: controller, model, template, and lib. root, core, and datasources are used by GraceNote framework
* @param category (String) category name. it has to match the predefined category name
* @param path (String) the path to the file that is to be included
* @return void
*/
Loader::import($category, $path);
</pre>
<pre>
/***
* Include a PHP template file. This method should be used with View::respondTemplate.
* @param filePath (String) file path to the template file. the category is forced to be template
* @return void 
*/
Loader::template($filePath);
</pre>
<pre>
/***
* Convert all assigned variables by View::assign into JavaScript variables with the given namespace.
* @param namespace (String) a string name of a namespace object to hold the converted variables in JavaScript. default is window
* @return void
*/
Loader::jsVars($namespace);
</pre>
> Required configurations

- None

### UserAgent
> Public methods
<pre>
/***
* Return a name of the client OS
* @return (String) a name of the client OS
*/
UserAgent::getOs();
</pre>
<pre>
/***
* Return a name of the client browser
* @return (String) a name of the client browser
*/
UserAgent::getBrowser();
</pre>
<pre>
/***
* Evaluate the given OS name against the client OS
* @param osName (String) a name of OS to be evaluated
* @return (Boolean) if osName matches the client OS, the method returns true
*/
UserAgent::isOs($osName);
</pre>
<pre>
/***
* Evaluate the given browser name against the client browser
* @param browserName (String) a name of browser to be evaluated
* @return (Boolean) if browserName matches the client browser, the method returns true
*/
UserAgent::isBrowser($browserName);
</pre>
> Required configurations
<pre>
// In order for UserAgent to parse and understand OS names and browser names, they must be added to the configuration as following:
"UserAgent": {
	"os": ["Android", "Windows", "Macintosh", "Linux", "iPohne", "iPod", "iPad"],
	"browser": ["IE", "Chrome", "Firefox", "Safari", "Opera"]
}
</pre>

### Asset
> Public methods
<pre>
/***
* Return string content of minified Javascript file.
* This method should be used in a template file to embed Javascript. Example: <?= Asset::js('js', 'my/javascript.js'); ?>
* @param category (String) Category name defined in configuration file
* @param path (String) path to the Javascript file. The method can read multiple JavaScript files if a directory path is given
* @ return (String) 
*/
Asset::js($category, $path);
</pre>
<pre>
/***
* Return string content of minified CSS file.
* This method should be used in a template file to embed CSS. Example: <?= Asset::css('css', 'my/cssFile.css'); ?>
* @param category (String) Category name defined in configuration file
* @param path (String) path to the CSS file. The method can read multiple CSS files if a directory path is given
* @ return (String) 
*/
Asset::css($category, $path);
</pre>

<pre>
/***
* Return a map of file paths to be used in the client. This method should be used with Loader::jsVars.
* @param httpUrlName (String) cooresponding JSON key in the configuration file
* @param path (String) path to usually a directory
* @return (Array) an associative array
*/
Asset::map($httpUrlName, $path)
</pre>
> Required configurations
<pre>
"Asset": {
	"embedPaths": {
		"js": "/var/www/GraceNote/js/",
		"css": "/var/www/GraceNote/css/",
		"media": "/var/www/htdocs/img/"
	},
	"httpUrls": {
		// "normal" will be used as httpUrlName for Asset::map method
		"normal": {
			"protocol": "http",
			"host": "mydomain.com",
			"sourcePath": "/var/www/htdocs/"
		},
		// "ssl" will be used as httpUrlName for Asset::map method
		"ssl": {
			"protocol": "https",
			"host": "mydomain.com",
			"sourcePath": "/var/www/htdocs/"
		}
	}
}
</pre>

- Log

## Other Classes
- Cache
- FileSystem
- DataModel

## Library Classes
> As a developer, you have a choice to include these classes in your application
>> These classes should be loaded in GraceNote/index.php file
>>> Example: Loader::import('lib', 'Encrypt.class.php');

- Encrypt
- EventEmitter
- Report
- Text

# Apache configuration example for GraceNote #

<pre>
RewriteEngine on
RewriteRule ^/(favicon.ico) /$1 [L]
RewriteRule ^/(img|css|js)/(.*) /$1/$2 [L]
RewriteCond %{REQUEST_URI} !^/?index.php$
RewriteRule . /var/www/mydomain/htdocs/index.php
# Block Hotlinking
RewriteEngine On
RewriteCond %{HTTP_REFERER} !^http://(www\.)?mydomain\.com(/.*)*$ [NC]
RewriteCond %{HTTP_REFERER} !^$
RewriteRule \.(jpe?g|gif|png|js|JPE?G|GIF|PNG|JS)$ - [F]
#
# Use name-based virtual hosting.
#
NameVirtualHost *:80
&lt;VirtualHost  *:80&gt;
    DocumentRoot /var/www/htdocs
    ServerName mydomain.com
    ErrorLog "|/usr/sbin/rotatelogs /var/www/mydomain.com/logs/error_%Y%m%d_log 86400 540"
    CustomLog "|/usr/sbin/rotatelogs /var/www/mydomain.com/logs/access_%Y%m%d_log 86400 540" combined
    # Redirect everything to document root index.php
    RewriteEngine on
    RewriteRule ^/(favicon.ico) /$1 [L]
    RewriteRule ^/(assets|img|css|js|phpPGAdmin|phpMyAdmin)/(.*) /$1/$2 [L]
    RewriteCond %{REQUEST_URI} !^/?index.php$
    RewriteRule . /var/www/htdocs/mydomain.com/index.php
    # Block PHP easter eggs for extra security
	RewriteCond %{QUERY_STRING} \=PHP[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12} [NC]
	RewriteRule .* - [F]
	# Log
    RewriteLog /var/www/mydomain.com/logs/rewriteLog.log
    RewriteLogLevel 0
    Header set X-ConnecTree hello
    AddOutputFilterByType DEFLATE text/css application/x-javascript
    &lt;Directory /var/www/htdocs/&gt;
        AllowOverride AuthConfig FileInfo Limit Options
    &lt;/Directory&gt;
&lt;/VirtualHost&gt;
</pre>

> Additional apache configurations: disable X-Powered-By header sent from apache for better security and performance
<pre>
ServerSignature Off
ServerTokens Prod
</pre>

> php.ini: disable X-Powered-By header sent by PHP
<pre>
expose_php Off
</pre>
