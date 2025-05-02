<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sus</title>
    <link rel="stylesheet" href="./index.css">
</head>
<body>
    <div id="sus-container">
        <h1 style="display: inline;">sus</h1> <span style="font-size: 0.8em;"><?php require('version.php'); echo SUS_VERSION; ?></span><p></p>
        <i>simple uploading server</i>
        <hr>
        <form enctype="multipart/form-data" action="upload.php" method="post">
            <input type="file" name="susfile[]" required multiple><p>max upload size: <b><?php echo ini_get("upload_max_filesize"); ?></b> / max post size: <b><?php echo ini_get("post_max_size"); ?></b></p>
            <input type="text" name="susfolder" placeholder="folder (optional)"><p></p>
            <label for="susunzip">decompress and/or extract (<i>for zip, tar and gz files</i>)</label>
            <input type="checkbox" name="susunzip">
            <label for="susunzipdestroy">delete after extraction</label>
            <input type="checkbox" name="susunzipdestroy"><p></p>
            <label for="susoverwrite">overwrite ok</label>
            <input type="checkbox" name="susoverwrite"><p></p>
            <p><i>*options apply for all files!</i></p>
            <input type="submit" value="upload">
        </form>
        <hr id="sus-hr-footer">
        <div id="sus-footer">powered by php <?php echo phpversion(); ?> running on <span title="<?php echo php_uname(); ?>"><?php echo php_uname("s"); ?></span> named <?php echo gethostname(); ?></div>
    </div>
</body>
</html>
