-- Add signature and responsible_person columns to checklists table
ALTER TABLE checklists ADD COLUMN signature LONGTEXT NULL;
ALTER TABLE checklists ADD COLUMN responsible_person VARCHAR(255) NULL;

-- Add is_parent and parent_id columns to checklist_items table
ALTER TABLE checklist_items ADD COLUMN is_parent TINYINT(1) DEFAULT 0;
ALTER TABLE checklist_items ADD COLUMN parent_id INT(11) NULL;
