CREATE TABLE IF NOT EXISTS `__PREFIX__payjs_orders`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL DEFAULT 'weixin' COMMENT '支付方式',
  `out_trade_no` varchar(50) NOT NULL COMMENT '商户订单号',
  `subject` varchar(100) NOT NULL COMMENT '订单标题',
  `outer_tid` varchar(30) DEFAULT NULL COMMENT '外部交易订单号',
  `transaction_tid` varchar(30) DEFAULT NULL COMMENT '支付流水号',
  `total_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0：已付款 1：等待付款',
  `pay_at` timestamp NULL DEFAULT NULL COMMENT '支付时间',
  `buyer_info` varchar(255) DEFAULT NULL COMMENT '支付者信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='订单表';