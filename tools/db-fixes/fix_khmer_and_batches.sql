-- Fix Khmer names for all materials (corrupted during backup import)
SET NAMES utf8mb4;

UPDATE materials SET name_km='ក្រដាសរលោង A1' WHERE id=1;
UPDATE materials SET name_km='ក្រដorg A1 100g' WHERE id=2;
UPDATE materials SET name_km='ក្រដorg A1 80g' WHERE id=3;
UPDATE materials SET name_km='ហ្វីមម៉ org់' WHERE id=4;
UPDATE materials SET name_km='ហ org រorg org org org org org org org org org org org org org' WHERE id=5;
UPDATE materials SET name_km='ទorg org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org org' WHERE id=6;
