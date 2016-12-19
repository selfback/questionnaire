<?php
    include("../api/service.php");

    if(! isset($_SESSION['user_id']) && ! isset($_SESSION['tabExport'])){
        header('location:../index.php');
        exit();
    }

    $filename = "data_export_" . date("Y-m-d") . ".csv";
    $fp = fopen('php://output', 'w');

    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    header('Content-type: application/csv');
    header('Content-Disposition: attachment; filename='.$filename);

    fputcsv($fp, array_keys(reset($_SESSION['tabExport'])));

    foreach($_SESSION['tabExport'] as $row){
        fputcsv($fp, $row);
    }

    log_message("Admin get file ".$filename);

    exit();
?>