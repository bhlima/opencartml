CREATE TABLE `NewTable` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`access_token`  varchar(500) NOT NULL ,
`refresh_token`  varchar(500) NOT NULL ,
`user_id`  int(11) NOT NULL ,
`scope`  varchar(50) NOT NULL ,
`expires_in`  varchar(30) NULL ,
`token_type`  varchar(10) NULL ,
PRIMARY KEY (`id`)
)
;
