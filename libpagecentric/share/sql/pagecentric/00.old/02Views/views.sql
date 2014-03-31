CREATE VIEW view_files AS
    SELECT FILE, version, kind, original_filename, filename, filetype, filesize, fileextension, salt, token FROM files;
