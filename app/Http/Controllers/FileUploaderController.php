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
        $remote_directory = "/448004/sue_test/";
        
        $uploaded = $this->uploadAll($local_directory,$remote_directory);

        if(count($uploaded))
            return response()->json(['status' => 'success','message' => $uploaded]);
        else
            return response()->json(['status' => 'fail']);

        $this->deleteDirectory('/tmp/rp_common_vod');
    }

    public function dirToArray($dir) { 
   
       $result = array(); 

       $cdir = scandir($dir); 
       foreach ($cdir as $key => $value) 
       { 
          if (!in_array($value,array(".",".."))) 
          { 
             if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) 
             { 
                $result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value); 
             } 
             else 
             { 
                $result[] = $value; 
             } 
          } 
       } 
       
       return $result; 
    } 

    private function uploadAll($local_directory, $remote_directory){
        /* We save all the filenames in the following array */
        $files_to_upload = $this->dirToArray($local_directory);
        $files_uploaded = array(); 

        if(!empty($files_to_upload))
        {
            /* Now upload all the files to the remote server */
            foreach($files_to_upload as $key => $files)
            {
                  /* Upload the local file to the remote server */
                  if(!empty($key)){
                        foreach ($files as $file) {
                            $local = $local_directory . $key .'/' . $file;
                            $remote = $remote_directory . $key .'/' . $file;
                            SSH::into('production')->put($local,$remote);
                            $files_uploaded[] = $remote;
                        }
                  }
            }
        }
        return $files_uploaded;
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
