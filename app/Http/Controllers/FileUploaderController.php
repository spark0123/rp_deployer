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
        //delete local files and zip file
        if (!is_dir('/tmp/rp_common_vod')) {
            mkdir('/tmp/rp_common_vod');
        }

        file_put_contents("/tmp/rp_common_vod/master.zip", 
            file_get_contents("https://github.com/spark0123/rp_common_vod/archive/master.zip")
        );

        $zip = new ZipArchive;

        if ($zip->open('/tmp/rp_common_vod/master.zip') === true) {
            for($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $fileinfo = pathinfo($filename);
                copy("zip://".$path."#".$filename, "/your/new/destination/".$fileinfo['basename']);
                SSH::into('production')->put("zip://".$path."#".$filename, 'sue_test');
            }                   
            $zip->close(); 
            return response()->json(['status' => 'success');                  
        }else {
          return response()->json(['status' => 'fail', 'message' => 'unzip failed.']);
        }

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
