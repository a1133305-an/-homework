-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2026 年 06 月 25 日 08:14
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `medbox`
--

-- --------------------------------------------------------

--
-- 資料表結構 `history_log`
--

CREATE TABLE `history_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `taken_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('正常','補吃','非計畫內用藥') DEFAULT '正常'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `history_log`
--

INSERT INTO `history_log` (`log_id`, `user_id`, `drug_id`, `taken_time`, `status`) VALUES
(1, 8, 5, '2026-06-12 11:58:33', '正常'),
(2, 8, 5, '2026-06-16 02:39:41', '正常'),
(3, 8, 1, '2026-06-16 02:54:46', '正常'),
(4, 3, 3, '2026-06-16 02:57:36', '正常'),
(5, 8, 5, '2026-06-16 03:46:52', '正常'),
(6, 8, 1, '2026-06-16 03:49:40', '正常');

-- --------------------------------------------------------

--
-- 資料表結構 `medication_plan`
--

CREATE TABLE `medication_plan` (
  `plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `daily_dosage` varchar(50) DEFAULT NULL,
  `once_qty` int(11) DEFAULT 1,
  `remaining_qty` int(11) DEFAULT 0,
  `alert_threshold` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `medication_plan`
--

INSERT INTO `medication_plan` (`plan_id`, `user_id`, `drug_id`, `daily_dosage`, `once_qty`, `remaining_qty`, `alert_threshold`) VALUES
(1, 3, 1, '一天三餐', 1, 30, 10),
(2, 3, 3, '一天三餐', 1, 9, 5),
(3, 5, 3, '一天兩次，早晚服用', 1, 50, 10),
(4, 8, 5, '一天三次，三餐服用', 1, 67, 5),
(5, 8, 1, '一天一次睡前吃', 1, 4, 5),
(6, 10, 2, '一天兩次', 1, 6, 5),
(7, 10, 4, '一天一次', 1, 10, 5),
(8, 10, 5, '一天三餐', 1, 15, 5);

-- --------------------------------------------------------

--
-- 資料表結構 `medicine_db`
--

CREATE TABLE `medicine_db` (
  `drug_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `indications` text DEFAULT NULL,
  `warnings` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `medicine_db`
--

INSERT INTO `medicine_db` (`drug_id`, `name`, `indications`, `warnings`) VALUES
(1, '普拿疼 (Panadol)', '緩解頭痛、肌肉酸痛、發燒及經痛。', '請勿空腹服用。一日不可超過 4 顆，服藥後請多喝水。'),
(2, '博力舒 (Blopress)', '治療高血壓、心臟衰竭。', '請定時服用。若有頭暈症狀請放慢站起速度，孕婦禁用。'),
(3, '阿斯匹靈 (Aspirin)', '預防心肌梗塞、腦中風及血栓。', '請於飯後隨水吞服。若有異常出血或胃痛請立刻停藥。'),
(4, '循利寧 (Cerenin)', '改善末梢血液循環障礙、手腳冰冷。', '建議飯後服用。手術前請告知醫師正在服用此藥。'),
(5, '維他命B群 (Vitamin B Complex)', '消除疲勞、維持神經系統健康與代謝。', '建議用溫開水在早餐或午餐後服用，尿液變黃屬於正常現象。');

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('patient','guardian') NOT NULL,
  `patient_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `patient_id`) VALUES
(3, 'Andy', '$2y$10$lspPyHhpNdcyNEyKJqUR4e8MZEvms1NBd7dnXfuh8nRXRSpVj.u.6', 'patient', NULL),
(4, 'Raven', '$2y$10$MQ34IoWl18GuGUoJVLFaTecJSjvco7bMqynrOaG65sVfuRfOSXiQy', 'guardian', 3),
(5, 'WeiMom', '$2y$10$NPQstFncSTspUK85Cor5ZuhQERSxMUQ6jI/ycuMcU0rg56YogjGfe', 'patient', NULL),
(6, 'WeiWei', '$2y$10$hoWLs4stWhy58YWiffu5g.SnYr/.qR1kVgJo6Hy6rvUMjpFqKZS7.', 'guardian', 5),
(8, 'Amy', '$2y$10$91aJLjk2yGNeT59gFu4NMuUWHQMZRoHBgZpFrftD3JA5TIPCtMbMC', 'patient', NULL),
(9, 'AmyDad', '$2y$10$FIlnGhlftuvmgvk5xOBo9.FkoBG7PNYHrwXZYIq0azrBI71i9SLxK', 'guardian', 8),
(10, 'Granny', '$2y$10$YTzF6RbHYiyVA1PZXwMdwOvIXcURlEL75mXlPaMNuHVv5xEynbqVq', 'patient', NULL),
(11, 'Father', '$2y$10$6kbdZeLiE39/KJGoyKqIQOpZQKFsFWefNhUseR3.OYfngShp4q7Bm', 'guardian', 10);

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `history_log`
--
ALTER TABLE `history_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `drug_id` (`drug_id`);

--
-- 資料表索引 `medication_plan`
--
ALTER TABLE `medication_plan`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `drug_id` (`drug_id`);

--
-- 資料表索引 `medicine_db`
--
ALTER TABLE `medicine_db`
  ADD PRIMARY KEY (`drug_id`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_guardian_patient` (`patient_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `history_log`
--
ALTER TABLE `history_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `medication_plan`
--
ALTER TABLE `medication_plan`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `medicine_db`
--
ALTER TABLE `medicine_db`
  MODIFY `drug_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `history_log`
--
ALTER TABLE `history_log`
  ADD CONSTRAINT `history_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `history_log_ibfk_2` FOREIGN KEY (`drug_id`) REFERENCES `medicine_db` (`drug_id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `medication_plan`
--
ALTER TABLE `medication_plan`
  ADD CONSTRAINT `medication_plan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medication_plan_ibfk_2` FOREIGN KEY (`drug_id`) REFERENCES `medicine_db` (`drug_id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_guardian_patient` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
