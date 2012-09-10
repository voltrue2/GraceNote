<<- About GraceNote Framework ->>
*********************************
What is it?   - PHP Framework
Requires      - PHP (5.1, 5.2, 5.3)
Version       - 1.0
Created       - 2011/01/17
Aurthor       - Nobuyori Takahashi
Last Modified - 2011/08/01
*********************************

<<- GraceNote Framework Concept ->>
*********************************
        URL Request
	     |
+---------------------------+
|  Document Root index.php  | 
+---------------------------+
             |
      +--------------+              
      |  Config.php  | 
      +--------------+     
             |       
    +------------------+           +-------------------+
    |  Main.class.php  |           |   SQL.class.php   |             
    +------------------+           +-------------------+
             |                               |
+---------------------------+      +-------------------+
|   View.class.php          |      |   ORM.class.php   |
+---------------------------+      +-------------------+
             |                               | 
  (This is where you write your own beautiful codes)
+-------------------------------+   +-------------------+     +------------------------------------------+
|  Action Class(es)             | - |  Model Class(es)  | +-- |  Library Class(es)                       |
|  Object Oriented PHP Scripts  |   |  SQL Handling     | |   |  Both External And Internal PHP Objects  |
+-------------------------------+   +-------------------+ |   +------------------------------------------+
             | |                                          |
	     | |                                          |
	     | +------------------------------------------+
	     |
	     |
+---------------------------+     
|   View.class.php          |      
+---------------------------+ 
             |
   	     |           
      (You get to chose wheather to use Smarty or just simple <? PHP ?>/<?= PHP ?> tags)
      +------------+
      |  Template  |
      +------------+
*********************************

<<- URI Translation ->>
*********************************
[Regular Translation]
/list/ --(translates to )--> /list/List.class.php

[Regular Translation]
/list/first/one/two/ --(translates to )--> /list/List.class.php::first(first, one, two) + $QUERIES[0] = list, $QUERIES[1] = first, $QUERIES[2] = one, $QUERIES[3] = two

#Config.php
[$routes translation]
$routes[list] = list/alt
/list/first/one/two/ --(translates to )--> /list/List.class.php::alt(first, one, two) + $QUERIES[0] = list, $QUERIES[1] = first, $QUERIES[2] = one, $QUERIES[3] = two

#Config.php
[$routes translation]
$routes[list] = index
/list/first/one/two/ --(translates to )--> /index/Index.class.php::first(one, two) + $QUERIES[0] = list, $QUERIES[1] = first, $QUERIES[2] = one, $QUERIES[3] = two

#Config.php
[$routes translation]
$routes[list/something] = index
/list/first/one/two/ --(translates to )--> /index/Index.class.php::something(first, one, two) + $QUERIES[0] = list, $QUERIES[1] = first, $QUERIES[2] = one, $QUERIES[3] = two

#Config.php
[$routes translation]
$routes[list/*] = list/alt
/list/one/two/ --(translates to )--> /list/List.class.php::alt(one, two) + $QUERIES[0] = list, $QUERIES[1] = one, $QUERIES[2] = two

#Config.php
# GraceNote Framework supports multibyte URI
[Multibyte Translation]
$routes[日本語] = japanese/method
/日本語/РУСКИЙ/ --(translates to )--> /japanese/Japanese.class.php::method(РУСКИЙ) + $QUERIES[0] = 日本語, $QUERIES[1] = РУСКИЙ
*********************************

<<- ORM(Object Relational Mapping) ->>
*********************************                                 
                                                                              +----------------------+                                                  
                                                                       +----- | ORM Object (DB Table)|    
                                                                       |      +----------------------+
+----------+                    +---------- -+     +--------------+    |      +----------------------+
| Database | ---(Connection)--- | SQL Object | --- | Model Object | ---+----- | ORM Object (DB Table)|
+----------+                    +---------- -+     +--------------+    |      +----------------------+
								       |      +----------------------+
                                                                       +----- | ORM Object (DB Table)|
                                                                              +----------------------+
*********************************
# Config.php
$db[name of your chice][host] = ip address OR host name
$db[name of your chice][type] = mysql OR pgsql
$db[name of your chice][user] = db user
$db[name of your chice][password] = db password
$db[name of your chice][db] = database

Example DB structure
e.i. sample.blah
+----------+-------+
| database | table |
+----------+-------+
| sample   |  blah |
+----------+-------+

$model = new Model('name of your chice'); // You will set this in GraceNote/configs/Congig.php
$blah = $model->table('blah');
# Available methods  
$blah->inflate()     # sets up a reference to inflate data on a chosen table column -> acts like a dynamically created vertual foreign key (select statement only)
$blah->recursive()   # select recursively for foreign keyed items * call this method before find() (select statement only)
$blah->transaction() # starts transaction
$blah->rollback()    # rollback query within transaction
$blah->commit()      # commits query and end transaction
$blah->show()        # show table structure
$blah->find()        # select from the table -> returns multi-dimensional associative array (if the result is one record, it will be single-dimensional associative array)
$blah->find_all()    # select from the table -> returns multi-dimensional associative array (if the result is one record, it will be still multi-dimensional associative array)
$blah->save()        # insert/update
$blah->delete()      # delete row(s)
$blah->create()      # create table
$blah->drop()        # drop table
$blah->get()         # execute raw SQL for select
$blah->send()        # execute raw SQL for insert/update/delete
$blah->escape()      # escapes values for ::cond method e.g. $blah->cond('id = '.$blah->escape(1)); will translate to id = ? and params[0] = 1;
* depricated $blah->cond()        # set conditional clause to the query
$blah->where()       # set conditional clause to the query (where)
$blah->and()	     # set conditional clause to the query (and)
$blah->or()          # set conditional clause to the query (or)
$blah->join()        # joins another table
$blah->order()       # set order by to the query 
$blah->group()       # set group by to the query
$blah->having()      # set having to the query
$blah->limit()       # set limit to the query
$blah->cache()       # set up a flag to use built-in memcache
$blah->cfind(key)    # use custom key for memcache and execute find() -> if the argument key is not provided it will use built-in cache key
$blah->cfind_all(key)# use custom key for memcache and execute find_all -> if the argument key is not provided it will use built-in cache key

# SQL translation
(Column x Find)
$blah->column('blah_id');
$blah->find(5);
--> Translates to SELECT * FROM blah WHERE blah_id = 5;

(Select x Column x Find)
$blah->select('blah_id');
$blah->select('blah_value AS value');
$blah->column('blah_id');
$blah->find(5);
--> Translates to SELECT blah_id, blah_value AS value FROM blah WHERE blah_id = 5;

(Conditional Query)
$blah->select('blah_id AS id');
$blah->where('blah_id = ?', 5); // have a ? to escape
$blah->find();
--> Translates to SELECT blah_id AS id FROM blah WHERE blah_id = ?;

(Conditional Query with multiple escaped values)
$blah->select('*');
$blah->where('blah_id IN (?, ?, ?)', 1, 2, 3);
$blah->find();
--> Translates to SELECT * FROM blah WHERE blah_id IN (?, ?, ?);

(Conditional Query with multiple escaped values)
$blah->select('*');
$blah->where('blah_id = ? AND blah_name = ? AND (occupation = ? OR occupation = ?)', 1, 'my name', 'politician', 'unemployed');
$blah->find();

	OR 

$blah->select('*');
$blah->where('blah_id = ?', 1);
$blah->and(blah_name = ? ', 'my name'); 
$blah->and('(occupation = ? OR occupation = ?)', 'politician', 'unemployed');
$blah->find();
--> Translates to SELECT * FROM blah WHERE blah_id = ? AND blah_name = ? AND (occupation = ? OR occupation);

(Join)
$blah->join('sample');
$blah->where('blah.blah_id = sample.sample_id'); // write the value directly to not to escape
$blah->and('blah.blah_id.id = ?', 5);
$blah->find();
--> Translates to SELECT * FROM blah, sample WHERE blah.blah_id = sample.sample_id AND blah.blah_id = ?;

(Join with condition)
$blah->select('*');
$blah->join('sample', 'left', 'ON blah_id = sample_id');
$blah->where('blah_id = ?', 1);
$blah->find();
--> Translates to SELECT * FROM blah LEFT JOIN sample ON blah_id = sample_id WHERE blah_id = ?;

(Order)
$blah->order('blah_id', 'DESC');
$blah->find();
--> Translates to SELECT * FROM blah ORDER BY blah_id DESC;

(Group)
$blah->select('blah_year');
$blah->group('blah_year');
$blah->find();
--> Translates to SELECT blah_year FROM blah GROUP BY blah_year;

(Having)
$blah->select('blah_name AS name');
$blah->select('COUNT(blah_name) AS name_count');
$blah->where('blah_id (BETWEEN ? AND ?) AND blah_name LIKE %?%', 1, 10, 'blah');
$blah->group('blah_name');
$blah->having('name_count > ?', 10);
$blah->find();
--> Translates to SELECT name, COUNT(blah_name) AS name_count FROM blah WHER blah_id (BETWEEN ? AND ?) AND blah_name LIKE %?% GROUP BY blah_name HAVING name_count > ?;

(Limit)
$blah->limit(0, 10);
--> Translates to SELECT * FROM blah LIMIT 0 OFFSET 10;

(Caching)
$blah->cache();
$blah->where('id = ?', 10);
$result = $blah->find();
--> The SQL result will be cached if there is no cache found for this query. If result for this query is found, the cached data will be returned instead of executing the qurey

$blah->where('id = ?', 10);
$result = $blah->cfind('cacheMe');
--> the cache would be blah:cacheMe and it will execute $blah->find with cache get/set

(IN Operator)
$blah->where('id IN (?, ?, ?)', array(1, 2, 3)); or $blah->where('id IN (?, ?, ?)', 1, 2, 3);
$blah->find();
--> Translates to SELECT * FROM blah WHERE id IN (?, ?, ?);

(Recursive)
+------+----+-------+  +------------+----+-----------+
| blah | id | value |  | child_blah | id | parent_id |
+------+----+-------+  +------------+----+-----------+
|      | 10 | aaaaa |  |            |  1 |     10    |
+------+----+-------+  +------------+----+-----------+
Constrains : foreign_key -> child_blah.parent_id referes to blah.id
$child_blah = $model->table('child_blah');
$child_blah->cond('id = ?', 1);
$child_blah->recursive();
$child_blah->find();
--> Translates to SELECT * FROM child_blah WHERE id = ?; --> SELECT * FROM blah WHERE id = 10; * query per record
Result Array Structure
array (
  [id] => 1
  [parent_id] => array(
	           [id] => 10
		   [value] => 'aaaaa'
		)
)

(Inflate)
$blah->cond('id = ?', 1);
$blah->inflate('blah', 'child_id', 'child_table', 'ref_id'); -> this means child_table.ref_id refers to blah.child_id
$blah->inflate('child_table', 'grand_child_id', 'grand_child_table', 'grand_ref_id'); -> this means grand_child_table.grand_ref_id refers to child_table.grand_child_id
$blah->find();
--> Translates to SELECT * FROM blah; --> SELECT * FROM child_table WHERE ref_if IN (xxxxx) and SELECT * FROM grand_child_table WHERE grand_ref_if IN (xxxxx) 
Result Array Structure
array (
   [id] => 1
   [child_id] => array(
	           [ref_id] => 2
		   [grand_child_id] => array(
					 [grand_ref_id] => 3
				       )
		 )
)

(Insert)
$blah->set('blah_name', 'name 1');
$blah->set('blah_type', 3);
$blah->set('id', 100);
$results = $blah->save();
--> Translates to INSERT INTO blah (blah_name, blah_type, id) VALUES(?, ?, ?);
--> on seuccess: $results = array (
		  		[rows] => 1 (int) // Number of rows affected
				[last_id] => 100 (int) // last id of the row
	       		    )
--> on failure: false

(Update)
$blah->set('blah_name', 'name 2');
$blah->set('blah_type', 4);
$blah->where('id = ?', 100);
$results = $blah->update();
--> Translates to UPDATE blah SET blah_name = ?, blah_type = ? WHERE id = ?;
--> on seuccess: $results = array (
                                [rows] => 1 (int) // Number of rows affected
                                [last_id] => 100 (int) // last id of the row
                            )   
--> on failure: false

(Delete)
$blah->where('id = ?', 100);
$results = $blah->delete();
--> Translates to DELETE FROM blah WHERE id = ?;
--> on seuccess: $results = array (
                                [rows] => 1 (int) // Number of rows affected
                                [last_id] => 100 (int) // last id of the row
                            )
--> on failure: false 

(Add Column)
$blah->set_column('new_column', 'int', 11, false, false); // column name, field type, field size, default value, primary key
$blah->add();
--> Translates to ALTER TABLE blah ADD COLUMN new_column int(11);

(Rename Column)
$blah->rename_column('olb_column_name', 'new_column_name');
--> Translates to postgreSQL: ALTER TABLE blah RENAME COLUMN old_column_name TO new_column_name;
--> Translates to MySQL:      ALTER TABLE blah CHANGE old_column_name new_column_name varchar(255); // Uses the old column's attributes

(Change Column TYPE)
$blah->change_data_type('new_column_name', 'int');
--> Translates to ALTER TABLE blah ALTER COLUMN new_column_name TYPE int;

(Drop Column)
$blah->set_column('new_column_name');
$blah->remove();
--> Translates to ALTER TABLE blah DROP COLUMN new_column_name CASCADE;

(Create Table)
$blah->set_auto_increment_column('id', 11, true); // column name, field size, primary key
$blah->set_column('name', 'varchar',  255, 'default string', false); // column name, field type, field size, default value, primary key
* MySQL Only $blah->create('InnoDB'); or $blah->create('MyISAM'); // by default it is InnoDB
$blah->create();
--> Translates to postgreSQL: CREATE TABLE blah (id SERIAL(11) PRIMARY, name VARCHAR(255) NOT NULL DEFAULT 'default string');
--> Translates to MySQL:      CREATE TABLE blah (id INT(11) NOT NULL AUTO_INCREMENT, name VARCHAR(255) NOT NULL DEFAULT, 'default string')ENGINE=InnoDB or MyISAM;

(Drop Table)
$blah->drop();
--> Translates to DROP TABLE blah;

*********************************

<<- Apache WrtieRule for GraceNote ->>
*********************************
# Redirect everything to document root index.php
RewriteEngine on
RewriteRule ^/(favicon.ico) /$1 [L]
RewriteRule ^/(img|css|js)/(.*) /$1/$2 [L]
RewriteCond %{REQUEST_URI} !^/?index.php$
RewriteRule . /var/www/domain/htdocs/index.php
# Block Hotlinking
RewriteEngine On
RewriteCond %{HTTP_REFERER} !^http://(www\.)?domain\.com(/.*)*$ [NC]
RewriteCond %{HTTP_REFERER} !^$
RewriteRule \.(jpe?g|gif|png|js|JPE?G|GIF|PNG|JS)$ - [F]

#
# Use name-based virtual hosting.
#
NameVirtualHost *:80

## Site Admin
<VirtualHost  *:80>
    DocumentRoot /var/www/htdocs
    ServerName your.domain.com
    ErrorLog "|/usr/sbin/rotatelogs /var/www/your.application/logs/error_%Y%m%d_log 86400 540"
    CustomLog "|/usr/sbin/rotatelogs /var/www/your.application/logs/access_%Y%m%d_log 86400 540" combined

    # Redirect everything to document root index.php
    RewriteEngine on
    RewriteRule ^/(favicon.ico) /$1 [L]
    RewriteRule ^/(img|css|js|phpPGAdmin|phpMyAdmin)/(.*) /$1/$2 [L]
    RewriteCond %{REQUEST_URI} !^/?index.php$
    RewriteRule . /var/www/htdocs/your.application/index.php
     # Block Hotlinking
    RewriteEngine On
    RewriteCond %{HTTP_REFERER} !^http://(www\.)?your.domain\.com(/.*)*$ [NC]
    RewriteCond %{HTTP_REFERER} !^$
    RewriteRule \.(jpe?g|gif|png|js|JPE?G|GIF|PNG|JS)$ - [F]
    # Log
    RewriteLog /var/www/your.application/logs/rewriteLog.log
    RewriteLogLevel 0

    Header set X-ConnecTree hello
    AddOutputFilterByType DEFLATE text/css application/x-javascript
    # <IfModule mod_cache.c>
        # <IfModule mod_disk_cache.c>
           # CacheRoot /usr/local/www/cache
           # CacheEnable disk /img/header
           # CacheEnable disk /backnumbers
           # CacheDirLevels 5
           # CacheDirLength 3
           # CacheIgnoreCacheControl On
           # CacheMaxFileSize 256000
           # CacheMinFileSize 1
        # </IfModule>
    # </IfModule>

    <Directory /backnumbers/>
        Header unset Expires
    </Directory>

    #CacheEnable mem /img
    #MCacheRemovalAlgorithm LRU
    #MCacheSize 16384
    #MCacheMaxObjectCount 100
    #MCacheMinObjectSize 1
    #MCacheMaxObjectSize 256
    <Directory /var/www/htdocs/>
        AllowOverride AuthConfig FileInfo Limit Options
    </Directory>
</VirtualHost>

*********************************

<<- php.ini ->>
*********************************
short_open_tag = On
*********************************

<<- Development ->>
*********************************
# How to create a page 
1. Create a directory under actions.
2. Name the directory to be the URI e.i. actions/firstpage/ would be http://domain/firstpage/
3. Create a php class file under the directory you created and name the file to be the same as directory (case insensitive) e.i. /actions/firstpage/firstpage.class.php or /FirstPage.class.php etc
4. Create a class with same name as the file name.
5. The class constructor will have one argument coming in e.i. public function FirstPage($view). --> $view this object will be the Framework core. 
6. create a method called init(configurable in configs/Config.php). This method will become the default method to be called for http://domain/firstpage/ -- ( this means the same as ) --> http://domain/firstpage/init/

# $view Object passed to action classes
- Avaiable Methods of View
* The method with have arguments passed from URI e.i. domain.com/page_class/method_name/argument1/argument2.. will be public function method_name(argument1, argument2...)
::trace(variable) -----------------------------------> Prints the variable(string, number, array, object) to either browser or in the error log. Controlled by debug flag in Config.php 
::error(variable) -----------------------------------> Prints the variable(string, number, array, object) to either browser or in the error log with *** Error. Controlled by debug flag in Config.php
::assign(key, value) --------------------------------> Assigns variable with the key provided to be used in a template.
::get(key) ------------------------------------------> Gets GET/POST/URI PARAMETERS AS QUERIES queries or ::assigned variable wit the mathcing key. * GET/POST/URI PARAMETERS AS QUERIES queries are under ::get('QUERIES'):Array
::queries(key) --------------------------------------> Gets GET/POST/URI PARAMETERS AS QUERIES with matching key
::redirect(URL path) --------------------------------> Redirects with header 301.
::get_session(key) ----------------------------------> Gets session variable(s) with the matching key.
::set_session(key, value) ---------------------------> Sets session variable with the key provided.
::get_cookie(key) -----------------------------------> Gets cookie variable(s) with the matching key.
::set_cookie(key, value) ----------------------------> Sets cookie variable with the key provided.
::contents(table name, alternate key) ---------------> Gets multilingual data contents data from DB table of DBF (Usually used to get text contents for pages). * The data will be under $CONTETNS in a template unless alternate key is provided to be otherwise.
::list_contents(table name, alternate key, get key) -> Gets multilingual list data contents data from DB table of DBF (Usually used to get text contents for pages). * The data will be under $CONTETNS in a template unless alternate key is provided to be otherwise.
::return_error(error code); -------------------------> displays error page along with error code header
::push(output data type, key) -----------------------> Outputs ::get data in a given format (json, xml, php serialize). 
::fetch(template path) ------------------------------> Constructs output data with the template given. (this method will not display the page).
::display(template path) ----------------------------> Displays the page with template data. 

# Load Object to include other files
- All methods of this class are called statically
* path example 'directory/file' No file extension needed e.i. Load::lib('XML'); will include an XML class
::core(path) -------------> includes a core file
::lib(path)  -------------> includes a lib file
::action(path) -----------> includes an action file
::model(path) ------------> includes a model file
::template(path) ---------> includes a template file

# Bacth.php
- Usage for this script * The file extension needs to be the same as other scripts of GN
> Write your own cron scripts and place them under batch/
> Set up your cron in crontab
> e.i. * * * * * /usr/bin/php /GraceNote/core/Batch.php [target] [object::method/function]
> Avaiable arguments
-> target(Required) Name of your cron script to run
-> object::method(Optional) or function Name of your object::Name of your public method e.i. Test::pester or function e.i. myfunction

# /GrageNote/batch/Migrate.class.php
- User for this script: Creates DB tables for GraceNote as defined in /configs/Migration.php
- Execute this script to create DB tables * Tables that exist will be ignored
* The syntax and rules to define DB tables in /configs/Migration.php
$tables[table name] = array(
	column => column type, ...
);
$metadata[table name][category] = category name;
$metadata[table name][description] = text for table description
