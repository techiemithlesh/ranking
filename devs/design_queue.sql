-- Background queue for "Download Design" (Template_manager/download_design).
-- One job = one super-admin request; one item = one branch × one template.

CREATE TABLE IF NOT EXISTS `design_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_by` int(11) NOT NULL,
  `status` enum('pending','processing','packaging','done','failed','expired') NOT NULL DEFAULT 'pending',
  `cancel_requested` tinyint(1) NOT NULL DEFAULT 0,
  `branch_count` int(11) NOT NULL DEFAULT 0,
  `template_count` int(11) NOT NULL DEFAULT 0,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `zip_path` varchar(255) DEFAULT NULL,
  `zip_size` bigint(20) DEFAULT NULL,
  `zip_count` int(11) DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`, `id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `design_job_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `type` enum('image','video') NOT NULL,
  `status` enum('pending','processing','done','failed','cancelled') NOT NULL DEFAULT 'pending',
  `attempts` tinyint(4) NOT NULL DEFAULT 0,
  `worker_id` varchar(32) DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_job_status` (`job_id`, `status`),
  KEY `idx_claim` (`status`, `id`),
  KEY `idx_worker` (`worker_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
