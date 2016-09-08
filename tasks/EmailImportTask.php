<?php

use PhpMimeMailParser\Parser as PHPMimeParser;

/**
 * Task used to import an email (should be called by an application
 * like postfix as part of their mail delivery piping).
 * 
 * @package app
 */
class EmailImportTask extends BuildTask
{
	
	protected $title = "Import Email";
	
	protected $description = "Import email via php email piping. NOTE this is intended to be called via a third party service like Postfix, not directly.";
	
	public function run($request)
    {
        // read email in from stdin
        $fd = fopen("php://stdin", "r");
        $string = "";
        
        while (!feof($fd)) {
            $string .= fread($fd, 1024);
        }
        
        fclose($fd);
        
        $email =EmailMessage::create()
            ->import($string)
            ->write();
        
        $this->log("Email {$email->Subject} created and imported...");
	}

    private function log($message)
    {
        if(Director::is_cli()) {
            echo $message . "\n";
        } else {
            echo $message . "<br/>";
        }
    }
	
}
