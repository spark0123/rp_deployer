<?php
/**
 * Created by sue.park on 5/1/17.
 */
namespace App\Http\Controllers;
use ZipArchive;
use SSH;
use Log;
use Illuminate\Http\Request;
use App\Notifications\ResourceDeployed;
use App\PlayerDeployer;
use Illuminate\Support\Facades\Storage;

ini_set('max_execution_time', 180);
define('NET_SSH2_LOGGING', 3);
class FileUploaderController extends Controller
{
    public function deployPlayerCommonPluginStage(Request $request)
    {
        
        $data = $request->json()->all();
        if($data['ref'] && $data['ref'] === 'refs/heads/master'){ //only deploy if master branch
            $local_folder_name = 'rp_common_plugin';
            $remote_directory = env('STAGE_FTP_ROOT', '').'player' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'plugin';
            $repo_name = 'player.common.plugin';
            $tag = 'master';
            $uploaded = $this->deploy($local_folder_name,$remote_directory,$repo_name,$tag,'stage');
            if(count($uploaded) > 0){
                $playerDeployer = new PlayerDeployer();
                $playerDeployer->notify(new ResourceDeployed($repo_name,$remote_directory));
                return response()->json(['status' => 'success','message' => $uploaded]);
            }
            else
                return response()->json(['status' => 'fail']);
        }

        if($data['ref'] && $data['ref'] === 'refs/heads/dev'){

            $local_folder_name = 'rp_common_plugin';
            $remote_directory = env('DEV_FTP_ROOT', '').'player' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'plugin';
            $repo_name = 'player.common.plugin';
            $tag = 'dev';
            $uploaded = $this->deploy($local_folder_name,$remote_directory,$repo_name,$tag,'dev');
            if(count($uploaded) > 0){
                $playerDeployer = new PlayerDeployer();
                $playerDeployer->notify(new ResourceDeployed($repo_name,$remote_directory));
                return response()->json(['status' => 'success','message' => $uploaded]);
            }
            else
                return response()->json(['status' => 'fail']);
        }


        return response()->json(['status' => 'success','message' => 'skipping deployment.']); 
    }

    public function deployPlayerCommonPluginProd(Request $request)
    {
        $data = $request->json()->all();

        $local_folder_name = 'rp_common_plugin';
        $remote_directory = env('FTP_ROOT', '').'player' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'plugin';
        $repo_name = 'player.common.plugin';
        $tag = '';
        if($data['release'] && $data['release']['tag_name']){
            $tag = $data['release']['tag_name'];
        }else{
            return response()->json(['status' => 'fail','message' => 'tag not found.']);
        }

        $uploaded = $this->deploy($local_folder_name,$remote_directory,$repo_name,$tag,'production');
        if(count($uploaded) > 0){
            $playerDeployer = new PlayerDeployer();
                $playerDeployer->notify(new ResourceDeployed($repo_name,$remote_directory));
            return response()->json(['status' => 'success','message' => $uploaded]);
        }
        else
            return response()->json(['status' => 'fail']);
        
    }


    public function deployPlayerCommonVODStage(Request $request)
    {
        $data = $request->json()->all();
        if($data['ref'] === 'refs/heads/master'){
            $local_folder_name = 'rp_common_vod';
            $remote_directory = env('STAGE_FTP_ROOT', '').'player' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'vod' . DIRECTORY_SEPARATOR . 'master'; 
            $repo_name = 'player.common.vod';
            $tag = 'master';
            $uploaded = $this->deploy($local_folder_name,$remote_directory,$repo_name,$tag,'stage');

            if(count($uploaded)){
                $playerDeployer = new PlayerDeployer();
                $playerDeployer->notify(new ResourceDeployed($repo_name,$remote_directory));
                return response()->json(['status' => 'success','message' => $uploaded]);
            }
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
        if(isset($data['release']['prerelease']) && $data['release']['prerelease']){
           return response()->json(['status' => 'success','message' => 'skip prerelease.']); 
        }
        $ftp_env = 'production';
        $local_folder_name = 'rp_common_vod';
        $remote_directory = env('FTP_ROOT', '').'player' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'vod' . DIRECTORY_SEPARATOR . str_replace('RP','',$tag); 
        
        $repo_name = 'player.common.vod';
         
        $uploaded = $this->deploy($local_folder_name,$remote_directory,$repo_name,$tag,$ftp_env);

        if(count($uploaded)){
            $playerDeployer = new PlayerDeployer();
                $playerDeployer->notify(new ResourceDeployed($repo_name,$remote_directory));
            return response()->json(['status' => 'success','message' => $uploaded]);
        }
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

        if($ftp_env == 'dev'){
            $uploaded = $this->uploadAllFTP($local_directory,$remote_directory, $ftp_env);
        }else{
            $uploaded = $this->uploadAll($local_directory,$remote_directory, $ftp_env);
        }

        $this->deleteDirectory('/tmp/'.$local_folder_name);

        return $uploaded;
    }

    private function getDirContents($dir, &$results = array()){
        $files = scandir($dir);

        foreach($files as $key => $value){
            if($value === 'README.md') //skip README.md
                continue;
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path)) {
                $results[] = $path;
            } else if($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
                //$results[] = $path;
            }
        }

        return $results;
    } 

    private function uploadAllFTP($local_directory, $remote_directory, $ftp_env){
        /* We save all the filenames in the following array */
        $files_to_upload = array();
        $files_uploaded = array(); 
         
        $files_to_upload = $this->getDirContents($local_directory);
        $ftp = Storage::disk($ftp_env.'_ftp');
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
                    $ftp->put($remote, $local);
                    $files_uploaded[] = $remote;
            }
        }
         
        return $files_uploaded;
    }

    private function uploadAll($local_directory, $remote_directory, $ftp_env){
        /* We save all the filenames in the following array */
        $files_to_upload = array();
        $files_uploaded = array(); 
         
        $files_to_upload = $this->getDirContents($local_directory);
        $sftp = SSH::into($ftp_env);
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
                    if ($remote_dir_arr = explode(DIRECTORY_SEPARATOR, $remote)) { 
                        $count = count($remote_dir_arr);
                        foreach ($remote_dir_arr as $idx => $remote_dir) { //mkdir if not exist
                            if (--$count <= 0) { // skip the last element (file)
                                break;
                            }
                            if($idx === 0){ //skip the first element and chdir to root
                                $sftp->getGateway()->getConnection()->chdir('/');
                                continue;
                            }
                            if(!$sftp->exists($remote_dir)){
                                $sftp->getGateway()->getConnection()->mkdir($remote_dir);
                            }
                            $sftp->getGateway()->getConnection()->chdir($remote_dir);
                        }
                    }
                    $sftp->put($local,$remote);
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

