<?php
namespace App\Http\Controllers;
use SSH;

class FileUploaderController extends Controller
{

    public function deploy()
    {
        /*
sftp -oIdentityFile=/Users/sue.park/Downloads/rationalized_key.rsa -oHostKeyAlgorithms=+ssh-dss sshacs@tverationalstg.upload.akamai.com
        */
		
        /*SSH::into('production')->run(array(
            'cd ~/public_html/mywebsite',
            'git pull origin master'
        ), function($line){
        
            echo $line.PHP_EOL; // outputs server feedback
        });*/
        SSH::into('production')->run('date', function($line) {dd($line); });
    }
}
