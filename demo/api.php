
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/PHPSchema.php';
$PHPSchema = new PHPSchema();

header('Content-Type: application/json');

if($_POST['endpoint'] == 'example-1')
{
    echo $PHPSchema->check($_POST, []);
}
else if($_POST['endpoint'] == 'example-2')
{
    echo $PHPSchema->check($_POST, []);
}

?>