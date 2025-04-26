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
            <input type="file" name="susfile[]" required multiple><p></p>
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
    </div>
</body>
</html>
