CREATE TABLE `email_senders` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`handler` varchar(100) NOT NULL,
	`status` tinyint(4) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `email_senders_addresses` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`sender` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`address` varchar(255) NOT NULL,
	`status` tinyint(4) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `sender` (`sender`),
	CONSTRAINT `email_senders_addresses_ibfk_1` FOREIGN KEY (`sender`) REFERENCES `email_senders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `email_senders_params` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`sender` int(11) NOT NULL,
	`name` varchar(100) NOT NULL,
	`value` text NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `sender_2` (`sender`,`name`),
	KEY `sender` (`sender`),
	CONSTRAINT `email_senders_params_ibfk_1` FOREIGN KEY (`sender`) REFERENCES `email_senders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `email_receivers` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`type` tinyint(4) NOT NULL,
	`hostname` varchar(255) NOT NULL,
	`port` smallint(5) unsigned NOT NULL,
	`username` varchar(255) NOT NULL,
	`password` varchar(255) NOT NULL,
	`authentication` tinyint(4) NULL,
	`encryption` tinyint(4) NOT NULL,
	`status` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `email_templates` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) COLLATE utf8_persian_ci NOT NULL,
	`lang` varchar(2) COLLATE utf8_persian_ci NOT NULL,
	`event` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
	`variables` text COLLATE utf8_persian_ci,
	`render` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
	`subject` varchar(255) COLLATE utf8_persian_ci NOT NULL,
	`text` text COLLATE utf8_persian_ci NOT NULL,
	`html` text COLLATE utf8_persian_ci,
	`status` tinyint(4) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

CREATE TABLE `email_sent` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`send_at` int(11) NOT NULL,
	`sender_address` int(11) NOT NULL,
	`sender_user` int(11) DEFAULT NULL,
	`receiver_name` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
	`receiver_address` varchar(100) COLLATE utf8_persian_ci NOT NULL,
	`receiver_user` int(11) DEFAULT NULL,
	`subject` varchar(255) COLLATE utf8_persian_ci NOT NULL,
	`text` text COLLATE utf8_persian_ci NOT NULL,
	`html` text COLLATE utf8_persian_ci,
	`status` tinyint(4) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `sender_user` (`sender_user`),
	KEY `sender_number` (`sender_address`),
	KEY `receiver_user` (`receiver_user`) USING BTREE,
	CONSTRAINT `email_sent_ibfk_1` FOREIGN KEY (`sender_address`) REFERENCES `email_senders_addresses` (`id`) ON DELETE CASCADE,
	CONSTRAINT `email_sent_ibfk_2` FOREIGN KEY (`sender_user`) REFERENCES `userpanel_users` (`id`) ON DELETE SET NULL,
	CONSTRAINT `email_sent_ibfk_3` FOREIGN KEY (`receiver_user`) REFERENCES `userpanel_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

CREATE TABLE `email_sent_attachments` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`mail` int(11) NOT NULL,
	`size` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`file` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `mail` (`mail`),
	CONSTRAINT `email_sent_attachments_ibfk_1` FOREIGN KEY (`mail`) REFERENCES `email_sent` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `email_get` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`serverid` varchar(100) COLLATE utf8_persian_ci DEFAULT NULL,
	`receive_at` int(11) NOT NULL,
	`sender_name` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
	`sender_address` varchar(255) COLLATE utf8_persian_ci NOT NULL,
	`sender_user` int(11) DEFAULT NULL,
	`receiver` int(11) NOT NULL,
	`receiver_name` varchar(255) COLLATE utf8_persian_ci DEFAULT NULL,
	`receiver_address` varchar(255) COLLATE utf8_persian_ci NOT NULL,
	`subject` varchar(255) COLLATE utf8_persian_ci NOT NULL,
	`text` text COLLATE utf8_persian_ci,
	`html` text COLLATE utf8_persian_ci,
	`status` tinyint(4) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `sender_user` (`sender_user`),
	KEY `reciver_number` (`receiver`),
	CONSTRAINT `email_get_ibfk_1` FOREIGN KEY (`receiver`) REFERENCES `email_receivers` (`id`) ON DELETE CASCADE,
	CONSTRAINT `email_get_ibfk_2` FOREIGN KEY (`sender_user`) REFERENCES `userpanel_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

CREATE TABLE `email_get_attachments` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`mail` int(11) NOT NULL,
	`size` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`file` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `mail` (`mail`),
	CONSTRAINT `email_get_attachments_ibfk_1` FOREIGN KEY (`mail`) REFERENCES `email_get` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `userpanel_usertypes_permissions` (`type`, `name`) VALUES
(1, 'email_get_list'),
(1, 'email_get_list_anonymous'),
(1, 'email_send'),
(1, 'email_sent_list'),
(1, 'email_sent_list_anonymous'),
(1, 'email_settings_receivers_add'),
(1, 'email_settings_receivers_delete'),
(1, 'email_settings_receivers_edit'),
(1, 'email_settings_receivers_list'),
(1, 'email_settings_senders_add'),
(1, 'email_settings_senders_delete'),
(1, 'email_settings_senders_edit'),
(1, 'email_settings_senders_list'),
(1, 'email_settings_templates_add'),
(1, 'email_settings_templates_delete'),
(1, 'email_settings_templates_edit'),
(1, 'email_settings_templates_list'),
(1, 'email_sent_view'),
(1, 'email_get_view'),
(2, 'email_sent_view'),
(2, 'email_get_view'),
(2, 'email_get_list'),
(2, 'email_sent_list');
