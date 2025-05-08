-- Add remark column to checklists table if it doesn't exist
ALTER TABLE checklists ADD COLUMN IF NOT EXISTS remark TEXT NULL;
