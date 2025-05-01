-- Script SQL pour créer la table badge_validations
-- Cette table stocke les informations de validation des badges de hackathon

CREATE TABLE IF NOT EXISTS `badge_validations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `badge_id` varchar(50) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `hackathon_id` int(11) NOT NULL,
  `generated_date` datetime NOT NULL,
  `last_scanned` datetime DEFAULT NULL,
  `scan_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badge_id` (`badge_id`),
  KEY `participant_id` (`participant_id`),
  KEY `hackathon_id` (`hackathon_id`),
  CONSTRAINT `badge_validations_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `badge_validations_ibfk_2` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;