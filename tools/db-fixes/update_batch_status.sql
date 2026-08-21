-- Update Batch 1 to "suspended" status since it's only 70% complete
UPDATE production_batches 
SET status = 'suspended',
    completed_at = NULL
WHERE id = 1;

-- Verify the update
SELECT id, name, status, started_at, completed_at 
FROM production_batches 
ORDER BY id;
