-- INSERT INTO `orders` (`id`, `invoice`, `customer_id`, `subscribe_id`, `name`, `phone`, `address`, `location`, `kelurahan`, `kecamatan`, `kota`, `provinsi`, `kode_pos`, `latitude`, `longitude`, `payment_method`, `payment_link`, `payment_date`, `payment_total`, `coupon_id`, `payment_discount_code`, `payment_discount`, `payment_code`, `order_weight`, `order_distance`, `delivery_status`, `delivery_fee`, `delivery_track`, `delivery_time`, `delivery_date`, `order_time`, `confirmation_time`, `notes`, `status`, `created_at`, `updated_at`, `deleted_at`, `payment_final`, `photo`, `courier`, `delivery_service`) VALUES (NULL, '1111', '14004', NULL, 'User', '+6281111111111', 'telaga murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-06 00:00:00', '150000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-06 00:00:00', NULL, NULL, '4', NULL, NULL, NULL, '150000', NULL, NULL, NULL), (NULL, '2222', '14004', NULL, 'User', '+6281111111111', 'Telaga Murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-05 00:00:00', '180000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-05 00:00:00', NULL, NULL, '4', '2021-07-05 00:00:00', '2021-07-05 00:00:00', NULL, '180000', NULL, NULL, NULL),
-- (NULL, '3333', '14004', NULL, 'User', '+6281111111111', 'Telaga Murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-05 00:00:00', '220000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-04 00:00:00', NULL, NULL, '4', '2021-07-04 00:00:00', '2021-07-06 00:00:00', NULL, '220000', NULL, NULL, NULL), (NULL, '4444', '14004', NULL, 'User', '+6281111111111', 'Telaga Murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '120000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-05 00:00:00', NULL, NULL, '2', '2021-07-05 00:00:00', '2021-07-06 00:00:00', NULL, '120000', NULL, NULL, NULL),
-- (NULL, '5555', '14004', NULL, 'User', '+6281111111111', 'Telaga Murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '390000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-06 00:00:00', NULL, NULL, '1', '2021-07-06 00:00:00', '2021-07-06 00:00:00', NULL, '390000', NULL, NULL, NULL), (NULL, '6666', '14004', NULL, 'User', '+6281111111111', 'Telaga Murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '160000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-05 00:00:00', NULL, NULL, '3', '2021-07-05 00:00:00', '2021-07-06 00:00:00', NULL, '160000', NULL, NULL, NULL),
-- (NULL, '7777', '14004', NULL, 'User', '+6281111111111', 'Telaga Murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-05 00:00:00', '52000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-03 00:00:00', NULL, NULL, '4', '2021-07-05 00:00:00', '2021-07-06 00:00:00', NULL, '52000', NULL, NULL, NULL), (NULL, '8888', '14004', NULL, 'User', '+6281111111111', 'Telaga Murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-05 00:00:00', '77000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-03 00:00:00', NULL, NULL, '4', '2021-07-05 00:00:00', '2021-07-06 00:00:00', NULL, '77000', NULL, NULL, NULL),
-- (NULL, '9999', '14004', NULL, 'User', '+6281111111111', 'Telaga Murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '118000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-06 00:00:00', NULL, NULL, '1', '2021-07-05 00:00:00', '2021-07-06 00:00:00', NULL, '118000', NULL, NULL, NULL), (NULL, '1010', '14004', NULL, 'User', '+6281111111111', 'Telaga Murni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '22000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-07-06 00:00:00', NULL, NULL, '1', '2021-07-05 00:00:00', '2021-07-06 00:00:00', NULL, '22000', NULL, NULL, NULL);


--  INSERT INTO `order_detail` (`id`, `product_id`, `order_id`, `price`, `qty`, `total_price`, `created_at`, `updated_at`, `deleted_at`, `location_id`, `description`, `status`) VALUES (NULL, '10001', '1', '150000', '1', '150000', NULL, NULL, NULL, NULL, NULL, NULL), (NULL, '10002', '2', '180000', '6', '180000', '2021-07-05 00:00:00', '2021-07-05 00:00:00', NULL, NULL, NULL, NULL), (NULL, '10003', '3', '220000', '3', '220000', '2021-07-04 00:00:00', '2021-07-05 00:00:00', NULL, NULL, NULL, NULL),
--  (NULL, '10004', '4', '120000', '7', '120000', '2021-07-05 00:00:00', '2021-07-06 00:00:00', NULL, NULL, NULL, NULL), (NULL, '10005', '5', '390000', '21', '390000', '2021-07-06 00:00:00', '2021-07-06 00:00:00', NULL, NULL, NULL, NULL), (NULL, '10006', '6', '160000', '21', '390000', '2021-07-05 00:00:00', '2021-07-06 00:00:00', NULL, NULL, NULL, NULL), (NULL, '10007', '7', '52000', '2', '52000', '2021-07-03 00:00:00', '2021-07-05 00:00:00', NULL, NULL, NULL, NULL),
--  (NULL, '10008', '8', '52000', '3', '77000', '2021-07-03 00:00:00', '2021-07-05 00:00:00', NULL, NULL, NULL, NULL), (NULL, '10009', '9', '118000', '3', '118000', '2021-07-06 00:00:00', '2021-07-06 00:00:00', NULL, NULL, NULL, NULL), (NULL, '10010', '10', '22000', '2', '22000', '2021-07-06 00:00:00', '2021-07-06 00:00:00', NULL, NULL, NULL, NULL);