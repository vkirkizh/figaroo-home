SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `devices` (
  `id` int(10) UNSIGNED NOT NULL,
  `uniq` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `title` varchar(128) NOT NULL,
  `type` varchar(128) NOT NULL,
  `addr` varchar(15) NOT NULL,
  `status` enum('on','off') NOT NULL DEFAULT 'on',
  `data` text NOT NULL,
  `updated1` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated2` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `devices` (`id`, `uniq`, `name`, `title`, `type`, `addr`, `status`, `data`, `updated1`, `updated2`) VALUES
(1, '00000001', 'lustra', 'Лампа', 'switch1', '192.168.1.2', 'on', '[]', '2020-07-05 15:26:30', '2020-07-05 15:26:30'),
(2, '00000002', 'klimat', 'Кондиционер', 'hvac1', '192.168.1.3', 'on', '{\"temp\":23,\"curtemp\":\"23.0\"}', '2020-07-05 15:26:30', '2020-07-05 15:26:30'),
(3, '00000003', 'vnutri', 'Микроклимат', 'meteo1', '192.168.1.4', 'on', '{\"temp\":\"23.0\",\"humd\":\"50\",\"co2\":\"400\",\"press\":\"760\"}', '2020-07-05 15:26:30', '2020-07-05 15:26:30');

ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `devices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
