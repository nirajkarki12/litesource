<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

try {
    
    $MYSQL_USER='litesource_app';
    $MYSQL_PASSWORD='E603@7xf0?vI2@7EYRGT5454rtdSbtO9bvsuzb656343vdD';
    $DATABASE_NAME='tt_litesource_invoice';

    $file_limit = 45;
    $folder_id = "1N2JPx27Tu5lSXYJD9XZC74JzUGB07nNj";
    $do_bkp_dir = "/var/www/html/do_backup/";
    $do_temp_bkp_dir = $do_bkp_dir."temp_backup/";
    $sql_filename = $DATABASE_NAME .'-'. date("Y-m-d");

    $cmd = "mysqldump -u {$MYSQL_USER} --password={$MYSQL_PASSWORD} {$DATABASE_NAME} > {$do_temp_bkp_dir}{$sql_filename} &&  cd {$do_temp_bkp_dir} &&  gzip {$sql_filename}";
    echo (" --------{$cmd}----- ");
    exec($cmd);
    sleep(30);


    echo ' ---Start google Clients--- ';
    $client = new Google_Client();
    $client->setApplicationName('Google Drive API PHP Quickstart');
    $client->setScopes(Google_Service_Drive::DRIVE);
    $client->setAuthConfig($do_bkp_dir.'litesource-355006-b83baee584dd.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    $service = new Google_Service_Drive($client);

    $files = $service->files->listFiles([
        'q' => '"'.$folder_id.'" in parents', // optionnal
        'pageSize' => 100, // 1 to 1000
        'fields' => 'nextPageToken, files(id, name, mimeType, parents)'
    ]);

    if ($files) {
        echo ' ---Google Drive has this folder access--- ';

        //delete unwanted files
        if( isset($files->files) ){
            if( count($files->files) > $file_limit ){
                $temp_file_limit = 1;
                foreach($files->files as $dlt_file){
                    if( ( $dlt_file->size != 0 ) && ($temp_file_limit > $file_limit) ){
                        $emptyFile = new Google_Service_Drive_DriveFile();
                        $removed_file = $service->files->update($dlt_file->id, $emptyFile, array(
                            'data' => '',
                            'uploadType' => 'multipart'
                        ));
                        if( isset($removed_file->name) ){
                            echo " ---Removed File: ".$dlt_file->namel."--- <br>";
                        }
                    }
                    $temp_file_limit++;
                }
            }
        }

        $bkp_files = array_diff(scandir($do_temp_bkp_dir, SCANDIR_SORT_DESCENDING), array('.', '..'));
        if ($bkp_files != null) {
            $this_file = $bkp_files[0];
            if ($this_file != '') {
                echo ' ---Backup file exists--- ';
                $file = new Google_Service_Drive_DriveFile();
                $file->setName($this_file);
                $file->setParents([$folder_id]);
                $result = $service->files->create(
                    $file,
                    array(
                        'data' => file_get_contents($do_temp_bkp_dir . $this_file),
                        'mimeType' => 'application/octet-stream',
                    )
                );
                if (isset($result['id']) && ($result['id'] != '')) {
                    echo ' ---File Uploaded to Google Drive--- ';
                    unlink($do_temp_bkp_dir . $this_file);
                    echo ' ---Successfully deleted.--- ';
                }
            }
        }
    }
    exit('ok');
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
