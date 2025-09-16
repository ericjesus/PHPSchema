
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../src/PHPSchema.php';
$PHPSchema = new PHPSchema();

if($_POST['endpoint'] == 'example-1')
{
    $validation = $PHPSchema->check($_POST, SchemaSample::Product, false);
}
else if($_POST['endpoint'] == 'example-2')
{
    $validation = $PHPSchema->check($_POST, SchemaSample::Products, false);
}

echo json_encode($validation, true);
