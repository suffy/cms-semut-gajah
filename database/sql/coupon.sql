CREATE TABLE `coupon` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(25) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `percent` varchar(10) DEFAULT NULL,
  `nominal` double DEFAULT NULL,
  `max_nominal` double DEFAULT NULL,
  `max_use` int(11) DEFAULT NULL,
  `max_use_user` int(11) DEFAULT NULL,
  `daily_use` int(11) DEFAULT NULL,
  `used` int(11) DEFAULT NULL,
  `min_transaction` double DEFAULT NULL,
  `max_transaction` double DEFAULT NULL,
  `category` varchar(25) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `termandcondition` text DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `is_public` int(11) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `icon` varchar(245) DEFAULT NULL,
  `is_new_user` int(11) DEFAULT NULL,
  `is_old_user` int(11) DEFAULT NULL,
  `is_expired` int(11) DEFAULT NULL,
  `location` text DEFAULT NULL,
  `available` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;