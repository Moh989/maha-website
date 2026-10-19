SET NAMES utf8mb4;

DELETE FROM settings WHERE setting_key = 'contact_phone';

INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('contact_email', 'info@almaha-group.com', 'contact'),
('contact_address_ar', 'العراق - بغداد', 'contact'),
('contact_address_en', 'Iraq - Baghdad', 'contact')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_group = VALUES(setting_group);

SET @drop_phone_sql = IF(
    EXISTS(
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'contact_messages'
          AND column_name = 'phone'
    ),
    'ALTER TABLE contact_messages DROP COLUMN phone',
    'SET @phone_drop_skipped = 1'
);

PREPARE drop_phone_statement FROM @drop_phone_sql;
EXECUTE drop_phone_statement;
DEALLOCATE PREPARE drop_phone_statement;
