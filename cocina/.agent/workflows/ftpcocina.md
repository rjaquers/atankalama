---
description: Upload specific files to the cocina FTP server
---
# FTP Cocina Workflow

This workflow is triggered when the user mentions `@ftpcocina` or asks to upload files to the `cocina` project FTP. The user will typically provide one or more file paths, formatted like `@[path/to/file.php]`.

## Steps

1. Extract the file paths the user wants to upload.
2. Run the `ftp_sync.py` script located at `/Volumes/Mac_Secundario/htdocs/cocina/ftp_sync.py`, passing the extracted file paths as arguments.
// turbo
   `python3 /Volumes/Mac_Secundario/htdocs/cocina/ftp_sync.py <file1> <file2> ...`

3. Confirm to the user that the files have been uploaded successfully.
