<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("you should post this bruh");
}

if (empty($_POST) && $_SERVER["CONTENT_LENGTH"] > 0) {
    die("your files are too thicc. try submitting something smaller");
}

require_once('config.php');
require_once('uploadlog.php');
require_once('extractor.php');

$upload_log = new SusUploadLog();
$config = new SusConfig();

// === some error handling ===
// prints warnings to log, dies upon errors

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    global $upload_log;
    $upload_log->message("UPLOADER WARNING: $errstr @ $errfile:$errline (errno=$errno)");
    return true;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error["type"], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $emsg = $error["message"];
        $efil = $error["file"];
        $elin = $error["line"];
        $upload_log->message("UPLOADER *******E R R O R*******: $emsg @ $efil:$elin");
        die("<span style=\"color:red\">The uploader has encountered a fatal error, please look in the log.</span><br><pre>{$upload_log->as_string()}</pre><br><span>we are cooked</span>");
    }
});

function try_extracting_tar($target_file_name, $extract_path)
{
    global $upload_log;

    try {
        extract_tar($target_file_name, $extract_path);
        $upload_log->message("Extracted successfully.");
        return true;
    } catch (PharException $exc) {
        $upload_log->message("Unable to extract tar file: " . $exc->getMessage());
        return false;
    }
}

function delete_unzipped($shall_delete_unzipped, $target_file_name)
{
    global $upload_log;

    if (!$shall_delete_unzipped) {
        return false;
    }
    if (unlink(realpath($target_file_name))) {
        $upload_log->message("Compressed file deleted.");
        return true;
    }
    $upload_log->message("Unable to delete compressed file.");
    return false;
}

function process_file($file_index)
{
    global $config, $upload_log; // balls

    $hr_file_index = $file_index + 1;

    $root_save_path = $config->get_root_save_path();
    $file = $_FILES["susfile"];
    $file_name = $file["name"][$file_index];
    $file_error = $file["error"][$file_index];
    $file_temp_name = $file["tmp_name"][$file_index];
    $file_mime_type = $file["type"][$file_index];
    $folder = $_POST["susfolder"];
    $file_save_path = $root_save_path;
    $shall_unzip = isset($_POST["susunzip"]);
    $shall_delete_unzipped = isset($_POST["susunzipdestroy"]);
    $overwrite_ok = isset($_POST["susoverwrite"]);

    $upload_log->message("Started processing file #$hr_file_index: " . $file_name);
    $upload_log->message("Mime type: " . $file_mime_type);

    if ($config->is_blacklisted($file_mime_type)) {
        $upload_log->message("This file type is not allowed.");
        return false;
    }

    // make rsp if not exists
    if (!file_exists($root_save_path)) {
        if (!mkdir($root_save_path, 0777, true)) {
            $upload_log->message("Unable to create root save path.");
        }
    }

    // make folder if not exsits and it was passed
    if (isset($folder) && strlen($folder) != 0) {
        $new_save_path = $file_save_path . "/" . $folder;
        if (!mkdir($new_save_path, 0777, true)) {
            $upload_log->message("Unable to create folder: '$folder'. Adding file to root save path.");
        } else {
            $file_save_path = $new_save_path;
        }
    }

    $actual_error = true; // this implies the existence of a non-actual error

    switch ($file_error) {
    case UPLOAD_ERR_OK:
        $upload_log->message("No error when uploading. Continuing.");
        $actual_error = false;
        break;
    case UPLOAD_ERR_INI_SIZE:
        $upload_log->message("File exceeds max size as defined by the server.");
        break;
    case UPLOAD_ERR_FORM_SIZE:
        $upload_log->message("File exceeds max size as defined by the upload form.");
        break;
    case UPLOAD_ERR_PARTIAL:
        $upload_log->message("The file was partially uploaded.");
        break;
    case UPLOAD_ERR_NO_FILE:
        $upload_log->message("No file was uploaded.");
        break;
    case UPLOAD_ERR_NO_TMP_DIR:
        $upload_log->message("Missing temporary folder.");
        break;
    case UPLOAD_ERR_CANT_WRITE:
        $upload_log->message("Failed to write the file to disk.");
        break;
    case UPLOAD_ERR_EXTENSION:
        $upload_log->message("Upload was stopped by a server extension.");
        break;
    default:
        $upload_log->message("Unknown error.");
        break;
    }

    if ($actual_error) {
        return false;
    }

    // move it
    $target_file_name = $file_save_path . "/" . $file_name;
    if (file_exists($target_file_name) && !$overwrite_ok) {
        $upload_log->message("File with this name already exists.");
        return false;
    }
    if (move_uploaded_file($file_temp_name, $target_file_name)) {
        $upload_log->message("File moved to: '$target_file_name' successfully.");
    } else {
        $upload_log->message("Unable to move file.");
        return false;
    }

    if ($shall_unzip) {
        if ($file_mime_type == "application/zip" || $file_mime_type == "application/x-tar") {
            // is a zip or tar
            $extract_path = $file_save_path . "/unzip-" . pathinfo($file_name, PATHINFO_FILENAME);
            $upload_log->message("File is an archive. Extracting contents to: " . $extract_path);
            if (!file_exists($extract_path)) {
                if (!mkdir($extract_path, 0777, true)) {
                    $upload_log->message("Unable to create folder for extracted files. Extraction canceled.");
                    return false;
                }
            }
            switch ($file_mime_type) {
            case "application/zip":
                // is a zip
                $extract_zip_value = extract_zip($target_file_name, $extract_path);
                if ($extract_zip_value === true) {
                    $upload_log->message("Extracted successfully.");
                    if (!delete_unzipped($shall_delete_unzipped, $target_file_name)) {
                        return false;
                    }
                } else {
                    $upload_log->message("Unable to open zip file: " . $extract_zip_value);
                }
                break;
            case "application/x-tar":
                // is a tar
                if (!try_extracting_tar($target_file_name, $extract_path)) {
                    return false;
                }
                if (!delete_unzipped($shall_delete_unzipped, $target_file_name)) {
                    return false;
                }
                break;
            }
        } else if ($file_mime_type == "application/x-gzip-compressed" || $file_mime_type == "application/gzip") {
            // is a gzip
            $uncompressed_file = $file_save_path . "/" . pathinfo($file_name, PATHINFO_FILENAME);
            $upload_log->message("File is compressed uzing gzip. Decompressing to: " . $uncompressed_file);
            $extract_gzip_value = extract_gzip($target_file_name, $uncompressed_file);
            if ($extract_gzip_value === true) {
                $upload_log->message("Uncompressed successfully.");
                if (!delete_unzipped($shall_delete_unzipped, $target_file_name)) {
                    return false;
                }
            } else {
                $error_message = "";
                switch ($extract_gzip_value) {
                case EXTRACT_GZIP_ERR_OPEN_GZIP:
                    $error_message = "Unable to open gzip file";
                    break;
                case EXTRACT_GZIP_ERR_OPEN_EXTRACT:
                    $error_message = "Unable to open destination file";
                    break;
                case EXTRACT_GZIP_ERR_FWRITE:
                    $error_message = "Unable to write uncompressed data to destination file";
                    break;
                }
                $upload_log->message("Failed to uncompress file: " . $error_message);
                return false;
            }
        } else {
            $upload_log->message("File is not an archive nor a compressed file. Extracting would be impossible.");
        }
    }

    return true;
}

$total_files = count($_FILES["susfile"]["name"]);
$error_count = 0;
$upload_log->message("$total_files files to process.");
for ($i = 0; $i < $total_files; $i++) {
    if (!process_file($i)) {
        $error_count++;
    }
    $hr_i = $i + 1;
    $upload_log->message("Finished processing file #$hr_i.");
    $upload_log->delimiter();
}
$percent_ok = round(100 - ($error_count / $total_files) * 100, 2);
$upload_log->message("Processed $total_files files with $error_count errors ($percent_ok% ok).");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sus | process upload</title>
    <link rel="stylesheet" href="upload.css">
</head>
<body>
    <p>sus has processed your upload:</p>
    <pre>
<?php echo $upload_log->as_string(); ?>
    </pre>
    <?php
if ($error_count) {
    echo "<span class=\"sus-upload-error\">Upload failed, check log above for details.</span>";
} else {
    echo "<span class=\"sus-upload-ok\">Upload successful.</span>";
}
    ?>
    <br>
    <a href="index.php">Back</a>
</body>
</html>
