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
        /*if (!is_dir('/tmp/rp_common_vod')) {
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
        }*/

        // upload files to remote
        /*SSH::into('production')->run(array(
            'put -r /tmp/rp_common_vod/rp_common_vod-master/* sue_test/'
        ), function($line)
        {
            return response()->json(['status' => 'success', 'message' => $line.PHP_EOL]);
        });*/

        //$ssh_msg = SSH::into('production')->exists( '/448004/sue_test/test2.txt');
        //return response()->json(['status' => 'success', 'message' => $ssh_msg]);

        $ssh_msg = SSH::into('production')->put('/tmp/rp_common_vod/rp_common_vod-master/css/rational-cc-panel.css', '/');
        return response()->json(['status' => 'success', 'message' => $ssh_msg]);
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
