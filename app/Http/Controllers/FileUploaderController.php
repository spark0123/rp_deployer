<?php
namespace App\Http\Controllers;
use ZipArchive;
use SSH;
use Log;

class FileUploaderController extends Controller
{
    public function deploy()
    {
        //get rp_common_vod master from github
        //unzip in local
        //deploy to sftp
        //delete local files and zip files
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
          return response()->json(['status' => 'fail', 'message' => 'unzip failed.']);
        }

        $local_directory = "/tmp/rp_common_vod/rp_common_vod-master/";
        $remote_directory = "/448004/sue_test/"
        /* We save all the filenames in the following array */
        $files_to_upload = array();
         
        /* Open the local directory form where you want to upload the files */
        if ($handle = opendir($local_directory)) 
        {
            /* This is the correct way to loop over the directory. */
            while (false !== ($file = readdir($handle))) 
            {
                if ($file != "." && $file != "..") 
                {
                    $files_to_upload[] = $file;
                }
            }
         
            closedir($handle);
        }
         
        if(!empty($files_to_upload))
        {
            /* Now upload all the files to the remote server */
            foreach($files_to_upload as $file)
            {
                  /* Upload the local file to the remote server */

                  $success = SSH::into('production')->put($local_directory . $file, $remote_directory . $file);
            }
        }
        

        if($success)
            return response()->json(['status' => 'success']);
        else
            return response()->json(['status' => 'fail']);

        //$this->deleteDirectory('/tmp/rp_common_vod');
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
