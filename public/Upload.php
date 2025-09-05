<?php
$targetDir = __DIR__ . "/uploads/";
if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

if (isset($_FILES["file"])) {
    $fileName = time() . "_" . basename($_FILES["file"]["name"]);
    $targetFile = $targetDir . $fileName;
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        echo json_encode(["success" => true, "url" => "/uploads/" . $fileName]);
    } else {
        echo json_encode(["success" => false]);
    }
}
