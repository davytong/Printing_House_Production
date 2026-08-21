-- Add the missing "Gum" consumable material
INSERT INTO materials (code, name, name_km, category, icon, sub_type, unit, min_stock, status, created_at, updated_at) 
VALUES ('CON-0014', 'Gum Solution', 'ទឹកថ្នាំ ហ្គូម', 'consumable', 'fa-solid fa-bottle-droplet', 'Chemicals', 'can', 2.00, 'active', NOW(), NOW());

-- Get the ID of the newly inserted Gum
SET @gum_id = LAST_INSERT_ID();

-- Insert stock adjustment records based on the Telegram daily reports
INSERT INTO stock_movements (material_id, type, quantity, reference, performed_by, notes, movement_date, created_at, updated_at) VALUES 
-- July 4 Report (Paper and Film)
(1, 'adjust', 11, 'Telegram Report', 'System Admin', 'Data Recovery from July 4 Telegram Report', '2026-07-04', NOW(), NOW()),
(2, 'adjust', 59, 'Telegram Report', 'System Admin', 'Data Recovery from July 4 Telegram Report', '2026-07-04', NOW(), NOW()),
(3, 'adjust', 234, 'Telegram Report', 'System Admin', 'Data Recovery from July 4 Telegram Report', '2026-07-04', NOW(), NOW()),
(5, 'adjust', 7, 'Telegram Report', 'System Admin', 'Data Recovery from July 4 Telegram Report', '2026-07-04', NOW(), NOW()),
(4, 'adjust', 35, 'Telegram Report', 'System Admin', 'Data Recovery from July 4 Telegram Report', '2026-07-04', NOW(), NOW()),

-- July 6 Report (Consumables)
(12, 'adjust', 25, 'Telegram Report', 'System Admin', 'Data Recovery from July 6 Telegram Report', '2026-07-06', NOW(), NOW()),
(@gum_id, 'adjust', 1, 'Telegram Report', 'System Admin', 'Data Recovery from July 6 Telegram Report', '2026-07-06', NOW(), NOW()),
(10, 'adjust', 13, 'Telegram Report', 'System Admin', 'Data Recovery from July 6 Telegram Report', '2026-07-06', NOW(), NOW()),
(11, 'adjust', 8, 'Telegram Report', 'System Admin', 'Data Recovery from July 6 Telegram Report', '2026-07-06', NOW(), NOW()),
(9, 'adjust', 11, 'Telegram Report', 'System Admin', 'Data Recovery from July 6 Telegram Report', '2026-07-06', NOW(), NOW()),
(8, 'adjust', 12, 'Telegram Report', 'System Admin', 'Data Recovery from July 6 Telegram Report', '2026-07-06', NOW(), NOW()),
(7, 'adjust', 12, 'Telegram Report', 'System Admin', 'Data Recovery from July 6 Telegram Report', '2026-07-06', NOW(), NOW()),
(6, 'adjust', 11, 'Telegram Report', 'System Admin', 'Data Recovery from July 6 Telegram Report', '2026-07-06', NOW(), NOW()),
(13, 'adjust', 10, 'Telegram Report', 'System Admin', 'Data Recovery from July 6 Telegram Report', '2026-07-06', NOW(), NOW());
