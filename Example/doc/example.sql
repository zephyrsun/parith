CREATE DATABASE `example` /*!40100 DEFAULT CHARACTER SET utf8mb4 */

CREATE TABLE `logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `get` text NOT NULL,
  `post` text NOT NULL,
  `data` text NOT NULL,
  `code` int(6) NOT NULL,
  `srv_ip` varchar(50) NOT NULL DEFAULT '',
  `client_ip` varchar(50) NOT NULL DEFAULT '',
  `time` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
