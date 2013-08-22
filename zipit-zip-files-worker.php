<?php
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

// specify namespace
   namespace OpenCloud;

// require zipit configuration
   require('zipit-config.php');

// get Auth Hash to protect this file from being run unintentially
   $auth = $argv[1];

// get name of progress file. This will keep on demand backuups from colliding with auto backups
   $progress_hash = $argv[2];
   $progress_file = $progress_hash. "-progress.php";

// check to see if this is being run as an automated backup
   $auto_check = $argv[3];

// check for rotation option
   $rotate = $argv[4];

// Set the default timezone
   date_default_timezone_set('America/Chicago');

// define backup path for auto
    $auto_path = getcwd();

// If set to rotate set date for backup name
   if ($rotate == "weekly") {
      $date = date("D");
   }

   else {
      $date = date("M-d-Y-h:i:s");
   }

// Set backup name
   $backupname = "$url-backup-$date.zip";

// define zipit log file
   if ($auto_check == "auto") {
    $zipitlog = "$auto_path/logs/zipit.log";
    $logsize = filesize($zipitlog);
   }
   else {
    $zipitlog = "../../../logs/zipit.log";
    $logsize = filesize($zipitlog);
   }

// create zipit log file if it doesn't exist
    if(!file_exists("$zipitlog")) { 
       $fp = fopen("$zipitlog","w");  
       fwrite($fp,"----Zipit Logs----\n\n");  
       fclose($fp); 
    }

// rotate log file to keep it from growing too large
   if ($logsize > 52428800) {
   if ($auto_check == "auto") {
   shell_exec("mv $auto_path/logs/zipit.log $auto_path/logs/zipit_old.log");
   }
   else {
   shell_exec("mv ../../../logs/zipit.log ../../../logs/zipit_old.log");
   }
   }

// clean up local backups if files are older than 24 hours (86400 seconds)
   $dir = "$path/zipit/zipit-backups/files/";
 
   if ($handle = opendir($dir)) {
      while (( $file = readdir($handle)) !== false ) {
         if ( $file == '.' || $file == '..' || is_dir($dir.'/'.$file) ) {
            continue;
         }
         if ($file != "index.php") {
            if ((time() - filemtime($dir.'/'.$file)) > 86400) {
               shell_exec("rm $dir/$file");
            }
         }
      }
      closedir($handle);
   }

// write to log
   $logtimestamp =  date("M-d-Y-h:i:s");
   $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Authorizing\n";
   fwrite($fh, $stringData);
   fclose($fh);

// update progress file
   file_put_contents($progress_file,'<br/><center>Authorizing...<br/><img src="images/progress.gif"/></center>');

// sleep for 3 seconds. This helps make the progress more aesthetic for smaller sites where the process would run so fast you couldn't see what happened.
   sleep(3);

   if ($auth_hash == $auth) {
      if ($auto_check == "auto") {
         echo date("h:i:s")." -- Authorized!\n";
      }
   }

   else {
// write to log
      $logtimestamp =  date("M-d-Y-h:i:s");
      $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
      $stringData = "$logtimestamp -- Authorization Failed!\n$logtimestamp Zipit completed\n\n";
      fwrite($fh, $stringData);
      fclose($fh);

      if ($auto_check == "auto") {
         echo date("h:i:s")." -- Not Authorized\n";
      } 
     
      else {
         file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Authorization Failed! Click to Close</button></center>');

// sleep for 3 seconds. This helps make the progress more aesthetic for smaller sites where the process would run so fast you couldn't see what happened
         sleep(3);
      }
   die();
   }

   if ($auto_check == "auto") {
      echo date("h:i:s")." -- Zip Started!\n";
   }

   else {
// write to log
      $logtimestamp =  date("M-d-Y-h:i:s");
      $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
      $stringData = "$logtimestamp -- Zipping!\n";
      fwrite($fh, $stringData);
      fclose($fh);

      file_put_contents($progress_file,'<br/><center>Zipping...<br/><img src="images/progress.gif"/></center>');

// sleep for 3 seconds. This helps make the progress more aesthetic for smaller sites where the process would run so fast you couldn't see what happened
      sleep(3);
   }

// Change our current working directory to prepare for zipping. We use the $path variable from the zipit-config.php then we go back two directories
   chdir("$path");
   chdir("../../");

// execute the zip
   shell_exec("zip -9pr $path/zipit/zipit-backups/files/$backupname lib logs web -x ./web/content/zipit\*");

// Change our current working directory back to the zipit directory
   if ($auto_check == "auto") {
      chdir("$auto_path");
   }
   else {
      chdir("$path/zipit");
   }

// check to see if the backup was created
   if (file_exists("$path/zipit/zipit-backups/files/$backupname")) {

// write to log
      $logtimestamp =  date("M-d-Y-h:i:s");
      $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
      $stringData = "$logtimestamp -- Zipping Complete!\n";
      fwrite($fh, $stringData);
      fclose($fh);

      if ($auto_check == "auto") {
         echo date("h:i:s")." -- Zip Completed!\n";
      }
   }

   else {
// write to log
      $logtimestamp =  date("M-d-Y-h:i:s");
      $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
      $stringData = "$logtimestamp -- Zip Failed!\n$logtimestamp Zipit completed\n\n";
      fwrite($fh, $stringData);
      fclose($fh);

      if ($auto_check == "auto") {
         echo date("h:i:s")." -- Zip Failed!\n";
      }

      else {
         file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Zip Failed! Click to Close</button></center>');

// sleep for 3 seconds. This helps make the progress more aesthetic for smaller sites where the process would run so fast you couldn't see what happened
         sleep(3);
      }

// clean up local backups, progress file, and end process
   shell_exec("rm $path/zipit/zipit-backups/files/$backupname");
   shell_exec("rm $progress_file");
   die();
   }

// md5 for local backup. this is used for integrity check once backup has been moved to Cloud Files
   $md5file = "$path/zipit/zipit-backups/files/$backupname";
   $md5 = md5_file($md5file);

// Set API Timeout
   define('RAXSDK_TIMEOUT', '3600');

// require Cloud Files API
   require_once("$path/zipit/api/lib/rackspace.php");

// Authenticate to Cloud Files

// write to log
   $logtimestamp =  date("M-d-Y-h:i:s");
   $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
   $stringData = "$logtimestamp -- Connecting to Cloud Files\n";
   fwrite($fh, $stringData);
   fclose($fh);

    if ($auto_check == "auto") {
       echo date("h:i:s")." -- Connecting to Cloud Files\n";
    }

    else {
       file_put_contents($progress_file,'<br/><center>Connecting to Cloud Files...<br/><img src="images/progress.gif"/></center>');

// sleep for 3 seconds. This helps make the progress more aesthetic for smaller sites where the process would run so fast you couldn't see what happened
    sleep(3);
    }

   try {
      define('AUTHURL', 'https://identity.api.rackspacecloud.com/v2.0/');
      $mysecret = array('username' => $username,'apiKey' => $key);

// write to log
      $logtimestamp =  date("M-d-Y-h:i:s");
      $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
      $stringData = "$logtimestamp -- Connected to Cloud Files\n";
      fwrite($fh, $stringData);
      fclose($fh);

      if ($auto_check == "auto") {
         echo date("h:i:s")." -- Connected to Cloud Files!\n";
      }

// establish our credentials
      $connection = new Rackspace(AUTHURL, $mysecret);

// now, connect to the ObjectStore service
      $ostore = $connection->ObjectStore('cloudFiles', "$datacenter");
   }

   catch (HttpUnauthorizedError $e) {

// write to log
      $logtimestamp =  date("M-d-Y-h:i:s");
      $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
      $stringData = "$logtimestamp -- Cloud Files API connection could not be established!\n$logtimestamp Zipit completed\n\n";
      fwrite($fh, $stringData);
      fclose($fh);

      if ($auto_check == "auto") {
         echo date("h:i:s")." -- Cloud Files API connection could not be established!\n";
      }

      else {
         file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Cloud Files API Connection Failed! Click to Close</button></center>');

// sleep for 3 seconds. This helps make the progress more aesthetic for smaller sites where the process would run so fast you couldn't see what happened
         sleep(3);
      }

// clean up local backups, progress file, and end process
      shell_exec("rm $path/zipit/zipit-backups/files/$backupname");
      shell_exec("rm $progress_file");
      die();
   }

// write to log
   $logtimestamp =  date("M-d-Y-h:i:s");
   $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
   $stringData = "$logtimestamp -- Creating Cloud Files Container.\n";
   fwrite($fh, $stringData);
   fclose($fh);

   if ($auto_check == "auto") {
      echo date("h:i:s")." -- Creating Cloud Files Container...\n"; 
   }

   else {
      file_put_contents($progress_file,'<br/><center>Creating Cloud Files Container...<br/><img src="images/progress.gif"/></center>'); 

// sleep for 3 seconds. This helps make the progress more aesthetic for smaller sites where the process would run so fast you couldn't see what happened
   sleep(3); 
   } 

// create container if it doesn't already exist
   $cont = $ostore->Container();
   $cont->Create(array('name'=>"zipit-backups-files-$url"));

// write to log
   $logtimestamp =  date("M-d-Y-h:i:s");
   $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
   $stringData = "$logtimestamp -- Cloud Files container created or already exists!\n";
   fwrite($fh, $stringData);
   fclose($fh);

   if ($auto_check == "auto") {
      echo date("h:i:s")." -- Cloud Files container created or already exists!\n";
   }
  
// write to log
   $logtimestamp =  date("M-d-Y-h:i:s");
   $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
   $stringData = "$logtimestamp -- Moving backup to Cloud Files\n";
   fwrite($fh, $stringData);
   fclose($fh);

   if ($auto_check == "auto") {
      echo date("h:i:s")." -- Moving backup to Cloud Files...\n";
   }

   else {
   file_put_contents($progress_file,'<br/><center>Moving Backup to Cloud Files...<br/><img src="images/progress.gif"/></center>');
 
// sleep for 3 seconds. This helps make the progress more aesthetic for smaller sites where the process would run so fast you couldn't see what happened
   sleep(3); 
   } 

// send backup to Cloud Files
   $obj = $cont->DataObject();
   $obj->Create(array('name' => "$backupname", 'content_type' => 'application/x-gzip'), $filename="$path/zipit/zipit-backups/files/$backupname");

// get etag(md5). This is used for integrity check
   $etag = $obj->hash;

// compare md5 wih etag. (integrity check)
   if ($md5 != $etag) {

// integrity check failed remove Cloud Files backup
      $obj->Delete(array('name'=>"$backupname"));

// write to log
      $logtimestamp =  date("M-d-Y-h:i:s");
      $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
      $stringData = "$logtimestamp -- Backup failed integrity check! Please try again.\n$logtimestamp Zipit completed\n\n";
      fwrite($fh, $stringData);
      fclose($fh);

      if ($auto_check == "auto") {
         echo date("h:i:s")." -- Backup failed integrity check! Please try again.\n";
      }

      else {
         file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Backup Failed Integrity Check! Click to Close</button></center>');

// sleep for 3 seconds. This helps make the progress more aesthetic for smaller sites where the process would run so fast you couldn't see what happened
      sleep(3);
      }

// clean up local backups, progress file, and end process
      shell_exec("rm $path/zipit/zipit-backups/files/$backupname");
      shell_exec("rm $progress_file");
      die();  
   }

   else {

// write to log
      $logtimestamp =  date("M-d-Y-h:i:s");
      $fh = fopen($zipitlog, 'a') or die(file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Can\'t Write to Log! Click to Close</button></center>'));
      $stringData = "$logtimestamp -- Backup Complete!\n$logtimestamp Zipit completed\n\n";
      fwrite($fh, $stringData);
      fclose($fh);

      if ($auto_check == "auto") {
         echo date("h:i:s")." -- Backup Complete!\n";
      }

      else {
         file_put_contents($progress_file,'<br/><center><button type="button" name="btnClose" value="OK" class="css3button" onclick="parent.$.colorbox.close();parent.refreshFiles();parent.refreshLogs();">Backup Complete! Click to Close</button></center>');  
      sleep(3);
      }

// clean up local backups and progress file
   shell_exec("rm $path/zipit/zipit-backups/files/$backupname"); 
   shell_exec("rm $progress_file");
}

?>
