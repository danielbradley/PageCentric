CREATE VIEW view_payments AS
  SELECT USER, created, customer_id, final_four FROM payments;
CREATE VIEW view_files AS
    SELECT FILE, version, kind, original_filename, filename, filetype, filesize, fileextension, salt, token FROM files;
CREATE VIEW view_files_tokens AS
    SELECT FILE, token FROM files;
