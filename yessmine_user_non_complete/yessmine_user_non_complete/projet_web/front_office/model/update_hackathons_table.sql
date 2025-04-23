-- First, backup existing data if creator_id exists
CREATE TEMPORARY TABLE hackathons_backup AS SELECT * FROM hackathons;

-- Drop the existing foreign key constraint if it exists
ALTER TABLE hackathons DROP FOREIGN KEY IF EXISTS fk_creator;

-- Add created_by column if it doesn't exist
ALTER TABLE hackathons ADD COLUMN IF NOT EXISTS created_by INT;

-- Copy data from creator_id to created_by if creator_id exists
UPDATE hackathons SET created_by = creator_id WHERE creator_id IS NOT NULL;

-- Drop the creator_id column if it exists
ALTER TABLE hackathons DROP COLUMN IF EXISTS creator_id;

-- Add the foreign key constraint for created_by
ALTER TABLE hackathons 
ADD CONSTRAINT fk_created_by 
FOREIGN KEY (created_by) 
REFERENCES users(user_id) 
ON DELETE SET NULL;