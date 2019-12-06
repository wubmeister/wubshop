# Database

## Layout

The database classes represent the layout of a relational database. There are 4 main classes:

- **Connection** Represents the connection to a database server.
- **Schema** Represents a schema a.k.a. database on the database server.
- **Table** Represents a table in a schema (database).
- **Row** Represents a row in the table.
- **ResultSet** When requesting multiple rows from a table, they will be contained by a result set. Result sets will be explained later on.

## Making a Connection

To make a connection, you create a Connection instance:

```
<?php

use Lib\Db\Connection;

$conn = new Connection([
	"adapter" => "mysql",
	"host" => "127.0.0.1",
	"username" => "mysql_user",
	"password" => "super_secret_password_01"
]);
```

As you can see, the Connection constructor takes an associative array as single parameter. This array should contain all the needed information to connect to the database. What information is needed depends on the adapter you use. The database connection will use PDO to establish a connection, so generally you would pass:

- The adapter name, like "mysql"
- All the variables in the DSN string as key/value pairs
- The username and password if needed

## Selecting a database

Note that making a connection does not require you to pass a database name. That is because you should be able to use multiple databases through one single connection. This can be useful if you use two or more databases which all reside on the same database server. To select a database, you should request a Schema object from the connection. This can be done via the 'schema' method:

```
$schema = $conn->schema("my_database");
```

This selects the database 'my_database' from the connection. Everything you need to access in this database should be accessed through this $schema object.

## Tables

Most of the time you want to interact with tables instead of databases. You can select a table from the database you just selected in a similar way:

```
$table = $schema->table("my_table");
```

### Listing rows in a table

Fetching rows from a table is mainly done through the method 'find'. Let's look at some examples:

```
$results = $table->find();
```

This is the most basic form and will just fetch all the rows.
You can also provide a simple search query to 'find' to fetch more specific results:

```
$results = $table->find([ "parent_id" => 12 ]);
```

This will fetch all the rows for which the column 'parent_id' is set to 12.

### Result sets

The 'find' method will not just return an array of rows. Instead, it returns a ResultSet object. A result set initially contains no fetched data. This is because you might want to further filter and limit your results. Only when you start accessing rows on the result set, the result set will fetch all the results.

These are the most common actions to perform on a result set:

```
$results->filter([ "column" => "value" ]); // Filters the result set in the same way as $table->find() would
$results->order("column");                 // Orders the result on a column
$results->order([ "column" => "asc|desc", ... ]);
$results->limit(20);                       // Limits the number of returned rows
$results->offset(60);                      // Begins fetching at a specific offset
$results->paginate($currentPage, $itemsPerPage);
```

You can treat a result set as an array of rows, for example by looping throug it:

```
foreach ($results as $row) {
    echo $row->title . "<br/>";
}
```

Note that when you do this, the results are fetched and the filtering methods described above will yield unpredicted results from here on. You can also get the entire array of rows at once and work with a native array by using 'getAll()':

```
$rows = $results->getAll();
foreach ($rows as $row) {
    echo $row->title . "<br/>";
}
```

### Fetching a single row

'find()'s little brother is called 'findOne()'. It acts in the same way as find() does (it can also take a filter parameter), except that it returns a single row instead of a result set.

```
$row = $table->findOne();               // Gets the first row in the table
$row = $table->findOne([ "id" => 42 ]); // Gets the row with ID 42
$row = $table->findOne([ "list_id" => 12, "email" => "account@domain.com" ]); // You get the drift
```

### Inserting and updating

You can insert and update rows with respectively 'insert()' and 'update()'. They both take an associative array of key/value pairs to set the column values. The update() method additionally takes a paramter with filter conditions to find the row(s) to update. This is the same format as the filters you would pass to find() or findOne().

```
$table->insert([ "foo" => "bar", "lorem" => 42 ]);
$table->update([ "foo" => "bar", "lorem" => 42 ], [ "id" => 23" ]);
```

## Rows

Rows are fetched as objects. You can access column values as object properties:

```
echo $row->title; // Will echo the value for the 'title' column.
$row->title = "New title"; // Will set the value for the 'title' column.
```

To save (either insert or update) a row after you set some values on the columns, just call:

```
$row->save();
```
