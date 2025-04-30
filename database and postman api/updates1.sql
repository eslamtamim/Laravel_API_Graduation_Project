
-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `craftsman_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`id`, `client_id`, `craftsman_id`, `created_at`, `updated_at`) VALUES
(1, 6, 5, '2025-04-29 10:33:04', '2025-04-29 10:33:04');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chat_id` bigint(20) UNSIGNED NOT NULL,
  `sender` enum('craftsman','client') NOT NULL,
  `msg` varchar(255) NOT NULL,
  `type` enum('text','image') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `chat_id`, `sender`, `msg`, `type`, `created_at`, `updated_at`) VALUES
(14, 1, 'client', 'hi', 'text', '2025-04-29 11:21:37', '2025-04-29 11:21:37'),
(15, 1, 'craftsman', 'hello', 'text', '2025-04-29 11:21:51', '2025-04-29 11:21:51'),
(16, 1, 'client', 'chat_images/xp20NOTbbCIBsYck.png', 'image', '2025-04-29 11:22:20', '2025-04-29 11:22:20');







ALTER TABLE clients
ADD COLUMN `mobile_token` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

ALTER TABLE craftsmen
ADD COLUMN `personal_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
ADD COLUMN `personal_id_back` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
ADD COLUMN `criminal_record` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
ADD COLUMN `mobile_token` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL;




--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id_chat` (`client_id`),
  ADD KEY `craftsman_id_chat` (`craftsman_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`);



--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;



--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `client_id_chat` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE NO ACTION,
  ADD CONSTRAINT `craftsman_id_chat` FOREIGN KEY (`craftsman_id`) REFERENCES `craftsmen` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_id` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
