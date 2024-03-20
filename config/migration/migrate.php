<?php
require_once 'Migration.php';
require_once __DIR__.'/../database.php';

// Check connection
$db = pg_connect("host=".Db::$dbHost." dbname=postgres user=".Db::$dbUser." password=".Db::$dbPass);

if (!$db) {
    die("Connection failed: " . pg_last_error($db));
}

// Create the database if it doesn't exist
$migration = new Migration($db);

if (!$migration->databaseExists(Db::$dbName)) {
    echo 'wadidadw';
    $migration->createDatabase(Db::$dbName);
} else {
    echo "Database already exists.";
}

// Close connection to PostgreSQL server
pg_close($db);

// Reconnect to the PostgreSQL server, this time with the database specified
$db = pg_connect("host=".Db::$dbHost." dbname=".Db::$dbName." user=".Db::$dbUser." password=".Db::$dbPass);

if (!$db) {
    die("Connection failed: " . pg_last_error($db));
}

// Create Mails
$migration = new Migration($db);

if (!$migration->tableExists('mails')) {
    $migration->createMailsTable();
} else {
    echo "Table Mails already exists.";
}

// Close database connection
pg_close($db);
?>
