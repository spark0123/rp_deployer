<?php
namespace App\Http\Controllers;
use ZipArchive;
use SSH;

class FileUploaderController extends Controller
{


    public function deploy()
    {
    	//get master from github
    	//unzip in local
    	//deploy to sftp
    	//delete local files
    	if (!is_dir('/tmp/rp_common_vod')) {
		    mkdir('/tmp/rp_common_vod');
		}


        file_put_contents("/tmp/rp_common_vod/master.zip", 
		    file_get_contents("https://github.com/spark0123/rp_common_vod/archive/master.zip")
		);

		$zip = new ZipArchive;
		$res = $zip->open('/tmp/rp_common_vod/master.zip');
		if ($res === TRUE) {
		  $zip->extractTo('/tmp/rp_common_vod');
		  $zip->close();
		} else {
		  echo 'unzip failed';
		}

		// upload file to remote
		SSH::into('production')->put( '/tmp/rp_common_vod/rp_common_vod-master', '/448004/sue_test/' );

		$this->deleteDirectory('/tmp/rp_common_vod');

        //SSH::into('production')->run('date', function($line) { echo $line; });
    }
    private function deleteDirectory($dir) {
	    if (!file_exists($dir)) {
	        return true;
	    }

	    if (!is_dir($dir)) {
	        return unlink($dir);
	    }

	    foreach (scandir($dir) as $item) {
	        if ($item == '.' || $item == '..') {
	            continue;
	        }

	        if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
	            return false;
	        }

	    }

	    return rmdir($dir);
	}
}
