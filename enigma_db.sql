-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 11-05-2023 a las 15:29:24
-- Versión del servidor: 10.11.2-MariaDB-1:10.11.2+maria~ubu2204
-- Versión de PHP: 8.1.2-1ubuntu2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `enigma_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `access_errors`
--

CREATE TABLE `access_errors` (
  `int_logs` longtext NOT NULL,
  `int_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `access_errors`
--

INSERT INTO `access_errors` (`int_logs`, `int_date`) VALUES
('Invalid username: formUser', '2023-04-25 21:09:59'),
('Invalid username: formUser', '2023-04-25 21:10:05'),
('Invalid username: aa', '2023-04-25 21:12:14'),
('Invalid username: aa', '2023-04-25 21:12:52'),
('Invalid username: aa', '2023-04-25 21:13:19'),
('Invalid username: aa', '2023-04-25 21:14:04'),
('Invalid username: aa', '2023-04-25 21:14:20'),
('Invalid username: pepe\' or 1=1 -- \'', '2023-04-26 14:21:21'),
('Invalid username: pepe\' or 1=1-- \'', '2023-04-26 14:21:42'),
('Invalid username: administrator@enigma.who', '2023-05-03 16:47:39'),
('Invalid username: administrator@enigma.who', '2023-05-03 16:47:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `booking`
--

CREATE TABLE `booking` (
  `book_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `ft_surname` varchar(200) NOT NULL,
  `sn_suname` varchar(200) DEFAULT NULL,
  `email` varchar(200) NOT NULL,
  `phone` varchar(90) DEFAULT NULL,
  `group_name` varchar(150) NOT NULL,
  `group_size` int(11) NOT NULL,
  `book_date` datetime NOT NULL,
  `group_data` longtext DEFAULT NULL CHECK (json_valid(`group_data`)),
  `escape_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `booking`
--

INSERT INTO `booking` (`book_id`, `name`, `ft_surname`, `sn_suname`, `email`, `phone`, `group_name`, `group_size`, `book_date`, `group_data`, `escape_name`) VALUES
(1, 'David', 'Poza', 'Martin', 'david.poza@gracia.lasalle.cat', NULL, 'Markzu', 3, '2023-06-12 19:30:00', NULL, 'Ace of Spades'),
(4, 'Daniel', 'Polla', NULL, 'daniel@gmail.com', NULL, 'Markzu', 3, '2023-06-12 19:30:00', NULL, 'Dementia');

--
-- Disparadores `booking`
--
DELIMITER $$
CREATE TRIGGER `booking_before_insert` BEFORE INSERT ON `booking` FOR EACH ROW BEGIN

    SET @book_id = (
        SELECT group_name
        FROM groups
        WHERE group_name = NEW.group_name);
    IF @book_id IS NULL THEN
        INSERT INTO groups (group_name, group_size, group_data)
        VALUES (NEW.group_name, NEW.group_size, JSON_ARRAY(JSON_OBJECT('book_id', "temp", 'escape_room', NEW.escape_name, 'group_won', "temp")));
    ELSE
        UPDATE groups
        SET group_data = JSON_ARRAY_APPEND(group_data, '$', JSON_OBJECT('book_id', "temp", 'escape_room', NEW.escape_name, 'group_won', "temp"))
        WHERE group_name = NEW.group_name;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `booking_insert_after` AFTER INSERT ON `booking` FOR EACH ROW BEGIN

	SET @book_id = (
    SELECT book_id
    FROM booking
    WHERE book_id = NEW.book_id);
    
	SET @temp = (
    SELECT JSON_SEARCH(group_data, 'one', 'temp', NULL, '$**.book_id')
    FROM groups
    WHERE group_name = NEW.group_name
    AND JSON_SEARCH(group_data, 'one', 'temp', NULL, '$**.book_id') IS NOT NULL
    LIMIT 1
  );
  SET @index = (SELECT REGEXP_REPLACE(@temp, '[^0-9]', '') AS numbers_only);
   UPDATE groups
  SET group_data = JSON_REPLACE(
    group_data,
    CONCAT("$[",@index,"].book_id"),
    @book_id
  )
  WHERE JSON_SEARCH(group_data, 'one', 'temp', NULL, '$**.book_id') IS NOT NULL
  LIMIT 1;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `groups`
--

CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `group_size` int(11) NOT NULL,
  `group_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`group_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `groups`
--

INSERT INTO `groups` (`group_id`, `group_name`, `group_size`, `group_data`) VALUES
(1, 'Markzu', 3, '[{\"book_id\": 1, \"escape_room\": \"Ace of Spades\", \"group_won\": \"temp\"}, {\"book_id\": 4, \"escape_room\": \"Dementia\", \"group_won\": \"yes\"}]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `email` varchar(100) NOT NULL,
  `username` varchar(90) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_root` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`email`, `username`, `password_hash`, `is_root`) VALUES
('administrator@enigma.who', 'administrator', '*BCDB46F9759BC3C7C2679D4E81145B53EE616059', 1),
('appUser', 'appUser', '*CE6CFDCFEBC2B253B74FCF3396D138BEB4C89A46', 0),
('skeepers@enigma.who', 'skeeper1', '*BCDB46F9759BC3C7C2679D4E81145B53EE616059', 1),
('test_user@enigma.who', 'testUser', '*3D3B92F242033365AE5BC6A8E6FC3E1679F4140A', 0);

--
-- Disparadores `users`
--
DELIMITER $$
CREATE TRIGGER `update_handler` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.email IS NULL OR NEW.email = '' THEN
        SET NEW.email = OLD.email;
    END IF;

    IF NEW.username IS NULL OR NEW.username = '' THEN
        SET NEW.username = OLD.username;
    END IF;

    IF NEW.password_hash IS NULL OR NEW.password_hash = '' THEN
        SET NEW.password_hash = OLD.password_hash;
    END IF;
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `fk_booking_groups` (`group_name`);

--
-- Indices de la tabla `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`group_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `booking`
--
ALTER TABLE `booking`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
