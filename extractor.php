<?php
function extract_zip($zip_file, $extract_path)
{
    $zip = new ZipArchive;
    if ($zip->open($target_file_name) == true) {
        $zip->extractTo($extract_path);
        $zip->close();
        return true;
    }
    return $zip->getStatusString();
}

// be careful it might throw the phar exception
function extract_tar($tar_file, $extract_path)
{
    $tar = new PharData($tar_file);
    $tar->extractTo($extract_path);
}

define("EXTRACT_GZIP_ERR_OPEN_GZIP", "1");
define("EXTRACT_GZIP_ERR_OPEN_EXTRACT", "2");
define("EXTRACT_GZIP_ERR_FWRITE", "3");

function extract_gzip($gzip_file, $extract_file)
{
    $gz = gzopen($gzip_file, 'rb');
    if (!$gz) {
        return EXTRACT_GZIP_ERR_OPEN_GZIP;
    }

    $fp = fopen($extract_file, 'wb');
    if (!$fp) {
        gzclose($gz);
        return EXTRACT_GZIP_ERR_OPEN_EXTRACT;
    }

    while ($data = gzread($gz, 4096)) {
        if (fwrite($fp, $data) === false) {
            fclose($fp);
            gzclose($gz);
            return EXTRACT_GZIP_ERR_FWRITE;
        }
    }

    fclose($fp);
    gzclose($gz);

    return true;
}
?>
