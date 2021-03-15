<?php

/**
 * @author : Fawaz Tahir, Arslan Hassan
 */
  
include('../includes/config.inc.php');

if ( isset($_REQUEST['plupload']) ) 
{
    $mode = "plupload";
}

if($_FILES['photoUpload'])
    $mode = "uploadPhoto";
if($_POST['photoForm'])
    $mode = "get_photo_form";
if($_POST['insertBeat'])
    $mode = "insert_beat";
if($_POST['updatePhoto'])
    $mode = "update_photo";         

switch($mode)
{
    case "insert_beat":
    {
        
    }
    break;
    case "update_photo":
    {
        $_POST['photo_title'] = genTags(str_replace(array('_','-'),' ',mysql_clean($_POST['photo_title'])));
        $_POST['photo_description'] = genTags(str_replace(array('_','-'),' ',mysql_clean($_POST['photo_description'])));
        $_POST['photo_tags'] = genTags(str_replace(array(' ','_','-'),', ',mysql_clean($_POST['photo_tags'])));
                
        $cbphoto->update_photo();
        
        if(error())
            $error = error('single');
        if(msg())
            $success = msg('single');
            
        $updateResponse['error'] = $error;
        $updateResponse['success'] = $success;
        
        echo json_encode($updateResponse);      
    }
    break;
   /* case "uploadPhoto":
    {
        $exts = $cbphoto->exts;
        $max_size = 1048576; // 2MB in bytes
        $form = "photoUpload";
        $path = PHOTOS_DIR."/";
        
        // These are found in $_FILES. We can access them like $_FILES['file']['error'].
        $upErrors = array(
                          0 => "There is no error, the file uploaded with success.",
                          1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
                          2 => " The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
                          3 => "The uploaded file was only partially uploaded.",
                          4 => "No file was uploaded.",
                          6 => "Missing a temporary folder.",
                          7 => "Failed to write file to disk."
                          );
                          
        // Let's see if everything is working fine by checking $_FILES.
        if(!isset($_FILES[$form])) {
            upload_error("No upload found in \$_FILES for " . $form);
            exit(0);
        }
        elseif(isset($_FILES[$form]['error']) && $_FILES[$form]['error'] != 0) {
            upload_error($upErrors[$_FILES[$form]['error']]);
            exit(0);
        }
        elseif(!isset($_FILES[$form]["tmp_name"]) || !@is_uploaded_file($_FILES[$form]["tmp_name"])) {
            upload_error("Upload failed is_uploaded_file test.");
            exit(0);
        } elseif(empty($_FILES[$form]['name'])) {
            upload_error("File name is empty");
            exit(0);    
        }
        
        // Time to check if Filesize is according to demands
        //$filesize = filesize($_FILES[$form]['tmp_name']);
        //if(!$filesize || $filesize > $max_size)
        //{
        //  upload_error("File exceeds the maximum allowed size");
        //  exit(0);
        //}
        //
        //if($filesize < 0)
        //{
        //  upload_error("File size outside allowed lower bound");
        //  exit(0);
        //}
        
        //Checking Extension of File
        $info = pathinfo($_FILES[$form]['name']);
        $extension  = strtolower($info['extension']);
        $valid_extension = false;
        
        foreach ($exts as $ext) {
            if (strcasecmp($extension, $ext) == 0) {
                $valid_extension = true;
                break;
            }
        }
        
        if(!$valid_extension)
        {
            upload_error("Invalid file extension");
            exit(0);    
        }
        
        $filename = $cbphoto->create_filename();
        
        
        //Now uploading the file
        if(move_uploaded_file($_FILES[$form]['tmp_name'],$path.$filename.".".$extension))
        {
            echo json_encode(array("success"=>"yes","filename"=>$filename,"extension"=>$extension));
            
        } else {    
            upload_error("File could not be saved.");
            exit(0);    
        }   
    }
    break;*/


    case 'plupload': {
        $status_array = array();
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        #exit("BEATS: ".CB_BEATS_UPLOAD_DIR."         and    PHOTO ".PHOTOS_DIR);
        //pr($_REQUEST);

        #checking for if the right file is uploaded
        $content_type = get_mime_type($_FILES['file']['tmp_name']);
        if ( $content_type != 'audio')  {
            echo json_encode(array("status"=>"400","err"=>"Invalid Content"));
            exit();
        }

        $extension = getExt( $_FILES['file']['name']);
        $types = strtolower(config('allowed_types'));
        $supported_extensions = explode(',', $types);

        if (!in_array($extension, $supported_extensions)) {
            echo json_encode(array("status"=>"504","msg"=>"Invalid extension"));
            exit();
        }

        $targetDir = CB_BEATS_UPLOAD_DIR;
       # $directory = create_dated_folder( CB_BEATS_UPLOAD_DIR );
       # $targetDir .= '/'.$directory;
        #pr($_REQUEST,true);

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds
        @set_time_limit(5 * 60);

        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        $fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
       #exit($targetDir . DIRECTORY_SEPARATOR . $fileName);
        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName))
        {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                $count++;

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        #echo $filePath;
        #echo "</br>TARGET DIR ".$targetDir;
        // Create target dir
        if (!file_exists($targetDir))
        {
            echo "creating file";
            mkdir($targetDir);
        }

        // Remove old temp files
        if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir)))
        {
            while (($file = readdir($dir)) !== false)
            {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part"))
                {
                    @unlink($tmpfilePath);
                }
            }

            closedir($dir);
        } else
            die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');


        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false)
        {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name']))
            {
                // Open temp file
                $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out)
                {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in)
                    {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
        } else
        {
            // Open temp file
            $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
            if ($out)
            {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");

                if ($in)
                {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

                fclose($in);
                fclose($out);
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1)
        {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);
        }

        $filename = $cbphoto->create_filename();
        $targetFileName = $filename . '.' . getExt($filePath);
        $targetFile = $targetDir . "/" . $targetFileName;

        rename($filePath, $targetFile);

        echo json_encode( array("success"=>"yes","file_name"=>$filename, "extension" => getExt( $filePath ), "file_directory" => $targetDir ) );
    }
    break;
}




//function used to display error
function upload_error($error)
{
    echo json_encode(array("error"=>$error));
} 
?> 