## Migrations Management Tools
    @see https://github.com/zircote/Akrabat
To get the Zend_Tool provider working:

1. If you haven't already done so, setup the storage directory and config file:
    
        zf --setup storage-directory
        zf --setup config-file
        
2. Edit the created `~/.zf.ini`. Change path so that it includes `Zend` and `Ifbyphone`, allow for auotoloading set up the provider:
    
        php.include_path = "/usr/local/zend/share/pear:<path to the Ifbyphone library>"
        autoloadernamespaces.0 = "Ifbyphone_"
        basicloader.classes.0 = "Ifbyphone_Tool_DatabaseSchemaProvider"

6. `zf` should provide a help screen with the `DatabaseSchema` provider at the bottom.

### Ifbyphone_Db_Schema_Manager

1. Create scripts/migrations folder in your ZF application

2. Create migration files within migrations with the file name format of nnn-Xxxx.php. e.g. 001-Users.php
    where:  
       nnn => any number. The lower numbered files are executed first  
       Xxx => any name. This is the class name within the file.

2.1 Alternatively you may generate a migrations template by performing the following:

    zf create-migration database-schema {MigrationClassName}
    
3. Create a class in your migrations file. Example for 001-Users.php:

```php
<?php
class Users extends Ifbyphone_Db_Schema_AbstractChange 
{
    function up()
    {
        $tableName = $this->_tablePrefix . 'users';
        $sql = "
            CREATE TABLE IF NOT EXISTS $tableName (
              id int(11) NOT NULL AUTO_INCREMENT,
              username varchar(50) NOT NULL,
              password varchar(75) NOT NULL,
              roles varchar(200) NOT NULL DEFAULT 'user',
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->_db->query($sql);

        $data = array();
        $data['username'] = 'admin';
        $data['password'] = sha1('password');
        $data['roles'] = 'user,admin';
        $this->_db->insert($tableName, $data);
    }
    
    function down()
    {
        $tableName = $this->_tablePrefix . 'users';
        $sql= "DROP TABLE IF EXISTS $tableName";
        $this->_db->query($sql);
    }

}
```    
4. If you want a table prefix, add this to your `application.ini`:

        resources.db.table_prefix = "prefix"

