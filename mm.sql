

DROP TABLE IF EXISTS `mm_user`;
CREATE TABLE IF NOT EXISTS `mm_user` (
  `key` binary(16) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pwd` varchar(42) NOT NULL,
  `screen_name` varchar(50) NOT NULL,
  `type` int NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`key`)
);

INSERT INTO `mm_user` 
(`key`, `email`, `pwd`, `screen_name`, `type`, `created_at`) 
VALUES 
(UNHEX('00000000000000000000000000000001'), 'test.mm.01@gmail.com', PASSWORD('test'), 'test mm 01', 0, UTC_TIMESTAMP());
INSERT INTO `mm_user` 
(`key`, `email`, `pwd`, `screen_name`, `type`, `created_at`) 
VALUES 
(UNHEX('00000000000000000000000000000002'), 'test.mm.02@gmail.com', PASSWORD('test'), 'test mm 02', 0, UTC_TIMESTAMP());

DROP TABLE IF EXISTS `mm_user_location`;
CREATE TABLE IF NOT EXISTS `mm_user_location` (
  `user_key` binary(16) NOT NULL,
  `lat` FLOAT(9,6) NOT NULL,
  `lon` FLOAT(9,6) NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`user_key`)
);

INSERT INTO `mm_user_location` 
(`user_key`, `lat`, `lon`, `updated_at`) 
VALUES 
(UNHEX('00000000000000000000000000000001'), '45.07557', '7.67562', UTC_TIMESTAMP());
INSERT INTO `mm_user_location` 
(`user_key`, `lat`, `lon`, `updated_at`) 
VALUES 
(UNHEX('00000000000000000000000000000002'), '45.05881', '7.64882', UTC_TIMESTAMP());

DROP TABLE IF EXISTS `mm_user_thumbnail`;
CREATE TABLE IF NOT EXISTS `mm_user_thumbnail` (
  `user_key` binary(16) NOT NULL,
  `thumbnail` BLOB NOT NULL
);

DROP TABLE IF EXISTS `mm_user_message`;
CREATE TABLE IF NOT EXISTS `mm_user_message` (
  `key` binary(16) NOT NULL,
  `user_key_sender` binary(16) NOT NULL,
  `user_key_receiver` binary(16) NOT NULL,
	`title` varchar(100) NOT NULL,
	`body` TEXT NOT NULL,
  `sent_at` DATETIME NOT NULL,
  `read_at` DATETIME NULL,
  PRIMARY KEY (`key`)
);