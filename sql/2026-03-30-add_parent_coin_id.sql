-- Add parent_coin_id field to support multiple merge-mined coins on same port
-- This allows linking auxpow coins to their parent chain (e.g., DOGE, FCH to LTC)

ALTER TABLE `coins` ADD `parent_coin_id` INT(11) NULL DEFAULT NULL AFTER `auxpow`;
ALTER TABLE `coins` ADD INDEX `idx_parent_coin_id` (`parent_coin_id`);

-- Example: Set parent coin for existing merge-mined coins
-- Update dogecoin to use litecoin as parent (assuming LTC id = 1)
-- UPDATE `coins` SET `parent_coin_id` = 1 WHERE `symbol` = 'DOGE' AND `auxpow` = 1;

-- Example: Set multiple merge-mined coins to same parent
-- UPDATE `coins` SET `parent_coin_id` = 1 WHERE `symbol` IN ('DOGE', 'FCH', 'MONA') AND `auxpow` = 1;
