<?php
namespace App\Http\Controllers;
use SSH;

class FileUploaderController extends Controller
{

    public function deploy()
    {
        SSH::into('production')->run(array(
            'cd ~/public_html/mywebsite',
            'git pull origin master'
        ), function($line){
        
            echo $line.PHP_EOL; // outputs server feedback
        });
    }
}
