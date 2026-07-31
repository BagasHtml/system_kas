USE db_kas_kelas;

INSERT INTO admin (username, password) VALUES
('admin', SHA2('admin123', 256));
