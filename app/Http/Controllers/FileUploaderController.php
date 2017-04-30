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
        // see if file exists on remote
		SSH::into('production')->exists( '/448004/sue_test/share_config.js' );

		// upload file to remote
		SSH::into('production')->put( '/var/www/storage/logs/laravel.log', '/448004/sue_test/laravel.log' );

        //SSH::into('production')->run('date', function($line) { echo $line; });
    }
}
