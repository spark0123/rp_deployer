<?php
/**
 * Created by sue.park on 5/1/17.
 */
namespace App\Http\Controllers;
use ZipArchive;
use SSH;
use Log;
use Illuminate\Http\Request;
ini_set('max_execution_time', 180);
define('NET_SSH2_LOGGING', 3);
class FileUploaderController extends Controller
{
 
    public function testDeploy(Request $request)
    {
        $data = $request->json()->all();
        $local = '/tmp/test/css/test.css';
        $remote = '/448004/sue_test/test/css/test.css';
       // SSH::into('production')->put($local,$remote);
        $sftp = SSH::into('production');
        $sftp->putSpring('test',$remote);
    }

    public function deployPlayerCommonPlugin(Request $request)
    {
        $data = $request->json()->all();
        if($data['ref'] === 'refs/heads/master'){
            $local_folder_name = 'rp_common_plugin';
            $remote_directory = env('STAGE_FTP_ROOT', '').$local_folder_name;
            $repo_name = 'player.common.plugin';
            $tag = 'master';
            $uploaded = $this->deploy($local_folder_name,$remote_directory,$repo_name,$tag,'stage');
            if(count($uploaded) > 0)
                return response()->json(['status' => 'success','message' => $uploaded]);
            else
                return response()->json(['status' => 'fail']);
        }else{
            return response()->json(['status' => 'success','message' => 'ignore '.$data['ref']]);
        } 
    }

    public function deployPlayerCommonPluginProd(Request $request)
    {
        $data = $request->json()->all();

        $local_folder_name = 'rp_common_plugin';
        $remote_directory = env('FTP_ROOT', '').$local_folder_name;
        $repo_name = 'player.common.plugin';
        $tag = $data['release']['tag_name'];

        $uploaded = $this->deploy($local_folder_name,$remote_directory,$repo_name,$tag,'production');
        if(count($uploaded) > 0)
            return response()->json(['status' => 'success','message' => $uploaded]);
        else
            return response()->json(['status' => 'fail']);
        
    }


    public function deployPlayerCommonVOD(Request $request)
    {
        $data = $request->json()->all();
        if($data['ref'] === 'refs/heads/master'){
            $local_folder_name = 'rp_common_vod';
            $remote_directory = env('STAGE_FTP_ROOT', '').$local_folder_name;
            $repo_name = 'player.common.vod';
            $tag = 'master';
            $uploaded = $this->deploy($local_folder_name,$remote_directory,$repo_name,$tag,'stage');

            if(count($uploaded))
                return response()->json(['status' => 'success','message' => $uploaded]);
            else
                return response()->json(['status' => 'fail']);  
        }else{
            return response()->json(['status' => 'success','message' => 'ignore '.$data['ref']]);
        }
    }

    public function deployPlayerCommonVODProd(Request $request)
    {
        $data = $request->json()->all();
        $tag = $data['release']['tag_name'];
        $prerelease = $data['release']['prerelease'];

        $ftp_env = 'production';
        $local_folder_name = 'rp_common_vod';
        $remote_directory = env('FTP_ROOT', '').'player' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'vod' . DIRECTORY_SEPARATOR . str_replace('RP','',$tag);
        if($prerelease){
           $remote_directory = env('STAGE_FTP_ROOT', '').'player' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'vod' . DIRECTORY_SEPARATOR . str_replace('RP','',$tag); 
           $ftp_env = 'stage';
        }
        $repo_name = 'player.common.vod';
         
        $uploaded = $this->deploy($local_folder_name,$remote_directory,$repo_name,$tag,$ftp_env);

        if(count($uploaded))
            return response()->json(['status' => 'success','message' => $uploaded]);
        else
            return response()->json(['status' => 'fail','message' => 'nothing to upload']);  

    }

    private function deploy($local_folder_name, $remote_directory, $repo_name, $tag ,$ftp_env ){
        //get master from github
        //unzip in local
        //deploy to sftp
        //delete local files
        if (!is_dir('/tmp/'.$local_folder_name)) {
            mkdir('/tmp/'.$local_folder_name);
        }

        exec('cd /tmp/'.$local_folder_name.'; wget --header="Authorization: token '.env('GITHUB_TOKEN', '').'" -O - \
    https://api.github.com/repos/NBCU-PAVE/'.$repo_name.'/tarball/'.$tag.' | \
    tar xz --strip-components=1',$output);
        $local_directory = "/tmp/".$local_folder_name;

        $uploaded = $this->uploadAll($local_directory,$remote_directory, $ftp_env);

        $this->deleteDirectory('/tmp/'.$local_folder_name);

        return $uploaded;
    }

    private function getDirContents($dir, &$results = array()){
        $files = scandir($dir);

        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                $results[] = $path;
            } else if($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    } 

    private function uploadAll($local_directory, $remote_directory, $ftp_env){
        /* We save all the filenames in the following array */
        $files_to_upload = array();
        $files_uploaded = array(); 
         
        $files_to_upload = $this->getDirContents($local_directory);

        if(!empty($files_to_upload))
        {
            /* Now upload all the files to the remote server */
            foreach($files_to_upload as $file)
            {
                  /* Upload the local file to the remote server 
                     put('remote file', 'local file');
                   */
                    $local = $file;
                    $remote = str_replace($local_directory,$remote_directory, $file);
                    SSH::into($ftp_env)->put($local,$remote);
                    $files_uploaded[] = $remote;
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
