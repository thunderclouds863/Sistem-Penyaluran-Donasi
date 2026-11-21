-- Akun & data demo. Admin bawaan schema: admin / 1234. User: donatur1 / demo123
USE donation_system;
INSERT INTO signup (id, full_name, phone, username, password, ktm_number, email) VALUES
(1, 'Donatur Demo', '081200000001', 'donatur1', '$2y$10$52hboDnXS8GD5GyOOao2AO6mH7qI2M0ROpfd4EL8tO.W5hBADvWv6', '21070000000001', 'donatur1@demo.local'),
(2, 'Pengurus Posko Demo', '081200000002', 'posko1', '$2y$10$52hboDnXS8GD5GyOOao2AO6mH7qI2M0ROpfd4EL8tO.W5hBADvWv6', '21070000000002', 'posko1@demo.local');
INSERT INTO donation_centers (id, name, address, contact_info, signup_id, status) VALUES
(1, 'Posko Bantuan Tembalang', 'Jl. Prof. Sudarto, Tembalang, Semarang', '081200000010', 2, 'Approved'),
(2, 'Posko Peduli Banjir Genuk', 'Kec. Genuk, Semarang', '081200000011', 2, 'Pending');
INSERT INTO financial_donations (amount, contact_info, status, donation_center_id, signup_id) VALUES
(250000, 'donatur1@demo.local', 'Confirmed', 1, 1), (100000, 'donatur1@demo.local', 'Pending', 1, 1);
INSERT INTO item_donations (item_type, location, contact_info, status, signup_id, donation_center_id) VALUES
('Beras 10 kg', 'Posko Tembalang', '081200000001', 'confirmed', 1, 1), ('Pakaian layak pakai', 'Posko Tembalang', '081200000001', 'pending', 1, 1);
INSERT INTO donation_requests (assistance_type, description, status, signup_id) VALUES
('Bantuan Barang dan Pangan', 'Kebutuhan logistik untuk 40 KK terdampak banjir', 'Pending', 2);
