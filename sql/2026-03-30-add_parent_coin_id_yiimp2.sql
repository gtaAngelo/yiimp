-- Add parent_coin_id field to support multiple merge-mined coins on same port (yiimp2 version)
-- This allows linking auxpow coins to their parent chain (e.g., DOGE, FCH to LTC)

ALTER TABLE `coins` ADD `parent_coin_id` INT(11) NULL DEFAULT NULL AFTER `auxpow`;
ALTER TABLE `coins` ADD INDEX `idx_parent_coin_id` (`parent_coin_id`);
