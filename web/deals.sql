CREATE TABLE `deals` (
	`id_profile` INT(10) NULL DEFAULT NULL,
	`diff_percent` DECIMAL(20,6) NULL DEFAULT NULL,
	`diff_price` DECIMAL(20,6) NULL DEFAULT NULL,
	`change_days` INT(10) NULL DEFAULT NULL,
	`before` DECIMAL(20,6) NULL DEFAULT NULL,
	`after` DECIMAL(20,6) NULL DEFAULT NULL,
	`ticker` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`currency` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci'
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;
