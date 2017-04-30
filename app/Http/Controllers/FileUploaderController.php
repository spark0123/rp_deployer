<?php
namespace App\Http\Controllers;
use SSH;

class FileUploaderController extends Controller
{

    public function deploy()
    {
    	//get master from github
    	//unzip in local
    	//deploy to sftp
    	//delete local files
    	if (!is_dir('/tmp')) {
		    mkdir('/tmp');
		}


        file_put_contents("/tmp/master.zip", 
		    file_get_contents("https://github.com/spark0123/rp_common_vod/archive/master.zip")
		);

		$zip = new ZipArchive;
		$res = $zip->open('/tmp/master.zip');
		if ($res === TRUE) {
		  $zip->extractTo('/tmp/rp_common_vod');
		  $zip->close();
		  echo 'woot!';
		} else {
		  echo 'doh!';
		}

		// upload file to remote
		SSH::into('production')->put( '/tmp/rp_common_vod', '/448004/sue_test/' );

		rmdir('/tmp/rp_common_vod');

        //SSH::into('production')->run('date', function($line) { echo $line; });
    }
}
