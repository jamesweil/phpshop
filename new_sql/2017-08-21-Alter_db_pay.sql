-- 删除多余字段
alter table `db_pay` drop column seller_id;

-- 增加私钥、密钥字段
ALTER TABLE db_pay ADD COLUMN private_pem TEXT DEFAULT NULL COMMENT '私钥' AFTER type;

ALTER TABLE db_pay ADD COLUMN public_pem TEXT DEFAULT NULL COMMENT '公钥' AFTER private_pem;